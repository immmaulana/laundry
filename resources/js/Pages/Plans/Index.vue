<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import PlanIcon from '@/Components/PlanIcon.vue';
import { money } from '@/Composables/format';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    plans: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

watch(search, (value) => {
    router.get(route('plan.index'), { search: value || undefined }, { preserveState: true, replace: true });
});

const destroy = (id) => {
    if (confirm('Hapus layanan ini?')) {
        router.delete(route('plan.destroy', id));
    }
};
</script>

<template>
    <Head title="Layanan" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-bold text-soft-900">Layanan & Harga</h1>
                <p class="text-sm text-soft-500">Paket laundry yang ditawarkan</p>
            </div>
        </template>

        <div class="space-y-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <input v-model="search" type="search" class="input-field max-w-sm" placeholder="Cari kode / nama..." />
                <Link :href="route('plan.create')" class="btn-primary">Tambah layanan</Link>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div v-for="plan in plans.data" :key="plan.id" class="card p-5">
                    <div class="flex items-start gap-4">
                        <PlanIcon :name="plan.icon || 'wash'" :logo-url="plan.logo_url" size="lg" />
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-brand-600">{{ plan.code }}</p>
                                    <h3 class="mt-1 text-lg font-bold text-soft-900">{{ plan.plan }}</h3>
                                    <p class="mt-1 text-sm text-soft-500">{{ plan.description || '—' }}</p>
                                </div>
                                <p class="shrink-0 text-base font-bold text-soft-900">{{ money(plan.price) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex gap-3 border-t border-soft-100 pt-4">
                        <Link :href="route('plan.edit', plan.id)" class="text-sm font-semibold text-brand-700">Edit</Link>
                        <button type="button" class="text-sm font-semibold text-rose-600" @click="destroy(plan.id)">Hapus</button>
                    </div>
                </div>
            </div>

            <Pagination :links="plans.links" />
        </div>
    </AuthenticatedLayout>
</template>
