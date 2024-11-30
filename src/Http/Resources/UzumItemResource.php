<?php

namespace KiranoDev\LaravelPayment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UzumItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $product = $this->productable;
        $info = $product->getFiscalizationInfo();
        $title = $info['title'];

        if (strlen($title) > 63) {
            $title = substr($title, 0, 60) . '...';
        }

        return [
            'title' => $title,
            'productId' => (string) $info['id'],
            'quantity' => $this->pivot->quantity,
            'unitPrice' => $info['price'] * 100,
            'total' => $info['price'] * 100 * $this->pivot->quantity,
            'receiptParams' => [
                'spic' => $info['ikpu'],
                'packageCode' => $info['package_code'],
                'vatPercent' => 12,
                'TIN' => config('payment.uzum.inn'),
            ]
        ];
    }
}
