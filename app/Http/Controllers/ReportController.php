<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItemLine;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = in_array($request->period, ['daily', 'weekly', 'monthly', 'yearly'], true)
            ? $request->period
            : 'monthly';

        $anchor = $request->filled('date')
            ? Carbon::parse($request->date)->startOfDay()
            : now()->startOfDay();

        [$start, $end, $prevStart, $prevEnd] = $this->range($period, $anchor);

        $base = Order::whereBetween('ordered_at', [$start->toDateString(), $end->toDateString()]);
        $prev = Order::whereBetween('ordered_at', [$prevStart->toDateString(), $prevEnd->toDateString()]);

        $billed    = (int) (clone $base)->sum('total');
        $revenue   = (int) (clone $base)->sum('payment');
        $orders    = (clone $base)->count();
        $prevRev   = (int) (clone $prev)->sum('payment');
        $delta     = $prevRev > 0 ? round((($revenue - $prevRev) / $prevRev) * 100, 1) : null;

        $status = [];
        foreach (OrderStatus::cases() as $case) {
            $status[$case->value] = (clone $base)->where('order_status', $case->value)->count();
        }

        $topServices = OrderItemLine::query()
            ->join('orders', 'orders.id', '=', 'order_item_lines.order_id')
            ->join('plans', 'plans.id', '=', 'order_item_lines.plan_id')
            ->whereBetween('orders.ordered_at', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('plans.plan as name, SUM(order_item_lines.quantity) as qty, SUM(order_item_lines.subtotal) as amount')
            ->groupBy('plans.id', 'plans.plan')
            ->orderByDesc('amount')
            ->limit(6)
            ->get();

        $series = $this->series($period, $start, $end);
        $maxSeries = max(1, max(array_column($series, 'value')) ?: 1);

        return view('pages.report', [
            'period' => $period,
            'anchor' => $anchor,
            'labels' => $this->labels($period, $start, $end),
            'start' => $start, 'end' => $end,
            'billed' => $billed, 'revenue' => $revenue, 'outstanding' => max(0, $billed - $revenue),
            'orders' => $orders, 'avg' => $orders > 0 ? (int) round($revenue / $orders) : 0,
            'delta' => $delta, 'status' => $status, 'topServices' => $topServices,
            'series' => $series, 'maxSeries' => $maxSeries,
        ]);
    }

    private function range(string $period, Carbon $anchor): array
    {
        switch ($period) {
            case 'daily':
                $start = $anchor->copy(); $end = $anchor->copy()->endOfDay();
                $prevStart = $anchor->copy()->subDay(); $prevEnd = $anchor->copy()->subDay()->endOfDay();
                break;
            case 'weekly':
                $start = $anchor->copy()->startOfWeek(Carbon::MONDAY); $end = $anchor->copy()->endOfWeek(Carbon::SUNDAY);
                $prevStart = $start->copy()->subWeek(); $prevEnd = $end->copy()->subWeek();
                break;
            case 'yearly':
                $start = $anchor->copy()->startOfYear(); $end = $anchor->copy()->endOfYear();
                $prevStart = $start->copy()->subYear(); $prevEnd = $end->copy()->subYear();
                break;
            default: // monthly
                $start = $anchor->copy()->startOfMonth(); $end = $anchor->copy()->endOfMonth();
                $prevStart = $start->copy()->subMonth(); $prevEnd = $end->copy()->subMonth();
        }
        return [$start, $end, $prevStart, $prevEnd];
    }

    private function series(string $period, Carbon $start, Carbon $end): array
    {
        $rows = [];
        if ($period === 'daily') {
            for ($h = 0; $h < 24; $h += 3) {
                $from = $start->copy()->addHours($h);
                $to = $self = $from->copy()->addHours(3)->subSecond();
                $rows[] = ['label' => sprintf('%02d', $h), 'value' => (int) Order::whereBetween('created_at', [$from, $to])->sum('payment')];
            }
            return $rows;
        }
        if ($period === 'yearly') {
            for ($m = 1; $m <= 12; $m++) {
                $s = $start->copy()->month($m)->startOfMonth();
                $e = $start->copy()->month($m)->endOfMonth();
                $rows[] = ['label' => $s->translatedFormat('M'), 'value' => (int) Order::whereBetween('ordered_at', [$s->toDateString(), $e->toDateString()])->sum('payment')];
            }
            return $rows;
        }
        $cursor = $start->copy();
        while ($cursor <= $end) {
            $rows[] = [
                'label' => $period === 'weekly' ? $cursor->translatedFormat('D') : $cursor->format('j'),
                'value' => (int) Order::whereDate('ordered_at', $cursor->toDateString())->sum('payment'),
            ];
            $cursor->addDay();
        }
        return $rows;
    }

    private function labels(string $period, Carbon $start, Carbon $end): array
    {
        return match ($period) {
            'daily' => ['Harian', $start->translatedFormat('l, j F Y')],
            'weekly' => ['Mingguan', $start->translatedFormat('j M') . ' – ' . $end->translatedFormat('j M Y')],
            'yearly' => ['Tahunan', 'Tahun ' . $start->year],
            default => ['Bulanan', $start->translatedFormat('F Y')],
        };
    }
}
