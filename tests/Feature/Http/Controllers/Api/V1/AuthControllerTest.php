<?php

use App\Models\User;

it('logs in with valid credentials', function () {
    $user = User::factory()->create([
        'password' => 'password',
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'test-device',
    ])
        ->assertSuccessful()
        ->assertJsonStructure(['token', 'user']);
});

it('rejects invalid credentials', function () {
    $user = User::factory()->create();

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
        'device_name' => 'test-device',
    ])
        ->assertUnprocessable();
});

it('registers a new user', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'device_name' => 'test-device',
    ])
        ->assertCreated()
        ->assertJsonStructure(['token', 'user']);

    $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
});

it('logs out authenticated user', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/v1/auth/logout')
        ->assertSuccessful();

    expect($user->tokens()->count())->toBe(0);
});

it('returns authenticated user', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/v1/user')
        ->assertSuccessful()
        ->assertJsonPath('data.id', $user->id);
});
