<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { money } from '@/Composables/format';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    billed: Number,
    revenue: Number,
    outstanding: Number,
    counts: Object,
    todayOrders: Number,
    todayRevenue: Number,
    recent: Array,
    series: Array,
    maxSeries: Number,
    statusLabels: Object,
});

const compactMoney = (value) => {
    const n = Number(value || 0);
    if (n >= 1_000_000) return `${(n / 1_000_000).toFixed(1)}jt`;
    if (n >= 1_000) return `${Math.round(n / 1_000)}rb`;
    return String(n);
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-bold text-soft-900">Dashboard</h1>
                <p class="text-sm text-soft-500">Ringkasan operasional bulan ini</p>
            </div>
        </template>

        <div class="space-y-6">
            <div class="mb-1 flex flex-wrap gap-2">
                <Link :href="route('kasir.index')" class="btn-primary min-h-11">Buka Kasir</Link>
                <Link :href="route('queue.index')" class="btn-secondary min-h-11">Papan Antrian</Link>
            </div>

            <div class="grid grid-cols-2 gap-3 md:gap-4 xl:grid-cols-4">
                <div class="card p-4 md:p-5">
                    <p class="text-sm text-soft-500">Pendapatan hari ini</p>
                    <p class="mt-2 text-xl font-bold tracking-tight text-soft-900 md:text-2xl">{{ money(todayRevenue) }}</p>
                    <p class="mt-1 text-xs text-soft-400">{{ todayOrders }} pesanan</p>
                </div>
                <div class="card p-4 md:p-5">
                    <p class="text-sm text-soft-500">Omzet bulan ini</p>
                    <p class="mt-2 text-xl font-bold tracking-tight text-soft-900 md:text-2xl">{{ money(revenue) }}</p>
                    <p class="mt-1 text-xs text-soft-400">Tertagih {{ money(billed) }}</p>
                </div>
                <div class="card p-4 md:p-5">
                    <p class="text-sm text-soft-500">Piutang</p>
                    <p class="mt-2 text-xl font-bold tracking-tight text-amber-700 md:text-2xl">{{ money(outstanding) }}</p>
                    <p class="mt-1 text-xs text-soft-400">Belum tertagih penuh</p>
                </div>
                <div class="card p-4 md:p-5">
                    <p class="text-sm text-soft-500">Antrian aktif</p>
                    <p class="mt-2 text-xl font-bold tracking-tight text-brand-700 md:text-2xl">
                        {{ (counts.todo || 0) + (counts.in_progress || 0) }}
                    </p>
                    <p class="mt-1 text-xs text-soft-400">Todo + proses</p>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-5">
                <div class="card overflow-hidden p-4 md:col-span-3 md:p-5">
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h2 class="font-semibold text-soft-900">Pendapatan 7 hari</h2>
                            <p class="text-sm text-soft-500">Tren kas masuk harian</p>
                        </div>
                    </div>
                    <div class="flex h-48 max-w-full items-stretch gap-2 sm:h-52 sm:gap-3">
                        <div
                            v-for="(point, i) in series"
                            :key="i"
                            class="group flex min-w-0 flex-1 flex-col items-center"
                        >
                            <div class="flex w-full flex-1 items-end justify-center">
                                <div
                                    class="w-full max-w-[2.75rem] rounded-t-xl bg-gradient-to-t from-brand-600 to-brand-300 transition group-hover:from-brand-700 group-hover:to-brand-400"
                                    :style="{
                                        height: `${Math.max(point.value > 0 ? 12 : 4, Math.round((point.value / (maxSeries || 1)) * 100))}%`,
                                    }"
                                    :title="money(point.value)"
                                />
                            </div>
                            <span class="mt-2 truncate text-[10px] font-medium text-soft-500 sm:text-[11px]">{{ point.label }}</span>
                            <span class="mt-0.5 max-w-full truncate text-[9px] font-semibold tabular-nums text-soft-700 sm:text-[10px]">
                                {{ point.value ? compactMoney(point.value) : '—' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card p-4 md:col-span-2 md:p-5">
                    <h2 class="font-semibold text-soft-900">Status pesanan</h2>
                    <p class="mb-4 text-sm text-soft-500">Bulan berjalan</p>
                    <div class="space-y-3">
                        <div
                            v-for="(label, key) in statusLabels"
                            :key="key"
                            class="flex items-center justify-between rounded-xl bg-soft-50 px-3 py-2.5"
                        >
                            <StatusBadge :status="key" :label="label" />
                            <span class="text-sm font-semibold text-soft-800">{{ counts[key] || 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card overflow-hidden">
                <div class="flex items-center justify-between border-b border-soft-100 px-5 py-4">
                    <div>
                        <h2 class="font-semibold text-soft-900">Pesanan terbaru</h2>
                        <p class="text-sm text-soft-500">6 transaksi terakhir</p>
                    </div>
                    <Link :href="route('order.index')" class="text-sm font-semibold text-brand-700 hover:text-brand-800">
                        Lihat semua
                    </Link>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-soft-50/80 text-left text-xs uppercase tracking-wide text-soft-500">
                            <tr>
                                <th class="px-5 py-3 font-semibold">ID</th>
                                <th class="px-5 py-3 font-semibold">Pelanggan</th>
                                <th class="px-5 py-3 font-semibold">Tanggal</th>
                                <th class="px-5 py-3 font-semibold">Total</th>
                                <th class="px-5 py-3 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-soft-100">
                            <tr v-for="order in recent" :key="order.id" class="hover:bg-soft-50/60">
                                <td class="px-5 py-3 font-medium text-soft-800">#{{ order.id }}</td>
                                <td class="px-5 py-3 text-soft-700">{{ order.customer?.name || '—' }}</td>
                                <td class="px-5 py-3 text-soft-500">{{ order.ordered_at }}</td>
                                <td class="px-5 py-3 font-medium text-soft-800">{{ money(order.total) }}</td>
                                <td class="px-5 py-3">
                                    <StatusBadge :status="order.order_status" :label="statusLabels[order.order_status]" />
                                </td>
                            </tr>
                            <tr v-if="!recent.length">
                                <td colspan="5" class="px-5 py-10 text-center text-soft-400">Belum ada pesanan</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
