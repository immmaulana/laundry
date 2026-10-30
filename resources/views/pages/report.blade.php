@extends('layouts.shell')

@section('title', 'Laporan')
@section('heading', 'Laporan')
@section('subheading', $labels[1] ?? '')

@section('topactions')
    <form method="get" action="{{ route('report.index') }}" style="display:flex;gap:8px;align-items:center">
        <input type="hidden" name="period" value="{{ $period }}">
        <input type="date" name="date" value="{{ $anchor->toDateString() }}" style="width:160px;padding:8px 10px">
        <button class="btn btn-g" type="submit">Terapkan</button>
    </form>
@endsection

@section('content')
<div class="row" style="align-items:center">
    <div class="seg">
        <a class="{{ $period === 'daily' ? 'on' : '' }}" href="{{ route('report.index', ['period' => 'daily', 'date' => $anchor->toDateString()]) }}">Harian</a>
        <a class="{{ $period === 'weekly' ? 'on' : '' }}" href="{{ route('report.index', ['period' => 'weekly', 'date' => $anchor->toDateString()]) }}">Mingguan</a>
        <a class="{{ $period === 'monthly' ? 'on' : '' }}" href="{{ route('report.index', ['period' => 'monthly', 'date' => $anchor->toDateString()]) }}">Bulanan</a>
        <a class="{{ $period === 'yearly' ? 'on' : '' }}" href="{{ route('report.index', ['period' => 'yearly', 'date' => $anchor->toDateString()]) }}">Tahunan</a>
    </div>
    <div class="muted" style="font-size:12.5px">
        Periode: <b>{{ $start->format('d/m/Y') }}</b> – <b>{{ $end->format('d/m/Y') }}</b>
    </div>
</div>

<div class="grid4">
    <div class="card kpi">
        <div class="lab"><span class="ico i-blue">Rp</span> Pendapatan</div>
        <div class="val">Rp {{ number_format($revenue, 0, ',', '.') }}</div>
        <div class="delta {{ ($delta ?? 0) >= 0 ? 'up' : 'down' }}">
            @if($delta === null)
                Tidak ada pembanding periode sebelumnya
            @else
                {{ $delta >= 0 ? '▲' : '▼' }} {{ abs($delta) }}% vs periode sebelumnya
            @endif
        </div>
    </div>
    <div class="card kpi">
        <div class="lab"><span class="ico i-amber">#</span> Jumlah pesanan</div>
        <div class="val">{{ $orders }}</div>
        <div class="delta muted">Rata-rata Rp {{ number_format($avg, 0, ',', '.') }} / pesanan</div>
    </div>
    <div class="card kpi">
        <div class="lab"><span class="ico i-green">✓</span> Nilai tagihan</div>
        <div class="val">Rp {{ number_format($billed, 0, ',', '.') }}</div>
        <div class="delta muted">Total nilai semua pesanan</div>
    </div>
    <div class="card kpi">
        <div class="lab"><span class="ico i-violet">!</span> Piutang periode</div>
        <div class="val">Rp {{ number_format($outstanding, 0, ',', '.') }}</div>
        <div class="delta {{ $outstanding > 0 ? 'down' : 'up' }}">{{ $outstanding > 0 ? 'Belum tertagih' : 'Semua lunas' }}</div>
    </div>
</div>

<div class="card">
    <div class="cardhd">
        <b>Pendapatan {{ strtolower($labels[0]) }}</b>
        <span class="muted" style="font-size:12px">{{ $labels[1] }}</span>
    </div>
    <div class="bars">
        @foreach($series as $s)
            <div class="bar">
                <i style="height:{{ max(3, (int) round(($s['value'] / $maxSeries) * 100)) }}%"></i>
                <span>{{ $s['label'] }}</span>
            </div>
        @endforeach
    </div>
</div>

<div class="row">
    <div class="card" style="flex:1;min-width:280px">
        <div class="cardhd"><b>Status pesanan</b></div>
        <table>
            <tr><th>Status</th><th style="text-align:right">Jumlah</th></tr>
            @foreach(['todo' => 'Menunggu', 'in_progress' => 'Dikerjakan', 'done' => 'Siap diambil', 'completed' => 'Diambil'] as $k => $lbl)
                <tr><td>{{ $lbl }}</td><td class="money" style="text-align:right">{{ $status[$k] ?? 0 }}</td></tr>
            @endforeach
            <tr><td><b>Total</b></td><td class="money" style="text-align:right"><b>{{ $orders }}</b></td></tr>
        </table>
    </div>

    <div class="card" style="flex:1.6;min-width:320px">
        <div class="cardhd"><b>Layanan terlaris</b></div>
        <table>
            <tr><th>Layanan</th><th style="text-align:right">Qty</th><th style="text-align:right">Pendapatan</th></tr>
            @forelse($topServices as $s)
                <tr>
                    <td>{{ $s->name }}</td>
                    <td class="money" style="text-align:right">{{ (int) $s->qty }}</td>
                    <td class="money" style="text-align:right">Rp {{ number_format((int) $s->amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="empty">Belum ada transaksi pada periode ini.</td></tr>
            @endforelse
        </table>
    </div>
</div>

<div class="muted" style="font-size:11.5px">
    Angka diambil dari tabel orders (ordered_at, total, payment) dan order_item_lines.
    Penjualan dihitung dari nilai yang benar-benar dibayar, piutang = tagihan − pembayaran.
</div>
@endsection
