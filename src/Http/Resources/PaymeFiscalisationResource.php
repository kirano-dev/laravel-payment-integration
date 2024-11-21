<?php

namespace KiranoDev\LaravelPayment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use KiranoDev\LaravelPayment\Base\OrderModel;

class PaymeFiscalisationResource extends JsonResource
{
    public function __construct(
        $resource,
        public OrderModel $order
    ) {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title[$this->order->user->language],
            'price' => $this->order->amount * 100,
            'count' => 1,
            'code' => config('services.payment.ikpu'),
            'vat_percent' => 12,
            'package_code' => config('services.payment.package_code'),
        ];
    }
}
