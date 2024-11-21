<?php

namespace KiranoDev\LaravelPayment\Http\Requests\InfinityPay;

class StatementRequest extends BaseRequest
{
    public function authorize(): bool {
        $request_signature = $this->SIGN_STRING;

        $signature = md5(implode(array: [
            config('payment.infinitypay.secret_key'),
            $this->FROM,
            $this->TO,
            $this->SIGN_TIME,
        ]));

        return hash_equals($request_signature, $signature);
    }

    public function rules(): array
    {
        return [
            'FROM' => ['required'],
            'TO' => ['required'],
            'SIGN_TIME' => ['required'],
            'SIGN_STRING' => ['required'],
        ];
    }
}
