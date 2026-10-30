<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItemLine;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KasirController extends Controller
{
    public function index()
    {
        return view('pages.kasir', [
            'plans' => Plan::orderBy('price')->get(),
            'customers' => Customer::orderBy('name')->get(),
            'todayCount' => Order::whereDate('created_at', today())->count(),
        ]);
    }

    public function store(Request $request)
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
            if (!$customerId) {
                $customerId = Customer::create([
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'user_id' => auth()->id(),
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
            ]);

            foreach ($lines as $l) {
                OrderItemLine::create([
                    'price' => $l['plan']->price,
                    'quantity' => $l['qty'],
                    'subtotal' => $l['subtotal'],
                    'description' => $l['plan']->plan . ' ' . $l['qty'] . ' ' . ($l['plan']->description ?? ''),
                    'plan_id' => $l['plan']->id,
                    'order_id' => $order->id,
                ]);
            }

            DB::commit();

            return redirect()->route('kasir.index')
                ->with('success', 'Pesanan #' . $order->id . ' tersimpan · Rp ' . number_format($total, 0, ',', '.'));
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan: ' . $th->getMessage());
        }
    }
}
