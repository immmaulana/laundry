<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    languages: Object,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    language: props.user.language || 'id',
});

const submit = () => form.post(route('setting.update'));
</script>

<template>
    <Head title="Pengaturan" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-bold text-soft-900">Pengaturan</h1>
                <p class="text-sm text-soft-500">Profil akun Anda</p>
            </div>
        </template>

        <form class="card max-w-xl space-y-4 p-6" @submit.prevent="submit">
            <div>
                <label class="label-field">Nama</label>
                <input v-model="form.name" type="text" class="input-field" required />
            </div>
            <div>
                <label class="label-field">Email</label>
                <input v-model="form.email" type="email" class="input-field" required />
            </div>
            <div>
                <label class="label-field">Bahasa</label>
                <select v-model="form.language" class="input-field">
                    <option v-for="(label, key) in languages" :key="key" :value="key">{{ label }}</option>
                </select>
            </div>
            <div class="rounded-xl bg-soft-50 px-4 py-3 text-sm text-soft-600">
                <p>
                    Role:
                    <span class="font-semibold capitalize text-soft-900">{{ user.role }}</span>
                </p>
                <p v-if="$page.props.auth.company" class="mt-1">
                    Perusahaan:
                    <span class="font-semibold text-soft-900">{{ $page.props.auth.company.name }}</span>
                    <span class="text-soft-400">({{ $page.props.auth.company.code }})</span>
                </p>
            </div>
            <button type="submit" class="btn-primary" :disabled="form.processing">Simpan perubahan</button>
        </form>
    </AuthenticatedLayout>
</template>
