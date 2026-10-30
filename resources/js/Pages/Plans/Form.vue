<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    plan: Object,
});

const editing = computed(() => !!props.plan);

const form = useForm({
    code: props.plan?.code || '',
    plan: props.plan?.plan || '',
    price: props.plan?.price || 0,
    description: props.plan?.description || '',
});

const submit = () => {
    if (editing.value) {
        form.put(route('plan.update', props.plan.id));
    } else {
        form.post(route('plan.store'));
    }
};
</script>

<template>
    <Head :title="editing ? 'Edit layanan' : 'Tambah layanan'" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-bold text-soft-900">{{ editing ? 'Edit layanan' : 'Tambah layanan' }}</h1>
                <p class="text-sm text-soft-500">Atur nama dan harga paket</p>
            </div>
        </template>

        <form class="card max-w-xl space-y-4 p-6" @submit.prevent="submit">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label-field">Kode</label>
                    <input v-model="form.code" type="text" class="input-field" required />
                </div>
                <div>
                    <label class="label-field">Harga</label>
                    <input v-model="form.price" type="number" min="0" class="input-field" required />
                </div>
            </div>
            <div>
                <label class="label-field">Nama layanan</label>
                <input v-model="form.plan" type="text" class="input-field" required />
            </div>
            <div>
                <label class="label-field">Deskripsi</label>
                <input v-model="form.description" type="text" class="input-field" placeholder="Per kilogram / per piece" />
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary" :disabled="form.processing">Simpan</button>
                <Link :href="route('plan.index')" class="btn-secondary">Batal</Link>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
