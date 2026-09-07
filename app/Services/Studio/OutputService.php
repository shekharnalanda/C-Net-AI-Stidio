<?php

namespace App\Services\Studio;

use App\Models\AIJob;
use App\Models\StudioOutput;
use Illuminate\Support\Str;

class OutputService
{
    public function syncFromJob(AIJob $job): ?StudioOutput
    {
        if ($job->status !== 'completed') {
            return null;
        }

        $result = $job->result ?? [];

        $sourcePath =
            $result['output_path']
            ?? $result['output_file']
            ?? null;

        if (! $sourcePath) {
            return null;
        }

        $format = strtolower(
            pathinfo($sourcePath, PATHINFO_EXTENSION)
        );

        $type = match ($format) {
            'mp4', 'mov', 'webm', 'mkv' => 'video',
            'jpg', 'jpeg', 'png', 'webp' => 'image',
            'mp3', 'wav', 'aac', 'm4a' => 'audio',
            default => 'file',
        };

        return StudioOutput::updateOrCreate(
            [
                'ai_job_id' => $job->id,
                'source_path' => $sourcePath,
            ],
            [
                'user_id' => $job->user_id,
                'project_id' => $job->project_id,
                'output_uuid' => (string) Str::uuid(),
                'name' => $job->project?->name
                    ? $job->project->name.' Output'
                    : 'AI Generated Output',
                'output_type' => $type,
                'format' => $format ?: null,
                'status' => 'ready',
                'path' => $sourcePath,
                'size_bytes' => $result['size_bytes'] ?? null,
                'duration_seconds' => $job->payload['duration'] ?? null,
                'metadata' => [
                    'job_uuid' => $job->job_uuid,
                    'job_type' => $job->job_type,
                    'engine' => $result['engine'] ?? null,
                    'result' => $result,
                ],
                'generated_at' => $job->completed_at ?? now(),
            ]
        );
    }
}
