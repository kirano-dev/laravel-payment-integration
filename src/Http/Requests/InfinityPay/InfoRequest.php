<?php

namespace KiranoDev\LaravelPayment\Http\Requests\InfinityPay;

class InfoRequest extends BaseRequest
{
    public function authorize(): bool {
        $request_signature = $this->SIGN_STRING;

        $signature = md5(implode('', [
            config('payment.infinitypay.secret_key'),
            $this->MERCHANT_TRANS_ID,
            $this->SIGN_TIME,
        ]));

        return hash_equals($request_signature, $signature);
    }

    public function rules(): array
    {
        return [
            'MERCHANT_TRANS_ID' => ['required'],
            'SIGN_TIME' => ['required'],
            'SIGN_STRING' => ['required'],
        ];
    }
}
