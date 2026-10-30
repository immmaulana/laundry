<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import FlashMessage from '@/Components/FlashMessage.vue';

const page = usePage();
const sidebarOpen = ref(false);
const user = computed(() => page.props.auth.user);
const company = computed(() => page.props.auth.company);
const isSuper = computed(() => user.value?.role === 'super_admin');
const isAdmin = computed(() => user.value?.role === 'admin');
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
            { name: 'Perusahaan', route: 'companies.index', match: 'companies.*' },
            { name: 'Pengaturan', route: 'setting.edit', match: 'setting.*' },
        ];
    }

    const items = [
        { name: 'Dashboard', route: 'dashboard', match: 'dashboard' },
        { name: 'Kasir', route: 'kasir.index', match: 'kasir.*' },
        { name: 'Pesanan', route: 'order.index', match: 'order.*' },
        { name: 'Pelanggan', route: 'customer.index', match: 'customer.*' },
    ];

    if (isAdmin.value) {
        items.push(
            { name: 'Layanan', route: 'plan.index', match: 'plan.*' },
            { name: 'Laporan', route: 'report.index', match: 'report.*' },
            { name: 'Karyawan', route: 'users.index', match: 'users.*' },
            { name: 'Profil PT', route: 'company.profile', match: 'companies.edit|company.profile' },
        );
    }

    items.push({ name: 'Pengaturan', route: 'setting.edit', match: 'setting.*' });

    return items;
});

const isActive = (match) => {
    if (match.includes('|')) {
        return match.split('|').some((m) => route().current(m));
    }
    return route().current(match);
};
</script>

<template>
    <div class="min-h-screen bg-soft-mesh">
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-40 bg-soft-900/30 backdrop-blur-sm lg:hidden"
            @click="sidebarOpen = false"
        />

        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col border-r border-soft-200/80 bg-white/95 shadow-soft-lg transition duration-300 lg:translate-x-0"
            :class="{ 'translate-x-0': sidebarOpen }"
        >
            <div class="flex h-20 items-center gap-3 border-b border-soft-100 px-6">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-brand-600 text-sm font-bold text-white shadow-soft">
                    {{ companyInitials }}
                </div>
                <div class="min-w-0">
                    <p class="truncate text-base font-bold tracking-tight text-soft-900">
                        {{ isSuper ? 'Laundry Soft' : (company?.name || 'Laundry Soft') }}
                    </p>
                    <p class="truncate text-xs text-soft-500">
                        {{ isSuper ? 'Platform owner' : (company?.code ? `${company.code} · ${company.city || 'Admin'}` : 'Admin panel') }}
                    </p>
                </div>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-4 py-5">
                <Link
                    v-for="item in nav"
                    :key="item.route"
                    :href="route(item.route)"
                    class="group flex items-center rounded-xl px-3.5 py-2.5 text-sm font-medium transition"
                    :class="
                        isActive(item.match)
                            ? 'bg-brand-50 text-brand-800 shadow-sm ring-1 ring-brand-100'
                            : 'text-soft-600 hover:bg-soft-50 hover:text-soft-900'
                    "
                    @click="sidebarOpen = false"
                >
                    <span
                        class="mr-3 h-1.5 w-1.5 rounded-full transition"
                        :class="isActive(item.match) ? 'bg-brand-500' : 'bg-soft-300 group-hover:bg-soft-400'"
                    />
                    {{ item.name }}
                </Link>
            </nav>

            <div class="border-t border-soft-100 p-4">
                <div class="rounded-2xl bg-soft-50 p-3">
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
                    class="mt-3 w-full rounded-xl px-3 py-2 text-left text-sm font-medium text-soft-500 transition hover:bg-rose-50 hover:text-rose-700"
                >
                    Keluar
                </Link>
            </div>
        </aside>

        <div class="lg:pl-72">
            <header class="sticky top-0 z-30 border-b border-soft-200/70 bg-white/80 backdrop-blur-md">
                <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            class="rounded-xl border border-soft-200 bg-white p-2 text-soft-600 shadow-soft lg:hidden"
                            @click="sidebarOpen = true"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M4 12h16M4 17h16" />
                            </svg>
                        </button>
                        <div>
                            <div v-if="$slots.header">
                                <slot name="header" />
                            </div>
                        </div>
                    </div>
                    <div class="hidden text-sm text-soft-500 sm:block">
                        {{ new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' }) }}
                    </div>
                </div>
            </header>

            <main class="px-4 py-6 sm:px-6 lg:px-8">
                <FlashMessage />
                <slot />
            </main>
        </div>
    </div>
</template>
