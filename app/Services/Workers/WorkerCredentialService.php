<?php

namespace App\Services\Workers;

use App\Models\AIWorker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WorkerCredentialService
{
    public function issueToken(AIWorker $worker): string
    {
        $token = 'cwk_'.Str::random(64);

        $worker->update([
            'token_hash' => Hash::make($token),
            'credential_rotated_at' => now(),
            'is_enabled' => true,
        ]);

        return $token;
    }

    public function verify(AIWorker $worker, string $token): bool
    {
        if (! $worker->is_enabled || ! $worker->token_hash || $token === '') {
            return false;
        }

        return Hash::check($token, $worker->token_hash);
    }
}
