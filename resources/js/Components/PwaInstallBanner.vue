<script setup>
import { onMounted, onUnmounted, ref } from 'vue';

const deferred = ref(null);
const visible = ref(false);
const STORAGE_KEY = 'cashierlaundry-pwa-dismissed';

const onBeforeInstall = (event) => {
    event.preventDefault();
    deferred.value = event;
    if (!localStorage.getItem(STORAGE_KEY)) {
        visible.value = true;
    }
};

const install = async () => {
    if (!deferred.value) return;
    deferred.value.prompt();
    await deferred.value.userChoice;
    deferred.value = null;
    visible.value = false;
};

const dismiss = () => {
    visible.value = false;
    localStorage.setItem(STORAGE_KEY, '1');
};

onMounted(() => {
    window.addEventListener('beforeinstallprompt', onBeforeInstall);
    // iOS tip: show soft hint if standalone not active
    const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
    const standalone = window.matchMedia('(display-mode: standalone)').matches
        || window.navigator.standalone === true;
    if (isIos && !standalone && !localStorage.getItem(STORAGE_KEY)) {
        visible.value = true;
    }
});

onUnmounted(() => {
    window.removeEventListener('beforeinstallprompt', onBeforeInstall);
});
</script>

<template>
    <div
        v-if="visible"
        class="fixed inset-x-3 bottom-3 z-[70] mx-auto max-w-md rounded-2xl border border-sky-100 bg-white p-4 shadow-soft-lg sm:inset-x-auto sm:right-4 sm:left-auto"
    >
        <div class="flex items-start gap-3">
            <img src="/icons/icon-192.png" alt="" class="h-11 w-11 rounded-xl" />
            <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-[#0b1f4d]">Install CashierLaundry</p>
                <p class="mt-0.5 text-xs text-slate-500">
                    Tambahkan ke layar utama HP/tablet biar langsung buka seperti aplikasi.
                </p>
                <div class="mt-3 flex gap-2">
                    <button
                        v-if="deferred"
                        type="button"
                        class="rounded-full bg-[#0b3d8c] px-3 py-1.5 text-xs font-semibold text-white"
                        @click="install"
                    >
                        Install
                    </button>
                    <p v-else class="text-[11px] leading-snug text-slate-500">
                        iOS: Share → <span class="font-semibold">Add to Home Screen</span>
                    </p>
                    <button type="button" class="ml-auto text-xs font-medium text-slate-400" @click="dismiss">
                        Nanti
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
