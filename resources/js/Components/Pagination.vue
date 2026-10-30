<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: { type: Array, default: () => [] },
});
</script>

<template>
    <div v-if="links.length > 3" class="flex flex-wrap items-center gap-1">
        <template v-for="(link, index) in links" :key="index">
            <Link
                v-if="link.url"
                :href="link.url"
                class="min-w-9 rounded-lg px-3 py-1.5 text-sm transition"
                :class="
                    link.active
                        ? 'bg-brand-600 text-white'
                        : 'bg-white text-soft-600 ring-1 ring-soft-200 hover:bg-soft-50'
                "
                v-html="link.label"
            />
            <span
                v-else
                class="min-w-9 rounded-lg px-3 py-1.5 text-sm text-soft-300"
                v-html="link.label"
            />
        </template>
    </div>
</template>
