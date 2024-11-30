<?php

use KiranoDev\LaravelPayment\Enums\Payme\Error as PaymeError;
use KiranoDev\LaravelPayment\Enums\InfinityPay\Error as InfinityPayError;

return [
    'payme' => [
        PaymeError::INVALID_AMOUNT->value => 'Invalid amount',
        PaymeError::INVALID_ORDER_ID->value => 'Invalid order_id',
        PaymeError::INVALID_TRANSACTION->value => 'Non-existent transaction',
        PaymeError::CANT_PERFORM->value => 'Unable to perform operation',
        PaymeError::AUTH->value => 'Not authorized',
        PaymeError::ALREADY_HAS_TRANSACTION->value => 'The transaction already exists',
    ],

    'infinitypay' => [
        InfinityPayError::SUCCESS->value => 'Success',
        InfinityPayError::INVOICE_ISSUED->value => 'Invoice issued',
        InfinityPayError::TRANSACTION_CONFIRMATION->value => 'Transaction confirmation',
        InfinityPayError::SIGN_CHECK_FAILED->value => 'SIGN CHECK FAILED!',
        InfinityPayError::INCORRECT_PARAMETER_AMOUNT->value => 'Incorrect parameter amount',
        InfinityPayError::ACTION_NOT_FOUND->value => 'Action not found',
        InfinityPayError::ALREADY_PAID->value => 'Already paid',
        InfinityPayError::USER_DOES_NOT_EXIST->value => 'User does not exist',
        InfinityPayError::TRANSACTION_DOES_NOT_EXIST->value => 'Transaction does not exist',
        InfinityPayError::FAILED_TO_UPDATE_USER->value => 'Failed to update user',
        InfinityPayError::INFINITYPAY_ERROR->value => 'Error in request from InfinityPay',
        InfinityPayError::TRANSACTION_CANCELLED->value => 'Transaction cancelled',
        InfinityPayError::VENDOR_NOT_FOUND->value => 'The vendor is not found',
        InfinityPayError::TRANSACTION_TYPE_INCORRECT->value => 'Transaction type is not correct',
    ]
];