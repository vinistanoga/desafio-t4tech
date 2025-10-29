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
    // Teams
    Route::middleware('ability:team:view,*')->get('/teams', [\App\Http\Controllers\Api\TeamController::class, 'index']);
    Route::middleware('ability:team:view,*')->get('/teams/{id}', [\App\Http\Controllers\Api\TeamController::class, 'show']);
    Route::middleware('ability:team:create,*')->post('/teams', [\App\Http\Controllers\Api\TeamController::class, 'store']);
    Route::middleware('ability:team:update,*')->put('/teams/{id}', [\App\Http\Controllers\Api\TeamController::class, 'update']);
    Route::middleware('ability:team:delete,*')->delete('/teams/{id}', [\App\Http\Controllers\Api\TeamController::class, 'destroy']);

    // Players
    Route::middleware('ability:player:view,*')->get('/players', [\App\Http\Controllers\Api\PlayerController::class, 'index']);
    Route::middleware('ability:player:view,*')->get('/players/{id}', [\App\Http\Controllers\Api\PlayerController::class, 'show']);
    Route::middleware('ability:player:create,*')->post('/players', [\App\Http\Controllers\Api\PlayerController::class, 'store']);
    Route::middleware('ability:player:update,*')->put('/players/{id}', [\App\Http\Controllers\Api\PlayerController::class, 'update']);
    Route::middleware('ability:player:delete,*')->delete('/players/{id}', [\App\Http\Controllers\Api\PlayerController::class, 'destroy']);
});
