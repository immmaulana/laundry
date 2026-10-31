<script setup>
import PwaInstallBanner from '@/Components/PwaInstallBanner.vue';
import { Head, Link } from '@inertiajs/vue3';
import { nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

const videoRef = ref(null);
const featuresScene = ref(null);
const parallaxY = ref(0);
const parallaxX = ref(0);
const heroIndex = ref(0);
const heroPaused = ref(false);

const heroSlides = [
    {
        eyebrow: 'Laundry POS',
        title: 'CashierLaundry bikin kasir laundry lebih santai.',
        body: 'Satu panel untuk kasir, antrian, dan laporan. Soft dipakai tiap shift — cepat disentuh, mudah dipahami.',
        media: 'video',
        video: '/brand/login-bg.mp4',
        chipTop: { label: 'Kasir tablet', value: 'Siap sentuh' },
        chipBottom: { label: 'Antrian hari ini', value: 'Rapi & jelas' },
    },
    {
        eyebrow: 'Struk Thermal',
        title: 'Cetak struk Bluetooth 58mm langsung dari kasir.',
        body: 'Hubungkan printer ESC/POS lewat Bluetooth, lihat preview struk, lalu print — tanpa kabel ribet.',
        media: 'video',
        video: '/brand/cashierlaundry-thermal-hero.mp4',
        chipTop: { label: 'Bluetooth', value: 'ESC/POS 58mm' },
        chipBottom: { label: 'Preview', value: 'Siap print' },
        hideChips: true,
    },
    {
        eyebrow: 'Siap shift',
        title: 'Install di tablet, buka langsung seperti aplikasi.',
        body: 'PWA CashierLaundry bisa dipasang di HP atau tablet — launcher cepat untuk kasir harian.',
        media: 'video',
        video: '/brand/cashierlaundry-pwa-hero.mp4',
        chipTop: { label: 'PWA', value: 'Installable' },
        chipBottom: { label: 'Mulai', value: 'Masuk panel' },
        hideChips: true,
    },
];

let raf = 0;
let heroTimer = null;

const goHero = (index) => {
    heroIndex.value = (index + heroSlides.length) % heroSlides.length;
};

const nextHero = () => goHero(heroIndex.value + 1);

const startHeroAutoplay = () => {
    clearInterval(heroTimer);
    heroTimer = setInterval(() => {
        if (!heroPaused.value) nextHero();
    }, 5500);
};

const playHeroVideo = async () => {
    await nextTick();
    const el = videoRef.value;
    if (!el || heroSlides[heroIndex.value].media !== 'video') return;
    el.muted = true;
    el.playsInline = true;
    const play = el.play();
    if (play?.catch) play.catch(() => {});
};

watch(heroIndex, () => {
    playHeroVideo();
});

const updateParallax = () => {
    const el = featuresScene.value;
    if (!el) return;
    const rect = el.getBoundingClientRect();
    const viewH = window.innerHeight || 1;
    // progress -0.5 .. 0.5 while section in view
    const mid = rect.top + rect.height / 2;
    const progress = (mid - viewH / 2) / viewH;
    parallaxY.value = Math.max(-1, Math.min(1, progress));
};

const onScroll = () => {
    cancelAnimationFrame(raf);
    raf = requestAnimationFrame(updateParallax);
};

const onMouseMove = (event) => {
    const el = featuresScene.value;
    if (!el) return;
    const rect = el.getBoundingClientRect();
    const nx = ((event.clientX - rect.left) / rect.width - 0.5) * 2;
    const ny = ((event.clientY - rect.top) / rect.height - 0.5) * 2;
    parallaxX.value = Math.max(-1, Math.min(1, nx));
    parallaxY.value = Math.max(-1, Math.min(1, (parallaxY.value + ny * 0.15) / 1.15));
};

onMounted(() => {
    playHeroVideo();
    updateParallax();
    startHeroAutoplay();
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
});

onUnmounted(() => {
    videoRef.value?.pause();
    clearInterval(heroTimer);
    cancelAnimationFrame(raf);
    window.removeEventListener('scroll', onScroll);
    window.removeEventListener('resize', onScroll);
});

const laptopStyle = () => ({
    transform: `translate3d(${parallaxX.value * -12}px, ${parallaxY.value * 28}px, 0) rotate(${parallaxX.value * -1.5}deg)`,
    imageRendering: 'auto',
});

const tabletStyle = () => ({
    transform: `translate3d(${parallaxX.value * 18}px, ${parallaxY.value * -36}px, 0) rotate(${parallaxX.value * 2}deg)`,
    imageRendering: 'auto',
});

const features = [
    {
        title: 'Kasir POS sentuh',
        body: 'Katalog layanan besar, filter kategori, keranjang tetap di kanan — nyaman di tablet meja kasir.',
        icon: 'pos',
    },
    {
        title: 'Papan antrian',
        body: 'Diterima → diproses → selesai → diambil. Update status satu ketuk, tanpa form ribet.',
        icon: 'queue',
    },
    {
        title: 'Multi perusahaan',
        body: 'Setiap PT punya data, tema, logo, dan karyawan sendiri — cocok untuk jaringan laundry.',
        icon: 'building',
    },
];

const steps = [
    { n: '1', title: 'Terima di Kasir', body: 'Catat pelanggan, pilih layanan, bayar tunai / QRIS / transfer.' },
    { n: '2', title: 'Proses Antrian', body: 'Tim produksi geser status cucian secara real-time.' },
    { n: '3', title: 'Serahkan', body: 'Cek pembayaran, tandai diambil, laporan tetap rapi.' },
];
</script>

<template>
    <Head title="CashierLaundry — Laundry POS" />

    <div class="min-h-screen overflow-x-hidden bg-[#f3f7fc] text-[#0b1f4d]">
        <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
            <div class="brand-float absolute -left-24 top-20 h-72 w-72 rounded-full bg-sky-200/50 blur-3xl" />
            <div class="brand-float-delayed absolute -right-16 top-40 h-80 w-80 rounded-full bg-[#0b3d8c]/10 blur-3xl" />
            <div class="brand-float absolute bottom-20 left-1/3 h-64 w-64 rounded-full bg-cyan-100/60 blur-3xl" />
        </div>

        <header class="sticky top-0 z-30 border-b border-sky-100/80 bg-white/80 backdrop-blur-md">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-5 sm:h-[4.25rem] sm:px-8">
                <Link href="/" class="inline-flex items-center">
                    <img src="/brand/cashierlaundry-logo.png" alt="CashierLaundry" class="h-11 w-auto sm:h-12" />
                </Link>
                <Link
                    :href="route('login')"
                    class="rounded-full bg-[#0b3d8c] px-5 py-2.5 text-sm font-semibold text-white shadow-soft transition hover:bg-[#092f6d]"
                >
                    Masuk
                </Link>
            </div>
        </header>

        <!-- Hero slider -->
        <section
            class="relative pb-16 pt-10 sm:pb-20 sm:pt-14 lg:pt-16"
            @mouseenter="heroPaused = true"
            @mouseleave="heroPaused = false"
        >
            <!-- Vertical dots — 10px from left edge, vertically centered -->
            <nav
                class="absolute left-[10px] top-1/2 z-20 hidden -translate-y-1/2 flex-col items-center gap-2.5 rounded-full bg-white/85 px-2.5 py-3.5 shadow-soft ring-1 ring-sky-100/90 backdrop-blur-md md:flex"
                aria-label="Navigasi hero"
            >
                <button
                    v-for="(slide, i) in heroSlides"
                    :key="`dot-${i}`"
                    type="button"
                    class="group relative flex h-9 w-3.5 items-center justify-center"
                    :aria-label="slide.eyebrow"
                    :aria-current="heroIndex === i ? 'true' : 'false'"
                    @click="goHero(i); startHeroAutoplay()"
                >
                    <span
                        class="block rounded-full transition-all duration-300 ease-out"
                        :class="
                            heroIndex === i
                                ? 'h-8 w-2.5 bg-[#0b3d8c] shadow-sm shadow-sky-200'
                                : 'h-2.5 w-2.5 bg-sky-300 group-hover:scale-110 group-hover:bg-sky-500'
                        "
                    />
                </button>
            </nav>

            <div class="relative mx-auto grid max-w-6xl items-center gap-10 px-5 sm:px-8 lg:grid-cols-2 lg:gap-12">
                <div class="relative order-2 min-h-[17rem] lg:order-1 lg:min-h-[20rem]">
                    <Transition name="hero-copy" mode="out-in">
                        <div :key="heroIndex">
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-sky-600">
                                {{ heroSlides[heroIndex].eyebrow }}
                            </p>
                            <h1 class="mt-3 text-4xl font-extrabold tracking-tight text-[#0b1f4d] sm:text-5xl lg:leading-[1.08]">
                                {{ heroSlides[heroIndex].title }}
                            </h1>
                            <p class="mt-5 max-w-md text-base leading-relaxed text-slate-600 sm:text-lg">
                                {{ heroSlides[heroIndex].body }}
                            </p>
                            <div class="mt-8 flex flex-wrap gap-3">
                                <Link
                                    :href="route('login')"
                                    class="inline-flex min-h-12 items-center justify-center rounded-full bg-[#0b3d8c] px-7 text-sm font-semibold text-white shadow-soft-lg transition hover:-translate-y-0.5 hover:bg-[#092f6d]"
                                >
                                    Masuk ke panel
                                </Link>
                                <a
                                    href="#fitur"
                                    class="inline-flex min-h-12 items-center justify-center rounded-full border border-sky-200 bg-white px-7 text-sm font-semibold text-[#0b3d8c] transition hover:bg-sky-50"
                                >
                                    Lihat fitur
                                </a>
                            </div>
                        </div>
                    </Transition>

                    <!-- Mobile horizontal dots -->
                    <div class="mt-8 flex items-center gap-2 lg:hidden">
                        <button
                            v-for="(slide, i) in heroSlides"
                            :key="`mdot-${i}`"
                            type="button"
                            class="rounded-full transition-all duration-300"
                            :class="heroIndex === i ? 'h-2.5 w-7 bg-[#0b3d8c]' : 'h-2.5 w-2.5 bg-sky-300'"
                            :aria-label="`Slide ${i + 1}`"
                            @click="goHero(i); startHeroAutoplay()"
                        />
                    </div>
                </div>

                <div class="relative order-1 lg:order-2">
                    <Transition name="hero-media" mode="out-in">
                        <div :key="`media-${heroIndex}`" class="relative">
                            <div
                                v-if="heroSlides[heroIndex].media === 'video'"
                                class="brand-float relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-[#0b3d8c] to-[#1d6bb8] p-3 shadow-soft-lg sm:p-4"
                            >
                                <div class="overflow-hidden rounded-[1.5rem]">
                                    <video
                                        :key="heroSlides[heroIndex].video"
                                        ref="videoRef"
                                        class="aspect-[4/3] w-full object-cover"
                                        autoplay
                                        muted
                                        loop
                                        playsinline
                                        preload="auto"
                                    >
                                        <source :src="heroSlides[heroIndex].video" type="video/mp4" />
                                    </video>
                                </div>
                            </div>

                            <div
                                v-else
                                class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-sky-50 to-[#dbeafe] p-4 shadow-soft-lg sm:p-6"
                            >
                                <div class="relative mx-auto aspect-[4/3] max-w-lg">
                                    <img
                                        src="/brand/cashierlaundry-laptop.png?v=2"
                                        alt="Dashboard"
                                        class="absolute bottom-[8%] left-0 w-[78%] drop-shadow-xl"
                                        draggable="false"
                                    />
                                    <img
                                        src="/brand/cashierlaundry-tablet.png?v=2"
                                        alt="Kasir tablet"
                                        class="absolute bottom-[2%] right-0 w-[46%] drop-shadow-2xl"
                                        draggable="false"
                                    />
                                </div>
                            </div>

                            <template v-if="!heroSlides[heroIndex].hideChips">
                                <div class="brand-float-delayed absolute -bottom-4 -left-3 rounded-2xl bg-white px-4 py-3 shadow-soft-lg ring-1 ring-sky-100 sm:-left-6">
                                    <p class="text-xs font-semibold text-sky-600">{{ heroSlides[heroIndex].chipBottom.label }}</p>
                                    <p class="text-lg font-bold text-[#0b1f4d]">{{ heroSlides[heroIndex].chipBottom.value }}</p>
                                </div>
                                <div class="brand-float absolute -right-2 -top-3 rounded-2xl bg-white px-4 py-3 shadow-soft-lg ring-1 ring-sky-100 sm:-right-4">
                                    <p class="text-xs font-semibold text-sky-600">{{ heroSlides[heroIndex].chipTop.label }}</p>
                                    <p class="text-lg font-bold text-[#0b1f4d]">{{ heroSlides[heroIndex].chipTop.value }}</p>
                                </div>
                            </template>
                        </div>
                    </Transition>
                </div>
            </div>
        </section>

        <!-- Fitur + parallax devices -->
        <section id="fitur" class="relative overflow-hidden bg-white">
            <div class="mx-auto max-w-6xl px-5 py-16 sm:px-8 sm:py-20">
                <div class="brand-rise max-w-2xl">
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-sky-600">Fitur utama</p>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-[#0b1f4d] sm:text-4xl">
                        Lucu dipakai, serius bantu operasional.
                    </h2>
                    <p class="mt-3 text-base text-slate-600">
                        Dashboard di laptop, kasir di tablet — satu alur kerja yang sinkron.
                    </p>
                </div>

                <!-- Parallax stage -->
                <div
                    ref="featuresScene"
                    class="relative mx-auto mt-12 h-[22rem] max-w-4xl sm:h-[26rem] lg:h-[30rem]"
                    @mousemove="onMouseMove"
                >
                    <div class="absolute inset-x-8 bottom-6 top-10 rounded-[2.5rem] bg-gradient-to-b from-sky-50 to-[#e8f1fb] sm:inset-x-12" />

                    <img
                        src="/brand/cashierlaundry-laptop.png?v=2"
                        alt="Dashboard CashierLaundry di laptop"
                        class="pointer-events-none absolute bottom-[6%] left-[-2%] z-10 w-[78%] max-w-none drop-shadow-xl transition-transform duration-300 ease-out will-change-transform sm:left-0 sm:w-[72%]"
                        :style="laptopStyle()"
                        width="917"
                        height="676"
                        decoding="async"
                        draggable="false"
                    />
                    <img
                        src="/brand/cashierlaundry-tablet.png?v=2"
                        alt="Kasir POS CashierLaundry di tablet"
                        class="pointer-events-none absolute bottom-[2%] right-[-2%] z-20 w-[48%] max-w-none drop-shadow-2xl transition-transform duration-300 ease-out will-change-transform sm:right-[1%] sm:w-[42%]"
                        :style="tabletStyle()"
                        width="755"
                        height="587"
                        decoding="async"
                        draggable="false"
                    />
                </div>

                <div class="mt-12 grid gap-5 md:grid-cols-3">
                    <article
                        v-for="(item, i) in features"
                        :key="item.title"
                        class="brand-rise rounded-3xl bg-[#f3f7fc] p-6 transition hover:-translate-y-1 hover:shadow-soft"
                        :style="{ animationDelay: `${120 + i * 90}ms` }"
                    >
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#0b3d8c] text-white">
                            <svg v-if="item.icon === 'pos'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16v12H4V7Zm3-3h10M8 11h2m4 0h2m-8 4h8" /></svg>
                            <svg v-else-if="item.icon === 'queue'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h10M7 12h10M7 17h6" /></svg>
                            <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 21V5a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v16M9 21v-4h2v4M14 9h5a1 1 0 0 1 1 1v11" /></svg>
                        </div>
                        <h3 class="mt-5 text-lg font-bold text-[#0b1f4d]">{{ item.title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ item.body }}</p>
                    </article>
                </div>
            </div>
        </section>

        <!-- Alur -->
        <section class="relative">
            <div class="mx-auto max-w-6xl px-5 py-16 sm:px-8 sm:py-20">
                <div class="brand-rise max-w-2xl">
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-sky-600">Alur kerja</p>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-[#0b1f4d] sm:text-4xl">
                        Tiga langkah, selesai.
                    </h2>
                </div>

                <ol class="mt-12 grid gap-5 md:grid-cols-3">
                    <li
                        v-for="(step, i) in steps"
                        :key="step.n"
                        class="brand-rise relative rounded-3xl border border-sky-100 bg-white p-6 shadow-soft"
                        :style="{ animationDelay: `${140 + i * 90}ms` }"
                    >
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-sky-100 text-sm font-bold text-[#0b3d8c]">
                            {{ step.n }}
                        </span>
                        <h3 class="mt-4 text-lg font-bold text-[#0b1f4d]">{{ step.title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ step.body }}</p>
                    </li>
                </ol>
            </div>
        </section>

        <!-- CTA -->
        <section class="relative px-5 pb-16 sm:px-8 sm:pb-20">
            <div class="brand-rise mx-auto max-w-6xl overflow-hidden rounded-[2rem] bg-gradient-to-br from-[#0b3d8c] via-[#1456a8] to-[#38bdf8] px-8 py-12 text-white sm:px-12 sm:py-14">
                <div class="flex flex-col items-start justify-between gap-6 md:flex-row md:items-center">
                    <div class="max-w-xl">
                        <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">Siap buka shift?</h2>
                        <p class="mt-3 text-base text-sky-50/90">
                            Masuk ke CashierLaundry — kasir dan antrian langsung siap dipakai.
                        </p>
                    </div>
                    <Link
                        :href="route('login')"
                        class="inline-flex min-h-12 shrink-0 items-center justify-center rounded-full bg-white px-7 text-sm font-semibold text-[#0b1f4d] shadow-lg transition hover:-translate-y-0.5 hover:bg-sky-50"
                    >
                        Masuk sekarang
                    </Link>
                </div>
            </div>
        </section>

        <footer class="border-t border-sky-100 bg-white">
            <div class="mx-auto flex max-w-6xl flex-col gap-2 px-5 py-8 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-8">
                <p>© {{ new Date().getFullYear() }} CashierLaundry</p>
                <p>Laundry POS · kasir · antrian · laporan</p>
            </div>
        </footer>

        <PwaInstallBanner />
    </div>
</template>

<style scoped>
.hero-copy-enter-active,
.hero-copy-leave-active,
.hero-media-enter-active,
.hero-media-leave-active {
    transition: all 0.45s cubic-bezier(0.22, 1, 0.36, 1);
}
.hero-copy-enter-from {
    opacity: 0;
    transform: translateY(14px);
}
.hero-copy-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
.hero-media-enter-from {
    opacity: 0;
    transform: translateX(18px) scale(0.98);
}
.hero-media-leave-to {
    opacity: 0;
    transform: translateX(-12px) scale(0.99);
}
</style>
