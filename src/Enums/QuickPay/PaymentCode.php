<?php

namespace KiranoDev\LaravelPayment\Enums\QuickPay;

use KiranoDev\LaravelPayment\Enums\BaseEnum;

enum PaymentCode: int
{
    use BaseEnum;

    case SUCCESS = 1;
    case SUCCESS_RETURN = 20;
    case EXPIRED = 31;
    case ERROR = 32;
}
