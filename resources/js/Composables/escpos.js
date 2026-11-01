/** ESC/POS helpers for 58mm thermal printers (~32 chars Font A). */

const ESC = 0x1b;
const GS = 0x1d;

const enc = (text) => new TextEncoder('utf-8').encode(text);

/** Prefer known BLE printer TX characteristics (common ESC/POS modules). */
const PREFERRED_WRITE_UUIDS = new Set([
    '0000ffe1-0000-1000-8000-00805f9b34fb',
    '0000ff02-0000-1000-8000-00805f9b34fb',
    '0000ff01-0000-1000-8000-00805f9b34fb',
    '0000fff1-0000-1000-8000-00805f9b34fb',
    '0000fff2-0000-1000-8000-00805f9b34fb',
    '49535343-8841-43f4-a8d4-ecbe34729bb3', // Nordic UART TX
]);

export function moneyPlain(value) {
    return new Intl.NumberFormat('id-ID').format(Number(value || 0));
}

function padLine(left, right, width = 32) {
    const l = String(left ?? '');
    const r = String(right ?? '');
    const space = Math.max(1, width - [...l].length - [...r].length);
    return `${l}${' '.repeat(space)}${r}`.slice(0, width);
}

function dash(width = 32) {
    return '-'.repeat(width);
}

/**
 * Build ESC/POS byte buffer for a receipt payload.
 * @param {object} receipt
 */
export function buildEscPosReceipt(receipt, width = 32) {
    const chunks = [];
    const push = (...bytes) => chunks.push(...bytes);
    const text = (line = '') => {
        chunks.push(...enc(`${line}\n`));
    };

    // Initialize printer
    push(ESC, 0x40);
    // Code page 0 (PC437) — safest for many cheap BLE printers
    push(ESC, 0x74, 0x00);
    // Line spacing
    push(ESC, 0x33, 0x28);

    // Center header
    push(ESC, 0x61, 0x01);
    push(ESC, 0x21, 0x10); // double height
    text(receipt.company_name || 'CashierLaundry');
    push(ESC, 0x21, 0x00);
    if (receipt.company_address) text(receipt.company_address);
    if (receipt.company_phone) text(`Tel: ${receipt.company_phone}`);
    text(dash(width));

    push(ESC, 0x61, 0x00); // left
    text(`No    : #${receipt.id}`);
    text(`Tanggal: ${receipt.ordered_at}`);
    text(`Kasir : ${receipt.cashier || '-'}`);
    text(`Pelanggan: ${receipt.customer_name || '-'}`);
    if (receipt.customer_phone) text(`HP    : ${receipt.customer_phone}`);
    text(dash(width));

    for (const item of receipt.items || []) {
        text(item.name);
        text(padLine(`${item.qty} x ${moneyPlain(item.price)}`, moneyPlain(item.subtotal), width));
    }

    text(dash(width));
    if (Number(receipt.discount) > 0) {
        text(padLine('Diskon', `- ${moneyPlain(receipt.discount)}`, width));
    }
    text(padLine('Subtotal', moneyPlain(receipt.subtotal ?? receipt.total), width));
    push(ESC, 0x21, 0x08); // emphasis
    text(padLine('TOTAL', moneyPlain(receipt.total), width));
    push(ESC, 0x21, 0x00);
    text(padLine('Bayar', moneyPlain(receipt.paid), width));
    text(padLine('Kembali', moneyPlain(receipt.change), width));
    text(padLine('Metode', receipt.payment_method_label || receipt.payment_method || '-', width));
    text(padLine('Status', receipt.payment_status_label || receipt.payment_status || '-', width));
    text(dash(width));

    push(ESC, 0x61, 0x01);
    text('Terima kasih');
    text('Simpan struk ini');
    // Feed enough paper so content clears the cutter
    text('');
    text('');
    text('');
    text('');
    text('');

    // Feed + partial cut (GS V m n) — more reliable than GS V 0 alone
    push(GS, 0x56, 0x41, 0x03);

    return new Uint8Array(chunks);
}

async function writeChunk(characteristic, slice, useWithoutResponse) {
    if (useWithoutResponse && characteristic.properties.writeWithoutResponse) {
        await characteristic.writeValueWithoutResponse(slice);
        return;
    }
    if (characteristic.properties.write) {
        await characteristic.writeValue(slice);
        return;
    }
    if (characteristic.properties.writeWithoutResponse) {
        await characteristic.writeValueWithoutResponse(slice);
        return;
    }
    throw new Error('Karakteristik tidak bisa ditulis.');
}

