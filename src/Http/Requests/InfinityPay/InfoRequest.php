<?php

namespace KiranoDev\LaravelPayment\Http\Requests\InfinityPay;

class InfoRequest extends BaseRequest
{
    public array $sign_fields = [
        'MERCHANT_TRANS_ID',
    ];
}
