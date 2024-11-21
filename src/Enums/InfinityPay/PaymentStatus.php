<?php

namespace KiranoDev\LaravelPayment\Enums\InfinityPay;

use KiranoDev\LaravelPayment\Enums\BaseEnum;

enum PaymentStatus: int
{
    use BaseEnum;

    case PAYED = 2;
    case CANCELLED = 3;
}
