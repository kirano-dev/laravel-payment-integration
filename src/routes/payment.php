<?php

use Illuminate\Support\Facades\Route;
use KiranoDev\LaravelPayment\Http\Controllers\Api\PaymentController;

Route::prefix('api/v1')->group(function () {
    Route::prefix('payment')->controller(PaymentController::class)->group(function () {
        Route::post('click', 'click');
        Route::post('payme', 'payme');
        Route::post('uzum', 'uzum');
    });
});