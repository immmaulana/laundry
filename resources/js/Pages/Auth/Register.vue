<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    company_name: '',
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Daftar perusahaan" />

        <div class="mb-6">
            <h1 class="text-xl font-bold text-soft-900">Buat akun perusahaan</h1>
            <p class="mt-1 text-sm text-soft-500">Setiap perusahaan punya data & karyawan sendiri</p>
        </div>

        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <label class="label-field" for="company_name">Nama perusahaan</label>
                <input
                    id="company_name"
                    v-model="form.company_name"
                    type="text"
                    class="input-field"
                    placeholder="Contoh: PT Fresh Wash"
                    required
                    autofocus
                />
                <p v-if="form.errors.company_name" class="mt-1 text-xs text-rose-600">{{ form.errors.company_name }}</p>
            </div>

            <div>
                <label class="label-field" for="name">Nama admin</label>
                <input id="name" v-model="form.name" type="text" class="input-field" required autocomplete="name" />
                <p v-if="form.errors.name" class="mt-1 text-xs text-rose-600">{{ form.errors.name }}</p>
            </div>

            <div>
                <label class="label-field" for="email">Email</label>
                <input id="email" v-model="form.email" type="email" class="input-field" required autocomplete="username" />
                <p v-if="form.errors.email" class="mt-1 text-xs text-rose-600">{{ form.errors.email }}</p>
            </div>

            <div>
                <label class="label-field" for="password">Password</label>
                <input id="password" v-model="form.password" type="password" class="input-field" required autocomplete="new-password" />
                <p v-if="form.errors.password" class="mt-1 text-xs text-rose-600">{{ form.errors.password }}</p>
            </div>

            <div>
                <label class="label-field" for="password_confirmation">Konfirmasi password</label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="input-field"
                    required
                    autocomplete="new-password"
                />
            </div>

            <button type="submit" class="btn-primary w-full" :disabled="form.processing">Daftarkan perusahaan</button>

            <p class="text-center text-sm text-soft-500">
                Sudah punya akun?
                <Link :href="route('login')" class="font-semibold text-brand-700 hover:text-brand-800">Masuk</Link>
            </p>
        </form>
    </GuestLayout>
</template>
