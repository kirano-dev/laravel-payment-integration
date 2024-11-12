<?php

namespace KiranoDev\LaravelPayment\Services;

use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use KiranoDev\LaravelPayment\Contracts\OrderModel;
use KiranoDev\LaravelPayment\Contracts\PaymentService;
use KiranoDev\LaravelPayment\Enums\PaymeError;
use KiranoDev\LaravelPayment\Enums\PaymentMethod;
use KiranoDev\LaravelPayment\Enums\TransactionStatus;
use KiranoDev\LaravelPayment\Http\Resources\PaymeFiscalisationResource;
use KiranoDev\LaravelPayment\Http\Resources\PaymeTransactionResource;

class Payme implements PaymentService
{
    public Request $request;
    private ?OrderModel $order;
    private ?Transaction $transaction;
    private string $login;
    private string $key;
    private string $merchant_id;

    const TRANSACTION_STATE_CREATED = 1;
    const TRANSACTION_STATE_FINISHED = 2;
    const TRANSACTION_STATE_CANCELLED = -1;
    const TRANSACTION_STATE_CANCELLED_AFTER_PERFORM = -2;

    const MIN_AMOUNT = 100;
    const MAX_AMOUNT = 999999999;

    public function __construct()
    {
        $this->login = config('services.payment.payme.username');
//        $this->key = config('services.payment.payme.test_key');
        $this->key = config('services.payment.payme.key');
        $this->merchant_id = config('services.payment.payme.merchant_id');
    }

    public function generateUrl(OrderModel $order): string
    {
        $params = [
            'm' => $this->merchant_id,
            'ac.order_id' => $order->id,
            'a' => $order->amount * 100,
            'l' => $order->user->language ?? 'ru',
        ];

        return config('services.payment.payme.host') . base64_encode(
                str_replace('&', ';', http_build_query($params))
            );
    }

    public function getError(?PaymeError $error): ?array
    {
        return $error ? [
            'code' => (int) $error->value,
            'message' => [
                'ru' => __('errors.payme.'.$error->value, locale: 'ru'),
                'en' => __('errors.payme.'.$error->value, locale: 'en'),
                'uz' => __('errors.payme.'.$error->value, locale: 'uz'),
            ]
        ] : null;
    }

    public function sendResponse(?array $result = null, PaymeError $error = null): JsonResponse
    {
        return response()->json([
            'result' => $result,
            'error' => $this->getError($error),
            'id' => $this->request->id,
        ]);
    }

    public function CheckPerformTransaction(): JsonResponse
    {
        return $this->sendResponse([
            'allow' => true,
            'detail' => [
                'receipt_type' => 0,
                'items' => PaymeFiscalisationResource::collection($this->order->products)
            ]
        ]);
    }

    public function CheckTransaction(): JsonResponse
    {
        if(!$this->transaction) {
            return $this->sendResponse(error: PaymeError::INVALID_TRANSACTION);
        }

        return $this->sendResponse([
            'create_time' => $this->transaction->extra['create_time'] ?? 0,
            'perform_time' => $this->transaction->extra['perform_time'] ?? 0,
            'cancel_time' => $this->transaction->extra['cancel_time'] ?? 0,
            'transaction' => (string) $this->transaction->id,
            'state' => $this->transaction->extra['state'] ?? -1,
            'reason' => $this->transaction->extra['reason'] ?? null,
        ]);
    }

    private function getTimestamp(): int
    {
        return round(microtime(true) * 1000);
    }

    public function CreateTransaction(array $data): JsonResponse
    {
        $amount = $data['amount'];

        if(!$this->transaction) {
            if($this->order->transaction) {
                return $this->sendResponse(error: PaymeError::ALREADY_HAS_TRANSACTION);
            }

            $this->transaction = Transaction::create([
                'type' => PaymentMethod::PAYME,
                'order_id' => $this->order->id,
                'amount' => $amount / 100,
                'extra' => [
                    'create_time' => $this->getTimestamp(),
                    'perform_time' => 0,
                    'cancel_time' => 0,
                    'receivers' => null,
                    'reason' => null,
                    'state' => 1,
                    'account' => $data['account'],
                    'payment_transaction_id' => $data['id'],
                ]
            ]);
        }

        return $this->sendResponse([
            'create_time' => $this->transaction->extra['create_time'],
            'transaction' => (string) $this->transaction->id,
            'state' => self::TRANSACTION_STATE_CREATED,
        ]);
    }

