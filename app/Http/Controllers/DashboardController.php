<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $monthOrders = Order::whereMonth('ordered_at', now()->month)
            ->whereYear('ordered_at', now()->year);

        $billed     = (clone $monthOrders)->sum('total');
        $revenue    = (clone $monthOrders)->sum('payment');
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
            ->get();

        // pendapatan 7 hari terakhir untuk grafik
        $series = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $series[] = [
                'label' => $d->translatedFormat('D'),
                'value' => (int) Order::whereDate('ordered_at', $d->toDateString())->sum('payment'),
            ];
        }
        $maxSeries = max(1, max(array_column($series, 'value')));

        return view('pages.dashboard', compact(
            'billed', 'revenue', 'outstanding', 'counts', 'todayOrders',
            'todayRevenue', 'recent', 'series', 'maxSeries'
        ));
    }
}
