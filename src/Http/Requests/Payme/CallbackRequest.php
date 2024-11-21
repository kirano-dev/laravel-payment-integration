<?php

namespace KiranoDev\LaravelPayment\Http\Requests\Payme;

use Illuminate\Foundation\Http\FormRequest;
use KiranoDev\LaravelPayment\Enums\Payme\Method;

class
CallbackRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'method' => ['required', 'string', 'in:'.implode(',', Method::values()),],
            'params' => ['required', 'array'],
            'id' => ['required', 'numeric'],
        ];
    }
}
