<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

const videoRef = ref(null);
const featuresScene = ref(null);
const parallaxY = ref(0);
const parallaxX = ref(0);

let raf = 0;

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
    const el = videoRef.value;
    if (el) {
        el.muted = true;
        el.playsInline = true;
        const play = el.play();
        if (play?.catch) play.catch(() => {});
    }
    updateParallax();
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
});

onUnmounted(() => {
    videoRef.value?.pause();
    cancelAnimationFrame(raf);
    window.removeEventListener('scroll', onScroll);
    window.removeEventListener('resize', onScroll);
});

const laptopStyle = () => ({
    transform: `translate3d(${parallaxX.value * -12}px, ${parallaxY.value * 28}px, 0) rotate(${parallaxX.value * -1.5}deg)`,
});

const tabletStyle = () => ({
    transform: `translate3d(${parallaxX.value * 18}px, ${parallaxY.value * -36}px, 0) rotate(${parallaxX.value * 2}deg)`,
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
    <Head title="KucekPos — Laundry POS" />

    <div class="min-h-screen overflow-x-hidden bg-[#f3f7fc] text-[#0b1f4d]">
        <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
            <div class="kucek-float absolute -left-24 top-20 h-72 w-72 rounded-full bg-sky-200/50 blur-3xl" />
            <div class="kucek-float-delayed absolute -right-16 top-40 h-80 w-80 rounded-full bg-[#0b3d8c]/10 blur-3xl" />
            <div class="kucek-float absolute bottom-20 left-1/3 h-64 w-64 rounded-full bg-cyan-100/60 blur-3xl" />
        </div>

        <header class="sticky top-0 z-30 border-b border-sky-100/80 bg-white/80 backdrop-blur-md">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-5 sm:h-[4.25rem] sm:px-8">
                <Link href="/" class="inline-flex items-center">
                    <img src="/brand/kucekpos-logo.png" alt="KucekPos" class="h-11 w-auto sm:h-12" />
                </Link>
                <Link
                    :href="route('login')"
                    class="rounded-full bg-[#0b3d8c] px-5 py-2.5 text-sm font-semibold text-white shadow-soft transition hover:bg-[#092f6d]"
                >
                    Masuk
                </Link>
            </div>
        </header>

        <!-- Hero -->
        <section class="relative mx-auto grid max-w-6xl items-center gap-10 px-5 pb-16 pt-10 sm:px-8 sm:pb-20 sm:pt-14 lg:grid-cols-2 lg:gap-12 lg:pt-16">
            <div class="kucek-rise order-2 lg:order-1">
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-sky-600">Laundry POS</p>
                <h1 class="mt-3 text-4xl font-extrabold tracking-tight text-[#0b1f4d] sm:text-5xl lg:leading-[1.08]">
                    KucekPos bikin kasir laundry lebih santai.
                </h1>
                <p class="mt-5 max-w-md text-base leading-relaxed text-slate-600 sm:text-lg">
                    Satu panel untuk kasir, antrian, dan laporan. Soft dipakai tiap shift — cepat disentuh, mudah dipahami.
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

            <div class="kucek-rise-delayed relative order-1 lg:order-2">
                <div class="kucek-float relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-[#0b3d8c] to-[#1d6bb8] p-3 shadow-soft-lg sm:p-4">
                    <div class="overflow-hidden rounded-[1.5rem]">
                        <video
                            ref="videoRef"
                            class="aspect-[4/3] w-full object-cover"
                            autoplay
                            muted
                            loop
                            playsinline
                            preload="auto"
                        >
                            <source src="/brand/login-bg.mp4" type="video/mp4" />
                        </video>
                    </div>
                </div>
                <div class="kucek-float-delayed absolute -bottom-4 -left-3 rounded-2xl bg-white px-4 py-3 shadow-soft-lg ring-1 ring-sky-100 sm:-left-6">
                    <p class="text-xs font-semibold text-sky-600">Antrian hari ini</p>
                    <p class="text-lg font-bold text-[#0b1f4d]">Rapi & jelas</p>
                </div>
                <div class="kucek-float absolute -right-2 -top-3 rounded-2xl bg-white px-4 py-3 shadow-soft-lg ring-1 ring-sky-100 sm:-right-4">
                    <p class="text-xs font-semibold text-sky-600">Kasir tablet</p>
                    <p class="text-lg font-bold text-[#0b1f4d]">Siap sentuh</p>
                </div>
            </div>
        </section>

        <!-- Fitur + parallax devices -->
        <section id="fitur" class="relative overflow-hidden bg-white">
            <div class="mx-auto max-w-6xl px-5 py-16 sm:px-8 sm:py-20">
                <div class="kucek-rise max-w-2xl">
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
                        src="/brand/kucekpos-laptop.png"
                        alt="Dashboard KucekPos di laptop"
                        class="pointer-events-none absolute bottom-[8%] left-[-2%] z-10 w-[72%] max-w-none drop-shadow-xl transition-transform duration-300 ease-out will-change-transform sm:left-[2%] sm:w-[68%]"
                        :style="laptopStyle()"
                        draggable="false"
                    />
                    <img
                        src="/brand/kucekpos-tablet.png"
                        alt="Kasir POS KucekPos di tablet"
                        class="pointer-events-none absolute bottom-[4%] right-[-4%] z-20 w-[42%] max-w-none drop-shadow-2xl transition-transform duration-300 ease-out will-change-transform sm:right-[2%] sm:w-[38%]"
                        :style="tabletStyle()"
                        draggable="false"
                    />
                </div>

                <div class="mt-12 grid gap-5 md:grid-cols-3">
                    <article
                        v-for="(item, i) in features"
                        :key="item.title"
                        class="kucek-rise rounded-3xl bg-[#f3f7fc] p-6 transition hover:-translate-y-1 hover:shadow-soft"
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
                <div class="kucek-rise max-w-2xl">
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-sky-600">Alur kerja</p>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-[#0b1f4d] sm:text-4xl">
                        Tiga langkah, selesai.
                    </h2>
                </div>

                <ol class="mt-12 grid gap-5 md:grid-cols-3">
                    <li
                        v-for="(step, i) in steps"
                        :key="step.n"
                        class="kucek-rise relative rounded-3xl border border-sky-100 bg-white p-6 shadow-soft"
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
            <div class="kucek-rise mx-auto max-w-6xl overflow-hidden rounded-[2rem] bg-gradient-to-br from-[#0b3d8c] via-[#1456a8] to-[#38bdf8] px-8 py-12 text-white sm:px-12 sm:py-14">
                <div class="flex flex-col items-start justify-between gap-6 md:flex-row md:items-center">
                    <div class="max-w-xl">
                        <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">Siap buka shift?</h2>
                        <p class="mt-3 text-base text-sky-50/90">
                            Masuk ke KucekPos — kasir dan antrian langsung siap dipakai.
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
                <p>© {{ new Date().getFullYear() }} KucekPos</p>
                <p>Laundry POS · kasir · antrian · laporan</p>
            </div>
        </footer>
    </div>
</template>
