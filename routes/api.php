<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// PUBLIC ROUTES
Route::post('/login', [AuthController::class, 'login']);

// PRIVATE ROUTES
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // X-Authorization
    Route::prefix('x-auth')->group(function () {
        Route::post('/generate', [AuthController::class, 'generateXAuthToken']);
        Route::get('/token', [AuthController::class, 'getXAuthToken']);
        Route::delete('/revoke', [AuthController::class, 'revokeXAuthToken']);
    });
});

Route::middleware(['auth:sanctum', 'x-auth'])->group(function () {

});
