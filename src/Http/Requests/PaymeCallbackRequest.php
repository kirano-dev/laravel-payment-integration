<?php

namespace KiranoDev\LaravelPayment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use KiranoDev\LaravelPayment\Enums\PaymeMethod;

class PaymeCallbackRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'method' => ['required', 'string', 'in:'.implode(',', PaymeMethod::values()),],
            'params' => ['required', 'array'],
            'id' => ['required', 'numeric'],
        ];
    }
}
