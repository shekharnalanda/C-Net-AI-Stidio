<?php

namespace App\Services\Studio;

use App\Models\StudioUsageEvent;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UsageService
{
    public function assertAndRecord(User $user, string $metric, int $quantity = 1, array $context = []): void
    {
        $period = now()->format('Y-m');
        $limit = $this->limitFor($user, $metric);
        $used = StudioUsageEvent::where('user_id', $user->id)->where('metric', $metric)
            ->where('period_key', $period)->sum('quantity');

        if ($limit !== null && $used + $quantity > $limit) {
            throw ValidationException::withMessages(['plan' => 'Monthly plan limit reached for '.$metric.'.']);
        }

        StudioUsageEvent::create(array_merge($context, [
            'user_id' => $user->id, 'metric' => $metric, 'quantity' => $quantity, 'period_key' => $period,
        ]));
    }

    public function summary(User $user): array
    {
        return StudioUsageEvent::where('user_id', $user->id)->where('period_key', now()->format('Y-m'))
            ->selectRaw('metric, SUM(quantity) as total')->groupBy('metric')->pluck('total', 'metric')->all();
    }

    private function limitFor(User $user, string $metric): ?int
    {
        if ($user->isAdmin()) return null;
        $subscription = $user->subscriptions()->whereIn('status', ['active', 'trial'])->latest()->first();
        $limits = $subscription?->plan?->limits ?? ['ai_jobs' => 20, 'smart_tools' => 50];
        return isset($limits[$metric]) ? (int) $limits[$metric] : null;
    }
}
