<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class QueueController extends Controller
{
    public function index(): Response
    {
        $orders = Order::query()
            ->with(['customer:id,name,phone', 'itemLines.plan:id,plan,code'])
            ->whereIn('order_status', [
                OrderStatus::TODO->value,
                OrderStatus::IN_PROGRESS->value,
                OrderStatus::DONE->value,
                OrderStatus::COMPLETED->value,
            ])
            ->where(function ($q) {
                $q->whereDate('ordered_at', '>=', now()->subDays(14))
                    ->orWhereIn('order_status', [
                        OrderStatus::TODO->value,
                        OrderStatus::IN_PROGRESS->value,
                        OrderStatus::DONE->value,
                    ]);
            })
            ->orderByDesc('ordered_at')
            ->orderByDesc('id')
            ->limit(120)
            ->get()
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'ordered_at' => $order->ordered_at?->format('d M Y'),
                'total' => $order->total,
                'order_status' => $order->order_status,
                'payment_status' => $order->payment_status,
                'customer' => $order->customer?->only(['id', 'name', 'phone']),
                'items' => $order->itemLines->map(fn ($line) => [
                    'label' => $line->plan?->plan ?? $line->description,
                    'qty' => $line->quantity,
                ])->take(4)->values(),
            ]);

        $columns = collect(OrderStatus::cases())->mapWithKeys(
            fn (OrderStatus $status) => [
                $status->value => $orders->where('order_status', $status->value)->values(),
            ]
        );

        return Inertia::render('Queue/Index', [
            'columns' => $columns,
            'statusLabels' => OrderStatus::options(),
            'paymentLabels' => PaymentStatus::options(),
            'nextStatus' => [
                OrderStatus::TODO->value => OrderStatus::IN_PROGRESS->value,
                OrderStatus::IN_PROGRESS->value => OrderStatus::DONE->value,
                OrderStatus::DONE->value => OrderStatus::COMPLETED->value,
                OrderStatus::COMPLETED->value => null,
            ],
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'order_status' => ['required', Rule::in(array_column(OrderStatus::cases(), 'value'))],
        ]);

        $payload = ['order_status' => $data['order_status']];

        if ($data['order_status'] === OrderStatus::DONE->value && ! $order->finished_at) {
            $payload['finished_at'] = now()->toDateString();
        }

        $order->update($payload);

        $label = OrderStatus::from($data['order_status'])->label();

        return back()->with('success', "Pesanan #{$order->id} → {$label}");
    }
}
