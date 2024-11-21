<?php

namespace KiranoDev\LaravelPayment\Services\Payment;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use KiranoDev\LaravelPayment\Base\OrderModel;
use KiranoDev\LaravelPayment\Contracts\PaymentService;
use KiranoDev\LaravelPayment\Enums\InfinityPay\Error;
use KiranoDev\LaravelPayment\Enums\InfinityPay\PaymentStatus;
use KiranoDev\LaravelPayment\Enums\TransactionStatus;

class InfinityPay implements PaymentService
{
    const HOST = 'https://gate.infinitypay.uz/pay/';
    const TEST_HOST = 'https://gate.infinitypay.uz/sandbox/';

    private string $vendor_id;
    private string $secret_key;
    private string $success_url;
    private string $host;
    private int $time;
    private ?OrderModel $order;

    public function __construct()
    {
        $this->vendor_id = config('payment.quickpay.vendor_id');
        $this->secret_key = config('payment.infinitypay.secret_key');
        $this->success_url = config('payment.infinitypay.success_url');
        $this->host = config('payment.infinitypay.is_test')
            ? self::TEST_HOST
            : self::HOST;
    }

    public function generateUrl(OrderModel $order): string
    {
        $this->order = $order;
        $this->time = round(microtime(true) * 1000);

        $params = [
            'VENDOR_ID' => $this->vendor_id,
            'MERCHANT_TRANS_ID' => $order->id,
            'MERCHANT_TRANS_AMOUNT' => $order->amount,
            'MERCHANT_CURRENCY' => 'sum',
            'MERCHANT_TRANS_NOTE' => 'test comment',
            'MERCHANT_TRANS_RETURN_URL' => $this->success_url,
            'SIGN_TIME' => $this->time,
            'SIGN_STRING' => $this->generateSignature(),
        ];

        return "$this->host?" . http_build_query($params);
    }

    private function generateSignature(): string
    {
        return md5(implode(array: [
            $this->secret_key,
            $this->vendor_id,
            $this->order->id,
            $this->order->amount,
            'sum',
            $this->time,
        ]));
    }

    public function sendResponse(
        Error $error = Error::SUCCESS,
        array $extra = []
    ): JsonResponse
    {
        return response()->json([
            'ERROR' => $error->value,
            'ERROR_NOTE' => __('payment::infinitypay.'.$error->value),
            ...$extra,
        ]);
    }

    private function checkOrder(): ?JsonResponse
    {
        if(!$this->order) {
            return $this->sendResponse(
                Error::TRANSACTION_DOES_NOT_EXIST
            );
        }

        return null;
    }

    private function checkUser(): ?JsonResponse
    {
        if(!$this->order->user) {
            return $this->sendResponse(
                Error::USER_DOES_NOT_EXIST
            );
        }

        return null;
    }

    private function checkAmount(Request $request): ?JsonResponse
    {
        if($this->order->amount !== $request->MERCHANT_TRANS_AMOUNT) {
            return $this->sendResponse(
                Error::INCORRECT_PARAMETER_AMOUNT
            );
        }

        return null;
    }

    private function getOrder(Request $request, string $key = 'MERCHANT_TRANS_ID'): void
    {
        $this->order = app(OrderModel::class)->find($request->$key);
    }

    public function info(Request $request): JsonResponse
    {
        $this->getOrder($request);

        $checkOrder = $this->checkOrder();

        if($checkOrder) return $checkOrder;

        $this->order->transaction()->create();

        return $this->sendResponse(extra: [
            'PARAMETERS' => []
        ]);
    }

    public function checkAlreadyPayed(): ?JsonResponse
    {
        if($this->order->is_payed) {
            return $this->sendResponse(
                Error::ALREADY_PAID
            );
        }

        return null;
    }

    public function checkOrderCancelled(): ?JsonResponse
    {
        if($this->order->transaction->status === TransactionStatus::CANCELLED) {
            return $this->sendResponse(
                Error::TRANSACTION_CANCELLED
            );
        }

        return null;
    }

    public function checkVendor(Request $request): ?JsonResponse
    {
        if($this->vendor_id !== $request->VENDOR_ID) {
            return $this->sendResponse(
                Error::VENDOR_NOT_FOUND
            );
        }

        return null;
    }

    public function pay(Request $request): JsonResponse
    {
        $this->getOrder($request);

        return $this->checkOrder() ??
            $this->checkAmount($request) ??
            $this->checkOrderCancelled() ??
            $this->checkAlreadyPayed() ??
            $this->checkUser() ??
            $this->checkVendor($request) ??
            $this->sendResponse(extra: [
                'VENDOR_TRANS_ID' => $this->order->id,
            ]);
    }

    public function notify(Request $request): JsonResponse
    {
        $this->getOrder($request, 'VENDOR_TRANS_ID');

        $checkOrder = $this->checkOrder();

        if($checkOrder) return $checkOrder;

        switch($request->STATUS) {
            case PaymentStatus::PAYED->value:
                $this->order->update([
                    'is_payed' => true,
                ]);

                $this->order->transaction->update([
                    'status' => TransactionStatus::ACTIVE,
                ]);

                break;
            case PaymentStatus::CANCELLED->value:
                $this->order->transaction->update([
                    'status' => TransactionStatus::CANCELLED,
                ]);

                break;
        }

        return $this->sendResponse();
    }

    public function cancel(Request $request): JsonResponse
    {
        $this->getOrder($request, 'VENDOR_TRANS_ID');

        return $this->checkOrder() ??
            $this->checkOrderCancelled() ??
            $this->sendResponse();
    }

    public function callback(Request $request): JsonResponse
    {
        return response()->json();
    }
}