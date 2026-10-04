<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Models\AIJob;
use App\Models\StudioOutput;
use App\Services\AI\HuggingFaceGenerationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class HuggingFaceController extends Controller
{
    public function index(Request $request): View
    {
        return view('studio.create', ['jobs' => AIJob::with('project')->where('user_id', $request->user()->id)
            ->where('engine', 'hf-gradio')->latest()->limit(12)->get()]);
    }

    public function store(Request $request, HuggingFaceGenerationService $service): JsonResponse
    {
        $data = $request->validate([
            'kind' => ['required', 'in:voiceover,image-to-video'],
            'name' => ['required', 'string', 'max:150'],
            'text' => ['required_if:kind,voiceover', 'nullable', 'string', 'max:5000'],
            'voice' => ['required_if:kind,voiceover', 'nullable', 'in:swara,madhur'],
            'rate' => ['nullable', 'integer', 'min:-30', 'max:30'],
            'image' => ['required_if:kind,image-to-video', 'nullable', 'image', 'mimes:jpeg,png,webp', 'max:6144', 'dimensions:max_width=4096,max_height=4096'],
            'prompt' => ['required_if:kind,image-to-video', 'nullable', 'string', 'max:1500'],
            'duration' => ['required_if:kind,image-to-video', 'nullable', 'integer', 'min:1', 'max:5'],
            'provider_consent' => ['accepted'],
        ]);
        $payload = ['kind' => $data['kind'], 'name' => $data['name']];
        if ($data['kind'] === 'voiceover') {
            $payload += ['text' => $data['text'], 'voice' => $data['voice'], 'rate' => $data['rate'] ?? 0];
        } else {
            Storage::disk('local')->makeDirectory('hf-inputs');
            Storage::disk('local')->put('hf-inputs/.htaccess', "Require all denied\n");
            $path = $request->file('image')->store('hf-inputs', 'local');
            abort_unless($path, 500, 'Could not save your image.');
            $payload += ['image_path' => $path, 'prompt' => $data['prompt'], 'duration' => (int) $data['duration']];
        }
        try {
            $job = $service->create($request->user(), $payload);
        } catch (Throwable $exception) {
            if (isset($payload['image_path'])) {
                Storage::disk('local')->delete($payload['image_path']);
            }
            throw $exception;
        }

        return response()->json(['id' => $job->id, 'status_url' => route('studio.create.status', $job)], 202);
    }

    public function status(Request $request, AIJob $job): JsonResponse
    {
        abort_unless((int) $job->user_id === (int) $request->user()->id && $job->engine === 'hf-gradio', 404);
        $output = $job->outputs()->first();

        return response()->json([
            'id' => $job->id, 'status' => $job->status, 'progress' => $job->progress,
            'error' => $job->error_message, 'name' => $job->project?->name,
            'type' => $output?->output_type,
            'preview_url' => $output ? route('studio.create.media', $output) : null,
            'download_url' => $output ? route('outputs.download', $output) : null,
        ])->header('Cache-Control', 'no-store, private');
    }

    public function media(Request $request, StudioOutput $output): BinaryFileResponse
    {
        abort_unless((int) $output->user_id === (int) $request->user()->id
            && ($output->metadata['result']['engine'] ?? null) === 'hf-gradio', 404);
        $path = realpath($output->path ?? '');
        $root = realpath(Storage::disk('local')->path('hf-outputs'));
        abort_unless($path && $root && str_starts_with($path, $root.DIRECTORY_SEPARATOR), 404);

        return response()->file($path, ['Content-Type' => $output->format === 'mp3' ? 'audio/mpeg' : 'video/mp4',
            'Cache-Control' => 'no-store, private']);
    }
}
