<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeachingAllocationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class);
    Route::resource('subjects', SubjectController::class);
    Route::resource('teaching-allocations', TeachingAllocationController::class)->only(['index', 'create', 'store', 'destroy']);
});

require __DIR__.'/settings.php';
