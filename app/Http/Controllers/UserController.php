<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /** Matriks hak akses yang ditampilkan di halaman dan dipakai sebagai acuan. */
    public const MATRIX = [
        ['modul' => 'Dashboard',        'kasir' => 'ringkas', 'admin' => true],
        ['modul' => 'Kasir (POS)',      'kasir' => true,      'admin' => true],
        ['modul' => 'Pesanan',          'kasir' => true,      'admin' => true],
        ['modul' => 'Pelanggan',        'kasir' => true,      'admin' => true],
        ['modul' => 'Layanan & Harga',  'kasir' => false,     'admin' => true],
        ['modul' => 'Laporan',          'kasir' => false,     'admin' => true],
        ['modul' => 'Karyawan & Akses', 'kasir' => false,     'admin' => true],
        ['modul' => 'Pengaturan',       'kasir' => 'profil',  'admin' => true],
    ];

    public function index()
    {
        return view('pages.users', [
            'users' => User::orderByRaw("FIELD(role,'admin','kasir')")->orderBy('name')->get(),
            'matrix' => self::MATRIX,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'kasir'])],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'is_active' => true,
            'language' => 'id',
        ]);

        return back()->with('success', 'Pengguna ' . $data['name'] . ' ditambahkan sebagai ' . $data['role'] . '.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(['admin', 'kasir'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($user->id === auth()->id() && $data['role'] !== 'admin') {
            return back()->with('error', 'Tidak bisa menurunkan role akun sendiri.');
        }

        $user->update([
            'role' => $data['role'],
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return back()->with('success', 'Akses ' . $user->name . ' diperbarui.');
    }
}
