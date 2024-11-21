<?php

namespace KiranoDev\LaravelPayment\Enums\InfinityPay;

use KiranoDev\LaravelPayment\Enums\BaseEnum;

enum Environment: string
{
    use BaseEnum;

    case LIVE = 'live';
    case SANDBOX = 'sandbox';
}
