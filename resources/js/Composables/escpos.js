/** ESC/POS helpers for 58mm thermal printers (~32 chars Font A). */

const ESC = 0x1b;
const GS = 0x1d;
const LF = 0x0a;

const enc = (text) => new TextEncoder('utf-8').encode(text);

export function moneyPlain(value) {
    return new Intl.NumberFormat('id-ID').format(Number(value || 0));
}

function padLine(left, right, width = 32) {
    const l = String(left ?? '');
    const r = String(right ?? '');
    const space = Math.max(1, width - l.length - r.length);
    return `${l}${' '.repeat(space)}${r}`.slice(0, width);
}

function center(text, width = 32) {
    const t = String(text ?? '').slice(0, width);
    const pad = Math.max(0, Math.floor((width - t.length) / 2));
    return `${' '.repeat(pad)}${t}`;
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

    // Initialize
    push(ESC, 0x40);
    // Code page / UTF-8 best effort (many BLE printers ignore)
    push(ESC, 0x74, 0x00);

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
    text('');
    text('');
    text('');

    // Partial cut
    push(GS, 0x56, 0x00);

    return new Uint8Array(chunks);
}

async function writeChunks(characteristic, data, chunkSize = 100) {
    for (let i = 0; i < data.length; i += chunkSize) {
        const slice = data.slice(i, i + chunkSize);
        // eslint-disable-next-line no-await-in-loop
        await characteristic.writeValueWithoutResponse?.(slice)
            ?? characteristic.writeValue(slice);
        // small delay helps flaky BLE printers
        // eslint-disable-next-line no-await-in-loop
        await new Promise((r) => setTimeout(r, 20));
    }
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
        ],
    });

    const server = await device.gatt.connect();
    const services = await server.getPrimaryServices();
    let writer = null;

    for (const service of services) {
        const chars = await service.getCharacteristics();
        for (const c of chars) {
            if (c.properties.write || c.properties.writeWithoutResponse) {
                writer = c;
                break;
            }
        }
        if (writer) break;
    }

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
        const chunkSize = 512;
        for (let i = 0; i < data.length; i += chunkSize) {
            // eslint-disable-next-line no-await-in-loop
            await writer.write(data.slice(i, i + chunkSize));
        }
    } finally {
        writer.releaseLock();
        await port.close();
    }
    return 'Serial printer';
}

export function paymentMethodLabel(method) {
    return ({ cash: 'Tunai', qris: 'QRIS', transfer: 'Transfer' })[method] || method || '-';
}
