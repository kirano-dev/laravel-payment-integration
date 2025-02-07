<?php

namespace KiranoDev\LaravelPayment\Services\Payment;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use KiranoDev\LaravelPayment\Base\OrderModel;
use KiranoDev\LaravelPayment\Contracts\PaymentService;
use KiranoDev\LaravelPayment\Http\Resources\OctobankBasketResource;

class Octobank implements PaymentService
{
    private string $host;
    private string $shop_id;
    private string $secret;
    private bool $is_test;

    const HOST = 'https://secure.octo.uz';

    const ROUTES = [
        'prepare_payment' => 'prepare_payment',
    ];

    public function __construct()
    {
        $this->host = self::HOST . '/';
        $this->is_test = config('payment.octobank.is_test');
        $this->shop_id = config('payment.octobank.shop_id');
        $this->secret = config('payment.octobank.secret');
    }

    public function generateUrl(OrderModel $order): string
    {
        $response = $this->sendRequest(self::ROUTES['prepare_payment'], [
            'octo_shop_id' => $this->shop_id,
            'octo_secret' => $this->secret,
            'shop_transaction_id' => $order->transaction->id,
            'total_sum' => $order->amount,
            'currency' => 'UZS',
            'return_url' => $order->getSuccessUrl(),

            'user_data' => [
                'user_id' => $order->user->id,
                'email' => $order->user->email,
                'phone' => $order->user->phone,
            ],

            'basket' => OctobankBasketResource::collection($order->products),

            'init_time' => date('Y-m-d H:i:s'),
            'auto_capture' => true,
            'test' => $this->is_test,
        ]);

        if($this->is_test) {
            info(json_encode($response));
        }

        if($response['error'] === 0) {
            return $response['octo_pay_url'];
        } else {
            info(json_encode($response));
        }

        return config('app.url');
    }

    public function callback(Request $request): JsonResponse
    {
        // TODO: Implement callback() method.
    }

    private function sendRequest(string $route, array $data): ?array
    {
        return Http::withHeaders([
            'Content-Language' => app()->getLocale() . '-' . strtoupper(app()->getLocale()),
        ])->post($this->host . $route, $data)->json();
    }
}