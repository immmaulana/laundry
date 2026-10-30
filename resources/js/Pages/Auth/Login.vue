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
        <Head title="Masuk" />

        <div class="mb-6">
            <h1 class="text-xl font-bold text-soft-900">Masuk ke panel</h1>
            <p class="mt-1 text-sm text-soft-500">Setiap perusahaan punya data terpisah</p>
        </div>

        <div v-if="status" class="mb-4 rounded-xl bg-brand-50 px-3 py-2 text-sm text-brand-700">
            {{ status }}
        </div>

        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <label class="label-field" for="email">Email</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="input-field"
                    required
                    autofocus
                    autocomplete="username"
                />
                <p v-if="form.errors.email" class="mt-1 text-xs text-rose-600">{{ form.errors.email }}</p>
            </div>

            <div>
                <label class="label-field" for="password">Password</label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="input-field"
                    required
                    autocomplete="current-password"
                />
                <p v-if="form.errors.password" class="mt-1 text-xs text-rose-600">{{ form.errors.password }}</p>
            </div>

            <label class="flex items-center gap-2 text-sm text-soft-600">
                <input v-model="form.remember" type="checkbox" class="rounded border-soft-300 text-brand-600 focus:ring-brand-500" />
                Ingat saya
            </label>

            <button type="submit" class="btn-primary w-full" :disabled="form.processing">Masuk</button>

            <p class="text-center text-sm text-soft-500">
                Perusahaan baru?
                <Link :href="route('register')" class="font-semibold text-brand-700 hover:text-brand-800">Daftar</Link>
            </p>

            <p v-if="canResetPassword" class="text-center text-sm">
                <Link :href="route('password.request')" class="font-medium text-brand-700 hover:text-brand-800">
                    Lupa password?
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
