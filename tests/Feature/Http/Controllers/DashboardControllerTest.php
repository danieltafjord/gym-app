<?php

use App\Models\User;

it('requires authentication', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

it('redirects authenticated user to account', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect('/account');
});
