<?php

namespace KiranoDev\LaravelPayment\Http\Requests\InfinityPay;

use KiranoDev\LaravelPayment\Enums\InfinityPay\Environment;

class PayRequest extends BaseRequest
{
    public function authorize(): bool {
        $request_signature = $this->SIGN_STRING;

        $signature = md5(implode(array: [
            config('payment.infinitypay.secret_key'),
            $this->AGR_TRANS_ID,
            $this->VENDOR_ID,
            $this->PAYMENT_ID,
            $this->PAYMENT_NAME,
            $this->MERCHANT_TRANS_ID,
            $this->MERCHANT_TRANS_AMOUNT,
            $this->ENVIRONMENT,
            $this->SIGN_TIME,
        ]));

        return hash_equals($request_signature, $signature);
    }

    public function rules(): array
    {
        return [
            'VENDOR_ID' => ['required'],
            'PAYMENT_ID' => ['required'],
            'PAYMENT_NAME' => ['required'],
            'AGR_TRANS_ID' => ['required'],
            'MERCHANT_TRANS_ID' => ['required'],
            'MERCHANT_TRANS_AMOUNT' => ['required'],
            'ENVIRONMENT' => ['required', 'in:'.implode(',', Environment::values())],
            'MERCHANT_TRANS_DATA' => ['required'],
            'STATUS' => ['required'],
            'SIGN_TIME' => ['required'],
            'SIGN_STRING' => ['required'],
        ];
    }
}
