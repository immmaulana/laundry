<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Plans/Index', [
            'filters' => [
                'search' => $request->search,
                'num_page' => $request->num_page ?? 10,
            ],
            'plans' => Plan::render($request->search, $request->num_page),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Plans/Form', [
            'plan' => null,
        ]);
    }

    public function store(StorePlanRequest $request): RedirectResponse
    {
        Plan::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
            'company_id' => auth()->user()->company_id,
        ]);

        return redirect()->route('plan.index')->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function edit(Plan $plan): Response
    {
        return Inertia::render('Plans/Form', [
            'plan' => $plan,
        ]);
    }

    public function update(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        $plan->update($request->validated());

        return redirect()->route('plan.index')->with('success', 'Layanan berhasil diperbarui.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        $plan->delete();

        return back()->with('success', 'Layanan dihapus.');
    }
}
