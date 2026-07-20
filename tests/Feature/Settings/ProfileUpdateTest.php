<?php

use App\Models\User;
use Livewire\Livewire;

test('profile page is displayed', function () {
    $this->actingAs($user = User::factory()->create());

    $this->get(route('profile.edit'))->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('first_name', 'Test')
        ->set('last_name', 'User')
        ->set('username', 'testuser')
        ->set('mobile', '09171234545')
        ->set('email', 'test@example.com')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->first_name)->toEqual('Test');
    expect($user->last_name)->toEqual('User');
    expect($user->username)->toEqual('testuser');
    expect($user->mobile)->toEqual('09171234545');
    expect($user->email)->toEqual('test@example.com');
    expect($user->email_verified_at)->toBeNull();
    expect($user->mobile_verified_at)->toBeNull();
});

test('profile mobile verification status is unchanged when mobile is unchanged', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('first_name', 'Updated')
        ->set('last_name', $user->last_name)
        ->set('username', $user->username)
        ->set('mobile', $user->mobile)
        ->set('email', $user->email ?? '')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($user->refresh()->mobile_verified_at)->not->toBeNull();
});

test('email verification status is unchanged when email address is unchanged', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('first_name', $user->first_name)
        ->set('last_name', $user->last_name)
        ->set('username', $user->username)
        ->set('mobile', $user->mobile)
        ->set('email', $user->email)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('profile email can be cleared', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('first_name', $user->first_name)
        ->set('last_name', $user->last_name)
        ->set('username', $user->username)
        ->set('mobile', $user->mobile)
        ->set('email', '')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($user->refresh()->email)->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.delete-user-form')
        ->set('password', 'password')
        ->call('deleteUser');

    $response
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh())->toBeNull();
    expect(auth()->check())->toBeFalse();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.delete-user-form')
        ->set('password', 'wrong-password')
        ->call('deleteUser');

    $response->assertHasErrors(['password']);

    expect($user->fresh())->not->toBeNull();
});
