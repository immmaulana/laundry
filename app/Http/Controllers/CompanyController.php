<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        $data = $this->validated($request);

        $company = Company::create($data + ['is_active' => true]);

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
            'company' => $company->only([
                'id', 'name', 'slug', 'code', 'phone', 'email', 'address', 'city', 'is_active',
            ]),
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
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        return $data;
    }
}
