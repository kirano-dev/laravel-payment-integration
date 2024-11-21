<?php

namespace KiranoDev\LaravelPayment\Enums\QuickPay;

use KiranoDev\LaravelPayment\Enums\BaseEnum;

enum PaymentStatus: string
{
    use BaseEnum;

    case SUCCESS = 'success';
    case FAIL = 'fail';
    case EXPIRED = 'expired';
    case REFUND = 'refund';
}
