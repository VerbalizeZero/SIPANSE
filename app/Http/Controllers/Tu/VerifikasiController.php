<?php

namespace App\Http\Controllers\Tu;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\TuFaktur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class VerifikasiController extends Controller
{
    /**
     * List verifikasi berbasis faktur TU.
     * Iterasi-06 versi awal: tampilkan daftar faktur sebagai sublist verifikasi.
     */
    public function index(): View
    {
        $query = TuFaktur::query()->with(['masterFaktur', 'creator']);

        // 1. Filter: Bulan Pembuatan (Format Y-m)
        if (request()->filled('bulan')) {
            try {
                $date = \Carbon\Carbon::parse(request()->bulan);
                $query->whereYear('created_at', $date->year)
                      ->whereMonth('created_at', $date->month);
            } catch (\Exception $e) {
                // Ignore invalid date strings
            }
        }

        // 2. Filter: Status Sublist Verifikasi
        $status = request('status', 'aktif'); // Default 'aktif' jika tidak ada url
        if ($status !== 'semua') {
            if ($status === 'aktif') {
                $query->where('status', '!=', 'diarsipkan');
            } elseif ($status === 'berlangsung') {
                $query->whereNotIn('status', ['selesai', 'diarsipkan']);
            } else {
                $query->where('status', $status);
            }
        }

        // 3. Search: Mencari dari Nama Faktur Master
        // [SECURITY: ANTI-SQL INJECTION]
        // Penggunaan where() dan function string di Eloquent ORM secara otomatis menggunakan 
        // PDO Parameter Binding yang mencegah SQL Injection secara total.
        if (request()->filled('search')) {
            $search = request()->search;
            $query->whereHas('masterFaktur', function ($q) use ($search) {
                $q->where('nama_faktur', 'like', '%' . $search . '%');
            });
        }

        $fakturs = $query->latest()->get();

        return view('tu.verifikasi.index', [
            'fakturs' => $fakturs,
            'groupedFakturs' => $this->groupFaktursForTimeline($fakturs),
        ]);
    }

    /**
     * Detail verifikasi untuk 1 faktur.
     */
    public function show(TuFaktur $faktur): View
    {
        $faktur->load(['masterFaktur', 'creator']);
        $query = $this->resolveTargetSiswas($faktur);

        // Alias tabel siswa agar aman jika ada bentrok column, gabungkan dengan data penyerahan
        $query->select('siswas.*', 'penyerahan_fakturs.status as penyerahan_status', 'penyerahan_fakturs.id as penyerahan_id', 'penyerahan_fakturs.berkas_file', 'penyerahan_fakturs.catatan_penolakan')
            ->leftJoin('penyerahan_fakturs', function ($join) use ($faktur) {
                $join->on('siswas.id', '=', 'penyerahan_fakturs.siswa_id')
                     ->where('penyerahan_fakturs.tu_faktur_id', '=', $faktur->id);
            });

        // Fitur Search untuk nama dan NISN
        if (request()->filled('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('siswas.nama_siswa', 'like', '%' . $search . '%')
                  ->orWhere('siswas.nisn', 'like', '%' . $search . '%');
            });
        }

        // Fitur Saring Status
        if (request()->filled('status') && request()->status !== 'semua') {
            if (request()->status === 'belum_ada_tindakan') {
                $query->whereNull('penyerahan_fakturs.id');
            } else {
                $query->where('penyerahan_fakturs.status', request()->status);
            }
        }

        $paginator = $query->paginate(20)->withQueryString();

