<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrtuPasswordResetController extends Controller
{
    /**
     * Display the password reset link request view for Ortu.
     */
    public function create(): View
    {
        return view('auth.ortu-forgot-password');
    }

    /**
     * Handle an incoming password reset link request for Ortu.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nisn' => ['required', 'regex:/^\d+$/', 'max:20'],
        ]);

        // Find user by NISN and role orang_tua
        $user = \App\Models\User::where('nisn', $request->nisn)
            ->where('role', 'orang_tua')
            ->first();

        if (!$user) {
            return back()->withErrors(['nisn' => 'NISN tidak ditemukan sebagai akun Orang Tua.'])->withInput();
        }

        // Generate password reset token
        $token = Str::random(60);

        // Store token in password_resets table
        DB::table('password_resets')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Send password reset link to user's email
        $user->sendPasswordResetNotification($token);

        return back()->with('status', 'Link reset password telah dikirim ke email Anda.');
    }

    /**
     * Display the password reset view for Ortu.
     */
    public function edit(Request $request, string $token): View
    {
        return view('auth.ortu-reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /**
     * Handle an incoming new password request for Ortu.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        // Find user by email and role orang_tua
        $user = \App\Models\User::where('email', $request->email)
            ->where('role', 'orang_tua')
            ->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email tidak ditemukan.'])->withInput();
        }

        // Verify token
        $resetToken = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$resetToken || !Hash::check($request->token, $resetToken->token)) {
            return back()->withErrors(['token' => 'Token reset password tidak valid atau telah kadaluarsa.']);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete used token
        DB::table('password_resets')
            ->where('email', $request->email)
            ->delete();

        return redirect()->route('login')->with('status', 'Password berhasil diubah. Silakan login dengan password baru.');
    }
}