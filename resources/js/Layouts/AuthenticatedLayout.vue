<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import FlashMessage from '@/Components/FlashMessage.vue';
import ThemeProvider from '@/Components/ThemeProvider.vue';

const page = usePage();
const mobileOpen = ref(false);
const collapsed = ref(false);
const user = computed(() => page.props.auth.user);
const company = computed(() => page.props.auth.company);
const isSuper = computed(() => user.value?.role === 'super_admin');
const isAdmin = computed(() => user.value?.role === 'admin');
const isKasirPage = computed(() => route().current('kasir.*'));

const companyInitials = computed(() => {
    if (isSuper.value) return 'LS';
    const name = company.value?.name || 'LS';
    return name
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((w) => w[0])
        .join('')
        .toUpperCase();
});

const nav = computed(() => {
    if (isSuper.value) {
        return [
            { name: 'Perusahaan', route: 'companies.index', match: 'companies.*', icon: 'building' },
            { name: 'Pengaturan', route: 'setting.edit', match: 'setting.*', icon: 'settings' },
        ];
    }

    const items = [
        { name: 'Dashboard', route: 'dashboard', match: 'dashboard', icon: 'home' },
        { name: 'Kasir', route: 'kasir.index', match: 'kasir.*', icon: 'pos' },
        { name: 'Antrian', route: 'queue.index', match: 'queue.*', icon: 'queue' },
        { name: 'Pesanan', route: 'order.index', match: 'order.*', icon: 'orders' },
        { name: 'Pelanggan', route: 'customer.index', match: 'customer.*', icon: 'users' },
    ];

    if (isAdmin.value) {
        items.push(
            { name: 'Layanan', route: 'plan.index', match: 'plan.*', icon: 'services' },
            { name: 'Laporan', route: 'report.index', match: 'report.*', icon: 'report' },
            { name: 'Karyawan', route: 'users.index', match: 'users.*', icon: 'staff' },
            { name: 'Profil PT', route: 'company.profile', match: 'companies.edit|company.profile', icon: 'building' },
        );
    }

    items.push({ name: 'Pengaturan', route: 'setting.edit', match: 'setting.*', icon: 'settings' });

    return items;
});

const isActive = (match) => {
    if (match.includes('|')) {
        return match.split('|').some((m) => route().current(m));
    }
    return route().current(match);
};

const STORAGE_KEY = 'laundry-soft-sidebar-collapsed';

onMounted(() => {
    const saved = localStorage.getItem(STORAGE_KEY);
    if (saved !== null) {
        collapsed.value = saved === '1';
    } else if (isKasirPage.value && window.matchMedia('(min-width: 768px) and (max-width: 1023px)').matches) {
        collapsed.value = true;
    }
});

watch(collapsed, (value) => {
    localStorage.setItem(STORAGE_KEY, value ? '1' : '0');
});

watch(isKasirPage, (onKasir) => {
    if (onKasir && window.matchMedia('(min-width: 768px) and (max-width: 1023px)').matches) {
        collapsed.value = true;
    }
});

const toggleCollapsed = () => {
    collapsed.value = !collapsed.value;
};
</script>

