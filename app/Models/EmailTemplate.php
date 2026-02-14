<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplate extends Model
{
    /** @use HasFactory<\Database\Factories\EmailTemplateFactory> */
    use HasFactory;

    protected $fillable = [
        'team_id',
        'trigger',
        'gym_id',
        'membership_plan_id',
        'subject',
        'body',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    public function membershipPlan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class);
    }

    /**
     * Resolve the most specific active template for the given trigger.
     * Resolution order: plan-specific → gym-specific → team default.
     */
    public static function resolve(string $trigger, Team $team, ?Gym $gym = null, ?MembershipPlan $plan = null): ?self
    {
        $query = static::query()
            ->where('team_id', $team->id)
            ->where('trigger', $trigger)
            ->where('is_active', true);

        if ($plan) {
            $planTemplate = (clone $query)->where('membership_plan_id', $plan->id)->first();
            if ($planTemplate) {
                return $planTemplate;
            }
        }

        if ($gym) {
            $gymTemplate = (clone $query)->where('gym_id', $gym->id)->whereNull('membership_plan_id')->first();
            if ($gymTemplate) {
                return $gymTemplate;
            }
        }

        return (clone $query)->whereNull('gym_id')->whereNull('membership_plan_id')->first();
    }
}
