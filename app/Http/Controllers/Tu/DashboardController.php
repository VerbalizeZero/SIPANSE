<?php

namespace App\Http\Controllers\Tu;

use App\Http\Controllers\Controller;
use App\Models\PenyerahanFaktur;
use App\Models\TuFaktur;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_faktur_dibuat' => TuFaktur::query()
                ->count(),
            'total_faktur_berjalan' => TuFaktur::query()
                ->whereNotIn('status', ['selesai', 'diarsipkan'])
                ->count(),
            'total_faktur_selesai' => TuFaktur::query()
                ->whereIn('status', ['selesai', 'diarsipkan'])
                ->count(),
            'total_diterima' => PenyerahanFaktur::query()
                ->where('status', 'diverifikasi')
                ->count(),
            'total_ditolak' => PenyerahanFaktur::query()
                ->where('status', 'ditolak')
                ->count(),
        ];

        return view('roles.tu', compact('stats'));
    }
}
