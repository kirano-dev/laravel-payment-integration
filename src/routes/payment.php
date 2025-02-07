<?php

use Illuminate\Support\Facades\Route;
use KiranoDev\LaravelPayment\Http\Controllers\Api\{
    ClickController,
    OctobankController,
    InfinityPayController,
    PaymeController,
    QuickPayController,
    UzumController,
};

Route::prefix(config('payment.api_prefix'))->group(function () {
    Route::prefix('payment')->as('payment.')
        ->group(function () {
            Route::post('octobank', OctobankController::class)->name('octobank');
            Route::post('click', ClickController::class)->name('click');
            Route::post('payme', PaymeController::class)->name('payme');
            Route::post('uzum', UzumController::class)->name('uzum');
            Route::post('quickpay', QuickPayController::class)->name('quickpay');

            Route::prefix('infinitypay')
                ->as('infinitypay.')
                ->controller(InfinityPayController::class)
                ->group(function () {
                    Route::post('info', 'info')->name('info');
                    Route::post('pay', 'pay')->name('pay');
                    Route::post('notify', 'notify')->name('notify');
                    Route::post('cancel', 'cancel')->name('cancel');
                    Route::post('statement', 'statement')->name('statement');
                    Route::post('fiscalization', 'fiscalization')->name('fiscalization');
            });
    });
});