<template>
    <ThemeProvider>
        <div class="min-h-screen bg-soft-mesh">
            <div
                v-if="mobileOpen"
                class="fixed inset-0 z-40 bg-soft-900/30 backdrop-blur-sm md:hidden"
                @click="mobileOpen = false"
            />

            <aside
                class="fixed inset-y-0 left-0 z-50 flex flex-col overflow-hidden border-r border-soft-200/80 bg-white/95 shadow-soft-lg transition-[width,transform] duration-300"
                :class="[
                    collapsed ? 'md:w-16' : 'md:w-60',
                    'w-72',
                    mobileOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
                ]"
            >
                <!-- Brand -->
                <div
                    class="flex h-16 shrink-0 items-center border-b border-soft-100 md:h-[4.25rem]"
                    :class="collapsed ? 'justify-center px-2' : 'gap-3 px-4'"
                >
                    <div
                        v-if="!isSuper && company?.logo_url"
                        class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-soft-100 bg-white shadow-soft"
                    >
                        <img :src="company.logo_url" :alt="company.name" class="h-full w-full object-contain p-0.5" />
                    </div>
                    <div
                        v-else
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-600 text-xs font-bold text-white shadow-soft"
                    >
                        {{ companyInitials }}
                    </div>

                    <div v-if="!collapsed" class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold tracking-tight text-soft-900">
                            {{ isSuper ? 'KucekPos' : (company?.name || 'KucekPos') }}
                        </p>
                        <p class="truncate text-xs text-soft-500">
                            {{ isSuper ? 'Platform owner' : (company?.code ? `${company.code} · ${company.city || 'Admin'}` : 'Admin panel') }}
                        </p>
                    </div>

                    <!-- Expand/collapse: only when sidebar expanded (room for button) -->
                    <button
                        v-if="!collapsed"
                        type="button"
                        class="hidden h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-soft-200 text-soft-500 transition hover:bg-soft-50 md:inline-flex"
                        title="Ciutkan menu"
                        @click="toggleCollapsed"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                </div>

                <!-- Expand control when rail mode -->
                <div v-if="collapsed" class="hidden shrink-0 justify-center border-b border-soft-100 py-2 md:flex">
                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-xl border border-soft-200 text-soft-500 transition hover:bg-brand-50 hover:text-brand-700"
                        title="Perbesar menu"
                        @click="toggleCollapsed"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                <nav class="flex-1 space-y-1 overflow-y-auto overflow-x-hidden px-2 py-3">
                    <Link
                        v-for="item in nav"
                        :key="item.route"
                        :href="route(item.route)"
                        class="group flex items-center rounded-xl text-sm font-medium transition"
                        :class="[
                            collapsed ? 'h-11 justify-center px-0' : 'min-h-11 gap-3 px-3',
                            isActive(item.match)
                                ? 'bg-brand-50 text-brand-800 shadow-sm ring-1 ring-brand-100'
                                : 'text-soft-600 hover:bg-soft-50 hover:text-soft-900',
                        ]"
                        :title="item.name"
                        @click="mobileOpen = false"
                    >
                        <span
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg"
                            :class="isActive(item.match) ? 'bg-brand-100 text-brand-700' : 'bg-soft-50 text-soft-500 group-hover:bg-soft-100'"
                        >
                            <svg v-if="item.icon === 'home'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9.5Z" /></svg>
                            <svg v-else-if="item.icon === 'pos'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16v12H4V7Zm3-3h10M8 11h2m4 0h2m-8 4h8" /></svg>
                            <svg v-else-if="item.icon === 'queue'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h10M7 12h10M7 17h6" /></svg>
                            <svg v-else-if="item.icon === 'orders'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" /></svg>
                            <svg v-else-if="item.icon === 'users'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm13 10v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" /></svg>
                            <svg v-else-if="item.icon === 'services'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="18" rx="2" stroke-width="1.8" /><circle cx="12" cy="13" r="4" stroke-width="1.8" /></svg>
                            <svg v-else-if="item.icon === 'report'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19V5m4 14V9m4 10V7m4 12v-6m4 6V5" /></svg>
                            <svg v-else-if="item.icon === 'staff'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm-7 9a7 7 0 0 1 14 0" /></svg>
                            <svg v-else-if="item.icon === 'building'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 21V5a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v16M9 21v-4h2v4M14 9h5a1 1 0 0 1 1 1v11M7 8h2M7 12h2M7 16h2" /></svg>
                            <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9c.3.6.9 1 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z" /></svg>
                        </span>
                        <span v-if="!collapsed" class="truncate">{{ item.name }}</span>
                    </Link>
                </nav>

                <div class="shrink-0 border-t border-soft-100" :class="collapsed ? 'p-2' : 'p-3'">
                    <div v-if="!collapsed" class="rounded-2xl bg-soft-50 p-3">
                        <p class="truncate text-sm font-semibold text-soft-900">{{ user?.name }}</p>
                        <p class="truncate text-xs text-soft-500">{{ user?.email }}</p>
                        <p class="mt-2 inline-flex rounded-full bg-white px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-brand-700 ring-1 ring-brand-100">
                            {{ user?.role }}
                        </p>
                    </div>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="mt-2 flex h-11 w-full items-center rounded-xl text-sm font-medium text-soft-500 transition hover:bg-rose-50 hover:text-rose-700"
                        :class="collapsed ? 'justify-center' : 'justify-start px-3'"
                        title="Keluar"
                    >
                        <svg class="h-4 w-4 shrink-0" :class="collapsed ? '' : 'mr-2'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12H3m12 0-4-4m4 4-4 4m13-9v10a2 2 0 0 1-2 2h-5" />
                        </svg>
                        <span v-if="!collapsed">Keluar</span>
                    </Link>
                </div>
            </aside>

            <div
                class="min-w-0 transition-[padding] duration-300"
                :class="collapsed ? 'md:pl-16' : 'md:pl-60'"
            >
                <header class="sticky top-0 z-30 border-b border-soft-200/70 bg-white/80 backdrop-blur-md">
                    <div class="flex h-14 items-center justify-between gap-4 px-4 sm:px-6 md:h-16 lg:px-8">
                        <div class="flex min-w-0 items-center gap-3">
                            <button
                                type="button"
                                class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-soft-200 bg-white text-soft-600 shadow-soft md:hidden"
                                @click="mobileOpen = true"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M4 12h16M4 17h16" />
                                </svg>
                            </button>
                            <div class="min-w-0">
                                <div v-if="$slots.header">
                                    <slot name="header" />
                                </div>
                            </div>
                        </div>
                        <div class="hidden text-sm text-soft-500 md:block">
                            {{ new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' }) }}
                        </div>
                    </div>
                </header>

                <main
                    class="px-4 sm:px-6 lg:px-8"
                    :class="isKasirPage ? 'py-3 md:py-4' : 'py-5 md:py-6'"
                >
                    <FlashMessage />
                    <slot />
                </main>
            </div>
        </div>
    </ThemeProvider>
</template>
