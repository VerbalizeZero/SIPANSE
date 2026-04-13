<?php

namespace App\Http\Controllers\Tu;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $currentPic = User::query()
            ->where('role', 'tu')
            ->where('is_pic', true)
            ->whereKeyNot($user->id)
            ->first();

        return view('tu.profile.edit', [
            'user' => $user,
            'currentPic' => $currentPic,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:30'],
            'is_pic' => ['nullable', 'boolean'],
        ]);

        $isPic = $request->boolean('is_pic');

        if ($isPic) {
            User::query()
                ->whereIn('role', ['tu'])
                ->whereKeyNot($user->id)
                ->update(['is_pic' => false]);
        }

        $user->fill([
            'name' => $validated['name'],
            'contact' => $validated['contact'] ?? null,
            'is_pic' => $isPic,
        ])->save();

        return redirect()->route('tu.profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('tu.profile.edit')->with('success', 'Password berhasil diperbarui.');
    }
}

