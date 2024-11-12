<?php

namespace KiranoDev\LaravelPayment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use KiranoDev\LaravelPayment\Contracts\OrderModel;
use KiranoDev\LaravelPayment\Enums\TransactionStatus;

class Transaction extends Model
{
    protected $fillable = [
        'type',
        'status',
        'order_id',
        'amount',
        'extra',

        'extra->cancel_time',
        'extra->perform_time',
        'extra->reason',
        'extra->state',
    ];

    protected $casts = [
        'extra' => 'array',
        'status' => TransactionStatus::class,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(get_class(app(OrderModel::class)));
    }
}
