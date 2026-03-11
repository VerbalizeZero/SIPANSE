<?php

namespace App\Http\Controllers\Tu;

use App\Http\Controllers\Controller;
use App\Models\DataKelas;
use App\Models\Siswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DataKelasController extends Controller
{
    /**
     * Menampilkan halaman Data Kelas.
     * Sumber utama data adalah tabel siswa (group by angkatan+kelas),
     * lalu diperkaya metadata dari tabel data_kelas jika sudah ada.
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'keyword' => ['nullable', 'string', 'max:100'],
            'angkatan' => ['nullable', 'string', 'max:20'],
            'level' => ['nullable', 'in:10,11,12,Graduated,-'],
        ]);

        $keyword = trim((string) ($validated['keyword'] ?? ''));
        $angkatan = trim((string) ($validated['angkatan'] ?? ''));
        $level = trim((string) ($validated['level'] ?? ''));

        $kelasSummary = Siswa::query()
            ->selectRaw('tahun_angkatan, kelas, COUNT(*) as total_siswa') // Hitung total siswa per kombinasi angkatan+kelas.
            ->whereNotNull('kelas') // Abaikan siswa tanpa kelas.
            ->where('kelas', '!=', '') // Abaikan siswa dengan kelas kosong.
            ->groupBy('tahun_angkatan', 'kelas') // Kelompokkan berdasarkan angkatan dan kelas.
            ->orderByDesc('tahun_angkatan') // Urutkan angkatan terbaru dulu.
            ->orderBy('kelas') // Urutkan kelas secara alfabet setelah mengurutkan angkatan.
            ->get(); // Eksekusi query dan dapatkan koleksi hasil.

        // Aman untuk environment yang belum migrate data_kelas.
        $kelasMetadata = Schema::hasTable('data_kelas')
            ? DataKelas::query()
                ->get()
                ->keyBy(fn (DataKelas $item) => $this->kelasKey($item->tahun_angkatan, $item->kelas))
            : collect();

        // Normalisasi data untuk langsung dipakai di tabel UI.
        $kelasRows = $kelasSummary->map(function ($row) use ($kelasMetadata) {
            $tahunRaw = $row->tahun_angkatan === null || $row->tahun_angkatan === ''
                ? '__NULL__'
                : (string) $row->tahun_angkatan;

            $key = $this->kelasKey($tahunRaw, (string) $row->kelas);
            $meta = $kelasMetadata->get($key);

            return [
                // id metadata dipakai untuk aksi edit/delete di UI.
                'data_kelas_id' => $meta?->id,
                'tahun_angkatan_raw' => $tahunRaw,
                'tahun_angkatan_display' => $tahunRaw === '__NULL__' ? '-' : $tahunRaw,
                'kelas' => (string) $row->kelas,
                // Fallback level diambil dari nama kelas saat metadata belum tersedia.
                'level' => $meta?->level ?? $this->extractLevel((string) $row->kelas),
                'wali_kelas' => $meta?->wali_kelas,
                'total_siswa' => (int) $row->total_siswa,
            ];
        });

        // Filter berdasarkan keyword, angkatan, dan level jika diberikan.
        $kelasRows = $kelasRows
        ->filter(function ($row) use ($keyword, $angkatan, $level) {
            $kelasMatch = stripos((string) ($row['kelas'] ?? ''), $keyword) !== false;
            $waliKelasMatch = stripos((string) ($row['wali_kelas'] ?? ''), $keyword) !== false;
            if ($keyword !== '' && ! $kelasMatch && ! $waliKelasMatch) {
                return false;
            }
            if ($angkatan !== '' && $row['tahun_angkatan_raw'] !== $angkatan) {
                return false;
            }
            if ($level !== '' && $row['level'] !== $level) {
                return false;
            }
            return true;
        })
        ->values(); // Reset index setelah filter.

        // PAGINATION manual karena data sudah dalam bentuk koleksi, bukan query builder.
        //$kelasRows = $kelasRows


        // Dropdown Options
        $angkatanOptions = collect($kelasSummary)
        ->map(fn ($row) => $row->tahun_angkatan === null || $row->tahun_angkatan === '' ? '__NULL__' : (string) $row->tahun_angkatan)
        ->unique()
        ->values();

        $levelOptions = collect(['10', '11', '12', 'Graduated', '-']);
            
        return view('tu.kelas.index', [
            'kelasRows' => $kelasRows,
            'kelasOptions' => $kelasRows,
            'angkatanOptions' => $angkatanOptions,
            'levelOptions' => $levelOptions,
            'filters' => [
                'keyword' => $keyword,
                'angkatan' => $angkatan,
                'level' => $level,
            ],
        ]);
    }

    /**
     * Simpan metadata kelas (level + wali kelas) untuk kombinasi angkatan/kelas.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kelas_ref' => ['required', 'string'],
            'level' => ['required', 'in:10,11,12,Graduated'],
            'wali_kelas' => ['nullable', 'string', 'max:255'],
        ]);

        [$tahunRaw, $kelas] = $this->parseKelasRef($validated['kelas_ref']);
        $tahunAngkatan = $tahunRaw === '__NULL__' ? null : $tahunRaw;

        // Cegah metadata kelas dibuat untuk kelas yang tidak ada di data siswa.
        $exists = Siswa::query()
            ->where('kelas', $kelas)
            ->where(function ($query) use ($tahunAngkatan) {
                if ($tahunAngkatan === null) {
                    $query->whereNull('tahun_angkatan')->orWhere('tahun_angkatan', '');

                    return;
                }

                $query->where('tahun_angkatan', $tahunAngkatan);
            })
            ->exists();

        if (! $exists) {
            return back()->withErrors(['kelas_ref' => 'Kelas yang dipilih tidak ditemukan pada data siswa.']);
        }

        DataKelas::updateOrCreate(
            ['tahun_angkatan' => $tahunRaw, 'kelas' => $kelas],
            [
                'level' => $validated['level'],
                'wali_kelas' => $validated['wali_kelas'] ?? null,
            ]
        );

        return redirect()->route('tu.kelas.index');
    }

    /**
     * Update metadata kelas yang sudah ada.
     */
    public function update(Request $request, DataKelas $dataKela): RedirectResponse
    {
        $validated = $request->validate([
            'level' => ['required', 'in:10,11,12,Graduated'],
            'wali_kelas' => ['nullable', 'string', 'max:255'],
        ]);

        $dataKela->update($validated);

        return redirect()->route('tu.kelas.index');
    }

    /**
     * Hapus metadata kelas (bukan menghapus siswa).
     */
    public function destroy(DataKelas $dataKela): RedirectResponse
    {
        $dataKela->delete();

        return redirect()->route('tu.kelas.index');
    }

    /**
     * Preview dampak promote: hitung total siswa terdampak tanpa menyimpan perubahan.
     */
    public function previewPromote(Request $request): JsonResponse
    {
        $validated = $this->validatePromoteMappings($request);
        $details = $this->buildPromotePreviewDetails($validated['mappings']);

        return response()->json([
            'data' => [
                'total_affected' => array_sum(array_column($details, 'affected')),
                'details' => $details,
            ],
        ]);
    }

    /**
     * Eksekusi promote massal dalam transaction agar perubahan konsisten.
     */
    public function executePromote(Request $request): RedirectResponse
    {
        $validated = $this->validatePromoteMappings($request);

        DB::transaction(function () use ($validated): void {
            foreach ($validated['mappings'] as $mapping) {
                $tahunRaw = $mapping['tahun_angkatan_raw'];
                $tahunAngkatan = $tahunRaw === '__NULL__' ? null : $tahunRaw;
                $kelas = $mapping['kelas'];

                $exists = Siswa::query()
                    ->where('kelas', $kelas)
                    ->where(function ($query) use ($tahunAngkatan) {
                        if ($tahunAngkatan === null) {
                            $query->whereNull('tahun_angkatan')->orWhere('tahun_angkatan', '');

                            return;
                        }

                        $query->where('tahun_angkatan', $tahunAngkatan);
                    })
                    ->exists();

                if (! $exists) {
                    continue;
                }

                DataKelas::updateOrCreate(
                    [
                        'tahun_angkatan' => $tahunRaw,
                        'kelas' => $kelas,
                    ],
                    [
                        'level' => $mapping['level'],
                    ]
                );
            }
        });

        return redirect()->route('tu.kelas.index');
    }

    /**
     * Fallback penentuan level dari awalan nama kelas (X/XI/XII).
     */
    private function extractLevel(string $kelas): string
    {
        $normalized = strtoupper(trim($kelas));

        if (str_starts_with($normalized, 'XII')) {
            return '12';
        }

        if (str_starts_with($normalized, 'XI')) {
            return '11';
        }

        if (str_starts_with($normalized, 'X')) {
            return '10';
        }

        return '-';
    }

    /**
     * Kunci gabungan angkatan+kelas untuk indexing koleksi metadata.
     */
    private function kelasKey(string $tahunAngkatanRaw, string $kelas): string
    {
        return $tahunAngkatanRaw.':::'.$kelas;
    }

    /**
     * Parse string referensi kelas dari hidden input: "{tahun}:::{kelas}".
     */
    private function parseKelasRef(string $kelasRef): array
    {
        $parts = explode(':::', $kelasRef, 2);

        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            abort(422, 'Format kelas tidak valid.');
        }

        return [$parts[0], $parts[1]];
    }

    /**
     * Validasi payload mapping promote dari UI.
     */
    private function validatePromoteMappings(Request $request): array
    {
        return $request->validate([
            'mappings' => ['required', 'array', 'min:1'],
            'mappings.*.tahun_angkatan_raw' => ['required', 'string', 'max:20'],
            'mappings.*.kelas' => ['required', 'string', 'max:50'],
            'mappings.*.level' => ['required', 'in:10,11,12,Graduated'],
        ]);
    }

    /**
     * Bangun detail preview per mapping: angkatan, kelas, target level, jumlah terdampak.
     */
    private function buildPromotePreviewDetails(array $mappings): array
    {
        $details = [];

        foreach ($mappings as $mapping) {
            $tahunRaw = $mapping['tahun_angkatan_raw'];
            $tahunAngkatan = $tahunRaw === '__NULL__' ? null : $tahunRaw;
            $affected = Siswa::query()
                ->where('kelas', $mapping['kelas'])
                ->where(function ($query) use ($tahunAngkatan) {
                    if ($tahunAngkatan === null) {
                        $query->whereNull('tahun_angkatan')->orWhere('tahun_angkatan', '');

                        return;
                    }

                    $query->where('tahun_angkatan', $tahunAngkatan);
                })
                ->count();

            $details[] = [
                'tahun_angkatan_raw' => $tahunRaw,
                'tahun_angkatan_display' => $tahunRaw === '__NULL__' ? '-' : $tahunRaw,
                'kelas' => $mapping['kelas'],
                'level' => $mapping['level'],
                'affected' => $affected,
            ];
        }

        return $details;
    }
}
