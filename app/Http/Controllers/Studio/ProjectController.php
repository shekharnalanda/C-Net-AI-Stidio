<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Models\StudioProject;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:150'],
            'type' => [
                'required',
                'in:video,text-to-video,image-to-video,business-ad,reel'
            ],
            'aspect_ratio' => ['nullable', 'in:16:9,9:16,1:1,4:5'],
        ]);

        $project = StudioProject::create([
            'user_id' => $request->user()->id,
            'name' => $data['name'] ?: 'Untitled AI Project',
            'type' => $data['type'],
            'status' => 'draft',
            'aspect_ratio' => $data['aspect_ratio'] ?? '16:9',
            'settings' => [
                'studio_version' => 'V3',
                'creation_mode' => $data['type'],
            ],
            'timeline' => [],
        ]);

        return redirect()
            ->route('studio.dashboard')
            ->with('success', 'Project created: '.$project->name);
    }
}
