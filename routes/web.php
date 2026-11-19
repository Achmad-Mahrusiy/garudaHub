<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BidController;
use App\Http\Controllers\DashboardController;
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
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Klien: CRUD proyek miliknya + pilih pemenang
    Route::middleware('role:klien')->group(function () {
        Route::resource('projects', ProjectController::class)->only(['create', 'store', 'edit', 'update']);
        Route::post('/projects/{project}/bids/{bid}/pick', [BidController::class, 'pick'])->name('bids.pick');
    });

    // Klien pemilik atau admin (dicek di ProjectPolicy)
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    // Developer: CRUD penawaran miliknya
    Route::middleware('role:developer')->group(function () {
        Route::post('/projects/{project}/bids', [BidController::class, 'store'])->name('bids.store');
        Route::resource('bids', BidController::class)->only(['edit', 'update', 'destroy']);
    });

    // Admin
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::patch('/users/{user}/role', [AdminController::class, 'updateRole'])->name('users.role');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    });
});

// Publik (didaftarkan terakhir & hanya angka agar tidak bentrok dengan /projects/create)
Route::get('/projects/{project}', [ProjectController::class, 'show'])->whereNumber('project')->name('projects.show');