/**
 * BLE printers often drop data if chunks > MTU (~20) or arrive too fast.
 */
async function writeChunks(characteristic, data) {
    const canWriteWithResponse = Boolean(characteristic.properties.write);
    // Prefer with-response for reliability; without-response needs tiny chunks + delay
    const useWithoutResponse = !canWriteWithResponse;
    const chunkSize = useWithoutResponse ? 20 : 100;
    const delayMs = useWithoutResponse ? 60 : 25;

    for (let i = 0; i < data.length; i += chunkSize) {
        const slice = data.slice(i, i + chunkSize);
        // eslint-disable-next-line no-await-in-loop
        await writeChunk(characteristic, slice, useWithoutResponse);
        // eslint-disable-next-line no-await-in-loop
        await new Promise((r) => setTimeout(r, delayMs));
    }

    // Let the printer drain its internal buffer before we finish
    await new Promise((r) => setTimeout(r, 400));
}

function scoreWriteCharacteristic(c) {
    const uuid = String(c.uuid || '').toLowerCase();
    let score = 0;
    if (PREFERRED_WRITE_UUIDS.has(uuid)) score += 50;
    if (c.properties.write) score += 20;
    if (c.properties.writeWithoutResponse) score += 10;
    if (uuid.includes('ffe1') || uuid.includes('ff02') || uuid.includes('fff2')) score += 15;
    return score;
}

async function findWriteCharacteristic(server) {
    const services = await server.getPrimaryServices();
    const candidates = [];

    for (const service of services) {
        // eslint-disable-next-line no-await-in-loop
        const chars = await service.getCharacteristics();
        for (const c of chars) {
            if (c.properties.write || c.properties.writeWithoutResponse) {
                candidates.push(c);
            }
        }
    }

    if (!candidates.length) return null;

    candidates.sort((a, b) => scoreWriteCharacteristic(b) - scoreWriteCharacteristic(a));
    return candidates[0];
}

/**
 * Print via Web Bluetooth (BLE ESC/POS printers).
 * Requires HTTPS or localhost + Chrome/Edge.
 */
export async function printBluetoothEscPos(data) {
    if (!navigator.bluetooth) {
        throw new Error('Browser tidak mendukung Web Bluetooth. Pakai Chrome/Edge di HTTPS atau localhost.');
    }

    const device = await navigator.bluetooth.requestDevice({
        acceptAllDevices: true,
        optionalServices: [
            '000018f0-0000-1000-8000-00805f9b34fb',
            '0000ff00-0000-1000-8000-00805f9b34fb',
            '0000ffe0-0000-1000-8000-00805f9b34fb',
            '0000fff0-0000-1000-8000-00805f9b34fb',
            '49535343-fe7d-4ae5-8fa9-9fafd205e455',
            '0000ae30-0000-1000-8000-00805f9b34fb',
            '0000ff10-0000-1000-8000-00805f9b34fb',
        ],
    });

    const server = await device.gatt.connect();
    const writer = await findWriteCharacteristic(server);

    if (!writer) {
        throw new Error('Karakteristik tulis tidak ditemukan di printer Bluetooth.');
    }

    await writeChunks(writer, data);
    return device.name || 'Bluetooth printer';
}

/**
 * Print via Web Serial (USB / Bluetooth SPP adapter).
 */
export async function printSerialEscPos(data, baudRate = 9600) {
    if (!navigator.serial) {
        throw new Error('Browser tidak mendukung Web Serial. Pakai Chrome/Edge.');
    }

    const port = await navigator.serial.requestPort();
    await port.open({ baudRate });
    const writer = port.writable.getWriter();
    try {
        const chunkSize = 256;
        for (let i = 0; i < data.length; i += chunkSize) {
            // eslint-disable-next-line no-await-in-loop
            await writer.write(data.slice(i, i + chunkSize));
            // eslint-disable-next-line no-await-in-loop
            await new Promise((r) => setTimeout(r, 15));
        }
        await new Promise((r) => setTimeout(r, 300));
    } finally {
        writer.releaseLock();
        await port.close();
    }
    return 'Serial printer';
}

export function paymentMethodLabel(method) {
    return ({ cash: 'Tunai', qris: 'QRIS', transfer: 'Transfer' })[method] || method || '-';
}