    public function PerformTransaction(): JsonResponse
    {
        if($this->transaction->status !== TransactionStatus::ACTIVE) {
            $this->transaction->update([
                'status' => TransactionStatus::ACTIVE,
                'extra->perform_time' => $this->getTimestamp(),
                'extra->state' => self::TRANSACTION_STATE_FINISHED,
            ]);
            $this->transaction->order->update([
                'is_payed' => true
            ]);
        }

        return $this->sendResponse([
            'transaction' => (string) $this->transaction->id,
            'perform_time' => $this->transaction->extra['perform_time'],
            'state' => self::getTransactionState($this->transaction),
        ]);
    }

    public function CancelTransaction(array $data): JsonResponse
    {
        if($this->transaction->status !== TransactionStatus::CANCELLED) {
            $this->transaction->update([
                'status' => TransactionStatus::CANCELLED,
                'extra->cancel_time' => $this->getTimestamp(),
                'extra->reason' => $data['reason'],
                'extra->state' => ($this->transaction->extra['state'] ?? -1) === self::TRANSACTION_STATE_FINISHED
                    ? self::TRANSACTION_STATE_CANCELLED_AFTER_PERFORM
                    : self::TRANSACTION_STATE_CANCELLED,
            ]);
        }

        return $this->sendResponse([
            'transaction' => (string) $this->transaction->id,
            'cancel_time' => $this->transaction->extra['cancel_time'],
            'state' => $this->transaction->extra['state'] ?? -1,
        ]);
    }

    public function ChangePassword(): JsonResponse
    {
        return $this->sendResponse(['success' => true]);
    }

    public function GetStatement(array $data): JsonResponse
    {
        $transactions = Transaction::where('type', PaymentMethod::PAYME)
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(extra, '$.create_time')) <= {$data['to']} AND JSON_UNQUOTE(JSON_EXTRACT(extra, '$.create_time')) >= {$data['from']}")
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->sendResponse([
            'transactions' => PaymeTransactionResource::collection($transactions),
        ]);
    }

    public function getTransactionState(mixed $transaction): int
    {
        return match ($transaction->status) {
            TransactionStatus::INACTIVE => self::TRANSACTION_STATE_CREATED,
            TransactionStatus::ACTIVE => self::TRANSACTION_STATE_FINISHED,
            TransactionStatus::CANCELLED => self::TRANSACTION_STATE_CANCELLED,
        };
    }

    public function getTransaction(string $payme_transaction_id): ?Transaction
    {
        return Transaction::whereRaw("JSON_UNQUOTE(JSON_EXTRACT(extra, '$.payment_transaction_id')) = '$payme_transaction_id'")->first();
    }

    public function validateAuth(Request $request): bool
    {
        $authorizationHeader = $request->header('Authorization');
        if(!$authorizationHeader) return false;

        $key_ = explode(' ', $authorizationHeader);

        if(isset($key_[1])) {
            $key = $key_[1];

            if($key === base64_encode($this->login . ":" . $this->key)) {
                return true;
            }
        }

        return false;
    }

    public function validateParams(array $data): ?PaymeError
    {


        if(isset($data['id'])) {
            $this->transaction = self::getTransaction($data['id']);
        }

        if(isset($data['account']['order_id'])) {
            $this->order = app(OrderModel::class)::find($data['account']['order_id']);

            if(!$this->order) {
                return PaymeError::INVALID_ORDER_ID;
            }
            if($this->order->amount !== $data['amount'] / 100) {
                return PaymeError::INVALID_AMOUNT;
            }
        }

        if(isset($data['amount']) && ($data['amount'] / 100 < self::MIN_AMOUNT || $data['amount'] / 100 > self::MAX_AMOUNT)) {
            return PaymeError::INVALID_AMOUNT;
        }

        return null;
    }

    public function callback(Request $request): JsonResponse
    {
        $this->request = $request;

        $auth_valid = self::validateAuth($request);
        $params_valid = self::validateParams($request->params);

        if($params_valid !== null) return $this->sendResponse(
            error: $params_valid
        );

        if (!$auth_valid) {
            return $this->sendResponse(
                error: PaymeError::AUTH
            );
        }

        return self::{$request->get('method')}($request->get('params'));
    }
}
