<?php


use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ContextController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\Scolarite\EleveController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::get('/context', [ContextController::class, 'index']);

        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::get('/scolarite/eleves', [EleveController::class, 'index'])
            ->name('api.v1.scolarite.eleves.index');


    });

});
