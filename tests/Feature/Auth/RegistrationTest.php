<?php

use App\Models\Membership;

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/account');
});

test('it links orphaned memberships on registration', function () {
    $membership = Membership::factory()->guest()->create([
        'email' => 'orphan@example.com',
    ]);

    expect($membership->user_id)->toBeNull();

    $this->post(route('register.store'), [
        'name' => 'Orphan User',
        'email' => 'orphan@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();

    $membership->refresh();
    expect($membership->user_id)->not->toBeNull();
    expect($membership->user_id)->toBe(auth()->id());
});
