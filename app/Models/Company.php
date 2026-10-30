<?php

namespace App\Models;

use App\Support\ThemePalette;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'phone',
        'email',
        'address',
        'city',
        'is_active',
        'primary_color',
        'secondary_color',
        'logo',
    ];

    protected $appends = [
        'logo_url',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo) {
            return null;
        }

        return Storage::disk('public')->url($this->logo);
    }

    public function themePayload(): array
    {
        return [
            'primary_color' => ThemePalette::normalize($this->primary_color ?: ThemePalette::DEFAULT_PRIMARY),
            'secondary_color' => ThemePalette::normalize(
                $this->secondary_color ?: ThemePalette::DEFAULT_SECONDARY,
                ThemePalette::DEFAULT_SECONDARY,
            ),
            'logo_url' => $this->logo_url,
        ];
    }
}
