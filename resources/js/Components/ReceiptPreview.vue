<script setup>
import { computed, ref } from 'vue';
import { money } from '@/Composables/format';
import {
    buildEscPosReceipt,
    paymentMethodLabel,
    printBluetoothEscPos,
    printSerialEscPos,
} from '@/Composables/escpos';

const props = defineProps({
    receipt: { type: Object, required: true },
});

const emit = defineEmits(['close']);

const printing = ref(false);
const printError = ref('');
const printOk = ref('');

const methodLabel = computed(() =>
    props.receipt.payment_method_label
    || paymentMethodLabel(props.receipt.payment_method),
);

const printBrowser = () => {
    window.print();
};

const runPrint = async (mode) => {
    printError.value = '';
    printOk.value = '';
    printing.value = true;
    try {
        const bytes = buildEscPosReceipt(props.receipt, 32);
        const name = mode === 'serial'
            ? await printSerialEscPos(bytes)
            : await printBluetoothEscPos(bytes);
        printOk.value = `Terkirim ke ${name}`;
    } catch (e) {
        printError.value = e?.message || 'Gagal mencetak';
    } finally {
        printing.value = false;
    }
};
</script>

<template>
    <div class="fixed inset-0 z-[60] flex items-end justify-center bg-soft-900/40 p-4 backdrop-blur-sm sm:items-center">
        <div
            class="absolute inset-0"
            @click="emit('close')"
        />

        <div class="relative z-10 flex w-full max-w-3xl flex-col gap-4 sm:flex-row sm:items-start">
            <!-- Preview 58mm -->
            <div
                id="receipt-print-area"
                class="receipt-58 mx-auto w-[280px] shrink-0 rounded-xl bg-white p-4 font-mono text-[11px] leading-relaxed text-soft-900 shadow-soft-lg ring-1 ring-soft-200"
            >
                <div class="text-center">
                    <p class="text-sm font-bold uppercase tracking-wide">{{ receipt.company_name }}</p>
                    <p v-if="receipt.company_address" class="mt-0.5 text-[10px] text-soft-600">{{ receipt.company_address }}</p>
                    <p v-if="receipt.company_phone" class="text-[10px] text-soft-600">Tel: {{ receipt.company_phone }}</p>
                    <p class="mt-2 text-[10px] text-soft-400">--------------------------------</p>
                </div>

                <div class="space-y-0.5 text-[10px]">
                    <div class="flex justify-between gap-2"><span>No</span><span>#{{ receipt.id }}</span></div>
                    <div class="flex justify-between gap-2"><span>Tanggal</span><span>{{ receipt.ordered_at }}</span></div>
                    <div class="flex justify-between gap-2"><span>Kasir</span><span>{{ receipt.cashier || '—' }}</span></div>
                    <div class="flex justify-between gap-2"><span>Pelanggan</span><span class="text-right">{{ receipt.customer_name || '—' }}</span></div>
                    <div v-if="receipt.customer_phone" class="flex justify-between gap-2"><span>HP</span><span>{{ receipt.customer_phone }}</span></div>
                </div>

                <p class="my-2 text-center text-[10px] text-soft-400">--------------------------------</p>

                <div class="space-y-2">
                    <div v-for="(item, i) in receipt.items" :key="i">
                        <p class="font-semibold">{{ item.name }}</p>
                        <div class="flex justify-between text-[10px] text-soft-600">
                            <span>{{ item.qty }} x {{ money(item.price) }}</span>
                            <span>{{ money(item.subtotal) }}</span>
                        </div>
                    </div>
                </div>

                <p class="my-2 text-center text-[10px] text-soft-400">--------------------------------</p>

                <div class="space-y-0.5 text-[10px]">
                    <div v-if="Number(receipt.discount) > 0" class="flex justify-between">
                        <span>Diskon</span><span>- {{ money(receipt.discount) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Subtotal</span><span>{{ money(receipt.subtotal ?? receipt.total) }}</span>
                    </div>
                    <div class="flex justify-between text-sm font-bold">
                        <span>TOTAL</span><span>{{ money(receipt.total) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Bayar</span><span>{{ money(receipt.paid) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Kembali</span><span>{{ money(receipt.change) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Metode</span><span>{{ methodLabel }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Status</span><span>{{ receipt.payment_status_label || receipt.payment_status }}</span>
                    </div>
                </div>

                <p class="my-2 text-center text-[10px] text-soft-400">--------------------------------</p>
                <p class="text-center text-[10px]">Terima kasih</p>
                <p class="text-center text-[10px] text-soft-500">Simpan struk ini</p>
                <p class="mt-3 text-center text-[9px] text-soft-400">Preview 58mm · ESC/POS</p>
            </div>

            <!-- Actions -->
            <div class="card relative z-10 w-full space-y-3 p-4 sm:max-w-xs">
                <div>
                    <h3 class="font-bold text-soft-900">Cetak struk</h3>
                    <p class="mt-1 text-sm text-soft-500">
                        Thermal 58mm Bluetooth ESC/POS, atau cetak browser.
                    </p>
                </div>

                <p v-if="printOk" class="rounded-xl bg-emerald-50 px-3 py-2 text-sm text-emerald-700">{{ printOk }}</p>
                <p v-if="printError" class="rounded-xl bg-rose-50 px-3 py-2 text-sm text-rose-700">{{ printError }}</p>

                <button
                    type="button"
                    class="btn-primary w-full min-h-11"
                    :disabled="printing"
                    @click="runPrint('bluetooth')"
                >
                    {{ printing ? 'Mengirim...' : 'Cetak Bluetooth 58mm' }}
                </button>
                <button
                    type="button"
                    class="btn-secondary w-full min-h-11"
                    :disabled="printing"
                    @click="runPrint('serial')"
                >
                    Cetak via USB / Serial
                </button>
                <button
                    type="button"
                    class="btn-secondary w-full min-h-11"
                    @click="printBrowser"
                >
                    Preview cetak browser
                </button>
                <button type="button" class="w-full text-sm font-medium text-soft-500 hover:text-soft-800" @click="emit('close')">
                    Tutup
                </button>

                <p class="text-[11px] leading-relaxed text-soft-400">
                    Chrome/Edge + HTTPS/localhost. Pilih printer ESC/POS 58mm saat dialog muncul.
                </p>
            </div>
        </div>
    </div>
</template>

<style>
@media print {
    body * {
        visibility: hidden !important;
    }
    #receipt-print-area,
    #receipt-print-area * {
        visibility: visible !important;
    }
    #receipt-print-area {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 58mm !important;
        margin: 0 !important;
        box-shadow: none !important;
        border: none !important;
        border-radius: 0 !important;
    }
}
</style>
