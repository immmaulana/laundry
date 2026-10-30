<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'company_id',
        'ordered_at',
        'finished_at',
        'total',
        'down_payment',
        'payment',
        'change',
        'payment_status',
        'order_status',
        'customer_id',
        'user_id',
    ];

    protected $casts = [
        'ordered_at' => 'date',
        'finished_at' => 'date',
    ];

    public function scopeCurrentMonthIncome($query)
    {
        return $query
            ->where('payment_status', PaymentStatus::PAID)
            ->whereMonth('ordered_at', now()->month)
            ->whereYear('ordered_at', now()->year)
            ->sum('total');
    }

    public function scopeCurrentMonthStatus($query, OrderStatus $status)
    {
        return $query
            ->where('order_status', $status)
            ->whereMonth('ordered_at', now()->month)
            ->whereYear('ordered_at', now()->year)
            ->count();
    }

    public function scopeCurrentMonthTodo($query)
    {
        return $query->currentMonthStatus(OrderStatus::TODO);
    }

    public function scopeCurrentMonthInProgress($query)
    {
        return $query->currentMonthStatus(OrderStatus::IN_PROGRESS);
    }

    public function scopeCurrentMonthDone($query)
    {
        return $query->currentMonthStatus(OrderStatus::DONE);
    }

    public function scopeCurrentMonthCompleted($query)
    {
        return $query->currentMonthStatus(OrderStatus::COMPLETED);
    }

    public function scopeRender($query, $search, $page = 10)
    {
        return $query
            ->with(['itemLines.plan', 'customer'])
            ->search($search)
            ->orderByDesc('ordered_at')
            ->orderByDesc('id')
            ->paginate($page ?: 10)
            ->withQueryString();
    }

    public function scopeSearch($query, $search)
    {
        return $query->when($search, function ($query, $find) {
            return $query->where(function ($q) use ($find) {
                $q->where('id', $find)
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$find}%"));
            });
        });
    }

    public function itemLines()
    {
        return $this->hasMany(OrderItemLine::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
