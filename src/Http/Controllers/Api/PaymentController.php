<?php

namespace KiranoDev\LaravelPayment\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use KiranoDev\LaravelPayment\Http\Requests\PaymeCallbackRequest;
use KiranoDev\LaravelPayment\Http\Requests\UzumCallbackRequest;
use KiranoDev\LaravelPayment\Services\Click;
use KiranoDev\LaravelPayment\Services\Payme;
use KiranoDev\LaravelPayment\Services\Uzum;

class PaymentController
{
    public function click(Request $request, Click $click): JsonResponse
    {
        return $click->callback($request);
    }

    public function payme(PaymeCallbackRequest $request, Payme $payme): JsonResponse
    {
        return $payme->callback($request);
    }

    public function uzum(UzumCallbackRequest $request, Uzum $uzum): JsonResponse
    {
        return $uzum->callback($request);
    }
}