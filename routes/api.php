<?php

use App\Http\Controllers\Api\V1\WorkerController;
use App\Http\Controllers\Api\V1\WorkerProvisioningController;
use App\Http\Middleware\WorkerTokenMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/v1/health', function () {
    return response()->json([
        'ok' => true,
        'product' => 'C-Net AI Studio',
        'version' => 'V3',
        'release' => 'batch-11',
        'worker_protocol' => 'V5',
        'worker_auth' => 'per-device',
        'worker_architecture' => 'distributed',
        'mandatory_paid_ai' => false,
        'capabilities' => ['smart-tools', 'engine-registry', 'usage-metering', 'secure-worker-v5'],
    ]);
});

Route::post(
    '/v1/workers/activate',
    [WorkerProvisioningController::class, 'activate']
)->middleware('throttle:10,1');

Route::middleware(WorkerTokenMiddleware::class)
    ->middleware('throttle:120,1')
    ->prefix('v1/workers')
    ->group(function () {
        Route::post('/register', [WorkerController::class, 'register']);
        Route::post('/heartbeat', [WorkerController::class, 'heartbeat']);
        Route::post('/next-job', [WorkerController::class, 'nextJob']);

        Route::post(
            '/jobs/{job}/progress',
            [WorkerController::class, 'progress']
        );

        Route::post(
            '/jobs/{job}/complete',
            [WorkerController::class, 'complete']
        );

        Route::post(
            '/jobs/{job}/fail',
            [WorkerController::class, 'fail']
        );
    });
