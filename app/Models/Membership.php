<?php

namespace App\Models;

use App\Enums\MembershipStatus;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Membership extends Model
{
    use HasFactory;

    protected $appends = [
        'is_currently_valid',
    ];

    protected $fillable = [
        'user_id',
        'team_id',
        'membership_plan_id',
        'email',
        'customer_name',
        'customer_phone',
        'access_code',
        'status',
        'starts_at',
        'ends_at',
        'activated_at',
        'entries_used',
        'cancelled_at',
        'expiry_reminder_sent_at',
        'stripe_subscription_id',
        'stripe_payment_intent_id',
        'stripe_status',
    ];

    protected function casts(): array
    {
        return [
            'status' => MembershipStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'activated_at' => 'datetime',
            'entries_used' => 'integer',
            'cancelled_at' => 'datetime',
            'expiry_reminder_sent_at' => 'datetime',
        ];
    }

    protected function isCurrentlyValid(): Attribute
    {
        return Attribute::get(fn (): bool => $this->canAccessGym());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'membership_plan_id');
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(MembershipNote::class);
    }

    public function regenerateAccessCode(): void
    {
        do {
            $code = strtoupper(Str::random(24));
        } while (self::where('access_code', $code)->exists());

        $this->update(['access_code' => $code]);
    }

    public static function generateAccessCode(): string
    {
        do {
            $code = strtoupper(Str::random(24));
        } while (self::where('access_code', $code)->exists());

        return $code;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', MembershipStatus::Active);
    }

    public function scopeForTeam(Builder $query, int $teamId): Builder
    {
        return $query->where('team_id', $teamId);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where(function (Builder $q2) {
                $q2->whereNotNull('ends_at')->where('ends_at', '<=', now());
            });
        })->where('status', MembershipStatus::Active);
    }

    public function scopeExpiringWithin(Builder $query, int $days): Builder
    {
        return $query->where('status', MembershipStatus::Active)
            ->whereNotNull('ends_at')
            ->where('ends_at', '>', now())
            ->where('ends_at', '<=', now()->addDays($days));
    }

    public function activate(DateTimeInterface $activatedAt): void
    {
        $this->loadMissing('plan');

        $activatedAt = CarbonImmutable::instance($activatedAt);

        $this->forceFill([
            'activated_at' => $activatedAt,
            'starts_at' => $activatedAt,
            'ends_at' => $this->plan?->calculateEndsAt($activatedAt),
        ])->save();
    }

    public function canAccessGym(): bool
    {
        if ($this->status !== MembershipStatus::Active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->hasExpiredAccessWindow()) {
            return false;
        }

        return ! $this->hasReachedEntryLimit();
    }

    public function hasExpiredAccessWindow(): bool
    {
        return $this->ends_at !== null && $this->ends_at->lessThanOrEqualTo(now());
    }

    public function hasReachedEntryLimit(): bool
    {
        $this->loadMissing('plan');

        return $this->plan?->max_entries !== null
            && $this->entries_used >= $this->plan->max_entries;
    }

    public function shouldRotateAccessCodeOnCheckIn(): bool
    {
        $this->loadMissing('plan');

        return $this->plan?->shouldRotateAccessCodeOnCheckIn() ?? true;
    }

    public function syncExpiredStatus(): void
    {
        if ($this->status !== MembershipStatus::Active) {
            return;
        }

        if (! $this->hasExpiredAccessWindow() && ! $this->hasReachedEntryLimit()) {
            return;
        }

        $attributes = ['status' => MembershipStatus::Expired];

        if ($this->ends_at === null) {
            $attributes['ends_at'] = now();
        }

        $this->forceFill($attributes)->save();
    }
}
