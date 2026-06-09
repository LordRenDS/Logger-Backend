<?php

use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\PcController;
use App\Http\Controllers\Api\v1\PcProcessController;
use App\Http\Controllers\Api\v1\PcScheduleController;
use App\Http\Controllers\Api\v1\ProcessController;
use App\Http\Controllers\Api\v1\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', function () {
        return response()->json(['status' => 'ok']);
    });

    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::middleware('auth:api')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('refresh', [AuthController::class, 'refresh']);
        });
    });

    Route::middleware('auth:api')->group(function () {
        Route::apiResource('pcs', PcController::class);
        Route::apiResource('pcs.processes', PcProcessController::class)->only(['index', 'store']);
        Route::apiResource('pcs.schedules', PcScheduleController::class)->only(['index', 'store']);
        Route::apiResource('processes', ProcessController::class)->only(['show', 'update', 'destroy']);
        Route::apiResource('schedules', ScheduleController::class)->only(['show', 'update', 'destroy']);
    });
});
