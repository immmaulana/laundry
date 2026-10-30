<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Customers/Index', [
            'filters' => [
                'search' => $request->search,
                'num_page' => $request->num_page ?? 10,
            ],
            'customers' => Customer::render($request->search, $request->num_page),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Customers/Form', [
            'customer' => null,
        ]);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        Customer::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
            'company_id' => auth()->user()->company_id,
        ]);

        return redirect()->route('customer.index')->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function edit(Customer $customer): Response
    {
        return Inertia::render('Customers/Form', [
            'customer' => $customer,
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        return redirect()->route('customer.index')->with('success', 'Pelanggan berhasil diperbarui.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return back()->with('success', 'Pelanggan dihapus.');
    }
}
