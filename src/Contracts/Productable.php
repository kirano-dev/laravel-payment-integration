<?php

namespace KiranoDev\LaravelPayment\Contracts;

use KiranoDev\LaravelPayment\Models\Product;

interface Productable
{
    public function getFiscalizationInfo(): array;
}