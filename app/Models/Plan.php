<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Plan extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'company_id',
        'code',
        'plan',
        'price',
        'description',
        'icon',
        'logo',
        'user_id',
    ];

    protected $appends = [
        'logo_url',
    ];

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo) {
            return null;
        }

        return Storage::disk('public')->url($this->logo);
    }

    public function scopeRender($query, $search, $page = 10)
    {
        return $query
            ->search($search)
            ->orderBy('code')
            ->paginate($page ?: 10)
            ->withQueryString();
    }

    public function scopeSearch($query, $search)
    {
        return $query->when($search, function ($query, $find) {
            return $query->where(function ($q) use ($find) {
                $q->where('code', 'like', "%{$find}%")
                    ->orWhere('plan', 'like', "%{$find}%");
            });
        });
    }
}
