<?php

namespace App\Services\AI;

use App\Models\AIJob;
use App\Models\StudioProject;
use Illuminate\Support\Str;

class AIJobOrchestrator
{
    public function create(
        StudioProject $project,
        int $userId,
        string $jobType,
        array $payload = []
    ): AIJob {
        $engine = match ($jobType) {
            'text-to-video' => 'video-generation',
            'image-to-video' => 'image-animation',
            'business-ad' => 'ad-generation',
            'reel' => 'short-video',
            'captions' => 'speech-to-text',
            'voiceover' => 'text-to-speech',
            default => 'media-processing',
        };

        return AIJob::create([
            'job_uuid' => (string) Str::uuid(),
            'user_id' => $userId,
            'project_id' => $project->id,
            'job_type' => $jobType,
            'status' => 'queued',
            'progress' => 0,
            'priority' => 100,
            'attempts' => 0,
            'engine' => $engine,
            'queue_name' => 'studio',
            'available_at' => now(),
            'payload' => array_merge([
                'project_id' => $project->id,
                'project_name' => $project->name,
                'aspect_ratio' => $project->aspect_ratio,
                'quality' => $project->quality,
                'language' => $project->language,
            ], $payload),
        ]);
    }
}
