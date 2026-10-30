@extends('layouts.shell')

@section('title', 'Kasir')
@section('heading', 'Kasir')
@section('subheading', 'Shift pagi · ' . auth()->user()->name)
@section('topactions')
    <a class="btn btn-g" href="{{ route('order.index') }}">Papan Pesanan</a>
@endsection

@section('content')
<form method="post" action="{{ route('kasir.store') }}" id="posForm">
    @csrf
    <div class="pos">
        <div class="left">
            <div class="card" style="padding:12px;margin-bottom:14px;color:#94A3B8">
                <input type="text" id="svcSearch" placeholder="Cari layanan…" style="border:0;padding:4px;font-size:13px">
            </div>
            <div class="grid3" id="svcGrid">
                @foreach($plans as $p)
                    <div class="svc" data-id="{{ $p->id }}" data-name="{{ $p->plan }}" data-price="{{ $p->price }}"
                         data-unit="{{ $p->description }}">
                        <div class="nm">{{ $p->plan }}</div>
                        <div class="pr">Rp {{ number_format($p->price, 0, ',', '.') }} <span class="un">/ {{ $p->description }}</span></div>
                        <div class="muted" style="font-size:11.5px;margin-top:8px">Klik untuk tambah</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="cart">
            <div class="hd"><b>Pesanan baru</b><span>{{ $todayCount }} transaksi hari ini</span></div>

            <div class="items" id="cartItems">
                <div class="empty" id="cartEmpty">Belum ada layanan dipilih.</div>
            </div>

            <div style="padding:12px 16px;border-top:1px solid var(--line)">
                <label>Pelanggan</label>
                <select name="customer_id" id="customerSelect">
                    <option value="">— Pelanggan baru —</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} · {{ $c->phone }}</option>
                    @endforeach
                </select>
                <div class="formgrid" id="newCustomerFields" style="margin-top:10px">
                    <div><label>Nama</label><input type="text" name="name" placeholder="Nama pelanggan"></div>
                    <div><label>No. HP</label><input type="text" name="phone" placeholder="08xx"></div>
                </div>
            </div>

            <div class="tot">
                <div class="line"><span>Subtotal</span><span id="subtotal">Rp 0</span></div>
                <div class="line">
                    <span>Diskon</span>
                    <span><input type="number" name="discount" id="discount" value="0" min="0" style="width:110px;padding:4px 8px;text-align:right"></span>
                </div>
                <div class="line grand"><span>Total</span><span id="grandTotal">Rp 0</span></div>

                <div class="pay">
                    <label><input type="radio" name="payment_method" value="cash" checked><span>Tunai</span></label>
                    <label><input type="radio" name="payment_method" value="qris"><span>QRIS</span></label>
                    <label><input type="radio" name="payment_method" value="transfer"><span>Transfer</span></label>
                </div>

                <div style="margin-bottom:12px">
                    <label>Uang diterima (kosongkan = pas)</label>
                    <input type="number" name="paid" id="paid" min="0" placeholder="Pas">
                </div>

                <button class="btn btn-p" type="submit" style="width:100%;padding:13px">Bayar &amp; Simpan</button>
            </div>
        </div>
    </div>
    <div id="hiddenItems"></div>
</form>
@endsection

@push('script')
<script>
(function () {
    const cart = [];
    const grid = document.getElementById('svcGrid');
    const itemsBox = document.getElementById('cartItems');
    const emptyMsg = document.getElementById('cartEmpty');
    const hidden = document.getElementById('hiddenItems');
    const fmt = n => 'Rp ' + n.toLocaleString('id-ID');

    function render() {
        itemsBox.querySelectorAll('.it').forEach(el => el.remove());
        emptyMsg.style.display = cart.length ? 'none' : 'block';

        cart.forEach((it, idx) => {
            const row = document.createElement('div');
            row.className = 'it';
            row.innerHTML = `<div><div class="nm">${it.name}</div>
                <small>${it.qty} × ${fmt(it.price)}</small>
                <div style="margin-top:6px;display:flex;gap:6px;align-items:center">
                    <button type="button" class="btn btn-g btn-s" data-dec="${idx}">−</button>
                    <span style="font-size:12px;font-weight:700">${it.qty}</span>
                    <button type="button" class="btn btn-g btn-s" data-inc="${idx}">+</button>
                    <button type="button" class="btn btn-g btn-s" data-del="${idx}" style="margin-left:auto;color:#B91C1C">Hapus</button>
                </div></div>
                <div class="pr">${fmt(it.price * it.qty)}</div>`;
            itemsBox.appendChild(row);
        });

        const subtotal = cart.reduce((a, b) => a + b.price * b.qty, 0);
        const disc = Math.min(parseInt(document.getElementById('discount').value || '0', 10) || 0, subtotal);
        document.getElementById('subtotal').textContent = fmt(subtotal);
        document.getElementById('grandTotal').textContent = fmt(subtotal - disc);

        hidden.innerHTML = cart.map((it, i) =>
            `<input type="hidden" name="items[${i}][plan_id]" value="${it.id}">
             <input type="hidden" name="items[${i}][qty]" value="${it.qty}">`).join('');
    }

    grid.addEventListener('click', e => {
        const card = e.target.closest('.svc');
        if (!card) return;
        const id = card.dataset.id;
        const found = cart.find(x => x.id === id);
        if (found) found.qty++;
        else cart.push({ id, name: card.dataset.name, price: +card.dataset.price, qty: 1 });
        render();
    });

    itemsBox.addEventListener('click', e => {
        const inc = e.target.dataset.inc, dec = e.target.dataset.dec, del = e.target.dataset.del;
        if (inc !== undefined) cart[+inc].qty++;
        if (dec !== undefined && cart[+dec]) { cart[+dec].qty--; if (cart[+dec].qty <= 0) cart.splice(+dec, 1); }
        if (del !== undefined) cart.splice(+del, 1);
        if (inc !== undefined || dec !== undefined || del !== undefined) render();
    });

    document.getElementById('discount').addEventListener('input', render);

    const sel = document.getElementById('customerSelect');
    const newFields = document.getElementById('newCustomerFields');
    function toggleCustomer() { newFields.style.display = sel.value ? 'none' : 'grid'; }
    sel.addEventListener('change', toggleCustomer); toggleCustomer();

    document.getElementById('svcSearch').addEventListener('input', e => {
        const q = e.target.value.toLowerCase();
        grid.querySelectorAll('.svc').forEach(c => {
            c.style.display = c.dataset.name.toLowerCase().includes(q) ? '' : 'none';
        });
    });

    document.getElementById('posForm').addEventListener('submit', e => {
        if (!cart.length) { e.preventDefault(); alert('Pilih minimal satu layanan.'); }
    });

    render();
})();
</script>
@endpush
