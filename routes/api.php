<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });

    // Clients
    Route::prefix('clients')->group(function () {
        Route::get('/', [ClientController::class, 'index']);
        Route::post('/', [ClientController::class, 'store']);
        Route::get('/{client}', [ClientController::class, 'show']);
        Route::put('/{client}', [ClientController::class, 'update']);
        Route::delete('/{client}', [ClientController::class, 'destroy']);
        Route::get('/{client}/accounts', [ClientController::class, 'accounts']);
        Route::get('/{client}/invoices', [ClientController::class, 'invoices']);
        Route::get('/{client}/payments', [ClientController::class, 'payments']);
        Route::get('/{client}/tickets', [ClientController::class, 'tickets']);
        Route::post('/{client}/suspend', [ClientController::class, 'suspend']);
        Route::post('/{client}/activate', [ClientController::class, 'activate']);
    });

});
