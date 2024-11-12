<?php

namespace KiranoDev\LaravelPayment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UzumCallbackRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'orderId' => ['required'],
            'orderNumber' => ['required'],
            'operationType' => ['required'],
            'operationState' => ['required'],
        ];
    }
}
