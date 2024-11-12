<?php

namespace KiranoDev\LaravelPayment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use KiranoDev\LaravelPayment\Contracts\OrderModel;
use KiranoDev\LaravelPayment\Contracts\ProductModel;
use KiranoDev\LaravelPayment\Enums\PaymentMethod;
use KiranoDev\LaravelPayment\Services\Click;
use KiranoDev\LaravelPayment\Services\Payme;
use KiranoDev\LaravelPayment\Services\Uzum;

class Order extends Model implements OrderModel
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'payment_method',
        'is_payed',
        'created_at',
    ];

    protected $casts = [
        'is_payed' => 'boolean',
        'payment_method' => PaymentMethod::class,
    ];

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(get_class(app(ProductModel::class)));
    }

    public function generateUrl(): string
    {
        return (match($this->payment_method) {
            PaymentMethod::CLICK => app(Click::class),
            PaymentMethod::PAYME => app(Payme::class),
            PaymentMethod::UZUM => app(Uzum::class),
        })->generateUrl($this);
    }
}
