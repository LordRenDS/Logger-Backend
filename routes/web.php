<?php

use App\Http\Controllers\Web\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/admin/dashboard', [DashboardController::class, 'adminIndex'])->name('admin.dashboard');
    
    // Auth routes for session-based login/logout would normally be handled by Laravel Fortify or Breeze
    // But for this project, the user wants to keep it simple.
    // I'll add a simple logout route.
    Route::post('/logout', function () {
        auth()->logout();
        return redirect('/');
    })->name('logout');
});
