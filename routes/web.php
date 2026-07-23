<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    // Administrator Panel
    Route::livewire('/panels/administrator/dashboard', 'pages::panels.administrator.dashboard.index')->name('panels.administrator.dashboard.index');
    Route::livewire('/panels/administrator/user-management/users', 'pages::panels.administrator.user-management.user.index')->name('panels.administrator.user-management.user.index');
    Route::livewire('/panels/administrator/user-management/roles', 'pages::panels.administrator.user-management.role.index')->name('panels.administrator.user-management.role.index');
    Route::livewire('/panels/administrator/user-management/permissions', 'pages::panels.administrator.user-management.permission.index')->name('panels.administrator.user-management.permission.index');
    Route::livewire('/panels/administrator/finance-management/currencies', 'pages::panels.administrator.finance-management.currency.index')->name('panels.administrator.finance-management.currency.index');
    Route::livewire('/panels/administrator/finance-management/wallets', 'pages::panels.administrator.finance-management.wallet.index')->name('panels.administrator.finance-management.wallet.index');
    Route::livewire('/panels/administrator/system-management/settings', 'pages::panels.administrator.system-management.setting.index')->name('panels.administrator.system-management.setting.index');
    Route::livewire('/panels/administrator/system-management/functions', 'pages::panels.administrator.system-management.function.index')->name('panels.administrator.system-management.function.index');
    Route::livewire('/panels/administrator/system-management/backups', 'pages::panels.administrator.system-management.backup.index')->name('panels.administrator.system-management.backup.index');

    // User Panel
    Route::livewire('/panels/user/dashboard', 'pages::panels.user.dashboard.index')->name('panels.user.dashboard.index');
    Route::livewire('/panels/user/settings', 'pages::panels.user.setting.index')->name('panels.user.setting.index');
});

require __DIR__.'/auth.php';
