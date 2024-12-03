<?php

namespace KiranoDev\LaravelPayment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use KiranoDev\LaravelPayment\Base\OrderModel;
use KiranoDev\LaravelPayment\Services\Payment\InfinityPay;

class InfinityPayFiscalizationItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $product = $this->productable;
        $info = $product->getFiscalizationInfo();

        return [
            'title' => $info['title'],
            'price' => $info['price'],
            'count' => $this->quantity,
            'code' => $info['ikpu'],
            'package_code' => $info['package_code'],
            'vat_percent' => 12,
        ];
    }
}
