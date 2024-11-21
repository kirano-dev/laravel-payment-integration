<?php

namespace KiranoDev\LaravelPayment\Http\Requests\InfinityPay;

use KiranoDev\LaravelPayment\Enums\InfinityPay\TransactionType;

class FiscalizationRequest extends BaseRequest
{
    public function authorize(): bool {
        $request_signature = $this->SIGN_STRING;

        $signature = md5(implode(array: [
            config('payment.infinitypay.secret_key'),
            $this->AGR_TRANS_ID,
            $this->SIGN_TIME,
        ]));

        return hash_equals($request_signature, $signature);
    }

    public function rules(): array
    {
        return [
            'AGR_TRANS_ID' => ['required'],
            'TYPE' => ['required', 'in:'.implode(',', TransactionType::values())],
            'SIGN_TIME' => ['required'],
            'SIGN_STRING' => ['required'],
        ];
    }
}
