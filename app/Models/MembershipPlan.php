<?php

namespace App\Models;

use App\Enums\AccessCodeStrategy;
use App\Enums\AccessDurationUnit;
use App\Enums\ActivationMode;
use App\Enums\BillingPeriod;
use App\Enums\PlanType;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MembershipPlan extends Model
{
    use HasFactory;

    protected $appends = [
        'access_duration_label',
        'price_formatted',
        'yearly_price_formatted',
    ];

    protected $fillable = [
        'team_id',
        'name',
        'description',
        'price_cents',
        'yearly_price_cents',
        'billing_period',
        'features',
        'is_active',
        'sort_order',
        'stripe_product_id',
        'stripe_price_id',
        'stripe_yearly_price_id',
        'plan_type',
        'access_duration_value',
        'access_duration_unit',
        'activation_mode',
        'requires_account',
        'access_code_strategy',
        'max_entries',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'billing_period' => BillingPeriod::class,
            'plan_type' => PlanType::class,
            'access_duration_unit' => AccessDurationUnit::class,
            'activation_mode' => ActivationMode::class,
            'requires_account' => 'boolean',
            'access_code_strategy' => AccessCodeStrategy::class,
            'is_active' => 'boolean',
            'price_cents' => 'integer',
            'yearly_price_cents' => 'integer',
            'sort_order' => 'integer',
            'access_duration_value' => 'integer',
            'max_entries' => 'integer',
        ];
    }

    protected function accessDurationLabel(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! $this->hasAccessDuration()) {
                return null;
            }

            $durationValue = $this->access_duration_value ?? 0;
            $unit = $this->access_duration_unit?->value ?? 'access period';

            return Str::plural($unit, $durationValue) === $unit
                ? "{$durationValue} {$unit}"
                : "{$durationValue} ".Str::plural($unit, $durationValue);
        });
    }

    protected function priceFormatted(): Attribute
    {
        return Attribute::get(fn () => number_format($this->price_cents / 100, 2));
    }

    protected function yearlyPriceFormatted(): Attribute
    {
        return Attribute::get(fn () => $this->yearly_price_cents === null
            ? null
            : number_format($this->yearly_price_cents / 100, 2));
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    public function hasYearlyPricingOption(): bool
    {
        return $this->plan_type === PlanType::Recurring
            && $this->billing_period === BillingPeriod::Monthly
            && $this->yearly_price_cents !== null;
    }

    public function hasAccessDuration(): bool
    {
        return $this->access_duration_value !== null
            && $this->access_duration_unit !== null;
    }

    public function activatesOnFirstCheckIn(): bool
    {
        return $this->activation_mode === ActivationMode::FirstCheckIn;
    }

    public function shouldRotateAccessCodeOnCheckIn(): bool
    {
        return $this->access_code_strategy === AccessCodeStrategy::RotateOnCheckIn;
    }

    public function priceCentsForBillingPeriod(BillingPeriod $billingPeriod): int
    {
        if ($billingPeriod === BillingPeriod::Yearly && $this->hasYearlyPricingOption()) {
            return $this->yearly_price_cents ?? $this->price_cents;
        }

        return $this->price_cents;
    }

    public function stripePriceIdForBillingPeriod(BillingPeriod $billingPeriod): ?string
    {
        if ($billingPeriod === BillingPeriod::Yearly && $this->hasYearlyPricingOption()) {
            return $this->stripe_yearly_price_id;
        }

        return $this->stripe_price_id;
    }

    public function calculateEndsAt(
        DateTimeInterface $startsAt,
        ?BillingPeriod $billingPeriod = null,
    ): ?DateTimeInterface {
        $start = CarbonImmutable::instance($startsAt);

        if ($this->plan_type === PlanType::Recurring) {
            $effectiveBillingPeriod = $billingPeriod ?? $this->billing_period;

            return match ($effectiveBillingPeriod) {
                BillingPeriod::Weekly => $start->addWeek(),
                BillingPeriod::Monthly => $start->addMonth(),
                BillingPeriod::Quarterly => $start->addMonths(3),
                BillingPeriod::Yearly => $start->addYear(),
            };
        }

        if (! $this->hasAccessDuration()) {
            return null;
        }

        return match ($this->access_duration_unit) {
            AccessDurationUnit::Hour => $start->addHours($this->access_duration_value),
            AccessDurationUnit::Day => $start->addDays($this->access_duration_value),
            AccessDurationUnit::Week => $start->addWeeks($this->access_duration_value),
            AccessDurationUnit::Month => $start->addMonths($this->access_duration_value),
            AccessDurationUnit::Year => $start->addYears($this->access_duration_value),
            default => null,
        };
    }
}
