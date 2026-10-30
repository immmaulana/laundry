<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItemLine;
use App\Models\Plan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@laundry.test'],
            ['name' => 'Admin Laundry', 'password' => Hash::make('rahasia123'), 'language' => 'id']
        );
        $user->forceFill(['role' => 'admin', 'is_active' => true])->save();

        $kasir = User::firstOrCreate(
            ['email' => 'kasir@laundry.test'],
            ['name' => 'Dewi Kasir', 'password' => Hash::make('kasir12345'), 'language' => 'id']
        );
        $kasir->forceFill(['role' => 'kasir', 'is_active' => true])->save();

        $plans = [
            ['code' => 'CK', 'plan' => 'Cuci Kering',   'price' => 7000,  'description' => 'Per kilogram'],
            ['code' => 'CS', 'plan' => 'Cuci Setrika',  'price' => 10000, 'description' => 'Per kilogram'],
            ['code' => 'ST', 'plan' => 'Setrika Saja',  'price' => 6000,  'description' => 'Per kilogram'],
            ['code' => 'BC', 'plan' => 'Bed Cover',     'price' => 25000, 'description' => 'Per piece'],
            ['code' => 'EX', 'plan' => 'Express 6 Jam', 'price' => 15000, 'description' => 'Per kilogram'],
        ];
        $planModels = [];
        foreach ($plans as $p) {
            $planModels[$p['code']] = Plan::firstOrCreate(['code' => $p['code']], $p + ['user_id' => $user->id]);
        }

        $customers = [
            ['name' => 'Budi Santoso',    'phone' => '081234567801', 'address' => 'Jl. Melati 12'],
            ['name' => 'Siti Aminah',     'phone' => '081234567802', 'address' => 'Jl. Kenanga 4'],
            ['name' => 'Rizky Pratama',   'phone' => '081234567803', 'address' => 'Perum Griya Blok C2'],
            ['name' => 'Dewi Lestari',    'phone' => '081234567804', 'address' => 'Jl. Anggrek 9'],
            ['name' => 'Agus Setiawan',   'phone' => '081234567805', 'address' => 'Kos Melati 3A'],
            ['name' => 'Laundry Kampus',  'phone' => '081234567806', 'address' => 'Jl. Pendidikan 1'],
        ];
        $custModels = [];
        foreach ($customers as $c) {
            $custModels[] = Customer::firstOrCreate(
                ['phone' => $c['phone']],
                $c + ['user_id' => $user->id]
            );
        }

        // 10 order tersebar antar status, tanggal dalam bulan berjalan
        if (Order::count() > 0) {
            $this->command->warn('Order sudah ada — seeding order dilewati (idempoten).');
        } else {
        $spec = [
            [0, 'CS', 4, OrderStatus::COMPLETED,  PaymentStatus::PAID,    -9],
            [1, 'CK', 5, OrderStatus::COMPLETED,  PaymentStatus::PAID,    -7],
            [2, 'EX', 3, OrderStatus::DONE,       PaymentStatus::PAID,    -5],
            [3, 'ST', 6, OrderStatus::DONE,       PaymentStatus::PENDING, -4],
            [4, 'CS', 7, OrderStatus::IN_PROGRESS, PaymentStatus::PARTIAL, -3],
            [5, 'BC', 2, OrderStatus::IN_PROGRESS, PaymentStatus::PAID,   -2],
            [0, 'CK', 8, OrderStatus::TODO,       PaymentStatus::PENDING, -2],
            [1, 'EX', 2, OrderStatus::TODO,       PaymentStatus::PENDING, -1],
            [2, 'CS', 5, OrderStatus::DONE,       PaymentStatus::PARTIAL, -1],
            [3, 'CK', 3, OrderStatus::TODO,       PaymentStatus::PENDING,  0],
        ];

        foreach ($spec as $i => [$ci, $code, $qty, $status, $pay, $dayOffset]) {
            $plan = $planModels[$code];
            $subtotal = $plan->price * $qty;
            $paid = match ($pay) {
                PaymentStatus::PAID => $subtotal,
                PaymentStatus::PARTIAL => (int) round($subtotal * 0.5),
                default => 0,
            };
            $orderedAt = Carbon::now()->startOfMonth()->addDays(max(0, Carbon::now()->day + $dayOffset - 1));

            $order = Order::create([
                'ordered_at'     => $orderedAt->toDateString(),
                'finished_at'    => $orderedAt->copy()->addDays(2)->toDateString(),
                'total'          => $subtotal,
                'down_payment'   => $paid,
                'payment'        => $paid,
                'change'         => 0,
                'payment_status' => $pay->value,
                'order_status'   => $status->value,
                'customer_id'    => $custModels[$ci]->id,
                'user_id'        => $user->id,
            ]);

            OrderItemLine::create([
                'price'       => $plan->price,
                'quantity'    => $qty,
                'subtotal'    => $subtotal,
                'description' => $plan->plan . ' ' . $qty . ($code === 'BC' ? ' pcs' : ' kg'),
                'plan_id'     => $plan->id,
                'order_id'    => $order->id,
            ]);
        }

        }

        $this->command->info('Demo: 1 user, ' . count($planModels) . ' paket, ' . count($custModels) . ' pelanggan, 10 order.');
    }
}
