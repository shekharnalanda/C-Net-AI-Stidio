<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Studio\AdminController;
use App\Http\Controllers\Studio\DashboardController;
use App\Http\Controllers\Studio\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('studio.home');
})->name('studio.home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.perform');

    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.perform');
});

Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::get('/studio', [DashboardController::class, 'index'])
        ->name('studio.dashboard');

    Route::post('/studio/projects', [ProjectController::class, 'store'])
        ->name('projects.store');

    Route::get('/admin', [AdminController::class, 'index'])
        ->name('admin.dashboard');
});
