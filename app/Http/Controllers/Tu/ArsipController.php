<?php

namespace App\Http\Controllers\Tu;

use App\Http\Controllers\Controller;
use App\Models\MasterFaktur;
use App\Models\Siswa;
use App\Models\TuFaktur;
use App\Models\PenyerahanFaktur;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Database\Eloquent\Collection;

class ArsipController extends Controller
{
    /**
     * Menampilkan daftar Faktur TU yang telah berstatus 'diarsipkan'.
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'bulan' => ['nullable', 'string', 'max:7'],
            'search' => ['nullable', 'string', 'max:100'],
            'kelas' => ['nullable', 'string', 'max:50'],
        ]);

        $bulan = (string) ($validated['bulan'] ?? '');
        $search = trim((string) ($validated['search'] ?? ''));
        $kelas = trim((string) ($validated['kelas'] ?? ''));

        // Ambil hanya Faktur TU yang sudah masuk tahap "diarsipkan"
        $fakturs = TuFaktur::query()
            ->with(['masterFaktur', 'creator'])
            ->where('status', 'diarsipkan')
            ->when($bulan !== '', function ($query) use ($bulan) {
                [$year, $month] = explode('-', $bulan);
                $query->whereYear('created_at', (int) $year)
                    ->whereMonth('created_at', (int) $month);
            })
            ->when($kelas !== '', function ($query) use ($kelas) {
                $query->where('target_type', 'kelas')
                    ->where('target_value', $kelas);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('masterFaktur', function ($masterFakturQuery) use ($search) {
                    $masterFakturQuery->where('nama_faktur', 'like', "%{$search}%");
                });
            })
            ->latest('updated_at') // Urutkan berdasarkan waktu mereka diarsipkan
            ->paginate(15)
            ->withQueryString();

        return view('tu.arsip.index', [
            'filters' => [
                'bulan' => $bulan,
                'search' => $search,
                'kelas' => $kelas,
            ],
            'fakturs' => $fakturs,
            'kelasOptions' => Siswa::query()
                ->whereNotNull('kelas')
                ->where('kelas', '!=', '')
                ->distinct()
                ->orderBy('kelas')
                ->pluck('kelas'),
        ]);
    }

    /**
     * Export Sublist (Export parsial untuk 1 row faktur).
     * Format dan logika mirip VerifikasiController@export
     */
    public function exportSublist(TuFaktur $faktur)
    {
        // Pastikan faktur benar-benar berstatus riwayat arsip
        if (strtolower($faktur->status) !== 'diarsipkan') {
            abort(403, 'Faktur belum masuk status arsip.');
        }

        $siswas = $this->resolveTargetSiswasQuery($faktur)->get();
        $fileName = 'Arsip_Sublist_' . $faktur->masterFaktur?->nama_faktur . '_' . now()->format('Ymd') . '.csv';

        return $this->generateCsvStream($siswas, clone $faktur, $fileName);
    }

