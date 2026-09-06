<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Models\StudioProject;
use App\Services\AI\AIJobOrchestrator;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        return view('studio.projects', [
            'projects' => StudioProject::where('user_id', $request->user()->id)
                ->latest('updated_at')
                ->paginate(18),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:150'],
            'type' => ['required', 'in:video,text-to-video,image-to-video,business-ad,reel'],
            'aspect_ratio' => ['nullable', 'in:16:9,9:16,1:1,4:5'],
        ]);

        $project = StudioProject::create([
            'user_id' => $request->user()->id,
            'name' => $data['name'] ?: 'Untitled AI Project',
            'type' => $data['type'],
            'status' => 'draft',
            'aspect_ratio' => $data['aspect_ratio'] ?? '16:9',
            'language' => 'en',
            'quality' => 'hd',
            'editor_mode' => 'smart',
            'settings' => [
                'studio_version' => 'V3',
                'creation_mode' => $data['type'],
            ],
            'timeline' => [
                'duration' => 30,
                'tracks' => [
                    ['id' => 'video-1', 'type' => 'video', 'items' => []],
                    ['id' => 'text-1', 'type' => 'text', 'items' => []],
                    ['id' => 'audio-1', 'type' => 'audio', 'items' => []],
                ],
            ],
        ]);

        return redirect()->route('projects.editor', $project);
    }

    public function editor(Request $request, StudioProject $project)
    {
        abort_unless(
            $project->user_id === $request->user()->id || $request->user()->isAdmin(),
            403
        );

        $project->update(['last_opened_at' => now()]);

        return view('studio.editor', [
            'project' => $project,
            'media' => $project->media()->latest()->get(),
            'jobs' => $project->aiJobs()->latest()->limit(20)->get(),
        ]);
    }

    public function update(Request $request, StudioProject $project)
    {
        abort_unless(
            $project->user_id === $request->user()->id || $request->user()->isAdmin(),
            403
        );

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'prompt' => ['nullable', 'string', 'max:10000'],
            'aspect_ratio' => ['required', 'in:16:9,9:16,1:1,4:5'],
            'language' => ['required', 'in:en,hi'],
            'quality' => ['required', 'in:sd,hd,full-hd'],
            'editor_mode' => ['required', 'in:smart,pro'],
        ]);

        $project->update($data);

        return back()->with('success', 'Project settings saved.');
    }

    public function generate(
        Request $request,
        StudioProject $project,
        AIJobOrchestrator $orchestrator
    ) {
        abort_unless(
            $project->user_id === $request->user()->id || $request->user()->isAdmin(),
            403
        );

        $data = $request->validate([
            'prompt' => ['required', 'string', 'max:10000'],
            'duration' => ['nullable', 'integer', 'min:5', 'max:300'],
            'style' => ['nullable', 'string', 'max:100'],
            'voice' => ['nullable', 'string', 'max:100'],
        ]);

        $project->update([
            'prompt' => $data['prompt'],
            'status' => 'queued',
            'generation_settings' => [
                'duration' => $data['duration'] ?? 30,
                'style' => $data['style'] ?? 'professional',
                'voice' => $data['voice'] ?? 'auto',
            ],
        ]);

        $job = $orchestrator->create(
            $project,
            $request->user()->id,
            $project->type,
            [
                'prompt' => $data['prompt'],
                'duration' => $data['duration'] ?? 30,
                'style' => $data['style'] ?? 'professional',
                'voice' => $data['voice'] ?? 'auto',
            ]
        );

        return back()->with(
            'success',
            'AI job queued successfully: '.$job->job_uuid
        );
    }
}
