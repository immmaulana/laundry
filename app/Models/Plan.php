<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'company_id',
        'code',
        'plan',
        'price',
        'description',
        'user_id',
    ];

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
