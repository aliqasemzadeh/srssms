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
        | حساب‌های کاربری (User Accounts)
        |--------------------------------------------------------------------------
        */
    'account_types' => [
        'iban' => 'شماره شبا',
        'card' => 'شماره کارت',
        'crypto' => 'آدرس کیف پول دیجیتال',
    ],

    /*
    |--------------------------------------------------------------------------
    | روش‌های واریز (Deposit Methods)
    |--------------------------------------------------------------------------
    */
    'deposit_methods' => [
        'gateway_zarinpal' => 'درگاه زرین‌پال',
        'gateway_mellat' => 'درگاه ملت',
        'receipt' => 'فیش بانکی',
        'crypto_transfer' => 'انتقال رمزارز',
    ],

    /*
    |--------------------------------------------------------------------------
    | روش‌های برداشت (Withdrawal Methods)
    |--------------------------------------------------------------------------
    */
    'withdrawal_methods' => [
        'paya_auto' => 'انتقال اتوماتیک پایا',
        'satna' => 'انتقال ساتنا',
        'card' => 'کارت به کارت',
        'manual' => 'واریز دستی توسط ادمین',
        'crypto' => 'انتقال رمزارز',
    ],

    /*
    |--------------------------------------------------------------------------
    | وضعیت‌ها (Statuses)
    |--------------------------------------------------------------------------
    */
    'statuses' => [
        'pending' => 'در انتظار',
        'processing' => 'در حال پردازش',
        'completed' => 'تکمیل شده',
        'rejected' => 'رد شده',
        'canceled' => 'لغو شده',
    ],
];
