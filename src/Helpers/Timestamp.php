<?php

namespace KiranoDev\LaravelPayment\Helpers;

class Timestamp
{
    public function __invoke(): int
    {
        return round(microtime(true) * 1000);
    }
}