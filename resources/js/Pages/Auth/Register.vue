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
        <Head title="Daftar · Spinly" />

        <div class="mb-7">
            <h1 class="text-2xl font-bold tracking-tight text-[#0b1f4d]">Buat akun perusahaan</h1>
            <p class="mt-1.5 text-sm text-slate-500">Setiap perusahaan punya data & karyawan sendiri</p>
        </div>

        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700" for="company_name">Nama perusahaan</label>
                <input
                    id="company_name"
                    v-model="form.company_name"
                    type="text"
                    class="w-full rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-sky-500 focus:ring-sky-500/25"
                    placeholder="Contoh: PT Fresh Wash"
                    required
                    autofocus
                />
                <p v-if="form.errors.company_name" class="mt-1 text-xs text-rose-600">{{ form.errors.company_name }}</p>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700" for="name">Nama admin</label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="w-full rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500/25"
                    required
                    autocomplete="name"
                />
                <p v-if="form.errors.name" class="mt-1 text-xs text-rose-600">{{ form.errors.name }}</p>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700" for="email">Email</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="w-full rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500/25"
                    required
                    autocomplete="username"
                />
                <p v-if="form.errors.email" class="mt-1 text-xs text-rose-600">{{ form.errors.email }}</p>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700" for="password">Password</label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="w-full rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500/25"
                    required
                    autocomplete="new-password"
                />
                <p v-if="form.errors.password" class="mt-1 text-xs text-rose-600">{{ form.errors.password }}</p>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700" for="password_confirmation">Konfirmasi password</label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="w-full rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500/25"
                    required
                    autocomplete="new-password"
                />
            </div>

            <button
                type="submit"
                class="mt-1 inline-flex w-full items-center justify-center rounded-xl bg-[#0b3d8c] px-4 py-3 text-sm font-semibold text-white shadow-md shadow-sky-900/15 transition hover:bg-[#092f6d] focus:outline-none focus:ring-2 focus:ring-sky-400/40 focus:ring-offset-2 disabled:opacity-60"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Memproses...' : 'Daftarkan perusahaan' }}
            </button>

            <p class="pt-1 text-center text-sm text-slate-500">
                Sudah punya akun?
                <Link :href="route('login')" class="font-semibold text-sky-700 hover:text-sky-900">Masuk</Link>
            </p>
        </form>
    </GuestLayout>
</template>
