<?php

namespace KiranoDev\LaravelPayment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UzumItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->name,
            'productId' => (string) $this->id,
            'quantity' => $this->pivot->count,
            'unitPrice' => $this->price * 100,
            'total' => $this->price * 100 * $this->pivot->count,
            'receiptParams' => [
                'spic' => $this->ikpu,
                'packageCode' => $this->package_code,
                'vatPercent' => 12,
                'TIN' => config('services.payment.uzum.inn'),
            ]
        ];
    }
}
