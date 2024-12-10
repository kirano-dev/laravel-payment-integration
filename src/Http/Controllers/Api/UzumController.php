<?php

namespace KiranoDev\LaravelPayment\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use KiranoDev\LaravelPayment\Http\Requests\Uzum\CallbackRequest;
use KiranoDev\LaravelPayment\Services\Payment\Uzum;

class UzumController
{
    public function __invoke(CallbackRequest $request, Uzum $uzum): JsonResponse
    {
        return $uzum->callback($request);
    }
}