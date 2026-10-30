<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    customer: Object,
});

const editing = computed(() => !!props.customer);

const form = useForm({
    name: props.customer?.name || '',
    phone: props.customer?.phone || '',
    address: props.customer?.address || '',
});

const submit = () => {
    if (editing.value) {
        form.put(route('customer.update', props.customer.id));
    } else {
        form.post(route('customer.store'));
    }
};
</script>

<template>
    <Head :title="editing ? 'Edit pelanggan' : 'Tambah pelanggan'" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-bold text-soft-900">{{ editing ? 'Edit pelanggan' : 'Tambah pelanggan' }}</h1>
                <p class="text-sm text-soft-500">Isi data kontak pelanggan</p>
            </div>
        </template>

        <form class="card max-w-xl space-y-4 p-6" @submit.prevent="submit">
            <div>
                <label class="label-field">Nama</label>
                <input v-model="form.name" type="text" class="input-field" required />
                <p v-if="form.errors.name" class="mt-1 text-xs text-rose-600">{{ form.errors.name }}</p>
            </div>
            <div>
                <label class="label-field">Telepon</label>
                <input v-model="form.phone" type="text" class="input-field" required />
                <p v-if="form.errors.phone" class="mt-1 text-xs text-rose-600">{{ form.errors.phone }}</p>
            </div>
            <div>
                <label class="label-field">Alamat</label>
                <textarea v-model="form.address" rows="3" class="input-field" />
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary" :disabled="form.processing">Simpan</button>
                <Link :href="route('customer.index')" class="btn-secondary">Batal</Link>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
