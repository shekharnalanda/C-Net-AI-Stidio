<?php

namespace App\Http\Middleware;

use App\Models\AIWorker;
use App\Services\Workers\WorkerCredentialService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WorkerTokenMiddleware
{
    public function __construct(
        protected WorkerCredentialService $credentials
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $uuid = (string) $request->input('worker_uuid');
        $token = (string) $request->bearerToken();

        if ($uuid === '' || $token === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Worker authentication required.',
            ], 401);
        }

        $worker = AIWorker::where('worker_uuid', $uuid)->first();

        if (! $worker || ! $this->credentials->verify($worker, $token)) {
            return response()->json([
                'ok' => false,
                'message' => 'Invalid or disabled worker credential.',
            ], 401);
        }

        $request->attributes->set(
            'authenticated_worker',
            $worker
        );

        return $next($request);
    }
}
