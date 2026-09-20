<?php


use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ContextController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\Finance\CaisseController;
use App\Http\Controllers\Api\V1\Finance\PerceptionClasseController;
use App\Http\Controllers\Api\V1\Finance\PerceptionController;
use App\Http\Controllers\Api\V1\Finance\StudentController;
use App\Http\Controllers\Api\V1\Finance\RevenueController;
use App\Http\Controllers\Api\V1\Finance\PaymentController;
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
        Route::get('/students', [EleveController::class, 'index'])
            ->name('api.v1.students.index');
        Route::get('/revenues', [RevenueController::class, 'index'])
            ->name('api.v1.revenues.index');
        Route::post('/payments', [PaymentController::class, 'store'])
            ->name('api.v1.payments.store');

        Route::post(
            '/finance/perceptions/classe',
            [PerceptionClasseController::class, 'store']
        )->name('api.v1.finance.perceptions.classe.store');

        Route::post(
            '/finance/caisse/perception/{perception_id}/pay',
            [CaisseController::class, 'pay']
        )->name('api.v1.finance.caisse.pay');
    });

    Route::get(
        '/finance/perceptions',
        [PerceptionController::class, 'index']
    );

    Route::get(
        '/finance/perceptions/total',
        [PerceptionController::class, 'total']
    );

    Route::get(
        '/finance/perceptions/by-fee',
        [PerceptionController::class, 'byFee']
    );

    Route::get(
        '/students/count-by-class',
        [StudentController::class, 'studentCountByClass']
    );

    Route::get(
        '/finance/students/by-class',
        [StudentController::class, 'byClass']
    );


    Route::get(
        '/finance/students/insolvables',
        [StudentController::class, 'insolvables']
    );



});




