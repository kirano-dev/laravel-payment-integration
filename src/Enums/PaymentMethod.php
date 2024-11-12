<?php

namespace KiranoDev\LaravelPayment\Enums;

enum PaymentMethod: string
{
    use BaseEnum;

    case CLICK = 'click';
    case PAYME = 'payme';
    case UZUM = 'uzum';
}
