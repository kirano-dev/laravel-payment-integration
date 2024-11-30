<?php

namespace KiranoDev\LaravelPayment\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use KiranoDev\LaravelPayment\Http\Requests\Click\CallbackRequest;
use KiranoDev\LaravelPayment\Services\Payment\Click;

class ClickController
{
    public function __invoke(CallbackRequest $request, Click $click): JsonResponse
    {
        info(json_encode($request->all()));
        return $click->callback($request);
    }
}