<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function settingEdit(): Response
    {
        $user = auth()->user();

        return Inertia::render('Settings/Index', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'language' => $user->language ?? 'id',
                'role' => $user->role,
            ],
            'languages' => [
                'id' => 'Bahasa Indonesia',
                'en' => 'English',
            ],
        ]);
    }

    public function settingUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'language' => ['required', 'string', Rule::in(['en', 'id'])],
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore(auth()->id()),
            ],
        ]);

        auth()->user()->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
