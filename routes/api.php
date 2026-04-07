<?php

use App\Http\Controllers\Api\V1\GenerationApiController;
use App\Http\Controllers\Api\V1\UserApiController;
use App\Http\Controllers\Api\TokenController;
use Illuminate\Support\Facades\Route;

// Token management (web auth, not API tokens)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserApiController::class, 'me']);

    Route::prefix('v1')->middleware(['plan:pro,enterprise'])->group(function () {
        // Generations
        Route::get('/generations', [GenerationApiController::class, 'index']);
        Route::post('/generations', [GenerationApiController::class, 'store']);
        Route::get('/generations/{generation}', [GenerationApiController::class, 'show']);

        // Account
        Route::get('/credits', [UserApiController::class, 'credits']);
    });
});
