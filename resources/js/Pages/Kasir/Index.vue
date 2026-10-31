<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PlanIcon from '@/Components/PlanIcon.vue';
import ReceiptPreview from '@/Components/ReceiptPreview.vue';
import { money } from '@/Composables/format';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    plans: Array,
    customers: Array,
    todayCount: Number,
});

const page = usePage();
const cart = ref([]);
const search = ref('');
const category = ref('all');
const receipt = ref(null);
const addingId = ref(null);
const justAddedId = ref(null);
let addTimer = null;
let addedTimer = null;
const form = useForm({
    customer_id: '',
    name: '',
    phone: '',
    items: [],
    discount: 0,
    payment_method: 'cash',
    paid: 0,
});

watch(
    () => page.props.flash?.receipt,
    (value) => {
        if (value) {
            receipt.value = value;
        }
    },
    { immediate: true },
);

const categories = computed(() => {
    const seen = new Map();
    for (const plan of props.plans) {
        const key = plan.icon || 'wash';
        if (!seen.has(key)) {
            seen.set(key, plan.icon_label || key);
        }
    }
    return [{ key: 'all', label: 'Semua' }, ...[...seen.entries()].map(([key, label]) => ({ key, label }))];
});

const filteredPlans = computed(() => {
    const q = search.value.trim().toLowerCase();
    return props.plans.filter((plan) => {
        const matchCat = category.value === 'all' || (plan.icon || 'wash') === category.value;
        if (!matchCat) return false;
        if (!q) return true;
        return (
            plan.plan?.toLowerCase().includes(q) ||
            plan.code?.toLowerCase().includes(q)
        );
    });
});

const subtotal = computed(() =>
    cart.value.reduce((sum, row) => {
        const plan = props.plans.find((p) => p.id === row.plan_id);
        return sum + (plan?.price || 0) * row.qty;
    }, 0),
);

const total = computed(() => Math.max(0, subtotal.value - Number(form.discount || 0)));

const planOf = (id) => props.plans.find((p) => p.id === id);

const addPlan = (plan) => {
    if (addingId.value) return;

    addingId.value = plan.id;
    justAddedId.value = null;
    clearTimeout(addTimer);
    clearTimeout(addedTimer);

    addTimer = setTimeout(() => {
        const existing = cart.value.find((row) => row.plan_id === plan.id);
        if (existing) {
            existing.qty += 1;
        } else {
            cart.value.push({ plan_id: plan.id, qty: 1 });
        }

        addingId.value = null;
        justAddedId.value = plan.id;
        addedTimer = setTimeout(() => {
            if (justAddedId.value === plan.id) {
                justAddedId.value = null;
            }
        }, 650);
    }, 280);
};

const bumpQty = (index, delta) => {
    const row = cart.value[index];
    if (!row) return;
    row.qty = Math.max(1, row.qty + delta);
};

const removeRow = (index) => {
    cart.value.splice(index, 1);
};

const setPayment = (method) => {
    form.payment_method = method;
    if (!form.paid) {
        form.paid = total.value;
    }
};

const submit = () => {
    form.items = cart.value;
    if (!form.paid) {
        form.paid = total.value;
    }
    form.post(route('kasir.store'), {
        onSuccess: () => {
            cart.value = [];
            form.reset('customer_id', 'name', 'phone', 'discount', 'paid');
            form.payment_method = 'cash';
        },
    });
};
</script>

