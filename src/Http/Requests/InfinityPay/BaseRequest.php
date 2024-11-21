<?php

namespace KiranoDev\LaravelPayment\Http\Requests\InfinityPay;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use KiranoDev\LaravelPayment\Enums\InfinityPay\Error;
use KiranoDev\LaravelPayment\Services\Payment\InfinityPay;

class BaseRequest extends FormRequest
{
    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            app(InfinityPay::class)->sendResponse(Error::SIGN_CHECK_FAILED)
        );
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            app(InfinityPay::class)->sendResponse(Error::INFINITYPAY_ERROR)
        );
    }
}