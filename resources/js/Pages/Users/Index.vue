<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    users: Array,
    matrix: Array,
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'kasir',
});

const submit = () => {
    form.post(route('users.store'), {
        onSuccess: () => form.reset(),
    });
};

const updateUser = (user, patch) => {
    router.patch(route('users.update', user.id), patch, { preserveScroll: true });
};
</script>

<template>
    <Head title="Karyawan" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-bold text-soft-900">Karyawan & Akses</h1>
                <p class="text-sm text-soft-500">Kelola role admin dan kasir</p>
            </div>
        </template>

        <div class="grid gap-6 xl:grid-cols-5">
            <div class="space-y-4 xl:col-span-3">
                <div class="card overflow-hidden">
                    <table class="min-w-full text-sm">
                        <thead class="bg-soft-50/80 text-left text-xs uppercase tracking-wide text-soft-500">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Nama</th>
                                <th class="px-5 py-3 font-semibold">Role</th>
                                <th class="px-5 py-3 font-semibold">Aktif</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-soft-100">
                            <tr v-for="user in users" :key="user.id">
                                <td class="px-5 py-3">
                                    <p class="font-medium text-soft-900">{{ user.name }}</p>
                                    <p class="text-xs text-soft-500">{{ user.email }}</p>
                                </td>
                                <td class="px-5 py-3">
                                    <select
                                        class="input-field py-1.5"
                                        :value="user.role"
                                        @change="updateUser(user, { role: $event.target.value, is_active: user.is_active })"
                                    >
                                        <option value="admin">Admin</option>
                                        <option value="kasir">Kasir</option>
                                    </select>
                                </td>
                                <td class="px-5 py-3">
                                    <label class="inline-flex items-center gap-2 text-sm text-soft-600">
                                        <input
                                            type="checkbox"
                                            class="rounded border-soft-300 text-brand-600 focus:ring-brand-500"
                                            :checked="user.is_active"
                                            @change="updateUser(user, { role: user.role, is_active: $event.target.checked })"
                                        />
                                        Aktif
                                    </label>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="card p-5">
                    <h2 class="mb-3 font-semibold text-soft-900">Matriks akses</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-xs uppercase text-soft-500">
                                <tr>
                                    <th class="py-2 pr-4">Modul</th>
                                    <th class="py-2 pr-4">Kasir</th>
                                    <th class="py-2">Admin</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-soft-100">
                                <tr v-for="row in matrix" :key="row.modul">
                                    <td class="py-2.5 pr-4 font-medium text-soft-800">{{ row.modul }}</td>
                                    <td class="py-2.5 pr-4 text-soft-600">
                                        {{ row.kasir === true ? 'Ya' : row.kasir === false ? 'Tidak' : row.kasir }}
                                    </td>
                                    <td class="py-2.5 text-soft-600">{{ row.admin ? 'Ya' : 'Tidak' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <form class="card h-fit space-y-4 p-5 xl:col-span-2" @submit.prevent="submit">
                <h2 class="font-semibold text-soft-900">Tambah karyawan</h2>
                <div>
                    <label class="label-field">Nama</label>
                    <input v-model="form.name" type="text" class="input-field" required />
                </div>
                <div>
                    <label class="label-field">Email</label>
                    <input v-model="form.email" type="email" class="input-field" required />
                </div>
                <div>
                    <label class="label-field">Password</label>
                    <input v-model="form.password" type="password" class="input-field" required />
                </div>
                <div>
                    <label class="label-field">Role</label>
                    <select v-model="form.role" class="input-field">
                        <option value="kasir">Kasir</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary w-full" :disabled="form.processing">Tambahkan</button>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
