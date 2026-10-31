<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Orders/Index', [
            'filters' => [
                'search' => $request->search,
                'num_page' => $request->num_page ?? 10,
            ],
            'orders' => Order::render($request->search, $request->num_page),
            'statusLabels' => OrderStatus::options(),
            'paymentLabels' => PaymentStatus::options(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Orders/Form', [
            'order' => null,
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'phone']),
            'statusLabels' => OrderStatus::options(),
            'paymentLabels' => PaymentStatus::options(),
            'plans' => [],
        ]);
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $payload = $request->all();

        DB::beginTransaction();
        try {
            if (empty($data['customer_id'])) {
                $customer = Customer::create([
                    'name' => $payload['name'],
                    'phone' => $payload['phone'],
                    'address' => $payload['address'] ?? null,
                    'user_id' => auth()->id(),
                    'company_id' => auth()->user()->company_id,
                ]);
                $data['customer_id'] = $customer->id;
            }

            $order = Order::create([
                ...$data,
                'user_id' => auth()->id(),
                'company_id' => auth()->user()->company_id,
                'total' => 0,
                'down_payment' => 0,
                'payment' => 0,
                'change' => 0,
                'payment_status' => PaymentStatus::PENDING->value,
                'order_status' => OrderStatus::TODO->value,
            ]);

            DB::commit();

            return redirect()
                ->route('order.edit', $order)
                ->with('success', 'Pesanan dibuat. Tambahkan item layanan.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return back()->with('error', 'Gagal membuat pesanan.');
        }
    }

    public function edit(Order $order): Response
    {
        $order->load(['itemLines.plan', 'customer']);

        return Inertia::render('Orders/Form', [
            'order' => [
                'id' => $order->id,
                'ordered_at' => $order->ordered_at?->format('Y-m-d'),
                'finished_at' => $order->finished_at?->format('Y-m-d'),
                'total' => $order->total,
                'down_payment' => $order->down_payment,
                'payment' => $order->payment,
                'change' => $order->change,
                'payment_status' => $order->payment_status,
                'order_status' => $order->order_status,
                'customer_id' => $order->customer_id,
                'customer' => $order->customer,
                'item_lines' => $order->itemLines->map(fn ($line) => [
                    'id' => $line->id,
                    'price' => $line->price,
                    'quantity' => $line->quantity,
                    'subtotal' => $line->subtotal,
                    'description' => $line->description,
                    'plan_id' => $line->plan_id,
                    'plan' => $line->plan?->only(['id', 'code', 'plan']),
                ]),
            ],
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'phone']),
            'plans' => Plan::orderBy('plan')->get(['id', 'code', 'plan', 'price']),
            'statusLabels' => OrderStatus::options(),
            'paymentLabels' => PaymentStatus::options(),
        ]);
    }

    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        $order->update($request->validated());

        return back()->with('success', 'Pesanan diperbarui.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        $order->delete();

        return redirect()->route('order.index')->with('success', 'Pesanan dihapus.');
    }

    public function receipt(Order $order): \Illuminate\Http\JsonResponse
    {
        $order->load(['customer', 'itemLines.plan']);
        $company = auth()->user()->company;
        $discount = 0;
        $itemsSubtotal = $order->itemLines->sum('subtotal');
        if ($itemsSubtotal > $order->total) {
            $discount = (int) ($itemsSubtotal - $order->total);
        }

        return response()->json([
            'id' => $order->id,
            'ordered_at' => $order->ordered_at?->format('d/m/Y').' '.($order->created_at?->format('H:i') ?? ''),
            'cashier' => auth()->user()->name,
            'customer_name' => $order->customer?->name,
            'customer_phone' => $order->customer?->phone,
            'company_name' => $company?->name ?? 'CashierLaundry',
            'company_address' => trim(collect([$company?->address, $company?->city])->filter()->implode(', ')),
            'company_phone' => $company?->phone,
            'items' => $order->itemLines->map(fn ($line) => [
                'name' => $line->plan?->plan ?? $line->description,
                'qty' => (int) $line->quantity,
                'price' => (int) $line->price,
                'subtotal' => (int) $line->subtotal,
            ])->values()->all(),
            'subtotal' => (int) $itemsSubtotal,
            'discount' => $discount,
            'total' => (int) $order->total,
            'paid' => (int) ($order->payment + $order->change),
            'change' => (int) $order->change,
            'payment_method' => null,
            'payment_method_label' => '—',
            'payment_status' => $order->payment_status,
            'payment_status_label' => PaymentStatus::tryFrom($order->payment_status)?->label() ?? $order->payment_status,
        ]);
    }
}
