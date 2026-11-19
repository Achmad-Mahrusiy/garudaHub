<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProjectController::class, 'index'])->name('home');

// Tamu
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Klien: CRUD proyek miliknya
    Route::middleware('role:klien')->group(function () {
        Route::resource('projects', ProjectController::class)->only(['create', 'store', 'edit', 'update']);
    });

    // Klien pemilik atau admin (dicek di ProjectPolicy)
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
});

// Publik (didaftarkan terakhir & hanya angka agar tidak bentrok dengan /projects/create)
Route::get('/projects/{project}', [ProjectController::class, 'show'])->whereNumber('project')->name('projects.show');
