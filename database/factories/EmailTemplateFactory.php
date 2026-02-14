<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmailTemplate>
 */
class EmailTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'trigger' => 'purchase_confirmation',
            'subject' => 'Welcome to {plan_name}!',
            'body' => "Hi {customer_name},\n\nThank you for purchasing {plan_name} at {gym_name}.\n\nYour access code is: {access_code}\n\nStart date: {starts_at}\nEnd date: {ends_at}",
            'is_active' => true,
        ];
    }
}
