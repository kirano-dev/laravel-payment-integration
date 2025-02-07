<?php

namespace KiranoDev\LaravelPayment\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use KiranoDev\LaravelPayment\Http\Requests\Octobank\CallbackRequest;
use KiranoDev\LaravelPayment\Services\Payment\Octobank;

class OctobankController
{
    public function __invoke(CallbackRequest $request, Octobank $octobank): JsonResponse
    {
        return $octobank->callback($request);
    }
}