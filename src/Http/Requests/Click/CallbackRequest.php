<?php

namespace KiranoDev\LaravelPayment\Http\Requests\Click;

use Illuminate\Foundation\Http\FormRequest;
use KiranoDev\LaravelPayment\Enums\Payme\Method;

class CallbackRequest extends FormRequest
{
    public function rules(): array
    {
        return [

        ];
    }
}
