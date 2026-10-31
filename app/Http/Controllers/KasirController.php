<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItemLine;
use App\Models\Plan;
use App\Support\PlanIcons;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class KasirController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Kasir/Index', [
            'plans' => Plan::orderBy('price')->get()->map(fn (Plan $plan) => [
                'id' => $plan->id,
                'code' => $plan->code,
                'plan' => $plan->plan,
                'price' => $plan->price,
                'description' => $plan->description,
                'icon' => $plan->icon ?: 'wash',
                'icon_label' => PlanIcons::OPTIONS[$plan->icon ?? 'wash'] ?? 'Layanan',
                'logo_url' => $plan->logo_url,
            ]),
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'phone']),
            'todayCount' => Order::whereDate('created_at', today())->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'name' => ['required_without:customer_id', 'nullable', 'string', 'max:100'],
            'phone' => ['required_without:customer_id', 'nullable', 'string', 'max:30'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.plan_id' => ['required', 'exists:plans,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:999'],
            'discount' => ['nullable', 'integer', 'min:0'],
            'payment_method' => ['required', 'in:cash,qris,transfer'],
            'paid' => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            DB::beginTransaction();

            $customerId = $data['customer_id'] ?? null;
            if (! $customerId) {
                $customerId = Customer::create([
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'user_id' => auth()->id(),
                    'company_id' => auth()->user()->company_id,
                ])->id;
            }

            $total = 0;
            $lines = [];
            foreach ($data['items'] as $row) {
                $plan = Plan::findOrFail($row['plan_id']);
                $subtotal = $plan->price * (int) $row['qty'];
                $total += $subtotal;
                $lines[] = ['plan' => $plan, 'qty' => (int) $row['qty'], 'subtotal' => $subtotal];
            }

            $discount = (int) ($data['discount'] ?? 0);
            $total = max(0, $total - $discount);
            $paidInput = (int) ($data['paid'] ?? $total);
            $change = max(0, $paidInput - $total);
            $paid = min($paidInput, $total);

            $paymentStatus = $paid <= 0
                ? PaymentStatus::PENDING
                : ($paid < $total ? PaymentStatus::PARTIAL : PaymentStatus::PAID);

            $order = Order::create([
                'ordered_at' => now()->toDateString(),
                'finished_at' => now()->addDays(2)->toDateString(),
                'total' => $total,
                'down_payment' => $paid,
                'payment' => $paid,
                'change' => $change,
                'payment_status' => $paymentStatus->value,
                'order_status' => OrderStatus::TODO->value,
                'customer_id' => $customerId,
                'user_id' => auth()->id(),
                'company_id' => auth()->user()->company_id,
            ]);

            foreach ($lines as $line) {
                OrderItemLine::create([
                    'price' => $line['plan']->price,
                    'quantity' => $line['qty'],
                    'subtotal' => $line['subtotal'],
                    'description' => $line['plan']->plan.' '.$line['qty'].' '.($line['plan']->description ?? ''),
                    'plan_id' => $line['plan']->id,
                    'order_id' => $order->id,
                ]);
            }

            DB::commit();

            $order->load(['customer', 'itemLines.plan']);
            $company = auth()->user()->company;
            $subtotalBeforeDiscount = collect($lines)->sum('subtotal');

            $receipt = [
                'id' => $order->id,
                'ordered_at' => now()->format('d/m/Y H:i'),
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
                'subtotal' => (int) $subtotalBeforeDiscount,
                'discount' => $discount,
                'total' => (int) $order->total,
                'paid' => (int) $paidInput,
                'change' => (int) $change,
                'payment_method' => $data['payment_method'],
                'payment_method_label' => match ($data['payment_method']) {
                    'qris' => 'QRIS',
                    'transfer' => 'Transfer',
                    default => 'Tunai',
                },
                'payment_status' => $order->payment_status,
                'payment_status_label' => PaymentStatus::from($order->payment_status)->label(),
            ];

            return redirect()->route('kasir.index')
                ->with('success', 'Pesanan #'.$order->id.' tersimpan · Rp '.number_format($total, 0, ',', '.'))
                ->with('receipt', $receipt);
        } catch (\Throwable $th) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan: '.$th->getMessage());
        }
    }
}
