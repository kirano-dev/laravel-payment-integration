<?php

namespace KiranoDev\LaravelPayment\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use KiranoDev\LaravelPayment\Http\Requests\QuickPay\CallbackRequest;
use KiranoDev\LaravelPayment\Services\Payment\QuickPay;

class QuickPayController
{
    public function __invoke(CallbackRequest $request, QuickPay $quickPay): JsonResponse
    {
        info(json_encode($request->all()));
        return $quickPay->callback($request);
    }
}