        $paginator->getCollection()->transform(function ($siswa) {
            $verifLabel = 'Belum Ada Tindakan';
            $verifBadge = 'bg-slate-100 text-slate-700 border-slate-300';
            $verifNote = 'Belum ada submit verifikasi dari Ortu atau input tindakan TU.';

            $cardClass = 'border-slate-200 bg-slate-50/60';

            if ($siswa->penyerahan_id) {
                if ($siswa->penyerahan_status === 'menunggu_verifikasi') {
                    $verifLabel = 'Menunggu Verifikasi';
                    $verifBadge = 'bg-yellow-100 text-yellow-800 border-yellow-400';
                    $verifNote = 'Menunggu tindak lanjut TU atas berkas yang diunggah.';
                    $cardClass = 'border-amber-300 bg-amber-50/60';
                } elseif ($siswa->penyerahan_status === 'diverifikasi') {
                    $verifLabel = 'Sudah Diverifikasi';
                    $verifBadge = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                    $verifNote = 'Telah diverifikasi TU.';
                    $cardClass = 'border-emerald-200 bg-emerald-50/30';
                } elseif ($siswa->penyerahan_status === 'ditolak') {
                    $verifLabel = 'Ditolak';
                    $verifBadge = 'bg-rose-100 text-rose-700 border-rose-200';
                    $verifNote = $siswa->catatan_penolakan ?? 'Tindakan ditolak.';
                    $cardClass = 'border-rose-200 bg-rose-50/40';
                }
            }

            return [
                'model' => $siswa,
                'berkas_file' => $siswa->berkas_file,
                'penyerahan_id' => $siswa->penyerahan_id,
                'verification_status' => [
                    'label' => $verifLabel,
                    'badge' => $verifBadge,
                    'note' => $verifNote,
                ],
                'proof_status' => [
                    'label' => $siswa->berkas_file ? 'Ada Berkas' : 'Bukti Belum Diunggah',
                    'badge' => $siswa->berkas_file ? 'bg-blue-100 text-blue-800 border-blue-400' : 'bg-amber-50 text-slate-800 border-amber-400',
                ],
                'source_status' => [
                    'label' => $siswa->penyerahan_id ? 'Telah Ditindak' : 'Belum Ditindaklanjuti',
                    'badge' => $siswa->penyerahan_id ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-blue-50 text-blue-700 border-blue-200',
                ],
                'card_class' => $cardClass,
            ];
        });

