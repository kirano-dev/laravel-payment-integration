<?php

namespace KiranoDev\LaravelPayment\Services;

use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use KiranoDev\LaravelPayment\Contracts\OrderModel;
use KiranoDev\LaravelPayment\Contracts\PaymentService;
use KiranoDev\LaravelPayment\Enums\ClickError;
use KiranoDev\LaravelPayment\Enums\PaymentMethod;
use KiranoDev\LaravelPayment\Enums\TransactionStatus;

class Click implements PaymentService
{
    public Request $request;
    private bool $with_split;
    private string $secret_key;
    private int $prepare_action;
    private int $complete_action;

    const MIN_AMOUNT = 100;
    const MAX_AMOUNT = 100000000;

    public function __construct()
    {
        $this->with_split = config('services.payment.click.with_split', false);
        $this->secret_key = config('services.payment.click.secret_key');
        $this->prepare_action = $this->with_split ? 1 : 0;
        $this->complete_action = $this->with_split ? 2 : 1;
    }

    public function generateUrl($order): string
    {
        $params = [
            'service_id' => config('services.payment.click.service_id'),
            'merchant_id' => config('services.payment.click.merchant_id'),
            'merchant_user_id' => $order->user_id,
            'amount' => $order->amount,
            'transaction_param' => $order->id,
        ];

        return config('services.payment.click.host') . '?' . http_build_query($params);
    }

    private function validateSignature($params): bool
    {
        $data = $this->with_split ? [
            $params['click_paydoc_id'],
            $params['attempt_trans_id'],
            $params['service_id'],
            config('services.payment.click.secret_key'),
            implode(array: array_values($params['params'])),
        ] : [
            $params['click_trans_id'],
            $params['service_id'],
            $this->secret_key,
            $params['merchant_trans_id'],
        ];

        if(!$this->with_split) {
            if((int) $params['action'] === $this->complete_action) {
                $data[] = $params['merchant_prepare_id'];
            }

            $data[] = $params['amount'];
        }

        $data[] = $params['action'];
        $data[] = $params['sign_time'];

        $sign = md5(implode('', $data));

        return $sign === $params['sign_string'];
    }

    private function validateParams($action, $data): ?JsonResponse
    {
        if($action === null) {
            return $this->makeErrorResponse(ClickError::INVALID_ACTION);
        }

        if(!$this->validateSignature($data)) {
            return $this->makeErrorResponse(ClickError::INVALID_SIGN);
        }

        if($this->with_split) {
            if($data['params']['amount'] < self::MIN_AMOUNT || $data['params']['amount'] > self::MAX_AMOUNT) {
                return $this->makeErrorResponse(ClickError::INVALID_AMOUNT);
            }

            if(!app(OrderModel::class)::find($data['params']['transaction_param'])) {
                return $this->makeErrorResponse(ClickError::INVALID_USER);
            }
        } else {
            if((int) $data['amount'] < self::MIN_AMOUNT || (int) $data['amount'] > self::MAX_AMOUNT) {
                return $this->makeErrorResponse(ClickError::INVALID_AMOUNT);
            }

            if(!app(OrderModel::class)::find($data['merchant_trans_id'])) {
                return $this->makeErrorResponse(ClickError::INVALID_USER);
            }
        }


        if(isset($data['merchant_prepare_id'])) {
            $transaction = Transaction::find($data['merchant_prepare_id']);
        } else $transaction = null;

        if($action === 'prepare') {
            if($transaction) {
                if($transaction->status === TransactionStatus::CANCELLED) {
                    return $this->makeErrorResponse(ClickError::TRANSACTION_CANCELLED);
                }

                return $this->makeErrorResponse(ClickError::ALREADY_PAID);
            }
        } else if (!$transaction) {
            return $this->makeErrorResponse(ClickError::INVALID_TRANSACTION);
        } else if ($transaction->amount !== (int) ($this->with_split ? $data['params']['amount'] : $data['amount'])) {
            return $this->makeErrorResponse(ClickError::INVALID_AMOUNT);
        }

        if($this->with_split) {
            if($data['error'] !== 0) {
                return $this->makeErrorResponse(ClickError::INVALID_REQUEST);
            }
        }

        return null;
    }

