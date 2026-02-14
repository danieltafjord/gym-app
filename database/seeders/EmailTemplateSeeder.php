<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use App\Models\Team;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $body = <<<'BODY'
Hi {customer_name},

Thank you for purchasing {plan_name} at {gym_name}!

Your access code is: {access_code}

Start date: {starts_at}
End date: {ends_at}

Please keep this email for your records.
BODY;

        Team::all()->each(function (Team $team) use ($body) {
            EmailTemplate::firstOrCreate(
                [
                    'team_id' => $team->id,
                    'trigger' => 'purchase_confirmation',
                    'gym_id' => null,
                    'membership_plan_id' => null,
                ],
                [
                    'subject' => 'Welcome to {plan_name}!',
                    'body' => $body,
                    'is_active' => true,
                ],
            );
        });
    }
}
