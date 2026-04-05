<?php

namespace App\Http\Controllers\Tu;

use App\Http\Controllers\Controller;
use App\Models\MasterFaktur;
use App\Models\Siswa;
use App\Models\TuFaktur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FakturController extends Controller
{
    // Tipe target faktur untuk menentukan cakupan penagihan.
    private const TARGET_OPTIONS = [
        'angkatan' => 'Angkatan',
        'kelas' => 'Kelas',
        'semua_siswa' => 'Semua Siswa',
        'siswa' => 'Siswa',
    ];
    //  status faktur pada level agregat TU.
    private const STATUS_OPTIONS = ['Pending', 'Selesai'];

    /**
     * Halaman list faktur TU + filter bulan, kelas, dan search nama faktur.
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

        // Aman saat migration belum dijalankan di environment tertentu.
        $fakturs = Schema::hasTable('tu_fakturs')
            ? TuFaktur::query()
                ->with('masterFaktur')
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
                ->latest()
                //->get()
                ->paginate(10)
                ->WithQueryString()
            : collect();

        return view('tu.faktur.index', [
            'filters' => [
                'bulan' => $bulan,
                'search' => $search,
                'kelas' => $kelas,
            ],
            'fakturs' => $fakturs,
            'masterFakturs' => MasterFaktur::query()->latest()->get(),
            // Opsi target kelas untuk filter UI.
            'kelasOptions' => Siswa::query()
                ->whereNotNull('kelas')
                ->where('kelas', '!=', '')
                ->distinct()
                ->orderBy('kelas')
                ->pluck('kelas'),
            // Opsi target angkatan untuk dynamic target field di modal.
            'angkatanOptions' => Siswa::query()
                ->whereNotNull('tahun_angkatan')
                ->where('tahun_angkatan', '!=', '')
                ->distinct()
                ->orderByDesc('tahun_angkatan')
                ->pluck('tahun_angkatan'),
            // Digunakan sebagai sumber pencarian siswa (nama/NISN) pada target_type = siswa.
            'siswaTargetOptions' => Siswa::query()
                ->select('nisn', 'nama_siswa')
                ->whereNotNull('nisn')
                ->where('nisn', '!=', '')
                ->orderBy('nama_siswa')
                ->get(),
            'targetOptions' => self::TARGET_OPTIONS,
            'statusOptions' => self::STATUS_OPTIONS,
        ]);
    }

    // Create faktur TU.
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->normalizePayload($this->validatePayload($request));
        $validated['created_by'] = Auth::id();
        TuFaktur::create($validated);

        return redirect()->route('tu.faktur.index');
    }

    // Update faktur TU.
    public function update(Request $request, TuFaktur $faktur): RedirectResponse
    {
        $validated = $this->normalizePayload($this->validatePayload($request));
        $faktur->update($validated);

        return redirect()->route('tu.faktur.index');
    }

    // Delete faktur TU.
    public function destroy(TuFaktur $faktur): RedirectResponse
    {
        $faktur->delete();

        return redirect()->route('tu.faktur.index');
    }

    // Validasi payload create/update faktur TU.
    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'master_faktur_id' => ['required', 'exists:master_fakturs,id'],
            'target_type' => ['required', 'in:'.implode(',', array_keys(self::TARGET_OPTIONS))],
            'target_value' => ['nullable', 'string', 'max:100', 'required_unless:target_type,semua_siswa'],
            'tersedia_pada' => ['required', 'date'],
            'jatuh_tempo' => ['required', 'date', 'after_or_equal:tersedia_pada'],
            'status' => ['required', 'in:'.implode(',', self::STATUS_OPTIONS)],
        ]);
    }

    // Target semua siswa dipaksa konsisten ke string "Semua Siswa".
    private function normalizePayload(array $validated): array
    {
        if (($validated['target_type'] ?? null) === 'semua_siswa') {
            $validated['target_value'] = 'Semua Siswa';
        }

        return $validated;
    }
}
