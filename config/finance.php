<?php

use App\Models\Finance\Currency;
use App\Models\Finance\Wallet;
use App\Models\User;

return [

    /*
    |--------------------------------------------------------------------------
    | Transaction morph references
    |--------------------------------------------------------------------------
    |
    | Models that can be linked to a transaction via the morph reference.
    | Key is stored in the UI select; model is used for morph type resolution.
    |
    */
    'transaction_references' => [
        'user' => [
            'model' => User::class,
            'label' => 'general.user',
        ],
        'wallet' => [
            'model' => Wallet::class,
            'label' => 'general.wallet',
        ],
        'currency' => [
            'model' => Currency::class,
            'label' => 'general.currency',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User account types
    |--------------------------------------------------------------------------
    |
    | Account types a user can register (IBAN, card, crypto wallet, etc.).
    | Values are translation keys resolved with __().
    |
    */
    'account_types' => [
        'iban' => 'general.account_types.iban',
        'card' => 'general.account_types.card',
        'crypto' => 'general.account_types.crypto',
    ],

    /*
    |--------------------------------------------------------------------------
    | Deposit methods
    |--------------------------------------------------------------------------
    |
    | Available ways to deposit funds into a wallet.
    | Values are translation keys resolved with __().
    |
    */
    'deposit_methods' => [
        'gateway_zarinpal' => 'general.deposit_methods.gateway_zarinpal',
        'gateway_mellat' => 'general.deposit_methods.gateway_mellat',
        'receipt' => 'general.deposit_methods.receipt',
        'crypto_transfer' => 'general.deposit_methods.crypto_transfer',
    ],

    /*
    |--------------------------------------------------------------------------
    | Withdrawal methods
    |--------------------------------------------------------------------------
    |
    | Available ways to withdraw funds from a wallet.
    | Values are translation keys resolved with __().
    |
    */
    'withdrawal_methods' => [
        'paya_auto' => 'general.withdrawal_methods.paya_auto',
        'satna' => 'general.withdrawal_methods.satna',
        'card' => 'general.withdrawal_methods.card',
        'manual' => 'general.withdrawal_methods.manual',
        'crypto' => 'general.withdrawal_methods.crypto',
    ],

    /*
    |--------------------------------------------------------------------------
    | Statuses
    |--------------------------------------------------------------------------
    |
    | Shared status labels for finance-related records.
    | Values are translation keys resolved with __().
    |
    */
    'statuses' => [
        'approved' => 'general.statuses.approved',
        'pending' => 'general.statuses.pending',
        'processing' => 'general.statuses.processing',
        'completed' => 'general.statuses.completed',
        'rejected' => 'general.statuses.rejected',
        'canceled' => 'general.statuses.canceled',
    ],
];
