<?php

namespace KiranoDev\LaravelPayment\Base;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use KiranoDev\LaravelPayment\Contracts\UserModel;
use KiranoDev\LaravelPayment\Enums\PaymentMethod;
use KiranoDev\LaravelPayment\Models\Transaction;
use KiranoDev\LaravelPayment\Services\Payment\Click;
use KiranoDev\LaravelPayment\Services\Payment\InfinityPay;
use KiranoDev\LaravelPayment\Services\Payment\Payme;
use KiranoDev\LaravelPayment\Services\Payment\QuickPay;
use KiranoDev\LaravelPayment\Services\Payment\Uzum;

abstract class OrderModel extends Model
{
    public string $cashRoute = '/';

    public function transaction(): HasOne {
        return $this->hasOne(Transaction::class);
    }

    abstract public function getProducts(): Collection|array;

    abstract public function getCashRoute(): string;

    public function generateUrl(): string {
        return match($this->payment_method) {
            PaymentMethod::CASH => $this->getCashRoute(),

            default => app(match($this->payment_method) {
                PaymentMethod::PAYME => Payme::class,
                PaymentMethod::UZUM => Uzum::class,
                PaymentMethod::CLICK => Click::class,
                PaymentMethod::QUICKPAY => QuickPay::class,
                PaymentMethod::INFINITYPAY => InfinityPay::class,
            })->generateUrl($this)
        };
    }

    public function user(): BelongsTo {
        return $this->belongsTo(get_class(app(UserModel::class)));
    }
}