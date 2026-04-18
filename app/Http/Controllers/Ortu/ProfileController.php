<?php

namespace App\Http\Controllers\Ortu;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Mews\Purifier\Facades\Purifier;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $siswa = $user->siswa;

        return view('ortu.profile.edit', [
            'user' => $user,
            'siswa' => $siswa,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return back()->with('error', 'Data siswa tidak ditemukan.');
        }

        $validated = $request->validate([
            'alamat' => ['nullable', 'string', 'max:500'],
            'no_hp_ortu' => ['nullable', 'numeric', 'min:1'],
        ]);

        $siswa->update([
            'alamat' => isset($validated['alamat']) ? Purifier::clean($validated['alamat'], 'plain_text') : $siswa->alamat,
            'no_hp_ortu' => $validated['no_hp_ortu'] ?? $siswa->no_hp_ortu,
        ]);

        return back()->with('success', 'Profil berhasil diperbarui.');
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

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}