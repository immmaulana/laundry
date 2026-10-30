<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItemLine;
use App\Models\Plan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        OrderItemLine::truncate();
        Order::withoutGlobalScopes()->truncate();
        Customer::withoutGlobalScopes()->truncate();
        Plan::withoutGlobalScopes()->truncate();
        User::query()->delete();
        Company::query()->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        User::create([
            'company_id' => null,
            'name' => 'Owner Platform',
            'email' => 'owner@laundry.test',
            'password' => 'owner12345',
            'role' => 'super_admin',
            'is_active' => true,
            'language' => 'id',
        ]);

        $this->seedCompany([
            'company' => [
                'name' => 'PT Fresh Wash Nusantara',
                'slug' => 'pt-fresh-wash',
                'code' => 'PTA',
                'phone' => '0215551001',
                'email' => 'hello@freshwash.test',
                'address' => 'Jl. Sudirman No. 88',
                'city' => 'Jakarta',
                'primary_color' => '#0B3D8C',
                'secondary_color' => '#64748B',
            ],
            'admin' => ['name' => 'Admin Fresh Wash', 'email' => 'admin@pta.test', 'password' => 'rahasia123'],
            'kasirs' => [
                ['name' => 'Dewi Kasir A', 'email' => 'kasir@pta.test', 'password' => 'kasir12345'],
                ['name' => 'Rina Shift Malam', 'email' => 'rina@pta.test', 'password' => 'kasir12345'],
            ],
            'plans' => [
                ['code' => 'CK', 'plan' => 'Cuci Kering', 'price' => 7000, 'description' => 'Per kilogram', 'icon' => 'dry'],
                ['code' => 'CS', 'plan' => 'Cuci Setrika', 'price' => 10000, 'description' => 'Per kilogram', 'icon' => 'wash'],
                ['code' => 'ST', 'plan' => 'Setrika Saja', 'price' => 6000, 'description' => 'Per kilogram', 'icon' => 'iron'],
                ['code' => 'BC', 'plan' => 'Bed Cover', 'price' => 25000, 'description' => 'Per piece', 'icon' => 'bed'],
                ['code' => 'EX', 'plan' => 'Express 6 Jam', 'price' => 15000, 'description' => 'Per kilogram', 'icon' => 'express'],
                ['code' => 'SL', 'plan' => 'Sepatu Laundry', 'price' => 35000, 'description' => 'Per pasang', 'icon' => 'shoes'],
            ],
            'customers' => [
                ['name' => 'Budi Santoso', 'phone' => '081211110001', 'address' => 'Jl. Melati 12'],
                ['name' => 'Siti Aminah', 'phone' => '081211110002', 'address' => 'Jl. Kenanga 4'],
                ['name' => 'Rizky Pratama', 'phone' => '081211110003', 'address' => 'Perum Griya Blok C2'],
                ['name' => 'Dewi Lestari', 'phone' => '081211110004', 'address' => 'Jl. Anggrek 9'],
                ['name' => 'Agus Setiawan', 'phone' => '081211110005', 'address' => 'Kos Melati 3A'],
                ['name' => 'Hotel Mentari', 'phone' => '081211110006', 'address' => 'Jl. Thamrin 20'],
                ['name' => 'Kost Putri Dahlia', 'phone' => '081211110007', 'address' => 'Jl. Salemba 5'],
                ['name' => 'Klinik Sehat', 'phone' => '081211110008', 'address' => 'Jl. Kesehatan 3'],
            ],
            'order_count' => 18,
        ]);

        $this->seedCompany([
            'company' => [
                'name' => 'PT Bersih Kilat Indonesia',
                'slug' => 'pt-bersih-kilat',
                'code' => 'PTB',
                'phone' => '0315552002',
                'email' => 'cs@bersihkilat.test',
                'address' => 'Jl. Basuki Rahmat 45',
                'city' => 'Surabaya',
                'primary_color' => '#2563EB',
                'secondary_color' => '#64748B',
            ],
            'admin' => ['name' => 'Admin Bersih Kilat', 'email' => 'admin@ptb.test', 'password' => 'rahasia123'],
            'kasirs' => [
                ['name' => 'Andi Kasir B', 'email' => 'kasir@ptb.test', 'password' => 'kasir12345'],
            ],
            'plans' => [
                ['code' => 'REG', 'plan' => 'Reguler 2 Hari', 'price' => 8000, 'description' => 'Per kilogram', 'icon' => 'shirt'],
                ['code' => 'VIP', 'plan' => 'VIP Same Day', 'price' => 18000, 'description' => 'Per kilogram', 'icon' => 'express'],
                ['code' => 'DRY', 'plan' => 'Dry Clean', 'price' => 45000, 'description' => 'Per item', 'icon' => 'dryclean'],
                ['code' => 'BLK', 'plan' => 'Selimut / Blanket', 'price' => 30000, 'description' => 'Per piece', 'icon' => 'blanket'],
                ['code' => 'CRT', 'plan' => 'Gorden', 'price' => 20000, 'description' => 'Per meter', 'icon' => 'curtain'],
            ],
            'customers' => [
                ['name' => 'PT Maju Jaya', 'phone' => '081322220001', 'address' => 'Kawasan Industri Rungkut'],
                ['name' => 'Wulan Sari', 'phone' => '081322220002', 'address' => 'Jl. Darmo 10'],
                ['name' => 'Bambang Hadi', 'phone' => '081322220003', 'address' => 'Jl. Ahmad Yani 7'],
                ['name' => 'Cafe Kopi Pagi', 'phone' => '081322220004', 'address' => 'Jl. Tunjungan 2'],
                ['name' => 'Apartemen Skyline', 'phone' => '081322220005', 'address' => 'Tower B Lantai 12'],
                ['name' => 'Nina Marlina', 'phone' => '081322220006', 'address' => 'Jl. Pucang Anom 8'],
            ],
            'order_count' => 14,
        ]);

        $this->command->info('Seeder siap: Owner platform + PT Fresh Wash (PTA) & PT Bersih Kilat (PTB).');
        $this->command->table(
            ['Akses', 'Email', 'Password'],
            [
                ['Super Admin (kelola semua PT)', 'owner@laundry.test', 'owner12345'],
                ['PTA Admin', 'admin@pta.test', 'rahasia123'],
                ['PTA Kasir', 'kasir@pta.test', 'kasir12345'],
                ['PTB Admin', 'admin@ptb.test', 'rahasia123'],
                ['PTB Kasir', 'kasir@ptb.test', 'kasir12345'],
            ]
        );
    }

    private function seedCompany(array $config): void
    {
        $company = Company::create($config['company'] + ['is_active' => true]);

        $admin = User::create([
            'company_id' => $company->id,
            'name' => $config['admin']['name'],
            'email' => $config['admin']['email'],
            'password' => $config['admin']['password'],
            'role' => 'admin',
            'is_active' => true,
            'language' => 'id',
        ]);

        foreach ($config['kasirs'] as $kasirData) {
            User::create([
                'company_id' => $company->id,
                'name' => $kasirData['name'],
                'email' => $kasirData['email'],
                'password' => $kasirData['password'],
                'role' => 'kasir',
                'is_active' => true,
                'language' => 'id',
            ]);
        }

        $planModels = [];
        foreach ($config['plans'] as $plan) {
            $planModels[$plan['code']] = Plan::withoutGlobalScopes()->create([
                ...$plan,
                'company_id' => $company->id,
                'user_id' => $admin->id,
            ]);
        }

        $customers = [];
        foreach ($config['customers'] as $customer) {
            $customers[] = Customer::withoutGlobalScopes()->create([
                ...$customer,
                'company_id' => $company->id,
                'user_id' => $admin->id,
            ]);
        }

        $planCodes = array_keys($planModels);
        $statuses = OrderStatus::cases();
        $payments = PaymentStatus::cases();

        for ($i = 0; $i < $config['order_count']; $i++) {
            $planCode = $planCodes[$i % count($planCodes)];
            $plan = $planModels[$planCode];
            $customer = $customers[$i % count($customers)];
            $qty = ($i % 5) + 2;
            $status = $statuses[$i % count($statuses)];
            $pay = $payments[$i % count($payments)];
            $subtotal = $plan->price * $qty;
            $paid = match ($pay) {
                PaymentStatus::PAID => $subtotal,
                PaymentStatus::PARTIAL => (int) round($subtotal * 0.5),
                default => 0,
            };

            $dayOffset = -($config['order_count'] - $i);
            $orderedAt = Carbon::now()->startOfMonth()->addDays(max(0, Carbon::now()->day + $dayOffset - 1));
            if ($orderedAt->month !== Carbon::now()->month) {
                $orderedAt = Carbon::now()->subDays(abs($dayOffset));
            }

            $order = Order::withoutGlobalScopes()->create([
                'company_id' => $company->id,
                'ordered_at' => $orderedAt->toDateString(),
                'finished_at' => $orderedAt->copy()->addDays(2)->toDateString(),
                'total' => $subtotal,
                'down_payment' => $paid,
                'payment' => $paid,
                'change' => 0,
                'payment_status' => $pay->value,
                'order_status' => $status->value,
                'customer_id' => $customer->id,
                'user_id' => $admin->id,
            ]);

            OrderItemLine::create([
                'price' => $plan->price,
                'quantity' => $qty,
                'subtotal' => $subtotal,
                'description' => $plan->plan.' '.$qty,
                'plan_id' => $plan->id,
                'order_id' => $order->id,
            ]);

            // beberapa order punya 2 item
            if ($i % 3 === 0) {
                $extraCode = $planCodes[($i + 1) % count($planCodes)];
                $extra = $planModels[$extraCode];
                $extraQty = 1;
                $extraSub = $extra->price * $extraQty;
                OrderItemLine::create([
                    'price' => $extra->price,
                    'quantity' => $extraQty,
                    'subtotal' => $extraSub,
                    'description' => $extra->plan.' '.$extraQty,
                    'plan_id' => $extra->id,
                    'order_id' => $order->id,
                ]);
                $order->update([
                    'total' => $subtotal + $extraSub,
                    'payment' => match ($pay) {
                        PaymentStatus::PAID => $subtotal + $extraSub,
                        PaymentStatus::PARTIAL => (int) round(($subtotal + $extraSub) * 0.5),
                        default => 0,
                    },
                    'down_payment' => match ($pay) {
                        PaymentStatus::PAID => $subtotal + $extraSub,
                        PaymentStatus::PARTIAL => (int) round(($subtotal + $extraSub) * 0.5),
                        default => 0,
                    },
                ]);
            }
        }

        $this->command->info(sprintf(
            '%s: %d layanan, %d pelanggan, %d order',
            $company->name,
            count($planModels),
            count($customers),
            $config['order_count']
        ));
    }
}
