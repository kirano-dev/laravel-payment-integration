<?php

namespace KiranoDev\LaravelPayment\Enums\QuickPay;

use KiranoDev\LaravelPayment\Enums\BaseEnum;

enum PaymentType: int
{
    use BaseEnum;

    case PAYMENT = 1;
    case RETURN = 2;
}
