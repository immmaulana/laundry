<script setup>
import { computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const page = usePage();
const visible = ref(true);

const success = computed(() => page.props.flash?.success);
const error = computed(() => page.props.flash?.error);

watch([success, error], () => {
    visible.value = true;
});
</script>

<template>
    <div v-if="visible && (success || error)" class="mb-6">
        <div
            v-if="success"
            class="flex items-start justify-between gap-3 rounded-2xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-800"
        >
            <p>{{ success }}</p>
            <button type="button" class="text-brand-600/70 hover:text-brand-800" @click="visible = false">✕</button>
        </div>
        <div
            v-else
            class="flex items-start justify-between gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"
        >
            <p>{{ error }}</p>
            <button type="button" class="text-rose-600/70 hover:text-rose-800" @click="visible = false">✕</button>
        </div>
    </div>
</template>
