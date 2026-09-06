<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WorkerTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('studio.worker_token');

        if ($expected === '') {
            abort(503, 'AI worker token is not configured.');
        }

        $provided = (string) $request->bearerToken();

        if ($provided === '' || ! hash_equals($expected, $provided)) {
            abort(401, 'Invalid worker token.');
        }

        return $next($request);
    }
}
