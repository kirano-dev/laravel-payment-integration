<?php

namespace KiranoDev\LaravelPayment\Providers;

use Illuminate\Support\ServiceProvider;
use KiranoDev\LaravelPayment\Contracts\OrderModel;
use KiranoDev\LaravelPayment\Contracts\ProductModel;
use KiranoDev\LaravelPayment\Models\Order;
use KiranoDev\LaravelPayment\Services\Click;
use KiranoDev\LaravelPayment\Services\Payme;
use KiranoDev\LaravelPayment\Services\Uzum;

class LaravelPaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrderModel::class, fn () => new Order());
        $this->app->bind(ProductModel::class, fn () => new Product());

        $this->app->singleton(Click::class, fn () => new Click());
        $this->app->singleton(Payme::class, fn () => new Payme());
        $this->app->singleton(Uzum::class, fn () => new Uzum());
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/payment.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ( ! defined('CURL_SSLVERSION_TLSv1_2')) { define('CURL_SSLVERSION_TLSv1_2', 6); }
    }
}