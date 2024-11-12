<?php

namespace KiranoDev\LaravelPayment\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use KiranoDev\LaravelPayment\Contracts\OrderModel;
use KiranoDev\LaravelPayment\Contracts\PaymentService;
use KiranoDev\LaravelPayment\Enums\PaymentMethod;
use KiranoDev\LaravelPayment\Http\Resources\UzumItemResource;

class Uzum implements PaymentService
{
    public Request $request;
    private string $terminal_id;
    private string $api_key;
    private string $host;

    const HOST = 'https://checkout-key.inplat-tech.com/';
    const TEST_HOST = 'https://test-chk-api.uzumcheckout.uz/';

    const SUCCESS_OPERATION_STATE = 'SUCCESS';
    const COMPLETE_OPERATION_TYPE = 'COMPLETE';
    const ORDER_STATUS_COMPLETED = 'COMPLETED';

    const ROUTE_REGISTER_PAYMENT = 'register';
    const ROUTE_GET_ORDER_STATUS = 'getOrderStatus';

    public function __construct()
    {
        $this->terminal_id = config('services.payment.uzum.terminal_id');
        $this->api_key = config('services.payment.uzum.api_key');
        $this->host = config('laravel-payment::uzum.' . (config('laravel-payment::uzum.is_test', true)
            ? self::TEST_HOST
            : self::HOST
        )) . 'api/v1/payment/';
    }

    public function generateUrl(OrderModel $order): string
    {
        $data = [
            'successUrl' => route('profile'),
            'failureUrl' => route('profile'),
            'viewType' => 'REDIRECT',
            'clientId' => (string) auth()->id(),
            'currency' => 860,
            'orderNumber' => (string) $order->id,
            'sessionTimeoutSecs' => 1800,
            'amount' => $order->amount * 100,
            'merchantParams' => [
                'cart' => [
                    'cartId' => request()->ip(),
                    'receiptType' => 'PURCHASE',
                    'items' => UzumItemResource::collection(
                        $order->getProducts()
                    ),
                    'total' => $order->amount * 100,
                ],
            ],
            'paymentParams' => [
                'operationType' => 'PAYMENT',
                'payType' => 'ONE_STEP',
                'phoneNumber' => auth()->user()->phone,
            ]
        ];

        $response = $this->sendRequest(self::ROUTE_REGISTER_PAYMENT, $data);

        if (isset($response['result']['orderId'])) {
            $order->transaction()->create([
                'type' => PaymentMethod::UZUM,
                'amount' => $order->amount,
                'extra' => [
                    'orderId' => $response['result']['orderId'],
                ]
            ]);

            return $response['result']['paymentRedirectUrl'];
        }

        return route('profile');
    }

    private function sendRequest(string $route, array $data): ?array
    {
        return Http::withHeaders([
            'X-Terminal-Id' => $this->terminal_id,
            'X-API-Key' => $this->api_key,
            'Content-Language' => app()->getLocale() . '-' . strtoupper(app()->getLocale()),
        ])->post($this->host . $route, $data)->json();
    }

    private function getOrderStatus(string $orderId): array
    {
        $data = [
            'orderId' => $orderId,
        ];

        return $this->sendRequest(self::ROUTE_GET_ORDER_STATUS, $data);
    }

    private function performOrder(): void
    {
        if (
            $this->request->operationState === self::SUCCESS_OPERATION_STATE &&
            $this->request->operationType === self::COMPLETE_OPERATION_TYPE
        ) {
            $order = app(OrderModel::class)::find($this->request->orderNumber);

            if ($order) {
                $response = $this->getOrderStatus($this->request->orderId);

                if (isset($response['result']['status'])) {
                    if ($response['result']['status'] === self::ORDER_STATUS_COMPLETED) {
                        $order->update([
                            'is_payed' => true
                        ]);
                    }
                }
            }
        }
    }

    public function callback(Request $request): JsonResponse
    {
        $this->request = $request;
        $this->performOrder();

        return response()->json('ok');
    }
}
