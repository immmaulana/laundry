<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Support\ThemePalette;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function index(): Response
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        $companies = Company::query()
            ->withCount(['users', 'customers', 'plans', 'orders'])
            ->orderBy('name')
            ->get()
            ->map(fn (Company $company) => [
                'id' => $company->id,
                'name' => $company->name,
                'slug' => $company->slug,
                'code' => $company->code,
                'phone' => $company->phone,
                'email' => $company->email,
                'city' => $company->city,
                'address' => $company->address,
                'is_active' => $company->is_active,
                'primary_color' => $company->primary_color,
                'secondary_color' => $company->secondary_color,
                'logo_url' => $company->logo_url,
                'users_count' => $company->users_count,
                'customers_count' => $company->customers_count,
                'plans_count' => $company->plans_count,
                'orders_count' => $company->orders_count,
                'created_at' => $company->created_at?->toDateString(),
            ]);

        return Inertia::render('Companies/Index', [
            'companies' => $companies,
            'stats' => [
                'total' => $companies->count(),
                'active' => $companies->where('is_active', true)->count(),
                'orders' => Order::withoutGlobalScopes()->count(),
                'customers' => Customer::withoutGlobalScopes()->count(),
            ],
        ]);
    }

    public function create(): Response
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        return Inertia::render('Companies/Form', [
            'company' => null,
            'presets' => $this->presets(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        $data = $this->validated($request);
        $company = Company::create($data + ['is_active' => true]);
        $this->storeLogo($request, $company);

        if ($request->filled('admin_name') && $request->filled('admin_email')) {
            $request->validate([
                'admin_name' => ['required', 'string', 'max:100'],
                'admin_email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')],
                'admin_password' => ['required', 'string', 'min:8'],
            ]);

            User::create([
                'company_id' => $company->id,
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => $request->admin_password,
                'role' => 'admin',
                'is_active' => true,
                'language' => 'id',
            ]);
        }

        return redirect()->route('companies.index')->with('success', 'Perusahaan '.$company->name.' ditambahkan.');
    }

    public function edit(Company $company): Response
    {
        $user = auth()->user();

        abort_unless(
            $user->isSuperAdmin() || ($user->isAdmin() && $user->company_id === $company->id),
            403
        );

        return Inertia::render('Companies/Form', [
            'company' => [
                ...$company->only([
                    'id', 'name', 'slug', 'code', 'phone', 'email', 'address', 'city', 'is_active',
                    'primary_color', 'secondary_color',
                ]),
                'logo_url' => $company->logo_url,
            ],
            'presets' => $this->presets(),
        ]);
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $user = auth()->user();

        abort_unless(
            $user->isSuperAdmin() || ($user->isAdmin() && $user->company_id === $company->id),
            403
        );

        $data = $this->validated($request, $company);

        if (! $user->isSuperAdmin()) {
            unset($data['is_active'], $data['code'], $data['slug']);
        }

        $company->update($data);
        $this->storeLogo($request, $company);

        if ($request->boolean('remove_logo') && ! $request->hasFile('logo')) {
            $this->deleteLogo($company);
            $company->update(['logo' => null]);
        }

        $redirect = $user->isSuperAdmin()
            ? route('companies.index')
            : route('companies.edit', $company);

        return redirect($redirect)->with('success', 'Data perusahaan diperbarui.');
    }

    public function destroy(Company $company): RedirectResponse
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        if ($company->orders()->withoutGlobalScopes()->exists()) {
            return back()->with('error', 'Tidak bisa hapus: masih ada pesanan. Nonaktifkan saja.');
        }

        $this->deleteLogo($company);
        $company->delete();

        return redirect()->route('companies.index')->with('success', 'Perusahaan dihapus.');
    }

    private function validated(Request $request, ?Company $company = null): array
    {
        $slugRule = Rule::unique('companies', 'slug');
        $codeRule = Rule::unique('companies', 'code');

        if ($company) {
            $slugRule->ignore($company->id);
            $codeRule->ignore($company->id);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150', $slugRule],
            'code' => ['required', 'string', 'max:20', $codeRule],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'primary_color' => ['nullable', 'string', 'regex:/^#?[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['nullable', 'string', 'regex:/^#?[0-9A-Fa-f]{6}$/'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);

        unset($data['logo'], $data['remove_logo']);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $data['primary_color'] = ThemePalette::normalize($data['primary_color'] ?? ThemePalette::DEFAULT_PRIMARY);
        $data['secondary_color'] = ThemePalette::normalize(
            $data['secondary_color'] ?? ThemePalette::DEFAULT_SECONDARY,
            ThemePalette::DEFAULT_SECONDARY,
        );

        return $data;
    }

    private function storeLogo(Request $request, Company $company): void
    {
        if (! $request->hasFile('logo')) {
            return;
        }

        $this->deleteLogo($company);

        $path = $request->file('logo')->store('logos/'.$company->id, 'public');
        $company->update(['logo' => $path]);
    }

    private function deleteLogo(Company $company): void
    {
        if ($company->logo && Storage::disk('public')->exists($company->logo)) {
            Storage::disk('public')->delete($company->logo);
        }
    }

    private function presets(): array
    {
        return ThemePalette::presets();
    }
}
