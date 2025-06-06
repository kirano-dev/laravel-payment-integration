<?php

namespace KiranoDev\LaravelPayment\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use KiranoDev\LaravelPayment\Contracts\UserModel;
use KiranoDev\LaravelPayment\Enums\PaymentMethod;
use KiranoDev\LaravelPayment\Models\Product;
use KiranoDev\LaravelPayment\Models\Transaction;
use KiranoDev\LaravelPayment\Services\Payment\Click;
use KiranoDev\LaravelPayment\Services\Payment\InfinityPay;
use KiranoDev\LaravelPayment\Services\Payment\Octobank;
use KiranoDev\LaravelPayment\Services\Payment\Payme;
use KiranoDev\LaravelPayment\Services\Payment\Paynet;
use KiranoDev\LaravelPayment\Services\Payment\QuickPay;
use KiranoDev\LaravelPayment\Services\Payment\Uzum;

abstract class OrderModel extends Model
{
    abstract public function onSuccessfulPay(): void;

    public function transaction(): HasOne {
        return $this->hasOne(Transaction::class);
    }

    public function getCashRoute(): string {
        return '';
    }

    public function getSuccessUrl(): string {
        return config('app.url');
    }
    public function getFailureUrl(): string {
        return config('app.url');
    }

    public function getTransactionParam(): mixed
    {
        return $this->id;
    }

    public function getParams(): array
    {
        return [];
    }

    public function generateUrl(): string {
        return match($this->payment_method) {
            PaymentMethod::CASH,
            PaymentMethod::TRANSFER,
            PaymentMethod::UZUM_NASIYA,
            PaymentMethod::ALIF_NASIYA => $this->getCashRoute(),

            default => app(match($this->payment_method) {
                PaymentMethod::PAYME => Payme::class,
                PaymentMethod::UZUM => Uzum::class,
                PaymentMethod::CLICK => Click::class,
                PaymentMethod::QUICKPAY => QuickPay::class,
                PaymentMethod::INFINITYPAY => InfinityPay::class,
                PaymentMethod::PAYNET => Paynet::class,
                PaymentMethod::OCTOBANK => Octobank::class,
            })->generateUrl($this)
        };
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('quantity', 'price');
    }

    public function user(): BelongsTo {
        return $this->belongsTo(get_class(app(UserModel::class)));
    }

    public function attachProducts(Collection|Model|array $ids): void {
        $models = $ids;

        if(is_array($ids)) {
            $models = new Collection($ids);
        } else if ($ids instanceof Model) {
            $models = new Collection([$ids]);
        }

        $this->products()->createMany($models->map(fn($model) => [
            'productable_id' => $model->id,
            'productable_type' => get_class($model),
        ]));
    }
}