<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    companies: Array,
    stats: Object,
});

const destroy = (company) => {
    if (confirm(`Hapus ${company.name}?`)) {
        router.delete(route('companies.destroy', company.id));
    }
};

const toggle = (company) => {
    router.put(route('companies.update', company.id), {
        ...company,
        is_active: !company.is_active,
    }, { preserveScroll: true });
};
</script>

<template>
    <Head title="Manajemen Perusahaan" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-bold text-soft-900">Manajemen Perusahaan</h1>
                <p class="text-sm text-soft-500">Kelola semua PT / tenant yang berlangganan</p>
            </div>
        </template>

        <div class="space-y-6">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="card p-5">
                    <p class="text-sm text-soft-500">Total PT</p>
                    <p class="mt-2 text-2xl font-bold text-soft-900">{{ stats.total }}</p>
                </div>
                <div class="card p-5">
                    <p class="text-sm text-soft-500">Aktif</p>
                    <p class="mt-2 text-2xl font-bold text-brand-700">{{ stats.active }}</p>
                </div>
                <div class="card p-5">
                    <p class="text-sm text-soft-500">Semua pesanan</p>
                    <p class="mt-2 text-2xl font-bold text-soft-900">{{ stats.orders }}</p>
                </div>
                <div class="card p-5">
                    <p class="text-sm text-soft-500">Semua pelanggan</p>
                    <p class="mt-2 text-2xl font-bold text-soft-900">{{ stats.customers }}</p>
                </div>
            </div>

            <div class="flex justify-end">
                <Link :href="route('companies.create')" class="btn-primary">Tambah perusahaan</Link>
            </div>

            <div class="card overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-soft-50/80 text-left text-xs uppercase tracking-wide text-soft-500">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Perusahaan</th>
                            <th class="px-5 py-3 font-semibold">Kota</th>
                            <th class="px-5 py-3 font-semibold">Data</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 font-semibold" />
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-soft-100">
                        <tr v-for="company in companies" :key="company.id" class="hover:bg-soft-50/50">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-xl shadow-soft ring-1 ring-black/5"
                                        :style="company.logo_url ? {} : { background: company.primary_color || '#0B3D8C' }"
                                    >
                                        <img
                                            v-if="company.logo_url"
                                            :src="company.logo_url"
                                            :alt="company.name"
                                            class="h-full w-full bg-white object-contain p-1"
                                        />
                                        <span v-else class="text-[10px] font-bold text-white">
                                            {{ company.code?.slice(0, 2) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-soft-900">{{ company.name }}</p>
                                        <p class="text-xs text-soft-500">
                                            {{ company.code }} · {{ company.email || '—' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-soft-600">{{ company.city || '—' }}</td>
                            <td class="px-5 py-4 text-soft-600">
                                <p>{{ company.users_count }} karyawan</p>
                                <p class="text-xs text-soft-400">
                                    {{ company.customers_count }} pelanggan · {{ company.orders_count }} order · {{ company.plans_count }} layanan
                                </p>
                            </td>
                            <td class="px-5 py-4">
                                <button
                                    type="button"
                                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"
                                    :class="company.is_active
                                        ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                                        : 'bg-soft-100 text-soft-500 ring-soft-200'"
                                    @click="toggle(company)"
                                >
                                    {{ company.is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <Link
                                    :href="route('companies.edit', company.id)"
                                    class="mr-3 text-sm font-semibold text-brand-700"
                                >
                                    Edit
                                </Link>
                                <button
                                    type="button"
                                    class="text-sm font-semibold text-rose-600"
                                    @click="destroy(company)"
                                >
                                    Hapus
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!companies.length">
                            <td colspan="5" class="px-5 py-10 text-center text-soft-400">Belum ada perusahaan</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
