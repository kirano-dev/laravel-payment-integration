<?php

namespace KiranoDev\LaravelPayment\Enums\InfinityPay;

use KiranoDev\LaravelPayment\Enums\BaseEnum;

enum TransactionType: string
{
    use BaseEnum;

    case PAYMENT = 'PAYMENT';
    case CANCEL = 'CANCEL';
}