        return view('tu.verifikasi.show', [
            'faktur' => $faktur,
            'targetDisplay' => $this->targetDisplay($faktur),
            'targetSiswas' => $paginator,
            'statusMeta' => $this->statusMeta($faktur),
            'lastExport' => $faktur->last_exported_at,
            'exportBy' => $faktur->lastExportedBy?->name ?? 'Sistem',
        ]);
    }

    /**
     * AJAX Endpoint:
     * Menyimpan keputusan verifikasi TU (Tolak/Terima) per siswa langsung ke Database.
     */
    public function updateStatusSiswa(Request $request, TuFaktur $faktur, Siswa $siswa)
    {
        // [SECURITY: XSS & PAYLOAD SANITIZATION]
        // Mencegah input aneh (Malicious Input) dari Hacker menggunakan $request->validate()
        $validated = $request->validate([
            'status' => ['required', 'in:diverifikasi,ditolak'],
            'catatan_penolakan' => ['nullable', 'string', 'max:1000'],
            'berkas_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:4096'],
        ]);

        $penyerahan = \App\Models\PenyerahanFaktur::firstOrNew([
            'tu_faktur_id' => $faktur->id,
            'siswa_id' => $siswa->id,
        ]);

        $penyerahan->status = $validated['status'];
        $penyerahan->verified_by = auth()->id();
        $penyerahan->verified_at = now();
        if ($validated['status'] === 'ditolak') {
            $penyerahan->catatan_penolakan = $validated['catatan_penolakan'];
        } else {
            $penyerahan->catatan_penolakan = null;
        }

        if ($request->hasFile('berkas_file')) {
            if ($penyerahan->berkas_file) {
                 \Illuminate\Support\Facades\Storage::disk('public')->delete($penyerahan->berkas_file);
            }
            $path = $request->file('berkas_file')->store('bukti_faktur', 'public');
            $penyerahan->berkas_file = $path;
        }

        $penyerahan->save();

        // Auto-completion check
        $totalSiswas = $this->resolveTargetSiswas($faktur)->count();
        $verifiedSiswas = \App\Models\PenyerahanFaktur::where('tu_faktur_id', $faktur->id)
            ->where('status', 'diverifikasi')
            ->count();

        // Debug log untuk melihat nilai-nilai status
        \Log::info('Auto-completion check: totalSiswas=' . $totalSiswas . ', verifiedSiswas=' . $verifiedSiswas);
        \Log::info('Faktur status saat ini: ' . $faktur->status);
        
        // Cek semua penyerahan untuk faktur ini
        $allPenyerahan = \App\Models\PenyerahanFaktur::where('tu_faktur_id', $faktur->id)->get();
        \Log::info('Total penyerahan untuk faktur ' . $faktur->id . ': ' . $allPenyerahan->count());
        
        foreach ($allPenyerahan as $penyerahan) {
            \Log::info('Penyerahan ID: ' . $penyerahan->id . ', status: ' . $penyerahan->status . ', siswa_id: ' . $penyerahan->siswa_id);
        }

        $currentStatus = strtolower((string) $faktur->status);

        if ($totalSiswas > 0 && $verifiedSiswas >= $totalSiswas && in_array($currentStatus, ['berlangsung', 'pending'], true)) {
            \Log::info('Semua siswa diverifikasi, akan mengubah status ke selesai');
            $faktur->update([
                'status' => 'selesai',
                'last_exported_at' => null,
                'last_exported_by' => null,
            ]);
        } elseif ($currentStatus === 'selesai' && $verifiedSiswas < $totalSiswas) {
            \Log::info('Status selesai tapi ada siswa belum diverifikasi, akan mengubah status ke berlangsung');
            $faktur->update(['status' => 'berlangsung']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status siswa berhasil direkam.',
            'sublist_status' => $faktur->status,
            'is_exported' => strtolower((string) $faktur->status) === 'diarsipkan'
                && !empty($faktur->last_exported_at),
        ]);
    }

    /**
     * Endpoint kompatibilitas untuk route lama reject per sublist.
     * Penolakan utama tetap dilakukan per siswa melalui updateStatusSiswa().
     */
    public function reject(Request $request, TuFaktur $faktur): RedirectResponse
    {
        $validated = $request->validate([
            'catatan_penolakan' => ['required', 'string', 'max:1000'],
        ]);

        return redirect()
            ->route('tu.verifikasi.show', $faktur)
            ->with('success', 'Catatan penolakan dicatat: ' . $validated['catatan_penolakan']);
    }

    /**
     * Export sublist faktur menjadi berkas CSV (SEMENTARA).
     * Mengubah status menjadi 'selesai' ketika semua siswa sudah Diterima.
     */
    public function exportSementara(TuFaktur $faktur)
    {
        $currentStatus = strtolower((string) $faktur->status);

        // Debug log untuk melihat status saat ini
        \Log::info('Export Sementara dipanggil untuk faktur ' . $faktur->id . ' dengan status: ' . $currentStatus);
        
        // Debug log untuk melihat semua data penyerahan_fakturs yang ada
        $allPenyerahan = \App\Models\PenyerahanFaktur::all();
        \Log::info('Total record di tabel penyerahan_fakturs: ' . $allPenyerahan->count());
        
        foreach ($allPenyerahan as $penyerahan) {
            \Log::info('Penyerahan ID: ' . $penyerahan->id . ', tu_faktur_id: ' . $penyerahan->tu_faktur_id . ', siswa_id: ' . $penyerahan->siswa_id . ', status: ' . $penyerahan->status);
        }
        
        // Debug log khusus untuk faktur ini
        $penyerahanForThisFaktur = \App\Models\PenyerahanFaktur::where('tu_faktur_id', $faktur->id)->get();
        \Log::info('Total penyerahan untuk faktur ' . $faktur->id . ': ' . $penyerahanForThisFaktur->count());
        
        // Export Sementara seharusnya TIDAK mengubah status faktur
        // Fungsinya hanya untuk export data saja, bukan untuk mengubah status
        // Status akan berubah hanya melalui tombol "Laporan Final"

        $siswas = $this->resolveTargetSiswas($faktur)->get();
        $fileName = 'Laporan_Verifikasi_Sementara_' . $faktur->masterFaktur?->nama_faktur . '_' . now()->format('Ymd') . '.csv';

        // Set Headers agar browser mengenali unduhan CSV
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        // Membangun file berurutan
        $callback = function() use($siswas, $faktur) {
            $file = fopen('php://output', 'w');
            
            // Header Kolom
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

            // Isi Data
            foreach ($siswas as $siswa) {
                // Tarik data riil dari PenyerahanFaktur (jika TU / Ortu sudah ada tindakan)
                $penyerahan = \App\Models\PenyerahanFaktur::where('tu_faktur_id', $faktur->id)
                    ->where('siswa_id', $siswa->id)->first();
                
                $statusFile = $penyerahan && $penyerahan->berkas_file ? 'Ada Berkas' : 'Belum Ada Berkas';
                $keputusan = $penyerahan ? ucfirst($penyerahan->status) : 'Belum Diverifikasi';
                $tu = auth()->user()?->name ?? '-';
                $catatan = $penyerahan ? $penyerahan->catatan_penolakan : '-';
                
                fputcsv($file, [
                    $siswa->nisn,
                    $siswa->nama_siswa,
                    $siswa->kelas ?? '-',
                    $siswa->nama_ortu ?? '-',
                    $statusFile,
                    $keputusan,
                    $tu,
                    $catatan
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export sublist faktur menjadi berkas CSV (FINAL).
     * Mengubah status menjadi 'diarsipkan' saat export final.
     */
    public function exportFinal(TuFaktur $faktur)
    {
        $currentStatus = strtolower((string) $faktur->status);

        if (!in_array($currentStatus, ['selesai', 'diarsipkan'], true)) {
            return back()->with('error', 'Laporan Final hanya bisa diproses setelah semua siswa selesai diverifikasi.');
        }

        // Jika status 'selesai', ubah status ke 'diarsipkan' dan catat audit export final.
        if ($currentStatus === 'selesai' || empty($faktur->last_exported_at)) {
            $faktur->forceFill([
                'status' => 'diarsipkan',
                'last_exported_at' => now(),
                'last_exported_by' => auth()->id(),
            ])->save();
        }

        $siswas = $this->resolveTargetSiswas($faktur)->get();
        $fileName = 'Laporan_Verifikasi_Final_' . $faktur->masterFaktur?->nama_faktur . '_' . now()->format('Ymd') . '.csv';

        // Set Headers agar browser mengenali unduhan CSV
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        // Membangun file berurutan
        $callback = function() use($siswas, $faktur) {
            $file = fopen('php://output', 'w');
            
            // Header Kolom
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

            // Isi Data
            foreach ($siswas as $siswa) {
                // Tarik data riil dari PenyerahanFaktur (jika TU / Ortu sudah ada tindakan)
                $penyerahan = \App\Models\PenyerahanFaktur::where('tu_faktur_id', $faktur->id)
                    ->where('siswa_id', $siswa->id)->first();
                
                $statusFile = $penyerahan && $penyerahan->berkas_file ? 'Ada Berkas' : 'Belum Ada Berkas';
                $keputusan = $penyerahan ? ucfirst($penyerahan->status) : 'Belum Diverifikasi';
                $tu = auth()->user()?->name ?? '-';
                $catatan = $penyerahan ? $penyerahan->catatan_penolakan : '-';
                
                fputcsv($file, [
                    $siswa->nisn,
                    $siswa->nama_siswa,
                    $siswa->kelas ?? '-',
                    $siswa->nama_ortu ?? '-',
                    $statusFile,
                    $keputusan,
                    $tu,
                    $catatan
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Susun faktur menjadi timeline bulan -> tanggal -> sublist faktur.
     */
    private function groupFaktursForTimeline(Collection $fakturs): Collection
    {
        return $fakturs
            ->groupBy(fn (TuFaktur $faktur) => optional($faktur->created_at)?->timezone('Asia/Jakarta')->format('Y-m'))
            ->map(function (Collection $monthItems, string $monthKey) {
                $monthDate = $monthItems->first()?->created_at?->timezone('Asia/Jakarta');

                return [
                    'month_key' => $monthKey,
                    'month_label' => $monthDate?->format('F Y') ?? 'Tanpa Bulan',
                    'dates' => $monthItems
                        ->groupBy(fn (TuFaktur $faktur) => optional($faktur->created_at)?->timezone('Asia/Jakarta')->toDateString())
                        ->map(function (Collection $dateItems, string $dateKey) {
                            $dateValue = $dateItems->first()?->created_at?->timezone('Asia/Jakarta');

                            return [
                                'date_key' => $dateKey,
                                'date_label' => $dateValue?->format('d F Y') ?? 'Tanpa Tanggal',
                                'items' => $dateItems->map(fn (TuFaktur $faktur) => [
                                    'model' => $faktur,
                                    'status_meta' => $this->statusMeta($faktur),
                                    'target_summary' => $this->targetSummary($faktur),
                                    'creator_name' => $faktur->creator?->name ?? 'Belum tercatat',
                                ])->values(),
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();
    }

    /**
     * Ambil daftar siswa target untuk detail faktur.
     */
    private function resolveTargetSiswas(TuFaktur $faktur): \Illuminate\Database\Eloquent\Builder
    {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
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

                $builder->orWhere('nama_siswa', $targetValue);
            });
        }

        return $query;
    }

    /**
     * Badge status visual untuk list verifikasi.
     */
    private function statusMeta(TuFaktur $faktur): array
    {
        $status = strtolower((string) $faktur->status);

        if ($status === 'selesai') {
            if ($faktur->last_exported_at) {
                return [
                    'label' => 'Aman',
                    'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                    'hint' => 'Laporan Final sudah di klik, sistem akan mulai laju menghitung 7 hari penuh.',
                ];
            }

            return [
                'label' => 'Selesai, Menunggu Export',
                'badge' => 'bg-amber-100 text-amber-800 border-amber-200',
                'hint' => 'Verifikasi dianggap rampung, tetapi laporan belum diamankan.',
            ];
        }

        if ($status === 'diarsipkan') {
            return [
                'label' => 'Aman',
                'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'hint' => 'Faktur dan laporan selesai, siap untuk diarsip.',
            ];
        }

        if ($status === 'ditolak') {
            return [
                'label' => 'Ada Penolakan',
                'badge' => 'bg-rose-100 text-rose-700 border-rose-200',
                'hint' => 'Sublist ini memiliki pengajuan yang ditolak atau perlu ditinjau ulang.',
            ];
        }

        return [
            'label' => 'Verifikasi Berlangsung',
            'badge' => 'bg-slate-100 text-slate-700 border-slate-200',
            'dot' => 'bg-slate-400',
            'hint' => 'Masih ada pengajuan yang menunggu diproses oleh Tata Usaha.',
        ];
    }

    /**
     * Ringkasan target untuk ditampilkan pada kartu faktur.
     */
    private function targetSummary(TuFaktur $faktur): string
    {
        return match ($faktur->target_type) {
            'angkatan' => 'Target angkatan '.$faktur->target_value,
            'kelas' => 'Target kelas '.$faktur->target_value,
            'siswa' => 'Target siswa '.$faktur->target_value,
            default => 'Target seluruh siswa',
        };
    }

    /**
     * Label target ringkas untuk heading halaman detail.
     */
    private function targetDisplay(TuFaktur $faktur): string
    {
        return match ($faktur->target_type) {
            'angkatan' => 'Angkatan - '.($faktur->target_value ?? '-'),
            'kelas' => 'Kelas - '.($faktur->target_value ?? '-'),
            'siswa' => 'Siswa - '.($faktur->target_value ?? '-'),
            'semua_siswa', 'semua' => 'Semua Siswa',
            default => 'Semua Siswa',
        };
    }

    // Ambil NISN dari nilai target siswa yang bisa berbentuk "Nama - 1234567890".
    private function extractNisn(string $targetValue): ?string
    {
        if (preg_match('/(\d{6,})$/', $targetValue, $matches) === 1) {
            return $matches[1];
        }

        return ctype_digit($targetValue) ? $targetValue : null;
    }
}
