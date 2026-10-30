<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const videoRef = ref(null);

onMounted(() => {
    const el = videoRef.value;
    if (!el) return;
    el.muted = true;
    el.playsInline = true;
    const play = el.play();
    if (play?.catch) play.catch(() => {});
});

onUnmounted(() => {
    videoRef.value?.pause();
});
</script>

<template>
    <div class="flex min-h-screen bg-white text-slate-900">
        <!-- Left: video stage (desktop) -->
        <section class="relative hidden min-h-screen w-[56%] overflow-hidden lg:block xl:w-[58%]">
            <video
                ref="videoRef"
                class="absolute inset-0 h-full w-full object-cover"
                autoplay
                muted
                loop
                playsinline
                preload="auto"
            >
                <source src="/brand/login-bg.mp4" type="video/mp4" />
            </video>
            <div class="absolute inset-0 bg-gradient-to-t from-[#071433]/85 via-[#0b2a66]/25 to-[#071433]/20" />
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-transparent to-white/10" />

            <div class="absolute inset-x-0 bottom-0 p-10 xl:p-12">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-sky-200/90">Laundry App</p>
                <p class="mt-3 max-w-md text-lg leading-relaxed text-white/95 xl:text-xl">
                    Kasir, antrian, dan pelanggan — satu alur kerja yang bersih.
                </p>
            </div>
        </section>

        <!-- Right: auth panel -->
        <section class="relative flex min-h-screen w-full flex-col justify-center px-5 py-10 sm:px-8 lg:w-[44%] lg:px-12 xl:w-[42%] xl:px-16">
            <!-- Mobile video strip -->
            <div class="relative mb-8 h-44 overflow-hidden rounded-2xl sm:h-52 lg:hidden">
                <video
                    class="absolute inset-0 h-full w-full object-cover"
                    autoplay
                    muted
                    loop
                    playsinline
                    preload="metadata"
                >
                    <source src="/brand/login-bg.mp4" type="video/mp4" />
                </video>
                <div class="absolute inset-0 bg-gradient-to-t from-[#071433]/70 to-[#071433]/15" />
            </div>

            <div class="mx-auto w-full max-w-[24rem]">
                <slot name="brand">
                    <Link href="/" class="mb-7 inline-flex">
                        <img
                            src="/brand/kucekpos-logo.png"
                            alt="KucekPos"
                            class="h-14 w-auto object-left sm:h-16"
                        />
                    </Link>
                </slot>

                <slot />

                <p class="mt-10 text-center text-xs text-slate-400 lg:text-left">
                    © {{ new Date().getFullYear() }} KucekPos
                </p>
            </div>
        </section>
    </div>
</template>
