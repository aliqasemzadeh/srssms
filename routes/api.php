<?php

use App\Http\Controllers\Sms\SendController;
use App\Http\Controllers\SmsController;
use Illuminate\Support\Facades\Route;

Route::post('/sms/webhook/{provider}/{type}', [SmsController::class, 'webhook'])
    ->name('sms.webhook');

Route::match(['get', 'post'], '/sms/send', [SendController::class, 'send'])
    ->name('api.sms.send');
