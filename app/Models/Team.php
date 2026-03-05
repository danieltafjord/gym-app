<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    /** @var list<string> */
    public const RESERVED_SLUGS = [
        'admin',
        'account',
        'team',
        'login',
        'register',
        'logout',
        'api',
        'settings',
        'password',
        'email',
        'verify-email',
        'two-factor-challenge',
        'confirm-password',
        'forgot-password',
        'reset-password',
        'up',
    ];

    /** @var array<string, mixed> */
    public const DEFAULT_CHECK_IN_SETTINGS = [
        'enabled' => true,
        'allowed_methods' => ['qr_scan', 'barcode_scanner', 'manual_entry'],
        'require_gym_selection' => true,
        'prevent_duplicate_minutes' => 5,
        'kiosk_mode' => 'camera',
    ];

    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'description',
        'default_currency',
        'default_language',
        'logo_path',
        'is_active',
        'stripe_account_id',
        'stripe_onboarding_complete',
        'widget_settings',
        'check_in_settings',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'stripe_onboarding_complete' => 'boolean',
            'widget_settings' => 'array',
            'check_in_settings' => 'array',
        ];
    }

    /**
     * @return Attribute<array<string, mixed>, never>
     */
    protected function widgetSettingsWithDefaults(): Attribute
    {
        return Attribute::get(
            fn () => array_merge(Gym::DEFAULT_WIDGET_SETTINGS, $this->widget_settings ?? [])
        );
    }

    /**
     * @return Attribute<array<string, mixed>, never>
     */
    protected function checkInSettingsWithDefaults(): Attribute
    {
        return Attribute::get(
            fn () => array_merge(self::DEFAULT_CHECK_IN_SETTINGS, $this->check_in_settings ?? [])
        );
    }

    public function hasStripeAccount(): bool
    {
        return $this->stripe_account_id !== null && $this->stripe_onboarding_complete;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function gyms(): HasMany
    {
        return $this->hasMany(Gym::class);
    }

    public function membershipPlans(): HasMany
    {
        return $this->hasMany(MembershipPlan::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
