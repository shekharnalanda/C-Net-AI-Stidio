<?php

namespace App\Services\AI;

use App\Models\AIJob;
use App\Models\StudioProject;
use Illuminate\Support\Str;
use App\Models\User;
use App\Services\Studio\UsageService;

class AIJobOrchestrator
{
    public function __construct(
        protected EngineRegistry $engines,
        protected UsageService $usage
    ) {
    }

    public function create(
        StudioProject $project,
        int $userId,
        string $jobType,
        array $payload = []
    ): AIJob {
        $category = match ($jobType) {
            'text-to-video' => 'video-generation',
            'image-to-video' => 'image-animation',
            'business-ad' => 'ad-generation',
            'reel' => 'short-video',
            'captions' => 'speech-to-text',
            'voiceover' => 'text-to-speech',
            default => 'media-processing',
        };

        $engine = $this->engines->resolve($category, [$jobType]);
        $user = User::findOrFail($userId);
        $this->usage->assertAndRecord($user, 'ai_jobs', 1, ['project_id' => $project->id]);

        return AIJob::create([
            'job_uuid' => (string) Str::uuid(),
            'user_id' => $userId,
            'project_id' => $project->id,
            'job_type' => $jobType,
            'status' => 'queued',
            'progress' => 0,
            'priority' => 100,
            'attempts' => 0,
            'engine' => $engine['key'],
            'queue_name' => 'studio',
            'available_at' => now(),
            'payload' => array_merge([
                'project_id' => $project->id,
                'project_name' => $project->name,
                'aspect_ratio' => $project->aspect_ratio,
                'quality' => $project->quality,
                'language' => $project->language,
                'engine_driver' => $engine['driver'],
                'pipeline_version' => 'v1',
            ], $payload),
        ]);
    }
}
