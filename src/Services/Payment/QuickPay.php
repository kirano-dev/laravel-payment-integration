<?php

namespace KiranoDev\LaravelPayment\Services\Payment;

use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use KiranoDev\LaravelPayment\Base\OrderModel;
use KiranoDev\LaravelPayment\Contracts\PaymentService;
use KiranoDev\LaravelPayment\Enums\QuickPay\PaymentCode;
use KiranoDev\LaravelPayment\Enums\QuickPay\PaymentStatus;
use KiranoDev\LaravelPayment\Enums\QuickPay\PaymentType;

class QuickPay implements PaymentService
{
    const HOST = 'https://api.quickpay.uz/';

    const CREATE_ROUTE = 'invoice/create';

    private string $api_key;
    private string $shop_id;

    public function __construct()
    {
        $this->api_key = config('payment.quickpay.api_key');
        $this->shop_id = config('payment.quickpay.shop_id');
    }

    private function sendRequest(string $route, array $data): Response
    {
        return Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'x-api-key' => $this->api_key,
        ])->post(self::HOST . $route, $data);
    }

    public function generateUrl(OrderModel $order): string
    {
        $response = $this->sendRequest(self::CREATE_ROUTE, [
            'amount' => $order->amount,
            'order_id' => $order->id,
            'shop_id' => $this->shop_id,
            'hook_url' => route('payment.quickpay'),
            'fail_url' => config('app.url'),
            'success_url' => config('payment.quickpay.success_url'),
        ]);

        if($response->ok()) {
            return $response->json()['data']['url'];
        } else {
            info('QuickPay error while creating payment');
            info($response->getStatusCode());
            info(json_encode($response->json()));
        }

        return config('app.url');
    }

    public function callback(Request $request): JsonResponse
    {
        $order = app(OrderModel::class)->find($request->order_id);

        if(!$order) return response()->json();

        if(
            $request->status === PaymentStatus::SUCCESS->value &&
            $request->type === PaymentType::PAYMENT->value &&
            $request->code === PaymentCode::SUCCESS->value
        ) {
            $order->update([
                'is_payed' => true,
            ]);
        }

        return response()->json();
    }
}