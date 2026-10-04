<?php

use App\Http\Controllers\Studio\HuggingFaceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/studio/create', [HuggingFaceController::class, 'index'])->name('studio.create');
    Route::post('/studio/create', [HuggingFaceController::class, 'store'])->middleware('throttle:6,1')->name('studio.create.store');
    Route::get('/studio/create/jobs/{job}', [HuggingFaceController::class, 'status'])->name('studio.create.status');
    Route::get('/studio/create/media/{output}', [HuggingFaceController::class, 'media'])->name('studio.create.media');
});
