<?php

use App\Models\User;

test('english locale uses ltr direction in layouts', function () {
    app()->setLocale('en');

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('dir="ltr"', false);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('dir="ltr"', false);

    expect(__('direction'))->toBe('ltr');
});

test('persian locale uses rtl direction in layouts', function () {
    app()->setLocale('fa');

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('dir="rtl"', false);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('dir="rtl"', false);

    expect(__('direction'))->toBe('rtl');
});
