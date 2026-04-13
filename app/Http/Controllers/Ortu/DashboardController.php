<?php

namespace App\Http\Controllers\Ortu;

use App\Http\Controllers\Controller;
use App\Models\PenyerahanFaktur;
use App\Models\Siswa;
use App\Models\TuFaktur;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $siswa = Siswa::query()->where('nisn', $user->nisn)->firstOrFail();
        $todayJakarta = Carbon::now('Asia/Jakarta')->toDateString();

        $fakturIds = TuFaktur::query()
            ->whereDate('tersedia_pada', '<=', $todayJakarta)
            ->where(function ($statusQuery) use ($siswa) {
                $statusQuery->whereRaw('LOWER(status) != ?', ['selesai'])
                    ->orWhereExists(function ($subQuery) use ($siswa) {
                        $subQuery->select(DB::raw(1))
                            ->from('penyerahan_fakturs')
                            ->whereColumn('penyerahan_fakturs.tu_faktur_id', 'tu_fakturs.id')
                            ->where('penyerahan_fakturs.siswa_id', $siswa->id);
                    });
            })
            ->where(function ($query) use ($siswa) {
                $query->whereIn('target_type', ['semua', 'semua_siswa'])
                    ->orWhere(function ($q) use ($siswa) {
                        $q->where('target_type', 'angkatan')
                            ->where('target_value', $siswa->tahun_angkatan);
                    })
                    ->orWhere(function ($q) use ($siswa) {
                        $q->where('target_type', 'kelas')
                            ->where(function ($kelasQuery) use ($siswa) {
                                $kelasQuery->where('target_value', $siswa->kelas)
                                    ->orWhere('target_value', $siswa->tahun_angkatan.'|'.$siswa->kelas);
                            });
                    })
                    ->orWhere(function ($q) use ($siswa) {
                        $q->where('target_type', 'siswa')
                            ->where(function ($targetQuery) use ($siswa) {
                                $targetQuery->where('target_value', 'like', '%'.$siswa->nisn.'%')
                                    ->orWhere('target_value', 'like', '%'.$siswa->nama_siswa.'%');
                            });
                    });
            })
            ->pluck('id');

        $riwayatByStatus = PenyerahanFaktur::query()
            ->where('siswa_id', $siswa->id)
            ->whereIn('tu_faktur_id', $fakturIds)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalFaktur = $fakturIds->count();
        $totalDiterima = (int) ($riwayatByStatus['diverifikasi'] ?? 0);
        $totalDitolak = (int) ($riwayatByStatus['ditolak'] ?? 0);

        $stats = [
            'total_faktur' => $totalFaktur,
            'total_berjalan' => max(0, $totalFaktur - $totalDiterima - $totalDitolak),
            'total_diterima' => $totalDiterima,
            'total_ditolak' => $totalDitolak,
        ];

        return view('ortu.dashboard', compact('siswa', 'stats'));
    }
}
