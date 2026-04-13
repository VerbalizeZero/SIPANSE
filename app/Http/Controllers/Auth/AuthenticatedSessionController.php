<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('welcome');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        $defaultRoute = match ($user?->role) {
            'bendahara' => 'bendahara.dashboard',
            'tu' => 'tu.dashboard',
            'orang_tua', 'ortu' => 'ortu.dashboard',
            default => 'dashboard', // default route jika role tidak dikenali, bisa diarahkan ke halaman umum atau dashboard
        };

        // return redirect()->intended(route($defaultRoute, absolute: false)); // intended akan redirect ke halaman sebelumnya jika ada, atau ke defaultRoute jika tidak ada
        return redirect()->route($defaultRoute); 
    }

    /**
     * Handle an incoming parent (ortu) authentication request by NISN.
     */
    public function storeOrtu(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'nisn' => ['required', 'regex:/^\d+$/', 'max:20'],
        ]);

        $user = \App\Models\User::where('nisn', $credentials['nisn'])
            ->where('role', 'orang_tua')
            ->first();

        if (!$user) {
            return redirect()->route('login')
                ->withErrors(['nisn' => 'NISN tidak ditemukan sebagai akun Orang Tua.'])
                ->onlyInput('nisn');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('ortu.dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
