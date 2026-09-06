<?php

use App\Http\Controllers\Api\V1\WorkerController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::get('/health', function () {
        return response()->json([
            'ok' => true,
            'product' => 'C-Net AI Studio',
            'version' => 'V3 Foundation',
            'paid_ai_dependency' => false,
        ]);
    });

    Route::prefix('workers')->group(function () {
        Route::post('/register', [WorkerController::class, 'register']);
        Route::post('/next-job', [WorkerController::class, 'nextJob']);
        Route::post('/jobs/{job}/complete', [WorkerController::class, 'complete']);
    });
});
