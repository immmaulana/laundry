<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    company: Object,
});

const page = usePage();
const editing = computed(() => !!props.company);
const isSuper = computed(() => page.props.auth.user?.role === 'super_admin');

const form = useForm({
    name: props.company?.name || '',
    slug: props.company?.slug || '',
    code: props.company?.code || '',
    phone: props.company?.phone || '',
    email: props.company?.email || '',
    address: props.company?.address || '',
    city: props.company?.city || '',
    is_active: props.company?.is_active ?? true,
    admin_name: '',
    admin_email: '',
    admin_password: '',
});

const submit = () => {
    if (editing.value) {
        form.put(route('companies.update', props.company.id));
    } else {
        form.post(route('companies.store'));
    }
};
</script>

<template>
    <Head :title="editing ? 'Edit perusahaan' : 'Tambah perusahaan'" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-bold text-soft-900">
                    {{ editing ? 'Edit perusahaan' : 'Tambah perusahaan' }}
                </h1>
                <p class="text-sm text-soft-500">
                    {{ isSuper ? 'Data tenant / PT pelanggan' : 'Profil perusahaan Anda' }}
                </p>
            </div>
        </template>

        <form class="card max-w-2xl space-y-4 p-6" @submit.prevent="submit">
            <div>
                <label class="label-field">Nama perusahaan</label>
                <input v-model="form.name" type="text" class="input-field" required placeholder="PT Fresh Wash Nusantara" />
                <p v-if="form.errors.name" class="mt-1 text-xs text-rose-600">{{ form.errors.name }}</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label-field">Kode</label>
                    <input
                        v-model="form.code"
                        type="text"
                        class="input-field uppercase"
                        required
                        :readonly="!isSuper && editing"
                        placeholder="PTA"
                    />
                    <p v-if="form.errors.code" class="mt-1 text-xs text-rose-600">{{ form.errors.code }}</p>
                </div>
                <div>
                    <label class="label-field">Kota</label>
                    <input v-model="form.city" type="text" class="input-field" placeholder="Jakarta" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label-field">Telepon</label>
                    <input v-model="form.phone" type="text" class="input-field" />
                </div>
                <div>
                    <label class="label-field">Email perusahaan</label>
                    <input v-model="form.email" type="email" class="input-field" />
                </div>
            </div>

            <div>
                <label class="label-field">Alamat</label>
                <textarea v-model="form.address" rows="2" class="input-field" />
            </div>

            <label v-if="isSuper" class="flex items-center gap-2 text-sm text-soft-700">
                <input v-model="form.is_active" type="checkbox" class="rounded border-soft-300 text-brand-600 focus:ring-brand-500" />
                Perusahaan aktif
            </label>

            <div v-if="isSuper && !editing" class="space-y-3 rounded-2xl border border-dashed border-soft-200 bg-soft-50/70 p-4">
                <p class="text-sm font-semibold text-soft-800">Admin perusahaan (opsional)</p>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input v-model="form.admin_name" type="text" class="input-field" placeholder="Nama admin" />
                    <input v-model="form.admin_email" type="email" class="input-field" placeholder="Email admin" />
                </div>
                <input v-model="form.admin_password" type="password" class="input-field" placeholder="Password admin (min 8)" />
                <p v-if="form.errors.admin_email" class="text-xs text-rose-600">{{ form.errors.admin_email }}</p>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary" :disabled="form.processing">Simpan</button>
                <Link
                    v-if="isSuper"
                    :href="route('companies.index')"
                    class="btn-secondary"
                >
                    Kembali
                </Link>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