    public function prepare(array $data): JsonResponse
    {
        $transaction = Transaction::create($this->with_split ? [
            'order_id' => $data['params']['transaction_param'],
            'amount' => $data['params']['amount'],
            'type' => PaymentMethod::CLICK,
            'extra' => [
                'click_paydoc_id' => $data['click_paydoc_id'],
                'attempt_trans_id' => $data['attempt_trans_id'],
                'service_id' => $data['service_id'],
                'sign_time' => $data['sign_time'],
            ],
        ] : [
            'order_id' => $data['merchant_trans_id'],
            'amount' => $data['amount'],
            'type' => PaymentMethod::CLICK,
            'extra' => [
                'click_trans_id' => $data['click_trans_id'],
                'click_paydoc_id' => $data['click_paydoc_id'],
                'service_id' => $data['service_id'],
                'sign_time' => $data['sign_time'],
            ],
        ]);

        return $this->makeResponse($transaction, $data['action']);
    }

    public function complete(array $data): JsonResponse
    {
        $transaction = Transaction::find($data['merchant_prepare_id']);
        $transaction->update([
            'status' => TransactionStatus::ACTIVE
        ]);
        $transaction->order->update([
            'is_payed' => true
        ]);

        return $this->makeResponse($transaction, $data['action']);
    }

    public function makeResponse(
        Transaction $transaction,
        int         $action,
    ): JsonResponse
    {
        $type = $this->with_split
            ? ($action === 1 ? 'prepare' : 'complete')
            : ($action === 1 ? 'complete' : 'prepare');

        $transaction->update([
            'extra' => $transaction->extra + [
                'attempt_trans_id' => time(),
            ]
        ]);

        $response = $this->with_split ? [
            'click_paydoc_id' => $transaction->extra['click_paydoc_id'],
            'attempt_trans_id ' => $transaction->extra['attempt_trans_id'],
            'params ' => [],
        ] : [
            'click_trans_id' => $transaction->extra['click_trans_id'],
            'merchant_trans_id' => $transaction->order_id,
        ];

        $response["merchant_{$type}_id"] = $transaction->id;

        return response()->json($response + $this->makeError(ClickError::SUCCESS));
    }

    public function makeErrorResponse(
        ClickError $error
    ): JsonResponse
    {
        return response()->json($this->makeError($error));
    }

    static public function makeError(
        ClickError $error
    ): array
    {
        return [
            'error' => $error->value,
            'error_note' => match($error) {
                ClickError::SUCCESS => 'Success',
                ClickError::INVALID_SIGN => 'SIGN CHECK FAILED!',
                ClickError::INVALID_AMOUNT => 'Incorrect parameter amount',
                ClickError::INVALID_ACTION => 'Action not found',
                ClickError::ALREADY_PAID => 'Already paid',
                ClickError::INVALID_USER => 'User does not exist',
                ClickError::INVALID_TRANSACTION => 'Transaction does not exist',
                ClickError::FAILED_UPDATE_USER => 'Failed to update user',
                ClickError::INVALID_REQUEST => 'Error in request from click',
                ClickError::TRANSACTION_CANCELLED => 'Transaction cancelled',
            },
        ];
    }

    public function getAction(string $action): ?string
    {
        return match((int) $action) {
            $this->prepare_action => 'prepare',
            $this->complete_action => 'complete',
            default => null,
        };
    }

    public function callback(Request $request): JsonResponse
    {
        $this->request = $request;

        $action = $this->getAction($request->action);
        $errors = $this->validateParams($action, $request->all());

        if($errors !== null) return $errors;

        return $this->$action($request->all());
    }
}
