<?php

test('public registration remains disabled for administrator-managed accounts', function () {
    $response = $this->get('/register');

    $response->assertNotFound();
    $this->assertGuest();
});

test('visitors cannot bypass administrator-managed account creation', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertStatus(405);
    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
});
