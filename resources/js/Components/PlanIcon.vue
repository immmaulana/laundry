<script setup>
defineProps({
    name: { type: String, default: 'wash' },
    logoUrl: { type: String, default: null },
    size: { type: String, default: 'md' }, // sm | md | lg
});

const sizeClass = {
    sm: 'h-10 w-10',
    md: 'h-12 w-12',
    lg: 'h-14 w-14',
};

const iconClass = {
    sm: 'h-5 w-5',
    md: 'h-6 w-6',
    lg: 'h-7 w-7',
};
</script>

<template>
    <div
        class="flex shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-brand-50 text-brand-700 ring-1 ring-brand-100"
        :class="sizeClass[size] || sizeClass.md"
    >
        <img
            v-if="logoUrl"
            :src="logoUrl"
            alt=""
            class="h-full w-full object-contain p-1.5"
        />
        <svg
            v-else-if="name === 'dry'"
            :class="iconClass[size]"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.7"
        >
            <path stroke-linecap="round" d="M8 4h8M7 8h10v9a3 3 0 0 1-3 3H10a3 3 0 0 1-3-3V8Z" />
            <path stroke-linecap="round" d="M10 12h4M10 15h2" />
            <path stroke-linecap="round" d="M16 3l1 2M19 6l2 1" />
        </svg>
        <svg
            v-else-if="name === 'iron'"
            :class="iconClass[size]"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.7"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 18h14a4 4 0 0 0 0-8H9L4 14v4Z" />
            <path stroke-linecap="round" d="M8 10V7a2 2 0 0 1 2-2h5" />
            <path stroke-linecap="round" d="M8 21h8" />
        </svg>
        <svg
            v-else-if="name === 'express'"
            :class="iconClass[size]"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.7"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 3 6 14h5l-1 7 8-12h-5l1-6Z" />
        </svg>
        <svg
            v-else-if="name === 'bed'"
            :class="iconClass[size]"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.7"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 18V9a2 2 0 0 1 2-2h6v7H3Zm10-4h6a2 2 0 0 1 2 2v4H13v-6Z" />
            <path stroke-linecap="round" d="M3 18h18M5 7V5" />
        </svg>
        <svg
            v-else-if="name === 'shoes'"
            :class="iconClass[size]"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.7"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 15c2-1 4-1.5 7-1.5 2 0 3 .5 4 1.5h5v2.5a1.5 1.5 0 0 1-1.5 1.5H5A2 2 0 0 1 3 17v-1l1-1Z" />
            <path stroke-linecap="round" d="M11 13.5V9.5c0-1.5 1-3 3-3.5" />
        </svg>
        <svg
            v-else-if="name === 'dryclean'"
            :class="iconClass[size]"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.7"
        >
            <circle cx="12" cy="13" r="6" />
            <path stroke-linecap="round" d="M12 4v3M10 4h4" />
            <path stroke-linecap="round" d="M9.5 13h5" />
        </svg>
        <svg
            v-else-if="name === 'curtain'"
            :class="iconClass[size]"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.7"
        >
            <path stroke-linecap="round" d="M4 5h16" />
            <path stroke-linecap="round" d="M6 5v14M10 5v14M14 5v14M18 5v14" />
            <path stroke-linecap="round" d="M6 19c1.2-1.5 2.8-1.5 4 0M14 19c1.2-1.5 2.8-1.5 4 0" />
        </svg>
        <svg
            v-else-if="name === 'blanket'"
            :class="iconClass[size]"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.7"
        >
            <rect x="4" y="6" width="16" height="12" rx="2" />
            <path stroke-linecap="round" d="M8 10h8M8 14h5" />
        </svg>
        <svg
            v-else-if="name === 'bag'"
            :class="iconClass[size]"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.7"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9h12l-1 10H7L6 9Z" />
            <path stroke-linecap="round" d="M9 9V7a3 3 0 0 1 6 0v2" />
        </svg>
        <svg
            v-else-if="name === 'sparkle'"
            :class="iconClass[size]"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.7"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5 18 18M18 6l-2.5 2.5M8.5 15.5 6 18" />
        </svg>
        <svg
            v-else-if="name === 'shirt'"
            :class="iconClass[size]"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.7"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 4 12 7l3-3 4 2-2 3v11H7V9L5 6l4-2Z" />
        </svg>
        <!-- default wash -->
        <svg
            v-else
            :class="iconClass[size]"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.7"
        >
            <rect x="4" y="3" width="16" height="18" rx="2" />
            <circle cx="12" cy="13" r="5" />
            <circle cx="12" cy="13" r="2" />
            <path stroke-linecap="round" d="M8 6h.01M11 6h.01" />
        </svg>
    </div>
</template>
