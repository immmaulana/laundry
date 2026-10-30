<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    customers: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

watch(search, (value) => {
    router.get(route('customer.index'), { search: value || undefined }, {
        preserveState: true,
        replace: true,
    });
});

const destroy = (id) => {
    if (confirm('Hapus pelanggan ini?')) {
        router.delete(route('customer.destroy', id));
    }
};
</script>

<template>
    <Head title="Pelanggan" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-bold text-soft-900">Pelanggan</h1>
                <p class="text-sm text-soft-500">Data pelanggan laundry</p>
            </div>
        </template>

        <div class="space-y-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <input v-model="search" type="search" class="input-field max-w-sm" placeholder="Cari nama / telepon..." />
                <Link :href="route('customer.create')" class="btn-primary">Tambah pelanggan</Link>
            </div>

            <div class="space-y-3 md:hidden">
                <article
                    v-for="customer in customers.data"
                    :key="`m-${customer.id}`"
                    class="card p-4"
                >
                    <p class="font-bold text-soft-900">{{ customer.name }}</p>
                    <p class="mt-1 text-sm text-soft-600">{{ customer.phone }}</p>
                    <p class="mt-1 text-sm text-soft-500">{{ customer.address || '—' }}</p>
                    <div class="mt-3 flex gap-2">
                        <Link :href="route('customer.edit', customer.id)" class="btn-secondary min-h-11 flex-1 text-center">Edit</Link>
                        <button type="button" class="btn-danger min-h-11" @click="destroy(customer.id)">Hapus</button>
                    </div>
                </article>
                <p v-if="!customers.data.length" class="card py-10 text-center text-soft-400">Belum ada data</p>
            </div>

            <div class="card hidden overflow-hidden md:block">
                <div class="table-scroll">
                    <table class="min-w-[520px] w-full text-sm">
                        <thead class="bg-soft-50/80 text-left text-xs uppercase tracking-wide text-soft-500">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Nama</th>
                                <th class="px-5 py-3 font-semibold">Telepon</th>
                                <th class="px-5 py-3 font-semibold">Alamat</th>
                                <th class="px-5 py-3 font-semibold" />
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-soft-100">
                            <tr v-for="customer in customers.data" :key="customer.id" class="hover:bg-soft-50/50">
                                <td class="px-5 py-3 font-medium text-soft-900">{{ customer.name }}</td>
                                <td class="px-5 py-3 text-soft-600">{{ customer.phone }}</td>
                                <td class="px-5 py-3 text-soft-500">{{ customer.address || '—' }}</td>
                                <td class="px-5 py-3 text-right">
                                    <Link :href="route('customer.edit', customer.id)" class="mr-3 text-sm font-semibold text-brand-700">
                                        Edit
                                    </Link>
                                    <button type="button" class="text-sm font-semibold text-rose-600" @click="destroy(customer.id)">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!customers.data.length">
                                <td colspan="4" class="px-5 py-10 text-center text-soft-400">Belum ada data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <Pagination :links="customers.links" />
        </div>
    </AuthenticatedLayout>
</template>
