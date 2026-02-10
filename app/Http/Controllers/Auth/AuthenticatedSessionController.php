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
        return view('auth.login');
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
            'bendahara' => 'dashboard',
            'tu' => 'tu.dashboard',
            'ortu' => 'ortu.dashboard',
            default => 'dashboard',
        };

        return redirect()->intended(route($defaultRoute, absolute: false));
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
            ->where('role', 'ortu')
            ->first();

        if (!$user) {
            return back()
                ->withErrors(['nisn' => __('auth.failed')])
                ->onlyInput('nisn');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('ortu.dashboard', absolute: false));
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
