<?php

namespace KiranoDev\LaravelPayment\Contracts;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface PaymentService
{
    public function generateUrl(OrderModel $order): string;
    public function callback(Request $request): JsonResponse;
}
