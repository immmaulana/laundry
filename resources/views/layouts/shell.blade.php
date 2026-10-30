<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'LaundryKu') · {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/majoo.css') }}">
</head>
<body>
@php($isAdmin = auth()->user()->role === 'admin')
<div class="app">
    <aside class="side">
        <div class="brand"><div class="dot">L</div><b>LaundryKu</b></div>
        <div class="outlet">
            <div><small>Outlet aktif</small><b>Cabang Malang</b></div>
            <span style="color:#64748B">▾</span>
        </div>

        <div class="navsec">Operasional</div>
        <a class="nav {{ Route::is('home') ? 'on' : '' }}" href="{{ route('home') }}"><span class="ic"></span> Dashboard</a>
        <a class="nav {{ Route::is('kasir.*') ? 'on' : '' }}" href="{{ route('kasir.index') }}"><span class="ic"></span> Kasir</a>
        <a class="nav {{ Route::is('order.*') ? 'on' : '' }}" href="{{ route('order.index') }}"><span class="ic"></span> Pesanan</a>
        <a class="nav {{ Route::is('customer.*') ? 'on' : '' }}" href="{{ route('customer.index') }}"><span class="ic"></span> Pelanggan</a>

        @if($isAdmin)
            <a class="nav {{ Route::is('plan.*') ? 'on' : '' }}" href="{{ route('plan.index') }}"><span class="ic"></span> Layanan &amp; Harga</a>
            <div class="navsec">Bisnis</div>
            <a class="nav {{ Route::is('report.*') ? 'on' : '' }}" href="{{ route('report.index') }}"><span class="ic"></span> Laporan</a>
            <a class="nav {{ Route::is('users.*') ? 'on' : '' }}" href="{{ route('users.index') }}"><span class="ic"></span> Karyawan &amp; Akses</a>
        @endif

        <a class="nav" href="{{ url('/setting') }}"><span class="ic"></span> Pengaturan</a>

        <div class="foot">
            <div class="ava">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
            <div>
                <b style="font-size:12.5px">{{ auth()->user()->name }}</b>
                <div class="muted" style="font-size:11px">{{ $isAdmin ? 'Admin / Owner' : 'Kasir' }}</div>
            </div>
            <form method="post" action="{{ route('logout') }}" style="margin-left:auto">
                @csrf
                <button class="btn btn-g btn-s" type="submit">Keluar</button>
            </form>
        </div>
    </aside>

    <div class="main">
        <div class="top">
            <b style="font-size:15px">@yield('heading', 'Dashboard')</b>
            <span class="muted" style="font-size:12px">@yield('subheading', '')</span>
            <div class="sp"></div>
            @yield('topactions')
            <div class="ava">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
        </div>

        <div class="wrap">
            @if(session('success'))
                <div class="alert alert-ok">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-err">{{ session('error') }}</div>
            @endif
            @yield('content')
        </div>
    </div>
</div>
@stack('script')
</body>
</html>
