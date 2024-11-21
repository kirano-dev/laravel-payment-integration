<?php

namespace KiranoDev\LaravelPayment\Http\Requests\InfinityPay;

use KiranoDev\LaravelPayment\Enums\InfinityPay\PaymentStatus;

class NotifyRequest extends BaseRequest
{
    public function authorize(): bool {
        $request_signature = $this->SIGN_STRING;

        $signature = md5(implode(array: [
            config('payment.infinitypay.secret_key'),
            $this->AGR_TRANS_ID,
            $this->VENDOR_TRANS_ID,
            $this->STATUS,
            $this->SIGN_TIME,
        ]));

        return hash_equals($request_signature, $signature);
    }

    public function rules(): array
    {
        return [
            'AGR_TRANS_ID' => ['required'],
            'VENDOR_TRANS_ID' => ['required'],
            'STATUS' => ['required', 'in:'.implode(',', PaymentStatus::values())],
            'SIGN_TIME' => ['required'],
            'SIGN_STRING' => ['required'],
        ];
    }
}
