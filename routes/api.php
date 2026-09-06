<?php

use App\Http\Controllers\Api\V1\WorkerController;
use App\Http\Middleware\WorkerTokenMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/v1/health', function () {
    return response()->json([
        'ok' => true,
        'product' => 'C-Net AI Studio',
        'version' => 'V3',
        'worker_protocol' => 'V4',
        'worker_architecture' => 'distributed',
        'mandatory_paid_ai' => false,
    ]);
});

Route::middleware(WorkerTokenMiddleware::class)
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
