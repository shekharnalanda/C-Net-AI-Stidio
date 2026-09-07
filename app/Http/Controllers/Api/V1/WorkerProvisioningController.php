<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AIWorker;
use App\Models\WorkerActivationCode;
use App\Services\Workers\WorkerCredentialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WorkerProvisioningController extends Controller
{
    public function activate(
        Request $request,
        WorkerCredentialService $credentials
    ) {
        $data = $request->validate([
            'activation_code' => ['required', 'string'],
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

        $activation = WorkerActivationCode::whereNull('used_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->latest('id')
            ->get()
            ->first(fn ($item) =>
                Hash::check($data['activation_code'], $item->code_hash)
            );

        if (! $activation) {
            return response()->json([
                'ok' => false,
                'message' => 'Activation code is invalid or expired.',
            ], 422);
        }

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
                'is_enabled' => true,
                'ip_address' => $request->ip(),
                'registered_at' => now(),
                'last_seen_at' => now(),
            ]
        );

        $token = $credentials->issueToken($worker);

        $activation->update([
            'used_at' => now(),
            'worker_id' => $worker->id,
        ]);

        return response()->json([
            'ok' => true,
            'worker_id' => $worker->id,
            'worker_uuid' => $worker->worker_uuid,
            'worker_token' => $token,
            'status' => 'activated',
        ]);
    }
}
