<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\PcActivityController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('register');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/per-page', [DashboardController::class, 'updatePerPage'])->name('dashboard.per-page');

    Route::middleware(['can:admin'])->group(function () {
        Route::get('/admin/users/{user}', [UserController::class, 'show'])->name('admin.users.show');
        Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    });

    Route::get('/pcs/{pc}/activities', [PcActivityController::class, 'index'])->name('pcs.activities');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
