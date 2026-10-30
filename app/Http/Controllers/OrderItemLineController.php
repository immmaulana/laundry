<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItemLine;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderItemLineController extends Controller
{
    public function store(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'numeric', 'min:1'],
            'description' => ['nullable', 'string'],
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        try {
            $plan = Plan::findOrFail($validated['plan_id']);
            $subtotal = $plan->price * $validated['quantity'];

            DB::beginTransaction();
            $order->itemLines()->create([
                'quantity' => $validated['quantity'],
                'description' => $validated['description'] ?? ($plan->plan.' '.$validated['quantity']),
                'plan_id' => $plan->id,
                'price' => $plan->price,
                'subtotal' => $subtotal,
            ]);
            $order->update([
                'total' => (int) $order->total + $subtotal,
            ]);
            DB::commit();

            return back()->with('success', 'Item ditambahkan.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return back()->with('error', 'Gagal menambah item.');
        }
    }

    public function destroy(Order $order, OrderItemLine $item): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $order->update([
                'total' => max(0, (int) $order->total - (int) $item->subtotal),
            ]);
            $item->delete();
            DB::commit();

            return back()->with('success', 'Item dihapus.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus item.');
        }
    }
}
