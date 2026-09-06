<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AIJob;
use App\Models\AIWorker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkerController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'worker_uuid' => ['nullable', 'string', 'max:64'],
            'name' => ['nullable', 'string', 'max:150'],
            'hostname' => ['nullable', 'string', 'max:150'],
            'platform' => ['nullable', 'string', 'max:100'],
            'cpu' => ['nullable', 'string'],
            'gpu' => ['nullable', 'string'],
            'ram_mb' => ['nullable', 'integer', 'min:0'],
            'disk_free_mb' => ['nullable', 'integer', 'min:0'],
            'ffmpeg_version' => ['nullable', 'string', 'max:255'],
            'capabilities' => ['nullable', 'array'],
        ]);

        $uuid = $data['worker_uuid'] ?? (string) Str::uuid();

        $worker = AIWorker::updateOrCreate(
            ['worker_uuid' => $uuid],
            [
                'name' => $data['name'] ?? ($data['hostname'] ?? 'C-Net AI Worker'),
                'hostname' => $data['hostname'] ?? null,
                'platform' => $data['platform'] ?? null,
                'cpu' => $data['cpu'] ?? null,
                'gpu' => $data['gpu'] ?? null,
                'ram_mb' => $data['ram_mb'] ?? null,
                'disk_free_mb' => $data['disk_free_mb'] ?? null,
                'ffmpeg_version' => $data['ffmpeg_version'] ?? null,
                'capabilities' => $data['capabilities'] ?? [],
                'status' => 'online',
                'ip_address' => $request->ip(),
                'last_seen_at' => now(),
                'registered_at' => now(),
            ]
        );

        return response()->json([
            'ok' => true,
            'worker_id' => $worker->id,
            'worker_uuid' => $worker->worker_uuid,
            'status' => 'registered',
            'studio' => 'C-Net AI Studio V3',
        ]);
    }

    public function heartbeat(Request $request)
    {
        $data = $request->validate([
            'worker_uuid' => ['required', 'string'],
            'disk_free_mb' => ['nullable', 'integer', 'min:0'],
        ]);

        $worker = AIWorker::where('worker_uuid', $data['worker_uuid'])->firstOrFail();

        $worker->update([
            'status' => 'online',
            'last_seen_at' => now(),
            'disk_free_mb' => $data['disk_free_mb'] ?? $worker->disk_free_mb,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'ok' => true,
            'worker_id' => $worker->id,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function nextJob(Request $request)
    {
        $data = $request->validate([
            'worker_uuid' => ['required', 'string'],
        ]);

        $worker = AIWorker::where('worker_uuid', $data['worker_uuid'])->firstOrFail();

        $worker->update([
            'status' => 'online',
            'last_seen_at' => now(),
        ]);

        if ($worker->current_job_id) {
            $existing = AIJob::find($worker->current_job_id);

            if ($existing && in_array($existing->status, ['processing', 'running'], true)) {
                return response()->json([
                    'ok' => true,
                    'job' => $existing,
                ]);
            }

            $worker->update(['current_job_id' => null]);
        }

        $job = DB::transaction(function () use ($worker) {

            $job = AIJob::where('status', 'queued')
                ->where(function ($query) {
                    $query->whereNull('available_at')
                        ->orWhere('available_at', '<=', now());
                })
                ->orderBy('priority')
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (!$job) {
                return null;
            }

            $job->update([
                'status' => 'processing',
                'worker_id' => $worker->id,
                'claimed_at' => now(),
                'started_at' => $job->started_at ?: now(),
                'heartbeat_at' => now(),
                'attempts' => ($job->attempts ?? 0) + 1,
            ]);

            $worker->update([
                'current_job_id' => $job->id,
                'status' => 'busy',
            ]);

            return $job->fresh();
        });

        if (!$job) {
            return response()->json([
                'ok' => true,
                'job' => null,
                'message' => 'No jobs available.',
            ]);
        }

        return response()->json([
            'ok' => true,
            'job' => [
                'id' => $job->id,
                'uuid' => $job->job_uuid,
                'type' => $job->job_type,
                'engine' => $job->engine,
                'payload' => $job->payload,
                'attempts' => $job->attempts,
            ],
        ]);
    }

    public function progress(Request $request, AIJob $job)
    {
        $data = $request->validate([
            'worker_uuid' => ['required', 'string'],
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $worker = AIWorker::where('worker_uuid', $data['worker_uuid'])->firstOrFail();

        abort_unless((int) $job->worker_id === (int) $worker->id, 409);

        $job->update([
            'progress' => $data['progress'],
            'heartbeat_at' => now(),
        ]);

        $worker->update([
            'last_seen_at' => now(),
            'status' => 'busy',
        ]);

        return response()->json([
            'ok' => true,
            'job_id' => $job->id,
            'progress' => $job->progress,
        ]);
    }

    public function complete(Request $request, AIJob $job)
    {
        $data = $request->validate([
            'worker_uuid' => ['required', 'string'],
            'result' => ['nullable', 'array'],
            'output_path' => ['nullable', 'string'],
        ]);

        $worker = AIWorker::where('worker_uuid', $data['worker_uuid'])->firstOrFail();

        abort_unless((int) $job->worker_id === (int) $worker->id, 409);

        $job->update([
            'status' => 'completed',
            'progress' => 100,
            'result' => $data['result'] ?? [],
            'output_path' => $data['output_path'] ?? null,
            'completed_at' => now(),
            'heartbeat_at' => now(),
            'error_message' => null,
        ]);

        if ($job->project) {
            $job->project->update([
                'status' => 'completed',
            ]);
        }

        $worker->update([
            'status' => 'online',
            'current_job_id' => null,
            'last_seen_at' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'job_id' => $job->id,
            'status' => 'completed',
        ]);
    }

    public function fail(Request $request, AIJob $job)
    {
        $data = $request->validate([
            'worker_uuid' => ['required', 'string'],
            'error' => ['required', 'string', 'max:5000'],
        ]);

        $worker = AIWorker::where('worker_uuid', $data['worker_uuid'])->firstOrFail();

        abort_unless((int) $job->worker_id === (int) $worker->id, 409);

        $retry = ($job->attempts ?? 0) < 3;

        $job->update([
            'status' => $retry ? 'queued' : 'failed',
            'error_message' => $data['error'],
            'failed_at' => now(),
            'heartbeat_at' => now(),
            'worker_id' => $retry ? null : $worker->id,
            'available_at' => $retry ? now()->addMinutes(1) : $job->available_at,
        ]);

        $worker->update([
            'status' => 'online',
            'current_job_id' => null,
            'last_seen_at' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'job_id' => $job->id,
            'status' => $job->fresh()->status,
            'retry' => $retry,
        ]);
    }
}
