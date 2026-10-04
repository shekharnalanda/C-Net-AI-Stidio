<?php

namespace Tests\Feature;

use App\Models\AIJob;
use App\Models\StudioOutput;
use App\Models\User;
use App\Services\AI\HuggingFaceClient;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class HuggingFaceGenerationTest extends TestCase
{
    use LazilyRefreshDatabase {
        migrateDatabases as migrateCurrentSchema;
    }

    /**
     * Legacy cPanel SQLite lacks table-valued PRAGMA introspection used by
     * historical migrations. Exercise generation against the same table
     * columns without changing production MySQL or framework internals.
     */
    protected function migrateDatabases(): void
    {
        if (DB::getDriverName() === 'sqlite' && version_compare(DB::selectOne('select sqlite_version() as version')->version, '3.16.0', '<')) {
            DB::unprepared(file_get_contents(base_path('tests/Fixtures/hf-sqlite-schema.sql')));

            return;
        }
        $this->migrateCurrentSchema();
    }

    private function voiceInput(): array
    {
        return ['kind' => 'voiceover', 'name' => 'Hindi sample', 'text' => 'नमस्कार।',
            'voice' => 'swara', 'rate' => 0, 'provider_consent' => '1'];
    }

    private function fakeVoice(string $event = 'complete', ?string $url = null): void
    {
        Http::preventStrayRequests();
        $host = HuggingFaceClient::TTS_HOST;
        $result = $event === 'error' ? 'null' : json_encode([['url' => $url ?? $host.'/gradio_api/file=/tmp/voice.mp3']]);
        Http::fake([
            $host.'/gradio_api/call/tts_interface' => Http::response(['event_id' => 'voice-event']),
            $host.'/gradio_api/call/tts_interface/voice-event' => Http::response("event: {$event}\ndata: {$result}\n\n"),
            $host.'/gradio_api/file=/tmp/voice.mp3' => Http::response('ID3'.str_repeat('a', 64)),
        ]);
    }

    public function test_guests_cannot_submit_creations(): void
    {
        $this->post('/studio/create', $this->voiceInput())->assertRedirect('/login');
    }

    public function test_voice_is_saved_and_processed_into_a_private_playable_export(): void
    {
        Storage::fake('local');
        $this->fakeVoice();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/studio/create', $this->voiceInput());

        $response->assertAccepted();
        $job = AIJob::findOrFail($response->json('id'));
        $this->assertSame('pending', $job->status);
        $this->artisan('studio:process-hf')->assertSuccessful();
        $this->assertSame('completed', $job->fresh()->status);
        $output = StudioOutput::where('ai_job_id', $job->id)->sole();
        $this->assertSame('audio', $output->output_type);
        Storage::disk('local')->assertExists('hf-outputs/'.$job->job_uuid.'.mp3');
        $this->getJson($response->json('status_url'))->assertJsonPath('status', 'completed')
            ->assertJsonPath('type', 'audio')->assertJsonMissing(['output_path']);
        $this->get(route('studio.create.media', $output))->assertOk()->assertHeader('Content-Type', 'audio/mpeg');
        $this->get(route('outputs.download', $output))->assertDownload('Hindi-sample-Output.mp3');
        Http::assertSent(fn ($request) => $request->url() === HuggingFaceClient::TTS_HOST.'/gradio_api/call/tts_interface'
            && $request['data'][1] === 'hi-IN-SwaraNeural - hi-IN (Female)');
    }

    public function test_image_is_uploaded_and_its_input_removed_after_video_finishes(): void
    {
        Storage::fake('local');
        Http::preventStrayRequests();
        $host = HuggingFaceClient::VIDEO_HOST;
        Http::fake([
            $host.'/gradio_api/upload' => Http::response(['/tmp/image.png']),
            $host.'/gradio_api/call/generate_video' => Http::response(['event_id' => 'video-event']),
            $host.'/gradio_api/call/generate_video/video-event' => Http::response('event: complete'."\n".'data: '.json_encode([['video' => ['url' => $host.'/gradio_api/file=/tmp/result.mp4']], 42])."\n\n"),
            $host.'/gradio_api/file=/tmp/result.mp4' => Http::response("\x00\x00\x00\x20ftypisom".str_repeat('a', 64)),
        ]);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/studio/create', [
            'kind' => 'image-to-video', 'name' => 'Landscape', 'image' => UploadedFile::fake()->image('landscape.png'),
            'prompt' => 'Gentle camera movement', 'duration' => 2, 'provider_consent' => '1',
        ]);

        $response->assertAccepted();
        $job = AIJob::findOrFail($response->json('id'));
        Storage::disk('local')->assertExists($job->payload['image_path']);
        $this->artisan('studio:process-hf')->assertSuccessful();
        $this->assertSame('completed', $job->fresh()->status);
        Storage::disk('local')->assertMissing($job->payload['image_path']);
        $this->getJson($response->json('status_url'))->assertJsonPath('type', 'video');
        Http::assertSent(fn ($request) => $request->url() === $host.'/gradio_api/call/generate_video'
            && $request['data'][4] == 2 && $request['data'][0]['path'] === '/tmp/image.png');
    }

    public function test_provider_failure_leaves_a_clear_failed_job_without_an_export(): void
    {
        $this->fakeVoice('error');
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson('/studio/create', $this->voiceInput());

        $this->artisan('studio:process-hf')->assertSuccessful();

        $this->getJson($response->json('status_url'))->assertJsonPath('status', 'failed')
            ->assertJsonPath('preview_url', null)->assertJsonPath('error', 'The service could not complete your creation. It may be busy or unavailable. Please try again later.');
        $this->assertDatabaseCount('studio_outputs', 0);
        Http::assertSentCount(2);
    }

    public function test_provider_cannot_redirect_a_download_to_another_host(): void
    {
        $this->fakeVoice('complete', 'https://example.com/private-file');
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson('/studio/create', $this->voiceInput());

        $this->artisan('studio:process-hf')->assertSuccessful();

        $this->assertSame('failed', AIJob::findOrFail($response->json('id'))->status);
        $this->assertDatabaseCount('studio_outputs', 0);
        Http::assertSentCount(2);
    }

    public function test_a_second_active_creation_is_rejected_without_an_extra_usage_record(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->postJson('/studio/create', $this->voiceInput())->assertAccepted();

        $this->postJson('/studio/create', $this->voiceInput())->assertUnprocessable()
            ->assertJsonValidationErrors('generation');

        $this->assertDatabaseCount('ai_jobs', 1);
        $this->assertDatabaseCount('studio_usage_events', 1);
    }

    public function test_other_users_cannot_read_status_preview_or_download(): void
    {
        Storage::fake('local');
        $this->fakeVoice();
        $owner = User::factory()->create();
        $response = $this->actingAs($owner)->postJson('/studio/create', $this->voiceInput());
        $this->artisan('studio:process-hf')->assertSuccessful();
        $output = StudioOutput::sole();
        $other = User::factory()->create();

        $this->actingAs($other)->getJson($response->json('status_url'))->assertNotFound();
        $this->get(route('studio.create.media', $output))->assertNotFound();
        $this->get(route('outputs.download', $output))->assertForbidden();

        Http::assertSentCount(3);
    }

    #[TestWith(['voice', 'unknown'])]
    #[TestWith(['text', ''])]
    #[TestWith(['provider_consent', '0'])]
    #[TestWith(['rate', 31])]
    public function test_invalid_voice_requests_do_not_create_records(string $field, mixed $value): void
    {
        $user = User::factory()->create();
        $input = $this->voiceInput();
        $input[$field] = $value;

        $this->actingAs($user)->postJson('/studio/create', $input)->assertUnprocessable()->assertJsonValidationErrors($field);

        $this->assertDatabaseCount('ai_jobs', 0);
        $this->assertDatabaseCount('studio_usage_events', 0);
    }

    public function test_long_videos_and_non_images_are_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/studio/create', ['kind' => 'image-to-video', 'name' => 'Invalid',
            'prompt' => 'Motion', 'duration' => 6, 'provider_consent' => '1',
            'image' => UploadedFile::fake()->create('script.php', 2, 'text/plain')])->assertUnprocessable()
            ->assertJsonValidationErrors(['image', 'duration']);

        $this->assertDatabaseCount('ai_jobs', 0);
    }

    public function test_creation_names_are_escaped_in_history(): void
    {
        $user = User::factory()->create();
        $input = $this->voiceInput();
        $input['name'] = '<script>alert(1)</script>';
        $this->actingAs($user)->postJson('/studio/create', $input)->assertAccepted();

        $this->get('/studio/create')->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }
}
