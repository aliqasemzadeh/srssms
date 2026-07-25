<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Sms\SendController;
use App\Http\Controllers\SmsController;
use App\Settings\ContactSettings;
use App\Settings\GeneralSettings;
use App\Settings\SocialSettings;
use App\Settings\WelcomePageSettings;
use Illuminate\Support\Facades\Route;

Route::get('/', function (
    GeneralSettings $general,
    WelcomePageSettings $welcome,
    ContactSettings $contact,
    SocialSettings $social,
) {
    return view('welcome', [
        'general' => $general,
        'welcome' => $welcome,
        'contact' => $contact,
        'social' => $social,
    ]);
})->name('home');

Route::match(['get', 'post'], '/payment/callback/{deposit}', [PaymentController::class, 'callback'])
    ->name('payment.callback');

Route::post('/sms/webhook/{provider}/{type}', [SmsController::class, 'webhook'])
    ->name('sms.webhook');

Route::match(['get', 'post'], '/api/sms/send', [SendController::class, 'send'])
    ->name('api.sms.send');

Route::middleware('auth')->group(function () {
    Route::match(['get', 'post'], '/payment/pay/{deposit}', [PaymentController::class, 'pay'])
        ->name('payment.pay');

    // Administrator Panel
    Route::middleware('administrator')->group(function () {
        Route::livewire('/panels/administrator/dashboard', 'pages::panels.administrator.dashboard.index')
            ->name('panels.administrator.dashboard.index');

        Route::middleware('permission:user-management.user.view')->group(function () {
            Route::livewire('/panels/administrator/user-management/users', 'pages::panels.administrator.user-management.user.index')
                ->name('panels.administrator.user-management.user.index');
            Route::livewire('/panels/administrator/user-management/users/{user}/wallets', 'pages::panels.administrator.user-management.user.wallet.index')
                ->name('panels.administrator.user-management.user.wallet.index');
            Route::livewire('/panels/administrator/user-management/users/{user}/wallets/{wallet}/transactions', 'pages::panels.administrator.user-management.user.wallet.transaction.index')
                ->scopeBindings()
                ->name('panels.administrator.user-management.user.wallet.transaction.index');
            Route::livewire('/panels/administrator/user-management/users/{user}/wallets/{wallet}/deposits', 'pages::panels.administrator.user-management.user.wallet.deposit.index')
                ->scopeBindings()
                ->name('panels.administrator.user-management.user.wallet.deposit.index');
            Route::livewire('/panels/administrator/user-management/users/{user}/wallets/{wallet}/withdrawals', 'pages::panels.administrator.user-management.user.wallet.withdrawal.index')
                ->scopeBindings()
                ->name('panels.administrator.user-management.user.wallet.withdrawal.index');
            Route::livewire('/panels/administrator/user-management/users/{user}/user-accounts', 'pages::panels.administrator.user-management.user.user-account.index')
                ->name('panels.administrator.user-management.user.user-account.index');
        });

        Route::middleware('permission:user-management.role.view')->group(function () {
            Route::livewire('/panels/administrator/user-management/roles', 'pages::panels.administrator.user-management.role.index')
                ->name('panels.administrator.user-management.role.index');
        });

        Route::middleware('permission:user-management.permission.view')->group(function () {
            Route::livewire('/panels/administrator/user-management/permissions', 'pages::panels.administrator.user-management.permission.index')
                ->name('panels.administrator.user-management.permission.index');
        });

        Route::middleware('permission:finance-management.currency.view')->group(function () {
            Route::livewire('/panels/administrator/finance-management/currencies', 'pages::panels.administrator.finance-management.currency.index')
                ->name('panels.administrator.finance-management.currency.index');
        });

        Route::middleware('permission:finance-management.wallet.view')->group(function () {
            Route::livewire('/panels/administrator/finance-management/wallets', 'pages::panels.administrator.finance-management.wallet.index')
                ->name('panels.administrator.finance-management.wallet.index');
        });

        Route::middleware('permission:finance-management.transaction.view')->group(function () {
            Route::livewire('/panels/administrator/finance-management/transactions', 'pages::panels.administrator.finance-management.transaction.index')
                ->name('panels.administrator.finance-management.transaction.index');
        });

        Route::middleware('permission:finance-management.deposit.view')->group(function () {
            Route::livewire('/panels/administrator/finance-management/deposits', 'pages::panels.administrator.finance-management.deposit.index')
                ->name('panels.administrator.finance-management.deposit.index');
        });

        Route::middleware('permission:finance-management.withdrawal.view')->group(function () {
            Route::livewire('/panels/administrator/finance-management/withdrawals', 'pages::panels.administrator.finance-management.withdrawal.index')
                ->name('panels.administrator.finance-management.withdrawal.index');
        });

        Route::middleware('permission:finance-management.payment.view')->group(function () {
            Route::livewire('/panels/administrator/finance-management/payments', 'pages::panels.administrator.finance-management.payment.index')
                ->name('panels.administrator.finance-management.payment.index');
        });

        Route::middleware('permission:finance-management.payment.setting.view')->group(function () {
            Route::livewire('/panels/administrator/finance-management/payments/settings', 'pages::panels.administrator.finance-management.payment.setting.index')
                ->name('panels.administrator.finance-management.payment.setting.index');
        });

        Route::middleware('permission:sms-management.provider.view')->group(function () {
            Route::livewire('/panels/administrator/sms-management/providers', 'pages::panels.administrator.sms-management.provider.index')
                ->name('panels.administrator.sms-management.provider.index');
        });

        Route::middleware('permission:sms-management.gateway.view')->group(function () {
            Route::livewire('/panels/administrator/sms-management/gateways', 'pages::panels.administrator.sms-management.gateway.index')
                ->name('panels.administrator.sms-management.gateway.index');
        });

        Route::middleware('permission:sms-management.gateway.user.view')->group(function () {
            Route::livewire('/panels/administrator/sms-management/gateways/{gateway}/users', 'pages::panels.administrator.sms-management.gateway.user.index')
                ->name('panels.administrator.sms-management.gateway.user.index');
        });

        Route::middleware('permission:sms-management.message.view')->group(function () {
            Route::livewire('/panels/administrator/sms-management/messages', 'pages::panels.administrator.sms-management.message.index')
                ->name('panels.administrator.sms-management.message.index');
            Route::livewire('/panels/administrator/sms-management/messages/{message}', 'pages::panels.administrator.sms-management.message.detail')
                ->name('panels.administrator.sms-management.message.detail');
        });

        Route::middleware('permission:sms-management.setting.view')->group(function () {
            Route::livewire('/panels/administrator/sms-management/settings', 'pages::panels.administrator.sms-management.setting.index')
                ->name('panels.administrator.sms-management.setting.index');
        });

        Route::middleware('permission:system-management.setting.view')->group(function () {
            Route::livewire('/panels/administrator/system-management/settings', 'pages::panels.administrator.system-management.setting.index')
                ->name('panels.administrator.system-management.setting.index');
        });

        Route::middleware('permission:system-management.function.view')->group(function () {
            Route::livewire('/panels/administrator/system-management/functions', 'pages::panels.administrator.system-management.function.index')
                ->name('panels.administrator.system-management.function.index');
        });

        Route::middleware('permission:system-management.backup.view')->group(function () {
            Route::livewire('/panels/administrator/system-management/backups', 'pages::panels.administrator.system-management.backup.index')
                ->name('panels.administrator.system-management.backup.index');
        });
    });

    // User Panel
    Route::livewire('/panels/user/dashboard', 'pages::panels.user.dashboard.index')->name('panels.user.dashboard.index');
    Route::livewire('/panels/user/settings', 'pages::panels.user.setting.index')->name('panels.user.setting.index');
    Route::livewire('/panels/user/wallet', 'pages::panels.user.wallet.index')->name('panels.user.wallet.index');
    Route::livewire('/panels/user/wallet/charge', 'pages::panels.user.wallet.charge')->name('panels.user.wallet.charge');
    Route::livewire('/panels/user/wallet/{wallet}/transactions', 'pages::panels.user.wallet.transaction.index')->name('panels.user.wallet.transaction.index');

    Route::livewire('/panels/user/phonebook', 'pages::panels.user.phonebook.index')->name('panels.user.phonebook.index');
    Route::livewire('/panels/user/phonebook/groups', 'pages::panels.user.phonebook.group.index')->name('panels.user.phonebook.group.index');
    Route::livewire('/panels/user/phonebook/groups/{group}', 'pages::panels.user.phonebook.group.view')->name('panels.user.phonebook.group.view');
    Route::livewire('/panels/user/phonebook/tags', 'pages::panels.user.phonebook.tag.index')->name('panels.user.phonebook.tag.index');
    Route::livewire('/panels/user/phonebook/notes', 'pages::panels.user.phonebook.note.index')->name('panels.user.phonebook.note.index');
    Route::livewire('/panels/user/phonebook/{contact}', 'pages::panels.user.phonebook.view')->name('panels.user.phonebook.view');

    Route::livewire('/panels/user/sms/messages', 'pages::panels.user.sms.message.index')->name('panels.user.sms.message.index');
    Route::livewire('/panels/user/sms/messages/{message}', 'pages::panels.user.sms.message.detail')->name('panels.user.sms.message.detail');
    Route::livewire('/panels/user/sms/send', 'pages::panels.user.sms.send')->name('panels.user.sms.send');
    Route::livewire('/panels/user/sms/preview', 'pages::panels.user.sms.preview')->name('panels.user.sms.preview');
    Route::livewire('/panels/user/sms/tokens', 'pages::panels.user.sms.token.index')->name('panels.user.sms.token.index');
    Route::livewire('/panels/user/sms/tokens/logs', 'pages::panels.user.sms.token.logs')->name('panels.user.sms.token.logs');
    Route::livewire('/panels/user/sms/tokens/doc', 'pages::panels.user.sms.token.doc')->name('panels.user.sms.token.doc');
    Route::livewire('/panels/user/sms/tokens/sample', 'pages::panels.user.sms.token.sample')->name('panels.user.sms.token.sample');
});

require __DIR__.'/auth.php';
