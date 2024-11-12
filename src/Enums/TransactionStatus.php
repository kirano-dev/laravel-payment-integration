<?php

namespace KiranoDev\LaravelPayment\Enums;

enum TransactionStatus: string
{
    use BaseEnum;

    case INACTIVE = 'inactive';
    case ACTIVE = 'active';
    case CANCELLED = 'cancelled';
}
