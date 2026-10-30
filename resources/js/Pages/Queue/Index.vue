<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { money } from '@/Composables/format';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    columns: Object,
    statusLabels: Object,
    paymentLabels: Object,
    nextStatus: Object,
});

const columnOrder = ['todo', 'in_progress', 'done', 'completed'];

const columnAccent = {
    todo: 'border-amber-200 bg-amber-50/60',
    in_progress: 'border-sky-200 bg-sky-50/60',
    done: 'border-emerald-200 bg-emerald-50/60',
    completed: 'border-soft-200 bg-soft-50/80',
};

const advanceLabel = {
    todo: 'Proses',
    in_progress: 'Selesai',
    done: 'Diambil',
};

const moveTo = (orderId, status) => {
    router.patch(route('queue.status', orderId), { order_status: status }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Antrian" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-bold text-soft-900">Papan Antrian</h1>
                <p class="text-sm text-soft-500">Diterima → diproses → selesai → diambil</p>
            </div>
        </template>

        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-soft-500">Ketuk aksi cepat untuk memindah status tanpa buka form.</p>
            <Link :href="route('kasir.index')" class="btn-primary min-h-11">+ Kasir baru</Link>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4 xl:h-[calc(100dvh-11rem)] xl:min-h-[28rem] xl:overflow-hidden">
            <section
                v-for="key in columnOrder"
                :key="key"
                class="flex min-h-0 flex-col rounded-2xl border p-3"
                :class="columnAccent[key]"
            >
                <header class="mb-3 flex shrink-0 items-center justify-between px-1">
                    <div>
                        <h2 class="font-bold text-soft-900">{{ statusLabels[key] }}</h2>
                        <p class="text-xs text-soft-500">{{ (columns[key] || []).length }} pesanan</p>
                    </div>
                </header>

                <div class="min-h-0 flex-1 space-y-3 overflow-y-auto pr-0.5">
                    <article
                        v-for="order in columns[key] || []"
                        :key="order.id"
                        class="rounded-2xl border border-white/80 bg-white p-4 shadow-soft transition active:scale-[0.99]"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-xs font-semibold text-brand-700">#{{ order.id }}</p>
                                <p class="mt-0.5 font-bold text-soft-900">{{ order.customer?.name || 'Pelanggan' }}</p>
                                <p class="text-xs text-soft-500">{{ order.ordered_at }}</p>
                            </div>
                            <StatusBadge :status="order.payment_status" :label="paymentLabels[order.payment_status]" />
                        </div>

                        <ul class="mt-3 space-y-1 border-t border-soft-100 pt-2 text-xs text-soft-600">
                            <li v-for="(item, i) in order.items" :key="i" class="flex justify-between gap-2">
                                <span class="truncate">{{ item.label }}</span>
                                <span class="shrink-0 font-medium">×{{ item.qty }}</span>
                            </li>
                            <li v-if="!order.items?.length" class="text-soft-400">Tanpa item</li>
                        </ul>

                        <div class="mt-3 flex items-center justify-between gap-2">
                            <p class="text-sm font-bold text-soft-900">{{ money(order.total) }}</p>
                            <div class="flex gap-1.5">
                                <Link
                                    :href="route('order.edit', order.id)"
                                    class="touch-btn rounded-xl border border-soft-200 bg-soft-50 px-3 text-xs text-soft-600"
                                >
                                    Detail
                                </Link>
                                <button
                                    v-if="nextStatus[key]"
                                    type="button"
                                    class="touch-btn rounded-xl bg-brand-600 px-3 text-xs text-white shadow-soft active:scale-95"
                                    @click="moveTo(order.id, nextStatus[key])"
                                >
                                    {{ advanceLabel[key] }} →
                                </button>
                            </div>
                        </div>

                        <div v-if="key !== 'todo'" class="mt-2 flex flex-wrap gap-1">
                            <button
                                v-for="target in columnOrder.filter((s) => s !== key)"
                                :key="target"
                                type="button"
                                class="rounded-lg px-2 py-1 text-[11px] font-medium text-soft-500 hover:bg-soft-100 hover:text-soft-800"
                                @click="moveTo(order.id, target)"
                            >
                                → {{ statusLabels[target] }}
                            </button>
                        </div>
                    </article>

                    <p
                        v-if="!(columns[key] || []).length"
                        class="rounded-xl border border-dashed border-soft-200 bg-white/50 px-3 py-8 text-center text-sm text-soft-400"
                    >
                        Kosong
                    </p>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
