<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { money } from '@/Composables/format';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    orders: Object,
    filters: Object,
    statusLabels: Object,
    paymentLabels: Object,
});

const search = ref(props.filters.search || '');

watch(search, (value) => {
    router.get(route('order.index'), { search: value || undefined }, { preserveState: true, replace: true });
});

const destroy = (id) => {
    if (confirm('Hapus pesanan ini?')) {
        router.delete(route('order.destroy', id));
    }
};
</script>

<template>
    <Head title="Pesanan" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-bold text-soft-900">Pesanan</h1>
                <p class="text-sm text-soft-500">Kelola status cucian & pembayaran</p>
            </div>
        </template>

        <div class="space-y-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <input v-model="search" type="search" class="input-field max-w-sm" placeholder="Cari ID / nama pelanggan..." />
                <div class="flex gap-2">
                    <Link :href="route('kasir.index')" class="btn-secondary">Buka Kasir</Link>
                    <Link :href="route('order.create')" class="btn-primary">Pesanan baru</Link>
                </div>
            </div>

            <div class="card overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-soft-50/80 text-left text-xs uppercase tracking-wide text-soft-500">
                        <tr>
                            <th class="px-5 py-3 font-semibold">ID</th>
                            <th class="px-5 py-3 font-semibold">Pelanggan</th>
                            <th class="px-5 py-3 font-semibold">Tanggal</th>
                            <th class="px-5 py-3 font-semibold">Total</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 font-semibold">Bayar</th>
                            <th class="px-5 py-3 font-semibold" />
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-soft-100">
                        <tr v-for="order in orders.data" :key="order.id" class="hover:bg-soft-50/50">
                            <td class="px-5 py-3 font-medium text-soft-900">#{{ order.id }}</td>
                            <td class="px-5 py-3 text-soft-700">{{ order.customer?.name || '—' }}</td>
                            <td class="px-5 py-3 text-soft-500">{{ order.ordered_at }}</td>
                            <td class="px-5 py-3 font-medium">{{ money(order.total) }}</td>
                            <td class="px-5 py-3">
                                <StatusBadge :status="order.order_status" :label="statusLabels[order.order_status]" />
                            </td>
                            <td class="px-5 py-3">
                                <StatusBadge :status="order.payment_status" :label="paymentLabels[order.payment_status]" />
                            </td>
                            <td class="px-5 py-3 text-right">
                                <Link :href="route('order.edit', order.id)" class="mr-3 text-sm font-semibold text-brand-700">Detail</Link>
                                <button type="button" class="text-sm font-semibold text-rose-600" @click="destroy(order.id)">Hapus</button>
                            </td>
                        </tr>
                        <tr v-if="!orders.data.length">
                            <td colspan="7" class="px-5 py-10 text-center text-soft-400">Belum ada pesanan</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="orders.links" />
        </div>
    </AuthenticatedLayout>
</template>
