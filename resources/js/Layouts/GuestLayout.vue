<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center bg-soft-mesh px-4 py-10">
        <div class="mb-8 flex flex-col items-center">
            <Link href="/" class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-600 text-base font-bold text-white shadow-soft-lg">
                    LS
                </div>
                <div>
                    <p class="text-xl font-bold tracking-tight text-soft-900">Laundry Soft</p>
                    <p class="text-sm text-soft-500">Kelola laundry dengan tenang</p>
                </div>
            </Link>
        </div>

        <div class="w-full max-w-md overflow-hidden rounded-3xl border border-white/80 bg-white/90 p-8 shadow-soft-lg backdrop-blur">
            <slot />
        </div>
    </div>
</template>
