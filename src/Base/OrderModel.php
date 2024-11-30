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
use KiranoDev\LaravelPayment\Services\Payment\Payme;
use KiranoDev\LaravelPayment\Services\Payment\QuickPay;
use KiranoDev\LaravelPayment\Services\Payment\Uzum;

abstract class OrderModel extends Model
{
    public function transaction(): HasOne {
        return $this->hasOne(Transaction::class);
    }

    abstract public function getCashRoute(): string;

    public function getSuccessUrl(): string {
        return config('app.url');
    }
    public function getFailureUrl(): string {
        return config('app.url');
    }

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

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot('quantity');
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