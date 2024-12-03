<?php

namespace KiranoDev\LaravelPayment\Http\Requests\InfinityPay;

class StatementRequest extends BaseRequest
{
    public array $sign_fields = [
        'FROM',
        'TO',
    ];
}
