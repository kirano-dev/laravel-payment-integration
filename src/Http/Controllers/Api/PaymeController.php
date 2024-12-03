<?php

namespace KiranoDev\LaravelPayment\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use KiranoDev\LaravelPayment\Http\Requests\Payme\CallbackRequest;
use KiranoDev\LaravelPayment\Services\Payment\Payme;

class PaymeController
{
    public function __invoke(CallbackRequest $request, Payme $payme): JsonResponse
    {
        return $payme->callback($request);
    }
}