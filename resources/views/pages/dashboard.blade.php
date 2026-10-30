@extends('layouts.shell')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subheading', now()->translatedFormat('l, j F Y'))

@section('topactions')
    <a class="btn btn-g" href="{{ route('order.create') }}">+ Pesanan Baru</a>
    <a class="btn btn-p" href="{{ route('kasir.index') }}">Buka Kasir</a>
@endsection

@section('content')
<?php
    $isAdmin = auth()->user()->role === 'admin';
    $statusMap = [
        'todo' => ['Menunggu', 'c-todo', 'Belum dicuci', '#94A3B8'],
        'in_progress' => ['Dicuci', 'c-prog', 'Sedang proses', '#F59E0B'],
        'done' => ['Siap diambil', 'c-done', 'Selesai diproses', '#2563EB'],
        'completed' => ['Diambil', 'c-take', 'Sudah diserahkan', '#16A34A'],
    ];
?>
<div>
    <h1>Selamat datang, {{ auth()->user()->name }}</h1>
    <div class="sub">Ringkasan operasional {{ now()->translatedFormat('F Y') }}.</div>
</div>

<div class="grid4">
    <?php if ($isAdmin): ?>
    <div class="card kpi">
        <div class="lab"><span class="ico i-blue">Rp</span> Pendapatan bulan ini</div>
        <div class="val">Rp {{ number_format($revenue, 0, ',', '.') }}</div>
        <div class="delta muted">Tagihan Rp {{ number_format($billed, 0, ',', '.') }}</div>
    </div>
    <?php endif; ?>

    <div class="card kpi">
        <div class="lab"><span class="ico i-amber">↻</span> Order diproses</div>
        <div class="val">{{ ($counts['todo'] ?? 0) + ($counts['in_progress'] ?? 0) }}</div>
        <div class="delta muted">{{ $counts['todo'] ?? 0 }} menunggu · {{ $counts['in_progress'] ?? 0 }} dikerjakan</div>
    </div>

    <div class="card kpi">
        <div class="lab"><span class="ico i-green">✓</span> Siap diambil</div>
        <div class="val">{{ $counts['done'] ?? 0 }}</div>
        <div class="delta warn">Belum diserahkan</div>
    </div>

    <div class="card kpi">
        <div class="lab"><span class="ico i-violet">{{ $isAdmin ? '!' : 'Rp' }}</span> {{ $isAdmin ? 'Piutang' : 'Penjualan hari ini' }}</div>
        <div class="val">
            <?php if ($isAdmin): ?>
                Rp {{ number_format($outstanding, 0, ',', '.') }}
            <?php else: ?>
                Rp {{ number_format($todayRevenue, 0, ',', '.') }}
            <?php endif; ?>
        </div>
        <div class="delta muted"><?php echo $isAdmin ? 'Tagihan dikurangi pembayaran' : $todayOrders . ' transaksi hari ini'; ?></div>
    </div>
</div>

<div class="row">
    <?php if ($isAdmin): ?>
    <div class="card" style="flex:1.55;min-width:320px">
        <div class="cardhd"><b>Pendapatan 7 hari terakhir</b><a href="{{ route('report.index') }}">Lihat laporan →</a></div>
        <div class="bars">
            @foreach($series as $s)
                <div class="bar">
                    <i style="height:{{ max(3, (int) round(($s['value'] / $maxSeries) * 100)) }}%"></i>
                    <span>{{ $s['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
    <?php endif; ?>

    <div class="card" style="flex:1;min-width:280px">
        <div class="cardhd"><b>Papan status cucian</b><a href="{{ route('order.index') }}">Semua →</a></div>
        <div style="display:flex;flex-direction:column;gap:10px">
            @foreach($statusMap as $key => $meta)
                <div class="strow">
                    <span class="pill" style="background:{{ $meta[3] }}"></span>
                    <div>
                        <b style="font-size:13px;display:block">{{ $meta[0] }}</b>
                        <span class="muted" style="font-size:11.5px">{{ $meta[2] }}</span>
                    </div>
                    <b>{{ $counts[$key] ?? 0 }}</b>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card">
    <div class="cardhd"><b>Pesanan terbaru</b><a href="{{ route('order.index') }}">Buka papan pesanan →</a></div>
    <table>
        <tr>
            <th>No. Nota</th><th>Pelanggan</th><th>Layanan</th><th>Tanggal</th><th>Status</th><th>Total</th><th>Bayar</th>
        </tr>
        @forelse($recent as $o)
            <tr>
                <td><b>INV-{{ str_pad($o->id, 4, '0', STR_PAD_LEFT) }}</b></td>
                <td>{{ $o->customer->name ?? '-' }}</td>
                <td class="muted">{{ optional($o->itemLines->first())->description ?? '-' }}</td>
                <td class="muted">{{ \Illuminate\Support\Carbon::parse($o->ordered_at)->format('d/m') }}</td>
                <td>
                    <span class="chip {{ $statusMap[$o->order_status][1] ?? 'c-todo' }}">
                        {{ $statusMap[$o->order_status][0] ?? $o->order_status }}
                    </span>
                </td>
                <td class="money">Rp {{ number_format($o->total, 0, ',', '.') }}</td>
                <td class="money">{{ $o->payment_status === 'paid' ? 'Lunas' : ($o->payment_status === 'partial' ? 'DP' : 'Belum') }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="empty">Belum ada pesanan.</td></tr>
        @endforelse
    </table>
</div>
@endsection
