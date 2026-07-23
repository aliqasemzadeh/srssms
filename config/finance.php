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

];
