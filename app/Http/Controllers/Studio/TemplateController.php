<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Models\StudioProject;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    private function templates(): array
    {
        return [
            'education-promo' => [
                'name' => 'Education Institute Promo',
                'type' => 'business-ad',
                'ratio' => '16:9',
                'duration' => 30,
                'style' => 'education',
                'icon' => '🎓',
                'description' => 'Professional admission and institution promotional video.',
            ],
            'school-reel' => [
                'name' => 'School Admission Reel',
                'type' => 'reel',
                'ratio' => '9:16',
                'duration' => 30,
                'style' => 'social',
                'icon' => '🏫',
                'description' => 'Vertical admission campaign for schools and play schools.',
            ],
            'business-ad' => [
                'name' => 'Local Business Advertisement',
                'type' => 'business-ad',
                'ratio' => '16:9',
                'duration' => 30,
                'style' => 'corporate',
                'icon' => '🏢',
                'description' => 'Modern advertisement for shops, services and companies.',
            ],
            'product-reel' => [
                'name' => 'Product Social Reel',
                'type' => 'reel',
                'ratio' => '9:16',
                'duration' => 20,
                'style' => 'social',
                'icon' => '🛍️',
                'description' => 'Short-form product showcase optimized for social media.',
            ],
            'youtube-intro' => [
                'name' => 'YouTube Intro',
                'type' => 'text-to-video',
                'ratio' => '16:9',
                'duration' => 15,
                'style' => 'cinematic',
                'icon' => '▶',
                'description' => 'Fast branded opening sequence for videos and channels.',
            ],
            'festival-post' => [
                'name' => 'Festival Greeting Video',
                'type' => 'text-to-video',
                'ratio' => '1:1',
                'duration' => 15,
                'style' => 'cinematic',
                'icon' => '✨',
                'description' => 'Festival, event and celebration greeting creative.',
            ],
            'photo-story' => [
                'name' => 'Photo Story',
                'type' => 'image-to-video',
                'ratio' => '16:9',
                'duration' => 30,
                'style' => 'cinematic',
                'icon' => '🖼️',
                'description' => 'Turn uploaded photographs into a structured visual story.',
            ],
            'vertical-story' => [
                'name' => 'Vertical Story',
                'type' => 'image-to-video',
                'ratio' => '9:16',
                'duration' => 20,
                'style' => 'social',
                'icon' => '📱',
                'description' => 'Mobile-first image animation and story workflow.',
            ],
            'course-launch' => [
                'name' => 'Course Launch',
                'type' => 'business-ad',
                'ratio' => '16:9',
                'duration' => 45,
                'style' => 'education',
                'icon' => '💻',
                'description' => 'Course launch, coaching and training promotion.',
            ],
            'news-explainer' => [
                'name' => 'News Explainer',
                'type' => 'text-to-video',
                'ratio' => '16:9',
                'duration' => 60,
                'style' => 'professional',
                'icon' => '📰',
                'description' => 'Structured informational or news-style explainer.',
            ],
            'real-estate' => [
                'name' => 'Property Promotion',
                'type' => 'business-ad',
                'ratio' => '16:9',
                'duration' => 45,
                'style' => 'corporate',
                'icon' => '🏠',
                'description' => 'Property and real-estate promotional workflow.',
            ],
            'blank-pro' => [
                'name' => 'Blank Pro Project',
                'type' => 'video',
                'ratio' => '16:9',
                'duration' => 30,
                'style' => 'professional',
                'icon' => '🎬',
                'description' => 'Start from an empty professional timeline.',
            ],
        ];
    }

    public function index()
    {
        return view('studio.templates', [
            'templates' => $this->templates(),
        ]);
    }

    public function create(Request $request, string $template)
    {
        $templates = $this->templates();

        abort_unless(isset($templates[$template]), 404);

        $preset = $templates[$template];

        $project = StudioProject::create([
            'user_id' => $request->user()->id,
            'name' => $preset['name'],
            'type' => $preset['type'],
            'status' => 'draft',
            'aspect_ratio' => $preset['ratio'],
            'language' => 'en',
            'quality' => 'hd',
            'editor_mode' => 'smart',
            'settings' => [
                'studio_version' => 'V3',
                'template' => $template,
                'creation_mode' => $preset['type'],
            ],
            'generation_settings' => [
                'duration' => $preset['duration'],
                'style' => $preset['style'],
                'voice' => 'auto',
            ],
            'timeline' => [
                'duration' => $preset['duration'],
                'tracks' => [
                    ['id' => 'video-1', 'type' => 'video', 'items' => []],
                    ['id' => 'overlay-1', 'type' => 'overlay', 'items' => []],
                    ['id' => 'text-1', 'type' => 'text', 'items' => []],
                    ['id' => 'audio-1', 'type' => 'audio', 'items' => []],
                    ['id' => 'effects-1', 'type' => 'effects', 'items' => []],
                ],
            ],
        ]);

        return redirect()
            ->route('projects.editor', $project)
            ->with('success', 'Template loaded into your AI workspace.');
    }
}
