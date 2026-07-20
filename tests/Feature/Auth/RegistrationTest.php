<?php

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('registration rejects mobile numbers without zero prefix', function () {
    $response = $this->post(route('register.store'), [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'username' => 'johndoe',
        'mobile' => '+989123456789',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('mobile');
    $this->assertGuest();
});

test('registration rejects invalid mobile numbers', function () {
    $response = $this->post(route('register.store'), [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'username' => 'johndoe',
        'mobile' => '08123456789',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('mobile');
    $this->assertGuest();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'username' => 'johndoe',
        'email' => 'test@example.com',
        'mobile' => '09171234545',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('new users can register without email', function () {
    $response = $this->post(route('register.store'), [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'username' => 'janedoe',
        'mobile' => '09901236565',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'username' => 'janedoe',
        'mobile' => '09901236565',
        'email' => null,
    ]);
});
