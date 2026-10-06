<?php


use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ContextController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\Finance\CaisseController;
use App\Http\Controllers\Api\V1\Finance\ExpenseController;
use App\Http\Controllers\Api\V1\Finance\PerceptionClasseController;
use App\Http\Controllers\Api\V1\Finance\PerceptionController;
use App\Http\Controllers\Api\V1\Finance\StudentController;
use App\Http\Controllers\Api\V1\Finance\RevenueController;
use App\Http\Controllers\Api\V1\Finance\PaymentController;
use App\Http\Controllers\Api\V1\Pos\PosPaymentController;
use App\Http\Controllers\Api\V1\Pos\PosStudentController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\Scolarite\EleveController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::post('/auth/login', [
        AuthController::class,
        'login'
    ]);

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/auth/logout', [
            AuthController::class,
            'logout'
        ]);

        Route::get('/auth/me', [
            AuthController::class,
            'me'
        ]);

        Route::get('/context', [
            ContextController::class,
            'index'
        ]);

        Route::prefix('pos')->group(function () {

            Route::get('/students', [
                PosStudentController::class,
                'index'
            ]);

            Route::get('/students/{identifier}', [
                PosStudentController::class,
                'show'
            ]);

            Route::get('/students/{identifier}/payment-context', [
                PosStudentController::class,
                'paymentContext'
            ]);

            Route::post('/payments', [
                PosPaymentController::class,
                'store'
            ]);

            Route::get('/payments/{id}', [
                PosPaymentController::class,
                'show'
            ]);
        });


        Route::get('/dashboard', [
            DashboardController::class,
            'index'
        ]);

    });

});


