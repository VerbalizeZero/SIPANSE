<?php

namespace App\Http\Controllers\Ortu;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $siswa = Siswa::query()->where('nisn', $user->nisn)->firstOrFail();

        return view('ortu.dashboard', compact('siswa'));
    }
}

