<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Models\AIJob;
use App\Models\StudioOutput;
use App\Models\StudioProject;
use App\Services\Studio\OutputService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class OutputController extends Controller
{
    protected function authorizeOutput(Request $request, StudioOutput $output): void
    {
        abort_unless(
            (int) $output->user_id === (int) $request->user()->id
            || $request->user()->isAdmin(),
            403
        );
    }

    protected function authorizeProject(Request $request, StudioProject $project): void
    {
        abort_unless(
            (int) $project->user_id === (int) $request->user()->id
            || $request->user()->isAdmin(),
            403
        );
    }

    public function index(Request $request)
    {
        $query = StudioOutput::with(['project', 'job'])
            ->where('user_id', $request->user()->id)
            ->latest('generated_at');

        return view('studio.outputs', [
            'outputs' => $query->paginate(24),
            'readyCount' => StudioOutput::where('user_id', $request->user()->id)
                ->where('status', 'ready')
                ->count(),
            'videoCount' => StudioOutput::where('user_id', $request->user()->id)
                ->where('output_type', 'video')
                ->count(),
            'imageCount' => StudioOutput::where('user_id', $request->user()->id)
                ->where('output_type', 'image')
                ->count(),
            'audioCount' => StudioOutput::where('user_id', $request->user()->id)
                ->where('output_type', 'audio')
                ->count(),
        ]);
    }

    public function project(Request $request, StudioProject $project)
    {
        $this->authorizeProject($request, $project);

        return view('studio.project-outputs', [
            'project' => $project,
            'outputs' => $project->outputs()
                ->latest('generated_at')
                ->paginate(20),
        ]);
    }

    public function download(Request $request, StudioOutput $output)
    {
        $this->authorizeOutput($request, $output);

        $path = $output->path ?: $output->source_path;

        abort_unless($path, 404);

        $candidates = [
            $path,
            base_path($path),
            storage_path('app/'.$path),
            public_path($path),
        ];

        $real = collect($candidates)
            ->first(fn ($candidate) =>
                $candidate && File::exists($candidate)
            );

        abort_unless($real, 404, 'Generated file is not available on this server.');

        $output->update([
            'downloaded_at' => now(),
        ]);

        $name = preg_replace('/[^A-Za-z0-9._-]+/', '-', $output->name);

        if ($output->format) {
            $name .= '.'.$output->format;
        }

        return response()->download($real, $name);
    }

    public function retry(Request $request, AIJob $job)
    {
        abort_unless(
            (int) $job->user_id === (int) $request->user()->id
            || $request->user()->isAdmin(),
            403
        );

        abort_unless(
            in_array($job->status, ['failed', 'cancelled'], true),
            422,
            'Only failed or cancelled jobs can be retried.'
        );

        $newJob = $job->replicate([
            'job_uuid',
            'status',
            'progress',
            'error_message',
            'started_at',
            'completed_at',
        ]);

        $newJob->job_uuid = (string) \Illuminate\Support\Str::uuid();
        $newJob->status = 'queued';
        $newJob->progress = 0;
        $newJob->error_message = null;
        $newJob->started_at = null;
        $newJob->completed_at = null;
        $newJob->available_at = now();
        $newJob->save();

        return back()->with(
            'success',
            'Generation job queued again successfully.'
        );
    }

    public function sync(Request $request, AIJob $job, OutputService $outputs)
    {
        abort_unless(
            (int) $job->user_id === (int) $request->user()->id
            || $request->user()->isAdmin(),
            403
        );

        $output = $outputs->syncFromJob($job);

        return response()->json([
            'ok' => (bool) $output,
            'output_id' => $output?->id,
        ]);
    }
}
