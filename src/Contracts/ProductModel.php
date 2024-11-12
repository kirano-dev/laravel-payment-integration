<?php

namespace KiranoDev\LaravelPayment\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface ProductModel
{
    public function order(): BelongsTo;
}