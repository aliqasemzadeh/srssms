<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    // Administrator Panel
    Route::livewire('/panels/administrator/dashboard', 'pages::panels.administrator.dashboard.index')->name('panels.administrator.dashboard.index');
    Route::livewire('/panels/administrator/user-management/users', 'pages::panels.administrator.user-management.user.index')->name('panels.administrator.user-management.user.index');
    Route::livewire('/panels/administrator/user-management/users/{user}/wallets', 'pages::panels.administrator.user-management.user.wallet.index')->name('panels.administrator.user-management.user.wallet.index');
    Route::livewire('/panels/administrator/user-management/users/{user}/wallets/{wallet}/transactions', 'pages::panels.administrator.user-management.user.wallet.transaction.index')->scopeBindings()->name('panels.administrator.user-management.user.wallet.transaction.index');
    Route::livewire('/panels/administrator/user-management/users/{user}/wallets/{wallet}/deposits', 'pages::panels.administrator.user-management.user.wallet.deposit.index')->scopeBindings()->name('panels.administrator.user-management.user.wallet.deposit.index');
    Route::livewire('/panels/administrator/user-management/users/{user}/wallets/{wallet}/withdrawals', 'pages::panels.administrator.user-management.user.wallet.withdrawal.index')->scopeBindings()->name('panels.administrator.user-management.user.wallet.withdrawal.index');
    Route::livewire('/panels/administrator/user-management/users/{user}/user-accounts', 'pages::panels.administrator.user-management.user.user-account.index')->name('panels.administrator.user-management.user.user-account.index');
    Route::livewire('/panels/administrator/user-management/roles', 'pages::panels.administrator.user-management.role.index')->name('panels.administrator.user-management.role.index');
    Route::livewire('/panels/administrator/user-management/permissions', 'pages::panels.administrator.user-management.permission.index')->name('panels.administrator.user-management.permission.index');
    Route::livewire('/panels/administrator/finance-management/currencies', 'pages::panels.administrator.finance-management.currency.index')->name('panels.administrator.finance-management.currency.index');
    Route::livewire('/panels/administrator/finance-management/wallets', 'pages::panels.administrator.finance-management.wallet.index')->name('panels.administrator.finance-management.wallet.index');
    Route::livewire('/panels/administrator/finance-management/transactions', 'pages::panels.administrator.finance-management.transaction.index')->name('panels.administrator.finance-management.transaction.index');
    Route::livewire('/panels/administrator/finance-management/deposits', 'pages::panels.administrator.finance-management.deposit.index')->name('panels.administrator.finance-management.deposit.index');
    Route::livewire('/panels/administrator/finance-management/withdrawals', 'pages::panels.administrator.finance-management.withdrawal.index')->name('panels.administrator.finance-management.withdrawal.index');
    Route::livewire('/panels/administrator/finance-management/payments', 'pages::panels.administrator.finance-management.payment.index')->name('panels.administrator.finance-management.payment.index');
    Route::livewire('/panels/administrator/finance-management/payments/settings', 'pages::panels.administrator.finance-management.payment.setting.index')->name('panels.administrator.finance-management.payment.setting.index');
    Route::livewire('/panels/administrator/system-management/settings', 'pages::panels.administrator.system-management.setting.index')->name('panels.administrator.system-management.setting.index');
    Route::livewire('/panels/administrator/system-management/functions', 'pages::panels.administrator.system-management.function.index')->name('panels.administrator.system-management.function.index');
    Route::livewire('/panels/administrator/system-management/backups', 'pages::panels.administrator.system-management.backup.index')->name('panels.administrator.system-management.backup.index');

    // User Panel
    Route::livewire('/panels/user/dashboard', 'pages::panels.user.dashboard.index')->name('panels.user.dashboard.index');
    Route::livewire('/panels/user/settings', 'pages::panels.user.setting.index')->name('panels.user.setting.index');
});

require __DIR__.'/auth.php';
