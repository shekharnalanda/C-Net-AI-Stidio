<?php

namespace App\Services\AI;

use App\Models\AIJob;
use App\Models\StudioProject;
use App\Models\User;
use App\Services\Studio\OutputService;
use App\Services\Studio\UsageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class HuggingFaceGenerationService
{
    public function __construct(protected HuggingFaceClient $client, protected UsageService $usage, protected OutputService $outputs) {}

    public function create(User $user, array $payload): AIJob
    {
        return DB::transaction(function () use ($user, $payload) {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if (AIJob::where('user_id', $user->id)->where('engine', 'hf-gradio')
                ->whereIn('status', ['pending', 'processing'])->exists()) {
                throw ValidationException::withMessages(['generation' => 'Your previous creation is still running. Please wait for its result.']);
            }
            $project = StudioProject::create([
                'user_id' => $user->id, 'name' => $payload['name'], 'type' => $payload['kind'],
                'status' => 'queued', 'language' => 'hi', 'settings' => ['provider' => 'hf-gradio'],
            ]);
            $this->usage->assertAndRecord($user, 'ai_jobs', 1, ['project_id' => $project->id]);

            return AIJob::create([
                'job_uuid' => (string) Str::uuid(), 'user_id' => $user->id, 'project_id' => $project->id,
                'job_type' => $payload['kind'], 'status' => 'pending', 'engine' => 'hf-gradio',
                'queue_name' => 'huggingface', 'available_at' => now(), 'payload' => $payload,
            ]);
        });
    }

    public function process(AIJob $job): void
    {
        if ($job->engine !== 'hf-gradio' || AIJob::whereKey($job->id)->where('status', 'pending')
            ->update(['status' => 'processing', 'started_at' => now(), 'progress' => 15, 'attempts' => DB::raw('attempts + 1')]) !== 1) {
            return;
        }
        $job->refresh();
        try {
            $result = $this->client->generate($job->payload, $job->job_uuid);
            DB::transaction(function () use ($job, $result) {
                $job->update(['result' => $result, 'status' => 'completed', 'progress' => 100, 'completed_at' => now(), 'error_message' => null]);
                $this->outputs->syncFromJob($job);
                $job->project->update(['status' => 'completed']);
            });
        } catch (Throwable $exception) {
            $job->update(['status' => 'failed', 'progress' => 0, 'completed_at' => now(),
                'error_message' => 'The service could not complete your creation. It may be busy or unavailable. Please try again later.']);
            $job->project->update(['status' => 'failed']);
            logger()->warning('HF generation failed', ['job_id' => $job->id, 'exception_type' => get_class($exception)]);
        } finally {
            if (isset($job->payload['image_path'])) {
                Storage::disk('local')->delete($job->payload['image_path']);
            }
        }
    }
}
