<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Models\StudioProject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WorkspaceController extends Controller
{
    protected function authorizeProject(Request $request, StudioProject $project): void
    {
        abort_unless(
            (int) $project->user_id === (int) $request->user()->id
            || $request->user()->isAdmin(),
            403
        );
    }

    public function autosave(Request $request, StudioProject $project)
    {
        $this->authorizeProject($request, $project);

        $data = $request->validate([
            'timeline' => ['nullable', 'array'],
            'brand_settings' => ['nullable', 'array'],
            'generation_settings' => ['nullable', 'array'],
        ]);

        $update = [];

        if (array_key_exists('timeline', $data)) {
            $update['timeline'] = $data['timeline'];
        }

        if (array_key_exists('brand_settings', $data)) {
            $update['brand_settings'] = $data['brand_settings'];
        }

        if (array_key_exists('generation_settings', $data)) {
            $update['generation_settings'] = array_merge(
                $project->generation_settings ?? [],
                $data['generation_settings'] ?? []
            );
        }

        if ($update) {
            $project->update($update);
        }

        return response()->json([
            'ok' => true,
            'saved_at' => now()->toIso8601String(),
        ]);
    }

    public function duplicate(Request $request, StudioProject $project)
    {
        $this->authorizeProject($request, $project);

        $copy = $project->replicate([
            'status',
            'last_opened_at',
        ]);

        $copy->user_id = $request->user()->id;
        $copy->name = Str::limit($project->name.' Copy', 150, '');
        $copy->status = 'draft';
        $copy->last_opened_at = now();
        $copy->save();

        return redirect()
            ->route('projects.editor', $copy)
            ->with('success', 'Project duplicated successfully.');
    }
}
