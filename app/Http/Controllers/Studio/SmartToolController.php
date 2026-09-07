<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Models\AIToolDocument;
use App\Models\StudioProject;
use App\Services\Studio\SmartToolService;
use App\Services\Studio\UsageService;
use Illuminate\Http\Request;

class SmartToolController extends Controller
{
    public function index(Request $request)
    {
        return view('studio.smart-tools', ['documents' => AIToolDocument::where('user_id', $request->user()->id)->latest()->limit(20)->get()]);
    }

    public function generate(Request $request, SmartToolService $tools, UsageService $usage)
    {
        $data = $request->validate([
            'tool_type' => ['required', 'in:script,scenes,captions,prompt'],
            'title' => ['required', 'string', 'max:150'],
            'source_text' => ['required', 'string', 'max:30000'],
            'language' => ['required', 'in:en,hi'],
            'project_id' => ['nullable', 'integer'],
            'style' => ['nullable', 'string', 'max:80'],
            'aspect_ratio' => ['nullable', 'in:16:9,9:16,1:1,4:5'],
        ]);

        $project = isset($data['project_id']) ? StudioProject::where('user_id', $request->user()->id)->findOrFail($data['project_id']) : null;
        $usage->assertAndRecord($request->user(), 'smart_tools', 1, ['project_id' => $project?->id]);
        $content = $tools->build($data['tool_type'], $data['source_text'], $data);
        $document = AIToolDocument::create([
            'user_id' => $request->user()->id, 'project_id' => $project?->id, 'tool_type' => $data['tool_type'],
            'title' => $data['title'], 'language' => $data['language'], 'source_text' => $data['source_text'],
            'status' => 'ready', 'content' => $content,
        ]);

        return redirect()->route('studio.smart-tools.show', $document)->with('success', 'Smart AI content generated.');
    }

    public function show(Request $request, AIToolDocument $document)
    {
        abort_unless($document->user_id === $request->user()->id || $request->user()->isAdmin(), 403);
        return view('studio.smart-tool-result', compact('document'));
    }
}
