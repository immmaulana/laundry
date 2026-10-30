<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { money } from '@/Composables/format';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    order: Object,
    customers: Array,
    plans: Array,
    statusLabels: Object,
    paymentLabels: Object,
});

const editing = computed(() => !!props.order);

const form = useForm({
    ordered_at: props.order?.ordered_at || new Date().toISOString().slice(0, 10),
    finished_at: props.order?.finished_at || '',
    customer_id: props.order?.customer_id || '',
    name: '',
    phone: '',
    address: '',
    payment_status: props.order?.payment_status || 'pending',
    order_status: props.order?.order_status || 'todo',
    down_payment: props.order?.down_payment || 0,
    payment: props.order?.payment || 0,
    change: props.order?.change || 0,
});

const itemForm = useForm({
    plan_id: '',
    quantity: 1,
    description: '',
});

const submit = () => {
    if (editing.value) {
        form.put(route('order.update', props.order.id));
    } else {
        form.post(route('order.store'));
    }
};

const addItem = () => {
    itemForm.post(route('order.item.store', props.order.id), {
        preserveScroll: true,
        onSuccess: () => itemForm.reset('quantity', 'description'),
    });
};

const removeItem = (itemId) => {
    router.delete(route('order.item.destroy', [props.order.id, itemId]), { preserveScroll: true });
};
</script>

<template>
    <Head :title="editing ? `Pesanan #${order.id}` : 'Pesanan baru'" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-bold text-soft-900">
                    {{ editing ? `Pesanan #${order.id}` : 'Buat pesanan' }}
                </h1>
                <p class="text-sm text-soft-500">Detail layanan dan status</p>
            </div>
        </template>

        <div class="grid gap-6 lg:grid-cols-3">
            <form class="card space-y-4 p-6 lg:col-span-2" @submit.prevent="submit">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label-field">Tanggal order</label>
                        <input v-model="form.ordered_at" type="date" class="input-field" required />
                    </div>
                    <div v-if="editing">
                        <label class="label-field">Estimasi selesai</label>
                        <input v-model="form.finished_at" type="date" class="input-field" />
                    </div>
                </div>

                <div v-if="!editing">
                    <label class="label-field">Pelanggan</label>
                    <select v-model="form.customer_id" class="input-field">
                        <option value="">Pelanggan baru...</option>
                        <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }} · {{ c.phone }}</option>
                    </select>
                    <div v-if="!form.customer_id" class="mt-3 grid gap-3 sm:grid-cols-2">
                        <input v-model="form.name" type="text" class="input-field" placeholder="Nama" required />
                        <input v-model="form.phone" type="text" class="input-field" placeholder="Telepon" required />
                        <input v-model="form.address" type="text" class="input-field sm:col-span-2" placeholder="Alamat (opsional)" />
                    </div>
                </div>

                <div v-else class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label-field">Pelanggan</label>
                        <select v-model="form.customer_id" class="input-field" required>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="label-field">Status cucian</label>
                        <select v-model="form.order_status" class="input-field">
                            <option v-for="(label, key) in statusLabels" :key="key" :value="key">{{ label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="label-field">Status bayar</label>
                        <select v-model="form.payment_status" class="input-field">
                            <option v-for="(label, key) in paymentLabels" :key="key" :value="key">{{ label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="label-field">Dibayar</label>
                        <input v-model="form.payment" type="number" min="0" class="input-field" />
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary" :disabled="form.processing">Simpan</button>
                    <Link :href="route('order.index')" class="btn-secondary">Kembali</Link>
                </div>
            </form>

            <div class="space-y-4">
                <div v-if="editing" class="card p-5">
                    <p class="text-sm text-soft-500">Total</p>
                    <p class="mt-1 text-2xl font-bold text-soft-900">{{ money(order.total) }}</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <StatusBadge :status="order.order_status" :label="statusLabels[order.order_status]" />
                        <StatusBadge :status="order.payment_status" :label="paymentLabels[order.payment_status]" />
                    </div>
                </div>

                <div v-if="editing" class="card p-5">
                    <h3 class="mb-3 font-semibold text-soft-900">Item layanan</h3>
                    <ul class="mb-4 space-y-2">
                        <li
                            v-for="item in order.item_lines"
                            :key="item.id"
                            class="flex items-start justify-between gap-3 rounded-xl bg-soft-50 px-3 py-2"
                        >
                            <div>
                                <p class="text-sm font-medium text-soft-800">{{ item.plan?.plan || item.description }}</p>
                                <p class="text-xs text-soft-500">{{ item.quantity }} × {{ money(item.price) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold">{{ money(item.subtotal) }}</p>
                                <button type="button" class="text-xs text-rose-600" @click="removeItem(item.id)">Hapus</button>
                            </div>
                        </li>
                        <li v-if="!order.item_lines.length" class="text-sm text-soft-400">Belum ada item</li>
                    </ul>

                    <form class="space-y-3 border-t border-soft-100 pt-4" @submit.prevent="addItem">
                        <select v-model="itemForm.plan_id" class="input-field" required>
                            <option value="" disabled>Pilih layanan</option>
                            <option v-for="plan in plans" :key="plan.id" :value="plan.id">
                                {{ plan.plan }} · {{ money(plan.price) }}
                            </option>
                        </select>
                        <input v-model="itemForm.quantity" type="number" min="1" class="input-field" placeholder="Qty" required />
                        <button type="submit" class="btn-secondary w-full" :disabled="itemForm.processing">Tambah item</button>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
