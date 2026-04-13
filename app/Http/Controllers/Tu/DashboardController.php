<?php

namespace App\Http\Controllers\Tu;

use App\Http\Controllers\Controller;
use App\Models\PenyerahanFaktur;
use App\Models\TuFaktur;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $stats = [
            'total_faktur_dibuat' => TuFaktur::query()
                ->where('created_by', $user->id)
                ->count(),
            'total_faktur_berjalan' => TuFaktur::query()
                ->where('created_by', $user->id)
                ->whereNotIn('status', ['selesai', 'diarsipkan'])
                ->count(),
            'total_faktur_selesai' => TuFaktur::query()
                ->where('created_by', $user->id)
                ->whereIn('status', ['selesai', 'diarsipkan'])
                ->count(),
            'total_diterima' => PenyerahanFaktur::query()
                ->where('verified_by', $user->id)
                ->where('status', 'diverifikasi')
                ->count(),
            'total_ditolak' => PenyerahanFaktur::query()
                ->where('verified_by', $user->id)
                ->where('status', 'ditolak')
                ->count(),
        ];

        return view('roles.tu', compact('stats'));
    }
}