<template>
    <Head title="Kasir" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-bold text-soft-900">Kasir POS</h1>
                <p class="text-sm text-soft-500">{{ todayCount }} transaksi hari ini</p>
            </div>
        </template>

        <ReceiptPreview
            v-if="receipt"
            :receipt="receipt"
            @close="receipt = null"
        />

        <!--
          Mobile: stack. Tablet+: side-by-side.
          Desktop: gunakan tinggi dinamis + panel kanan bisa scroll agar tombol bayar selalu terlihat.
        -->
        <div
            class="flex flex-col gap-5 xl:gap-6 lg:grid lg:h-[calc(100dvh-9rem)] lg:min-h-[32rem] lg:grid-cols-[minmax(0,1fr)_minmax(20rem,24rem)] xl:grid-cols-[minmax(0,1fr)_minmax(22rem,28rem)] lg:items-stretch lg:gap-6"
        >
            <!-- Katalog -->
            <div class="flex min-h-0 min-w-0 flex-col gap-4 lg:overflow-hidden">
                <div class="shrink-0 space-y-3">
                    <input
                        v-model="search"
                        type="search"
                        class="input-field min-h-11 w-full"
                        placeholder="Cari nama atau kode layanan..."
                    />

                    <div class="flex gap-2 overflow-x-auto pb-1">
                        <button
                            v-for="cat in categories"
                            :key="cat.key"
                            type="button"
                            class="touch-btn shrink-0 rounded-full border px-4 text-sm"
                            :class="
                                category === cat.key
                                    ? 'border-brand-300 bg-brand-50 text-brand-800'
                                    : 'border-soft-200 bg-white text-soft-600 hover:bg-soft-50'
                            "
                            @click="category = cat.key"
                        >
                            {{ cat.label }}
                        </button>
                    </div>
                </div>

                <div class="min-h-0 flex-1 lg:overflow-y-auto lg:pr-1">
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                        <button
                            v-for="plan in filteredPlans"
                            :key="plan.id"
                            type="button"
                            class="card relative flex min-h-[7rem] flex-col items-start gap-2.5 overflow-hidden p-3.5 text-left transition hover:-translate-y-0.5 hover:shadow-soft-lg sm:min-h-[8rem] sm:p-4 xl:min-h-[8.5rem] xl:p-5"
                            :class="[
                                addingId === plan.id ? 'pointer-events-none scale-[0.98] ring-2 ring-brand-400' : 'active:scale-[0.98]',
                                justAddedId === plan.id ? 'ring-2 ring-emerald-400' : '',
                                addingId && addingId !== plan.id ? 'opacity-60' : '',
                            ]"
                            :disabled="!!addingId"
                            @click="addPlan(plan)"
                        >
                            <PlanIcon :name="plan.icon || 'wash'" :logo-url="plan.logo_url" />
                            <div class="min-w-0 w-full">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-brand-600">{{ plan.code }}</p>
                                <p class="mt-1 line-clamp-2 font-bold leading-snug text-soft-900">{{ plan.plan }}</p>
                                <p class="mt-2 text-sm font-semibold text-soft-700 sm:text-base">{{ money(plan.price) }}</p>
                            </div>

                            <!-- Loading / added overlay -->
                            <div
                                v-if="addingId === plan.id || justAddedId === plan.id"
                                class="absolute inset-0 flex items-center justify-center bg-white/80 backdrop-blur-[1px]"
                            >
                                <div
                                    v-if="addingId === plan.id"
                                    class="flex flex-col items-center gap-2"
                                >
                                    <span class="kasir-add-spinner h-8 w-8 rounded-full border-[3px] border-brand-200 border-t-brand-600" />
                                    <span class="text-xs font-semibold text-brand-700">Menambah...</span>
                                </div>
                                <div
                                    v-else
                                    class="flex flex-col items-center gap-1.5 text-emerald-700"
                                >
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                    <span class="text-xs font-semibold">Ditambahkan</span>
                                </div>
                            </div>
                        </button>
                    </div>
                    <p v-if="!filteredPlans.length" class="py-12 text-center text-sm text-soft-400">
                        Tidak ada layanan yang cocok
                    </p>
                </div>
            </div>

            <!-- Checkout: di desktop tinggi penuh + scroll internal, footer bayar sticky -->
            <form
                class="card flex w-full min-h-0 flex-col overflow-hidden lg:h-full"
                @submit.prevent="submit"
            >
                <div class="min-h-0 flex-1 space-y-3 overflow-y-auto p-4 xl:p-5">
                    <h2 class="text-base font-bold text-soft-900">Keranjang</h2>

                    <div>
                        <label class="label-field">Pelanggan</label>
                        <select v-model="form.customer_id" class="input-field min-h-11">
                            <option value="">Pelanggan baru...</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>

                    <div v-if="!form.customer_id" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                        <input v-model="form.name" type="text" class="input-field min-h-11" placeholder="Nama" required />
                        <input v-model="form.phone" type="text" class="input-field min-h-11" placeholder="Telepon" required />
                    </div>

                    <div class="space-y-2 rounded-2xl bg-soft-50 p-3">
                        <div
                            v-for="(row, index) in cart"
                            :key="`${row.plan_id}-${index}`"
                            class="rounded-xl bg-white p-3 shadow-soft"
                        >
                            <div class="flex items-start gap-2">
                                <PlanIcon
                                    :name="planOf(row.plan_id)?.icon || 'wash'"
                                    :logo-url="planOf(row.plan_id)?.logo_url"
                                    size="sm"
                                />
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-soft-800">{{ planOf(row.plan_id)?.plan }}</p>
                                    <p class="text-xs text-soft-500">{{ money(planOf(row.plan_id)?.price || 0) }} / item</p>
                                </div>
                                <button type="button" class="touch-target text-rose-500" @click="removeRow(index)">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="mt-3 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        class="touch-target rounded-xl border border-soft-200 bg-soft-50 text-lg font-bold text-soft-700 active:scale-95"
                                        @click="bumpQty(index, -1)"
                                    >
                                        −
                                    </button>
                                    <span class="min-w-[2rem] text-center text-base font-bold">{{ row.qty }}</span>
                                    <button
                                        type="button"
                                        class="touch-target rounded-xl border border-soft-200 bg-soft-50 text-lg font-bold text-soft-700 active:scale-95"
                                        @click="bumpQty(index, 1)"
                                    >
                                        +
                                    </button>
                                </div>
                                <p class="font-bold text-soft-900">
                                    {{ money((planOf(row.plan_id)?.price || 0) * row.qty) }}
                                </p>
                            </div>
                        </div>
                        <p v-if="!cart.length" class="py-6 text-center text-sm text-soft-400">Ketuk layanan di kiri</p>
                    </div>

                    <div>
                        <label class="label-field">Diskon (Rp)</label>
                        <input v-model="form.discount" type="number" min="0" class="input-field min-h-11" />
                    </div>

                    <div>
                        <p class="label-field">Metode bayar</p>
                        <div class="grid grid-cols-3 gap-2">
                            <button
                                v-for="m in [
                                    { key: 'cash', label: 'Tunai' },
                                    { key: 'qris', label: 'QRIS' },
                                    { key: 'transfer', label: 'Transfer' },
                                ]"
                                :key="m.key"
                                type="button"
                                class="touch-btn border"
                                :class="
                                    form.payment_method === m.key
                                        ? 'border-brand-400 bg-brand-50 text-brand-800'
                                        : 'border-soft-200 bg-white text-soft-600'
                                "
                                @click="setPayment(m.key)"
                            >
                                {{ m.label }}
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="label-field">Dibayar</label>
                        <input
                            v-model="form.paid"
                            type="number"
                            min="0"
                            class="input-field min-h-11 text-lg font-semibold"
                            :placeholder="String(total)"
                        />
                    </div>
                </div>

                <div class="shrink-0 space-y-3 border-t border-soft-100 bg-white p-4 xl:p-5">
                    <div class="rounded-2xl bg-brand-600 px-4 py-3 text-white shadow-soft sm:py-4">
                        <p class="text-sm text-brand-100">Total bayar</p>
                        <p class="text-2xl font-bold tracking-tight sm:text-3xl">{{ money(total) }}</p>
                    </div>

                    <button
                        type="submit"
                        class="btn-primary min-h-12 w-full text-base active:scale-[0.98]"
                        :disabled="form.processing || !cart.length"
                    >
                        {{ form.processing ? 'Menyimpan...' : 'Bayar & simpan' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
