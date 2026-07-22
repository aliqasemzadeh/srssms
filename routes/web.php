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

    // User Panel
    Route::livewire('/panels/user/dashboard', 'pages::panels.user.dashboard.index')->name('panels.user.dashboard.index');
});

require __DIR__.'/auth.php';
