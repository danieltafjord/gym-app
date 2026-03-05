<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gym extends Model
{
    use HasFactory;

    /** @var array<string, mixed> */
    public const DEFAULT_WIDGET_SETTINGS = [
        'primary_color' => '#2563eb',
        'background_color' => '#ffffff',
        'text_color' => '#111827',
        'secondary_text_color' => '#6b7280',
        'font_family' => 'system-ui, -apple-system, sans-serif',
        'card_border_radius' => 16,
        'button_border_radius' => 8,
        'input_border_color' => '#e5e7eb',
        'input_background_color' => '#ffffff',
        'input_border_radius' => 8,
        'card_border_color' => '#e5e7eb',
        'button_text_color' => '#ffffff',
        'padding' => 16,
        'show_features' => true,
        'show_description' => true,
        'button_text' => 'Sign Up',
        'yearly_toggle_promo_text' => 'Get 1 month free',
        'columns' => 3,
        'show_access_code' => true,
        'show_success_details' => true,
        'show_cta_card' => true,
        'success_heading' => "You're all set!",
        'success_message' => 'Your membership is now active.',
    ];

    protected $fillable = [
        'team_id',
        'name',
        'slug',
        'address',
        'phone',
        'email',
        'is_active',
        'widget_settings',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'widget_settings' => 'array',
        ];
    }

    /**
     * @return Attribute<array<string, mixed>, never>
     */
    protected function widgetSettingsWithDefaults(): Attribute
    {
        return Attribute::get(
            fn () => array_merge(
                self::DEFAULT_WIDGET_SETTINGS,
                $this->team?->widget_settings ?? [],
                $this->widget_settings ?? [],
            )
        );
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
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
