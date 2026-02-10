<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\MasterFaktur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MasterFakturController extends Controller
{
    private const JENIS_FAKTUR = [
        'SPP',
        'Kegiatan',
        'Seragam',
        'Buku',
        'Ujian',
        'Lainnya',
    ];

    public function index(): View
    {
        $search = request('search');
        $jenis = request('jenis_faktur');

        $masterFakturs = MasterFaktur::query()
            ->when($search, function ($query, $search) {
                $query->where('nama_faktur', 'like', "%{$search}%");
            })
            ->when($jenis, function ($query, $jenis) {
                $query->where('jenis_faktur', $jenis);
            })
            ->latest()
            ->get();

        return view('bendahara.master-faktur.index', [
            'masterFakturs' => $masterFakturs,
            'jenisFakturOptions' => self::JENIS_FAKTUR,
            'filters' => [
                'search' => $search,
                'jenis_faktur' => $jenis,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'jenis_faktur' => ['required', 'in:'.implode(',', self::JENIS_FAKTUR)],
            'nama_faktur' => ['required', 'string', 'max:255'],
            'nominal' => ['required', 'integer', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        MasterFaktur::create($validated);

        return redirect()->route('bendahara.master-faktur.index');
    }

    public function update(Request $request, MasterFaktur $masterFaktur): RedirectResponse
    {
        $validated = $request->validate([
            'jenis_faktur' => ['required', 'in:'.implode(',', self::JENIS_FAKTUR)],
            'nama_faktur' => ['required', 'string', 'max:255'],
            'nominal' => ['required', 'integer', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $masterFaktur->update($validated);

        return redirect()->route('bendahara.master-faktur.index');
    }

    public function destroy(MasterFaktur $masterFaktur): RedirectResponse
    {
        $masterFaktur->delete();

        return redirect()->route('bendahara.master-faktur.index');
    }
}
