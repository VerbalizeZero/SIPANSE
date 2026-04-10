<?php

namespace App\Http\Controllers\Ortu;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman khusus login Orang Tua
     */
    public function showLoginForm()
    {
        return view('ortu.auth.login');
    }

    /**
     * Proses login tanpa password (Hanya menggunakan NISN)
     */
    public function login(Request $request)
    {
        // Validasi input NISN
        $request->validate([
            'nisn' => ['required', 'string'],
        ]);

        // Cek secara spesifik role orang_tua dan ketersediaan NISN tersebut
        $user = User::where('role', 'orang_tua')->where('nisn', $request->nisn)->first();

        // Jika ditemukan, login secara paksa menggunakan instance User tersebut
        if ($user) {
            Auth::login($user);
            $request->session()->regenerate();

            // Arahkan ke Dasbor Faktur
            return redirect()->intended('/ortu/faktur');
        }

        // Jika tidak, tolak
        return back()->withErrors([
            'nisn' => 'NISN tersebut tidak ditemukan sebagai akun Orang Tua terdaftar.',
        ])->onlyInput('nisn');
    }

    /**
     * Logout untuk Orang Tua (Tentu berbagi fungsi standar, tapi dipisah agar rapi
     * jika ada logout redirect khusus nantinya)
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/ortu/login');
    }
}
