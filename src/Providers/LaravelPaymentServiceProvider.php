<?php

namespace KiranoDev\LaravelPayment\Providers;

use Illuminate\Support\ServiceProvider;
use KiranoDev\LaravelPayment\Services\Payment\Click;
use KiranoDev\LaravelPayment\Services\Payment\Payme;
use KiranoDev\LaravelPayment\Services\Payment\InfinityPay;
use KiranoDev\LaravelPayment\Services\Payment\QuickPay;
use KiranoDev\LaravelPayment\Services\Payment\Uzum;

class LaravelPaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Click::class, fn () => new Click());
        $this->app->singleton(Payme::class, fn () => new Payme());
        $this->app->singleton(Uzum::class, fn () => new Uzum());
        $this->app->singleton(QuickPay::class, fn () => new QuickPay());
        $this->app->singleton(InfinityPay::class, fn () => new InfinityPay());
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/payment.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'payment');

        $this->publishes([
            __DIR__.'/../config/payment.php' => config_path('payment.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__.'/../config/payment.php', 'payment'
        );

        if ( ! defined('CURL_SSLVERSION_TLSv1_2')) { define('CURL_SSLVERSION_TLSv1_2', 6); }
    }
}