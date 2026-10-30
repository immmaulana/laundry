<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'company_name' => 'required|string|max:150',
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($request) {
            $baseSlug = Str::slug($request->company_name) ?: 'laundry';
            $slug = $baseSlug;
            $i = 1;
            while (Company::where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$i++;
            }

            $code = strtoupper(Str::substr(preg_replace('/[^A-Za-z0-9]/', '', $request->company_name) ?: 'LY', 0, 6));
            $baseCode = $code;
            $n = 1;
            while (Company::where('code', $code)->exists()) {
                $code = Str::substr($baseCode, 0, 4).$n++;
            }

            $company = Company::create([
                'name' => $request->company_name,
                'slug' => $slug,
                'code' => $code,
                'email' => $request->email,
                'is_active' => true,
            ]);

            return User::create([
                'company_id' => $company->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'role' => 'admin',
                'is_active' => true,
                'language' => 'id',
            ]);
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
