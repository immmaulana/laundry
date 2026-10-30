<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Models\Plan;
use App\Support\PlanIcons;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'icons' => PlanIcons::options(),
        ]);
    }

    public function store(StorePlanRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['logo', 'remove_logo']);

        $plan = Plan::create([
            ...$data,
            'user_id' => auth()->id(),
            'company_id' => auth()->user()->company_id,
        ]);

        $this->storeLogo($request, $plan);

        return redirect()->route('plan.index')->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function edit(Plan $plan): Response
    {
        return Inertia::render('Plans/Form', [
            'plan' => [
                ...$plan->only(['id', 'code', 'plan', 'price', 'description', 'icon']),
                'logo_url' => $plan->logo_url,
            ],
            'icons' => PlanIcons::options(),
        ]);
    }

    public function update(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        $data = $request->safe()->except(['logo', 'remove_logo']);
        $plan->update($data);
        $this->storeLogo($request, $plan);

        if ($request->boolean('remove_logo') && ! $request->hasFile('logo')) {
            $this->deleteLogo($plan);
            $plan->update(['logo' => null]);
        }

        return redirect()->route('plan.index')->with('success', 'Layanan berhasil diperbarui.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        $this->deleteLogo($plan);
        $plan->delete();

        return back()->with('success', 'Layanan dihapus.');
    }

    private function storeLogo(Request $request, Plan $plan): void
    {
        if (! $request->hasFile('logo')) {
            return;
        }

        $this->deleteLogo($plan);
        $path = $request->file('logo')->store('plan-logos/'.$plan->company_id, 'public');
        $plan->update(['logo' => $path]);
    }

    private function deleteLogo(Plan $plan): void
    {
        if ($plan->logo && Storage::disk('public')->exists($plan->logo)) {
            Storage::disk('public')->delete($plan->logo);
        }
    }
}
