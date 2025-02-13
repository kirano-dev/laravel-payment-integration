<?php

namespace KiranoDev\LaravelPayment\Services\Payment;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use KiranoDev\LaravelPayment\Base\OrderModel;
use KiranoDev\LaravelPayment\Contracts\PaymentService;
use KiranoDev\LaravelPayment\Enums\TransactionStatus;
use KiranoDev\LaravelPayment\Http\Resources\OctobankBasketResource;
use KiranoDev\LaravelPayment\Models\Transaction;

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
        $transaction = $order->transaction()->create();

        $response = $this->sendRequest(self::ROUTES['prepare_payment'], [
            'octo_shop_id' => intval($this->shop_id),
            'octo_secret' => $this->secret,
            'shop_transaction_id' => "$transaction->id",
            'total_sum' => $order->amount,
            'currency' => 'UZS',
            'description' => config('app.name', 'Payment'),
            'return_url' => $order->getSuccessUrl(),

//            'user_data' => [
//                'user_id' => $order->user->id,
//                'email' => $order->user->email,
//                'phone' => $order->user->phone,
//            ],

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

    private function validateSignature(Request $request): bool
    {
        return sha1(sha1($this->secret . $request->hask_key) . $request->octo_payment_UUID . $request->status) === $request->signature;
    }

    private function sendResponse(string $message): JsonResponse
    {
        return response()->json('Invalid signature');
    }

    public function callback(Request $request): JsonResponse
    {
        if(!$this->validateSignature($request)) {
            return $this->sendResponse('Invalid signature');
        }

        $transaction = Transaction::find($request->shop_transaction_id);

        if(!$transaction) {
            return $this->sendResponse('Invalid transaction');
        }

        $transaction->update([
            'status' => TransactionStatus::ACTIVE,
        ]);

        $transaction->order->update([
            'is_payed' => true
        ]);

        return $this->sendResponse('ok');
    }

    private function sendRequest(string $route, array $data): ?array
    {
        return Http::post($this->host . $route, $data)->json();
    }
}