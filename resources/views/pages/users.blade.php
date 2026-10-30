@extends('layouts.shell')

@section('title', 'Karyawan & Akses')
@section('heading', 'Karyawan & Akses')
@section('subheading', 'Matriks hak akses Admin vs Kasir')

@section('content')
<div class="card">
    <div class="cardhd">
        <b>Matriks hak akses</b>
        <span class="muted" style="font-size:12px">Acuan implementasi middleware <code>role</code></span>
    </div>
    <table>
        <tr>
            <th>Modul</th>
            <th style="text-align:center">Kasir</th>
            <th style="text-align:center">Admin / Owner</th>
            <th>Catatan</th>
        </tr>
        @foreach($matrix as $row)
            <tr>
                <td><b>{{ $row['modul'] }}</b></td>
                <td style="text-align:center">
                    @if($row['kasir'] === true)
                        <span class="chip c-take">Penuh</span>
                    @elseif($row['kasir'] === false)
                        <span class="chip c-off">Tidak</span>
                    @elseif($row['kasir'] === 'ringkas')
                        <span class="chip c-prog">Ringkas</span>
                    @else
                        <span class="chip c-done">Terbatas</span>
                    @endif
                </td>
                <td style="text-align:center"><span class="chip c-take">Penuh</span></td>
                <td class="muted" style="font-size:12px">
                    @if($row['kasir'] === 'ringkas') Hanya order & status hari ini, tanpa nominal pendapatan
                    @elseif($row['kasir'] === 'profil') Hanya profil sendiri
                    @elseif($row['kasir'] === false) Menu tidak tampil, route diblokir middleware
                    @else Akses penuh @endif
                </td>
            </tr>
        @endforeach
    </table>
</div>

<div class="card">
    <div class="cardhd">
        <b>Pengguna</b>
        <span class="muted" style="font-size:12px">{{ $users->count() }} akun</span>
    </div>
    <table>
        <tr><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th style="text-align:right">Ubah akses</th></tr>
        @foreach($users as $u)
            <tr>
                <td><b>{{ $u->name }}</b>@if($u->id === auth()->id()) <span class="muted">(Anda)</span>@endif</td>
                <td class="muted">{{ $u->email }}</td>
                <td>
                    <span class="chip {{ $u->role === 'admin' ? 'c-done' : 'c-todo' }}">
                        {{ $u->role === 'admin' ? 'Admin' : 'Kasir' }}
                    </span>
                </td>
                <td>
                    <span class="chip {{ $u->is_active ? 'c-take' : 'c-off' }}">{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                </td>
                <td style="text-align:right">
                    <form method="post" action="{{ route('users.update', $u) }}" style="display:flex;gap:6px;justify-content:flex-end">
                        @csrf @method('patch')
                        <select name="role" style="width:110px;padding:6px 8px;font-size:12px">
                            <option value="kasir" @selected($u->role === 'kasir')>Kasir</option>
                            <option value="admin" @selected($u->role === 'admin')>Admin</option>
                        </select>
                        <select name="is_active" style="width:110px;padding:6px 8px;font-size:12px">
                            <option value="1" @selected($u->is_active)>Aktif</option>
                            <option value="0" @selected(!$u->is_active)>Nonaktif</option>
                        </select>
                        <button class="btn btn-g btn-s" type="submit">Simpan</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </table>
</div>

<div class="card">
    <div class="cardhd"><b>Tambah pengguna</b><span class="muted" style="font-size:12px">Password minimal 8 karakter</span></div>
    <form method="post" action="{{ route('users.store') }}">
        @csrf
        <div class="grid4">
            <div><label>Nama</label><input type="text" name="name" required></div>
            <div><label>Email</label><input type="email" name="email" required></div>
            <div><label>Password</label><input type="password" name="password" required minlength="8"></div>
            <div>
                <label>Role</label>
                <select name="role" required>
                    <option value="kasir">Kasir</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
        </div>
        <div style="margin-top:14px"><button class="btn btn-p" type="submit">Tambah pengguna</button></div>
    </form>
</div>
@endsection
