<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk · KucekPos" />

        <template #brand>
            <div class="mb-7 flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <h1 class="text-2xl font-bold tracking-tight text-[#0b1f4d]">Masuk</h1>
                    <p class="mt-1 text-sm leading-snug text-slate-500">
                        Akses panel operasional laundry Anda
                    </p>
                </div>
                <Link href="/" class="ml-auto shrink-0">
                    <img
                        src="/brand/kucekpos-logo.png"
                        alt="KucekPos"
                        class="kucek-logo-enter h-16 w-auto origin-center sm:h-[4.5rem]"
                    />
                </Link>
            </div>
        </template>

        <div
            v-if="status"
            class="mb-4 rounded-xl bg-sky-50 px-3 py-2 text-sm text-sky-800 ring-1 ring-sky-100"
        >
            {{ status }}
        </div>

        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700" for="email">Email</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="w-full rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-sky-500 focus:ring-sky-500/25"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="nama@perusahaan.com"
                />
                <p v-if="form.errors.email" class="mt-1 text-xs text-rose-600">{{ form.errors.email }}</p>
            </div>

            <div>
                <div class="mb-1.5 flex items-center justify-between gap-3">
                    <label class="block text-sm font-medium text-slate-700" for="password">Password</label>
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-xs font-semibold text-sky-700 hover:text-sky-900"
                    >
                        Lupa password?
                    </Link>
                </div>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="w-full rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-sky-500 focus:ring-sky-500/25"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />
                <p v-if="form.errors.password" class="mt-1 text-xs text-rose-600">{{ form.errors.password }}</p>
            </div>

            <label class="flex items-center gap-2.5 text-sm text-slate-600">
                <input
                    v-model="form.remember"
                    type="checkbox"
                    class="rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                />
                Ingat saya
            </label>

            <button
                type="submit"
                class="mt-1 inline-flex w-full items-center justify-center rounded-xl bg-[#0b3d8c] px-4 py-3 text-sm font-semibold text-white shadow-md shadow-sky-900/15 transition hover:bg-[#092f6d] focus:outline-none focus:ring-2 focus:ring-sky-400/40 focus:ring-offset-2 disabled:opacity-60"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Memproses...' : 'Masuk ke KucekPos' }}
            </button>
        </form>
    </GuestLayout>
</template>
