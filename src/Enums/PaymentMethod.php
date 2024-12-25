<?php

namespace KiranoDev\LaravelPayment\Enums;

enum PaymentMethod: string
{
    use BaseEnum;

    case CLICK = 'click';
    case PAYME = 'payme';
    case UZUM = 'uzum';
    case QUICKPAY = 'quickpay';
    case INFINITYPAY = 'infinitypay';
    case PAYNET = 'paynet';
    case CASH = 'cash';
}
