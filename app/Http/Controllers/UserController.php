<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public const MATRIX = [
        ['modul' => 'Dashboard', 'kasir' => 'ringkas', 'admin' => true],
        ['modul' => 'Kasir (POS)', 'kasir' => true, 'admin' => true],
        ['modul' => 'Pesanan', 'kasir' => true, 'admin' => true],
        ['modul' => 'Pelanggan', 'kasir' => true, 'admin' => true],
        ['modul' => 'Layanan & Harga', 'kasir' => false, 'admin' => true],
        ['modul' => 'Laporan', 'kasir' => false, 'admin' => true],
        ['modul' => 'Karyawan & Akses', 'kasir' => false, 'admin' => true],
        ['modul' => 'Pengaturan', 'kasir' => 'profil', 'admin' => true],
    ];

    public function index(): Response
    {
        $companyId = auth()->user()->company_id;

        return Inertia::render('Users/Index', [
            'users' => User::query()
                ->where('company_id', $companyId)
                ->orderByRaw("FIELD(role,'admin','kasir')")
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'role', 'is_active', 'created_at']),
            'matrix' => self::MATRIX,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'kasir'])],
        ]);

        User::create([
            'company_id' => auth()->user()->company_id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'is_active' => true,
            'language' => 'id',
        ]);

        return back()->with('success', 'Pengguna '.$data['name'].' ditambahkan sebagai '.$data['role'].'.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        if ($user->company_id !== auth()->user()->company_id) {
            abort(403);
        }

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

        return back()->with('success', 'Akses '.$user->name.' diperbarui.');
    }
}
