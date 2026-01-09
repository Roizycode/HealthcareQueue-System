<?php

use App\Http\Controllers\QueueController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public API Routes
Route::prefix('v1')->group(function () {
    // Services
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/{service}', [ServiceController::class, 'show']);

    // Queue Status (Public)
    Route::get('/queue/{queue}/status', [PatientController::class, 'getStatus']);
    Route::get('/queue/current-serving', [QueueController::class, 'currentServing']);

    // Join Queue (Virtual Queue Registration)
    Route::post('/queue/join', [PatientController::class, 'register']);

    // Protected Routes (require API token - for future implementation)
    Route::middleware('auth:sanctum')->group(function () {
        // Queue Operations
        Route::get('/queue/service/{service}/waiting', [QueueController::class, 'waitingList']);
        Route::get('/queue/service/{service}/stats', [QueueController::class, 'serviceStats']);
        Route::get('/queue/stats', [QueueController::class, 'overallStats']);

        Route::post('/queue/service/{service}/call-next', [QueueController::class, 'callNext']);
        Route::post('/queue/{queue}/recall', [QueueController::class, 'recall']);
        Route::post('/queue/{queue}/start-serving', [QueueController::class, 'startServing']);
        Route::post('/queue/{queue}/complete', [QueueController::class, 'complete']);
        Route::post('/queue/{queue}/skip', [QueueController::class, 'skip']);
        Route::post('/queue/{queue}/cancel', [QueueController::class, 'cancel']);

        // User info
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });
});
