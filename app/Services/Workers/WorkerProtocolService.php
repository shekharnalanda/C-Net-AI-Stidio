<?php

namespace App\Services\Workers;

use App\Models\AIJob;
use App\Models\AIWorker;
use Illuminate\Support\Str;

class WorkerProtocolService
{
    public function register(array $data): AIWorker
    {
        return AIWorker::updateOrCreate(
            ['worker_uuid' => $data['worker_uuid'] ?? (string) Str::uuid()],
            [
                'name' => $data['name'] ?? 'C-Net AI Worker',
                'status' => 'online',
                'platform' => $data['platform'] ?? null,
                'cpu' => $data['cpu'] ?? null,
                'gpu' => $data['gpu'] ?? null,
                'ram_mb' => $data['ram_mb'] ?? null,
                'capabilities' => $data['capabilities'] ?? [],
                'last_seen_at' => now(),
            ]
        );
    }

    public function nextJob(string $workerUuid): ?AIJob
    {
        $worker = AIWorker::where('worker_uuid', $workerUuid)->first();

        if (!$worker) {
            return null;
        }

        $worker->update([
            'status' => 'online',
            'last_seen_at' => now(),
        ]);

        $job = AIJob::where('status', 'queued')
            ->orderBy('id')
            ->first();

        if ($job) {
            $job->update([
                'status' => 'assigned',
                'worker_id' => $workerUuid,
            ]);
        }

        return $job;
    }
}
