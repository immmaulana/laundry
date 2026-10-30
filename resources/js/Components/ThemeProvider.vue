<script setup>
import { watchEffect } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { applyTheme } from '@/Composables/theme';

const page = usePage();

watchEffect(() => {
    const company = page.props.auth?.company;
    applyTheme({
        primary_color: company?.primary_color,
        secondary_color: company?.secondary_color,
    });
});
</script>

<template>
    <slot />
</template>
