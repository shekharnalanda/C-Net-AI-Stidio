<?php

namespace App\Services\AI;

use App\Models\AIEngineProfile;

class EngineRegistry
{
    public function resolve(string $category, array $required = []): array
    {
        $profile = AIEngineProfile::where('category', $category)->where('is_active', true)
            ->orderBy('priority')->get()->first(fn ($engine) => empty(array_diff($required, $engine->capabilities ?? [])));

        return $profile ? [
            'key' => $profile->key, 'driver' => $profile->driver, 'capabilities' => $profile->capabilities ?? [],
        ] : ['key' => 'distributed-worker', 'driver' => 'worker', 'capabilities' => $required];
    }
}
