<?php

namespace App\Models;

use App\Enums\BillingPeriod;
use App\Enums\PlanType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipPlan extends Model
{
    use HasFactory;

    protected $appends = [
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
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'billing_period' => BillingPeriod::class,
            'plan_type' => PlanType::class,
            'is_active' => 'boolean',
            'price_cents' => 'integer',
            'yearly_price_cents' => 'integer',
            'sort_order' => 'integer',
        ];
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
}