    /**
     * Export Global (Export massal untuk semua faktur yang sedang di filter di layar / Bulan terpilih).
     */
    public function exportGlobal(Request $request)
    {
        $bulan = (string) $request->query('bulan', '');
        $kelas = (string) $request->query('kelas', '');
        $search = (string) $request->query('search', '');

        $fakturQuery = TuFaktur::query()
            ->with(['masterFaktur'])
            ->where('status', 'diarsipkan')
            ->when($bulan !== '', function ($query) use ($bulan) {
                [$year, $month] = explode('-', $bulan);
                $query->whereYear('created_at', (int) $year)
                    ->whereMonth('created_at', (int) $month);
            })
            ->when($kelas !== '', function ($query) use ($kelas) {
                $query->where('target_type', 'kelas')
                    ->where('target_value', $kelas);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('masterFaktur', function ($masterFakturQuery) use ($search) {
                    $masterFakturQuery->where('nama_faktur', 'like', "%{$search}%");
                });
            });

        // Ambil semua ID faktur yang lolos filter
        $fakturs = $fakturQuery->get();

        if ($fakturs->isEmpty()) {
            return back()->with('error', 'Tidak ada data arsip yang tersedia untuk kriteria filter ini.');
        }

        $bulanLabel = $bulan !== '' ? $bulan : 'SemuaBulan';
        $fileName = 'Arsip_Global_' . $bulanLabel . '_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($fakturs) {
            $file = fopen('php://output', 'w');

            // Header Kolom ditambahkan relasi Nama Faktur karena datanya global (gabungan banyak faktur)
            fputcsv($file, [
                'Nama Faktur',
                'Tipe Target',
                'Nama Target',
                'NISN',
                'Nama Siswa',
                'Kelas',
                'Ortunya',
                'Status File Bukti',
                'Keputusan TU',
                'Nama TU Bertugas',
                'Catatan / Alasan'
            ]);

            // Chunk processing untuk mencegah out-of-memory pada ribuan baris array
            foreach ($fakturs as $faktur) {
                $namaMaster = $faktur->masterFaktur?->nama_faktur ?? '-';
                $tipeTarget = $faktur->target_type;
                $namaTarget = $faktur->target_value ?: 'Semua Siswa';

                // Menyatukan logic extractor agar lebih cepat diproses
                $this->resolveTargetSiswasQuery($faktur)->chunk(100, function ($siswasChunk) use ($file, $faktur, $namaMaster, $tipeTarget, $namaTarget) {
                    foreach ($siswasChunk as $siswa) {
                        $penyerahan = PenyerahanFaktur::where('tu_faktur_id', $faktur->id)
                            ->where('siswa_id', $siswa->id)->first();

                        $statusFile = $penyerahan && $penyerahan->berkas_file ? 'Ada Berkas' : 'Belum Ada Berkas';
                        $keputusan = $penyerahan ? ucfirst($penyerahan->status) : 'Belum Diverifikasi';
                        $tuName = $penyerahan?->verifiedBy?->name ?? '-';
                        $catatan = $penyerahan ? $penyerahan->catatan_penolakan : '-';

                        fputcsv($file, [
                            $namaMaster,
                            $tipeTarget,
                            $namaTarget,
                            $siswa->nisn,
                            $siswa->nama_siswa,
                            $siswa->kelas ?? '-',
                            $siswa->nama_ortu ?? '-',
                            $statusFile,
                            $keputusan,
                            $tuName,
                            $catatan
                        ]);
                    }
                });
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Memanggil list target murid. (Kode ini mendaur ulang logika VerifikasiController)
     */
    private function resolveTargetSiswasQuery(TuFaktur $faktur): \Illuminate\Database\Eloquent\Builder
    {
        $query = Siswa::query()
            ->when($faktur->target_type === 'angkatan', fn ($builder) => $builder->where('tahun_angkatan', $faktur->target_value))
            ->when($faktur->target_type === 'kelas', function ($builder) use ($faktur) {
                $targetVal = (string) $faktur->target_value;
                if (str_contains($targetVal, '|')) {
                    [$thn, $kls] = explode('|', $targetVal);
                    $builder->where('tahun_angkatan', $thn)->where('kelas', $kls);
                } else {
                    $builder->where('kelas', $targetVal);
                }
            })
            ->orderBy('nama_siswa')
            ->orderBy('nisn');

        if ($faktur->target_type === 'siswa') {
            $targetValue = trim((string) $faktur->target_value);
            $nisn = $this->extractNisn($targetValue);

            $query->where(function ($builder) use ($targetValue, $nisn) {
                if ($nisn !== null) {
                    $builder->orWhere('nisn', $nisn);
                }
                $builder->orWhere('nama_siswa', 'like', '%' . $targetValue . '%');
            });
        }

        return $query;
    }

    private function extractNisn(string $targetValue): ?string
    {
        if (preg_match('/-\s*(\d+)$/', $targetValue, $matches)) {
            return $matches[1];
        }
        if (preg_match('/^\d+$/', $targetValue)) {
            return $targetValue;
        }
        return null;
    }

    private function generateCsvStream($siswas, TuFaktur $faktur, string $fileName)
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($siswas, $faktur) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'NISN',
                'Nama Siswa',
                'Kelas',
                'Ortunya',
                'Status File Bukti',
                'Keputusan TU',
                'Nama TU Bertugas',
                'Catatan / Alasan'
            ]);

            foreach ($siswas as $siswa) {
                $penyerahan = PenyerahanFaktur::where('tu_faktur_id', $faktur->id)
                    ->where('siswa_id', $siswa->id)->first();

                $statusFile = $penyerahan && $penyerahan->berkas_file ? 'Ada Berkas' : 'Belum Ada Berkas';
                $keputusan = $penyerahan ? ucfirst($penyerahan->status) : 'Belum Diverifikasi';
                $tuName = $penyerahan?->verifiedBy?->name ?? '-';
                $catatan = $penyerahan ? $penyerahan->catatan_penolakan : '-';

                fputcsv($file, [
                    $siswa->nisn,
                    $siswa->nama_siswa,
                    $siswa->kelas ?? '-',
                    $siswa->nama_ortu ?? '-',
                    $statusFile,
                    $keputusan,
                    $tuName,
                    $catatan
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
