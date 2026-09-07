<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Studio\AdminController;
use App\Http\Controllers\Studio\AIJobController;
use App\Http\Controllers\Studio\DashboardController;
use App\Http\Controllers\Studio\MediaController;
use App\Http\Controllers\Studio\ProjectController;
use App\Http\Controllers\Studio\WorkerDownloadController;
use App\Http\Controllers\Studio\WorkerManagementController;
use App\Http\Controllers\Studio\TemplateController;
use App\Http\Controllers\Studio\WorkspaceController;
use App\Http\Controllers\Studio\OutputController;
use App\Http\Controllers\Studio\SmartToolController;
use App\Http\Controllers\Studio\BillingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('studio.home');
})->name('studio.home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.perform');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.perform');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/studio', [DashboardController::class, 'index'])
        ->name('studio.dashboard');

    Route::get('/studio/ai-tools', [SmartToolController::class, 'index'])->name('studio.smart-tools');
    Route::post('/studio/ai-tools', [SmartToolController::class, 'generate'])->middleware('throttle:30,1')->name('studio.smart-tools.generate');
    Route::get('/studio/ai-tools/{document}', [SmartToolController::class, 'show'])->name('studio.smart-tools.show');
    Route::get('/studio/billing', [BillingController::class, 'index'])->name('studio.billing');

    Route::get('/studio/projects', [ProjectController::class, 'index'])
        ->name('projects.index');

    Route::post('/studio/projects', [ProjectController::class, 'store'])
        ->name('projects.store');

    Route::get('/studio/projects/{project}', [ProjectController::class, 'editor'])
        ->name('projects.editor');

    Route::put('/studio/projects/{project}', [ProjectController::class, 'update'])
        ->name('projects.update');

    Route::post(
        '/studio/projects/{project}/generate',
        [ProjectController::class, 'generate']
    )->name('projects.generate');

    Route::post(
        '/studio/projects/{project}/media',
        [MediaController::class, 'store']
    )->name('media.store');

    Route::delete(
        '/studio/media/{media}',
        [MediaController::class, 'destroy']
    )->name('media.destroy');

    Route::get(
        '/studio/jobs/{job}/status',
        [AIJobController::class, 'status']
    )->name('jobs.status');


    Route::get('/studio/templates', [TemplateController::class, 'index'])
        ->name('studio.templates');

    Route::post('/studio/templates/{template}', [TemplateController::class, 'create'])
        ->name('studio.templates.create');

    Route::post(
        '/studio/projects/{project}/autosave',
        [WorkspaceController::class, 'autosave']
    )->name('projects.autosave');

    Route::post(
        '/studio/projects/{project}/duplicate',
        [WorkspaceController::class, 'duplicate']
    )->name('projects.duplicate');


    Route::get('/studio/outputs', [OutputController::class, 'index'])
        ->name('studio.outputs');

    Route::get(
        '/studio/projects/{project}/outputs',
        [OutputController::class, 'project']
    )->name('projects.outputs');

    Route::get(
        '/studio/outputs/{output}/download',
        [OutputController::class, 'download']
    )->name('outputs.download');

    Route::post(
        '/studio/jobs/{job}/retry',
        [OutputController::class, 'retry']
    )->name('jobs.retry');

    Route::post(
        '/studio/jobs/{job}/sync-output',
        [OutputController::class, 'sync']
    )->name('jobs.sync-output');

    Route::get('/admin', [AdminController::class, 'index'])
        ->name('admin.dashboard');

    Route::get(
        '/admin/worker/download',
        [WorkerDownloadController::class, 'download']
    )->name('admin.worker.download');

    Route::get('/admin/workers', [WorkerManagementController::class, 'index'])
        ->name('admin.workers');

    Route::post('/admin/workers/activation', [WorkerManagementController::class, 'createActivation'])
        ->name('admin.workers.activation');

    Route::post('/admin/workers/{worker}/toggle', [WorkerManagementController::class, 'toggle'])
        ->name('admin.workers.toggle');

    Route::post('/admin/workers/{worker}/rotate', [WorkerManagementController::class, 'rotate'])
        ->name('admin.workers.rotate');

    Route::delete('/admin/workers/{worker}', [WorkerManagementController::class, 'destroy'])
        ->name('admin.workers.destroy');

});
