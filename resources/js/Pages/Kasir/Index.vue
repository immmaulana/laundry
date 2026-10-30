<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { money } from '@/Composables/format';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    plans: Array,
    customers: Array,
    todayCount: Number,
});

const cart = ref([]);
const form = useForm({
    customer_id: '',
    name: '',
    phone: '',
    items: [],
    discount: 0,
    payment_method: 'cash',
    paid: 0,
});

const subtotal = computed(() =>
    cart.value.reduce((sum, row) => {
        const plan = props.plans.find((p) => p.id === row.plan_id);
        return sum + (plan?.price || 0) * row.qty;
    }, 0),
);

const total = computed(() => Math.max(0, subtotal.value - Number(form.discount || 0)));

const addPlan = (plan) => {
    const existing = cart.value.find((row) => row.plan_id === plan.id);
    if (existing) {
        existing.qty += 1;
    } else {
        cart.value.push({ plan_id: plan.id, qty: 1 });
    }
};

const removeRow = (index) => {
    cart.value.splice(index, 1);
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

        <div class="grid gap-6 lg:grid-cols-5">
            <div class="space-y-4 lg:col-span-3">
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    <button
                        v-for="plan in plans"
                        :key="plan.id"
                        type="button"
                        class="card p-4 text-left transition hover:-translate-y-0.5 hover:shadow-soft-lg"
                        @click="addPlan(plan)"
                    >
                        <p class="text-xs font-semibold uppercase tracking-wide text-brand-600">{{ plan.code }}</p>
                        <p class="mt-1 font-bold text-soft-900">{{ plan.plan }}</p>
                        <p class="mt-2 text-sm text-soft-600">{{ money(plan.price) }}</p>
                    </button>
                </div>
            </div>

            <form class="card space-y-4 p-5 lg:col-span-2" @submit.prevent="submit">
                <h2 class="font-semibold text-soft-900">Checkout</h2>

                <div>
                    <label class="label-field">Pelanggan</label>
                    <select v-model="form.customer_id" class="input-field">
                        <option value="">Pelanggan baru...</option>
                        <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>

                <div v-if="!form.customer_id" class="grid gap-3 sm:grid-cols-2">
                    <input v-model="form.name" type="text" class="input-field" placeholder="Nama" required />
                    <input v-model="form.phone" type="text" class="input-field" placeholder="Telepon" required />
                </div>

                <div class="space-y-2 rounded-2xl bg-soft-50 p-3">
                    <div
                        v-for="(row, index) in cart"
                        :key="`${row.plan_id}-${index}`"
                        class="flex items-center justify-between gap-2 text-sm"
                    >
                        <div>
                            <p class="font-medium text-soft-800">
                                {{ plans.find((p) => p.id === row.plan_id)?.plan }}
                            </p>
                            <div class="mt-1 flex items-center gap-2">
                                <input v-model.number="row.qty" type="number" min="1" class="input-field w-20 py-1" />
                                <button type="button" class="text-xs text-rose-600" @click="removeRow(index)">Hapus</button>
                            </div>
                        </div>
                        <p class="font-semibold">
                            {{ money((plans.find((p) => p.id === row.plan_id)?.price || 0) * row.qty) }}
                        </p>
                    </div>
                    <p v-if="!cart.length" class="text-sm text-soft-400">Pilih layanan di kiri</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="label-field">Diskon</label>
                        <input v-model="form.discount" type="number" min="0" class="input-field" />
                    </div>
                    <div>
                        <label class="label-field">Metode</label>
                        <select v-model="form.payment_method" class="input-field">
                            <option value="cash">Tunai</option>
                            <option value="qris">QRIS</option>
                            <option value="transfer">Transfer</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="label-field">Dibayar</label>
                    <input v-model="form.paid" type="number" min="0" class="input-field" :placeholder="String(total)" />
                </div>

                <div class="rounded-2xl bg-brand-50 px-4 py-3">
                    <p class="text-sm text-brand-700">Total bayar</p>
                    <p class="text-2xl font-bold text-brand-900">{{ money(total) }}</p>
                </div>

                <button type="submit" class="btn-primary w-full" :disabled="form.processing || !cart.length">
                    Simpan transaksi
                </button>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
