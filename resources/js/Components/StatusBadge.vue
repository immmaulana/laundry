<script setup>
import { statusTone } from '@/Composables/format';

defineProps({
    status: { type: String, required: true },
    label: { type: String, default: '' },
});
</script>

<template>
    <span
        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"
        :class="statusTone(status)"
    >
        {{ label || status }}
    </span>
</template>
