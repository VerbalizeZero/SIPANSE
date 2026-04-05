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
        $fakturs = TuFaktur::query()
            ->with(['masterFaktur', 'creator'])
            ->latest()
            ->get();

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
        $targetSiswas = $this->resolveTargetSiswas($faktur)->map(function (Siswa $siswa) {
            return [
                'model' => $siswa,
                'verification_status' => [
                    'label' => 'Belum Ada Pengajuan',
                    'badge' => 'bg-slate-100 text-slate-700 border-slate-200',
                    'note' => 'Belum ada submit verifikasi dari Ortu atau input bantuan TU.',
                ],
                'proof_status' => [
                    'label' => 'Bukti Belum Diunggah',
                    'badge' => 'bg-amber-50 text-amber-700 border-amber-200',
                ],
                'source_status' => [
                    'label' => 'Belum Ditindaklanjuti',
                    'badge' => 'bg-blue-50 text-blue-700 border-blue-200',
                ],
            ];
        });

        return view('tu.verifikasi.show', [
            'faktur' => $faktur,
            'targetSiswas' => $targetSiswas,
            'statusMeta' => $this->statusMeta($faktur),
        ]);
    }

    /**
     * Aksi tolak wajib menyertakan catatan penolakan.
     */
    public function reject(Request $request, TuFaktur $faktur): RedirectResponse
    {
        $validated = $request->validate([
            'catatan_penolakan' => ['required', 'string', 'max:1000'],
        ]);

        // Placeholder iterasi-06:
        // status faktur ditandai ditolak + menyimpan alasan pada session flash.
        $faktur->update([
            'status' => 'Ditolak',
        ]);

        return redirect('/tu/verifikasi')
            ->with('rejection_note', $validated['catatan_penolakan']);
    }

    /**
     * Export sublist faktur (placeholder),
     * nanti dipakai untuk aktivasi timer auto-delete 7 hari.
     */
    public function export(TuFaktur $faktur): RedirectResponse
    {
        return redirect('/tu/verifikasi')
            ->with('exported_faktur_id', $faktur->id);
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
    private function resolveTargetSiswas(TuFaktur $faktur): Collection
    {
        $query = Siswa::query()
            ->when($faktur->target_type === 'angkatan', fn ($builder) => $builder->where('tahun_angkatan', $faktur->target_value))
            ->when($faktur->target_type === 'kelas', fn ($builder) => $builder->where('kelas', $faktur->target_value))
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

        return $query->get();
    }

    /**
     * Badge status visual untuk list verifikasi.
     */
    private function statusMeta(TuFaktur $faktur): array
    {
        $status = strtolower((string) $faktur->status);

        if ($status === 'selesai') {
            return [
                'label' => 'Selesai, Menunggu Export',
                'badge' => 'bg-amber-100 text-amber-800 border-amber-200',
                'dot' => 'bg-amber-400',
                'hint' => 'Verifikasi dianggap rampung, tetapi laporan belum diamankan.',
            ];
        }

        if ($status === 'ditolak') {
            return [
                'label' => 'Ada Penolakan',
                'badge' => 'bg-rose-100 text-rose-700 border-rose-200',
                'dot' => 'bg-rose-400',
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

    // Ambil NISN dari nilai target siswa yang bisa berbentuk "Nama - 1234567890".
    private function extractNisn(string $targetValue): ?string
    {
        if (preg_match('/(\d{6,})$/', $targetValue, $matches) === 1) {
            return $matches[1];
        }

        return ctype_digit($targetValue) ? $targetValue : null;
    }
}
