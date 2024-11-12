<?php

namespace KiranoDev\LaravelPayment\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasOne;

interface OrderModel
{
    public function transaction(): HasOne;
    public function getProducts(): Collection|array;
    public function generateUrl(): string;
}