<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { money } from '@/Composables/format';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    period: String,
    anchor: String,
    labels: Array,
    billed: Number,
    revenue: Number,
    outstanding: Number,
    orders: Number,
    avg: Number,
    delta: Number,
    status: Object,
    statusLabels: Object,
    topServices: Array,
    series: Array,
    maxSeries: Number,
});

const period = ref(props.period);
const date = ref(props.anchor);

const apply = () => {
    router.get(route('report.index'), { period: period.value, date: date.value }, { preserveState: true });
};
</script>

<template>
    <Head title="Laporan" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-bold text-soft-900">Laporan</h1>
                <p class="text-sm text-soft-500">{{ labels[0] }} · {{ labels[1] }}</p>
            </div>
        </template>

        <div class="space-y-6">
            <form class="card flex flex-col gap-3 p-4 sm:flex-row sm:items-end" @submit.prevent="apply">
                <div class="flex-1">
                    <label class="label-field">Periode</label>
                    <select v-model="period" class="input-field">
                        <option value="daily">Harian</option>
                        <option value="weekly">Mingguan</option>
                        <option value="monthly">Bulanan</option>
                        <option value="yearly">Tahunan</option>
                    </select>
                </div>
                <div class="flex-1">
                    <label class="label-field">Tanggal acuan</label>
                    <input v-model="date" type="date" class="input-field" />
                </div>
                <button type="submit" class="btn-primary">Terapkan</button>
            </form>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="card p-5">
                    <p class="text-sm text-soft-500">Pendapatan</p>
                    <p class="mt-2 text-2xl font-bold">{{ money(revenue) }}</p>
                    <p v-if="delta !== null" class="mt-1 text-xs" :class="delta >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                        {{ delta >= 0 ? '+' : '' }}{{ delta }}% vs periode sebelumnya
                    </p>
                </div>
                <div class="card p-5">
                    <p class="text-sm text-soft-500">Tertagih</p>
                    <p class="mt-2 text-2xl font-bold">{{ money(billed) }}</p>
                </div>
                <div class="card p-5">
                    <p class="text-sm text-soft-500">Pesanan</p>
                    <p class="mt-2 text-2xl font-bold">{{ orders }}</p>
                    <p class="mt-1 text-xs text-soft-400">Rata-rata {{ money(avg) }}</p>
                </div>
                <div class="card p-5">
                    <p class="text-sm text-soft-500">Piutang</p>
                    <p class="mt-2 text-2xl font-bold text-amber-700">{{ money(outstanding) }}</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-5">
                <div class="card p-5 lg:col-span-3">
                    <h2 class="mb-4 font-semibold text-soft-900">Grafik pendapatan</h2>
                    <div class="flex h-44 items-end gap-1.5">
                        <div v-for="(point, i) in series" :key="i" class="flex flex-1 flex-col items-center gap-2">
                            <div
                                class="w-full rounded-t-lg bg-brand-500/80"
                                :style="{ height: `${Math.max(6, (point.value / maxSeries) * 100)}%` }"
                            />
                            <span class="text-[10px] text-soft-500">{{ point.label }}</span>
                        </div>
                    </div>
                </div>
                <div class="card p-5 lg:col-span-2">
                    <h2 class="mb-4 font-semibold text-soft-900">Status</h2>
                    <div class="space-y-2">
                        <div v-for="(label, key) in statusLabels" :key="key" class="flex items-center justify-between">
                            <StatusBadge :status="key" :label="label" />
                            <span class="font-semibold text-soft-800">{{ status[key] || 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card p-5">
                <h2 class="mb-4 font-semibold text-soft-900">Layanan terlaris</h2>
                <div class="space-y-3">
                    <div
                        v-for="service in topServices"
                        :key="service.name"
                        class="flex items-center justify-between rounded-xl bg-soft-50 px-4 py-3"
                    >
                        <div>
                            <p class="font-medium text-soft-900">{{ service.name }}</p>
                            <p class="text-xs text-soft-500">{{ service.qty }} item</p>
                        </div>
                        <p class="font-semibold">{{ money(service.amount) }}</p>
                    </div>
                    <p v-if="!topServices.length" class="text-sm text-soft-400">Belum ada data</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
