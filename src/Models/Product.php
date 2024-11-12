<?php

namespace KiranoDev\LaravelPayment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use KiranoDev\LaravelPayment\Contracts\OrderModel;
use KiranoDev\LaravelPayment\Contracts\ProductModel;

class Product extends Model implements ProductModel
{
    protected $fillable = [
        'order_id',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(get_class(app(OrderModel::class)));
    }
}
