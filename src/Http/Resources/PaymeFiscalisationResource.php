<?php

namespace KiranoDev\LaravelPayment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use KiranoDev\LaravelPayment\Base\OrderModel;

class PaymeFiscalisationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $product = $this->productable;
        $info = $product->getFiscalizationInfo();

        return [
            'title' => $info['title'],
            'price' => $info['price'] * 100,
            'count' => $this->pivot->quantity,
            'code' => $info['ikpu'],
            'vat_percent' => 12,
            'package_code' => $info['package_code'],
        ];
    }
}
