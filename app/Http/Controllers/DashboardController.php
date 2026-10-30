<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response|\Illuminate\Http\RedirectResponse
    {
        if (auth()->user()->isSuperAdmin()) {
            return redirect()->route('companies.index');
        }

        $monthOrders = Order::whereMonth('ordered_at', now()->month)
            ->whereYear('ordered_at', now()->year);

        $billed = (clone $monthOrders)->sum('total');
        $revenue = (clone $monthOrders)->sum('payment');
        $outstanding = max(0, $billed - $revenue);

        $counts = [];
        foreach (OrderStatus::cases() as $case) {
            $counts[$case->value] = (clone $monthOrders)->where('order_status', $case->value)->count();
        }

        $todayOrders = Order::whereDate('ordered_at', today())->count();
        $todayRevenue = Order::whereDate('ordered_at', today())->sum('payment');

        $recent = Order::with(['customer', 'itemLines'])
            ->orderByDesc('ordered_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get()
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'ordered_at' => $order->ordered_at?->format('Y-m-d'),
                'total' => $order->total,
                'payment' => $order->payment,
                'order_status' => $order->order_status,
                'payment_status' => $order->payment_status,
                'customer' => $order->customer?->only(['id', 'name', 'phone']),
            ]);

        $series = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $series[] = [
                'label' => $d->translatedFormat('D'),
                'value' => (int) Order::whereDate('ordered_at', $d->toDateString())->sum('payment'),
            ];
        }

        return Inertia::render('Dashboard', [
            'billed' => (int) $billed,
            'revenue' => (int) $revenue,
            'outstanding' => (int) $outstanding,
            'counts' => $counts,
            'todayOrders' => $todayOrders,
            'todayRevenue' => (int) $todayRevenue,
            'recent' => $recent,
            'series' => $series,
            'maxSeries' => max(1, max(array_column($series, 'value'))),
            'statusLabels' => OrderStatus::options(),
        ]);
    }
}
