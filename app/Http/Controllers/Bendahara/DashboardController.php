<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\MasterFaktur;
use App\Models\TuFaktur;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_master_faktur' => MasterFaktur::query()->count(),
            'total_faktur_berjalan' => TuFaktur::query()
                ->whereNotIn('status', ['selesai', 'diarsipkan'])
                ->count(),
            'total_faktur_selesai' => TuFaktur::query()
                ->whereIn('status', ['selesai', 'diarsipkan'])
                ->count(),
        ];

        return view('roles.bendahara', compact('stats'));
    }
}

