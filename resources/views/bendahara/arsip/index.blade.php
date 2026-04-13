<x-app-layout>
    {{-- Halaman Arsip untuk role Bendahara dengan pola visual yang sama seperti Arsip TU. --}}
    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-4">
                <h1 class="text-2xl font-semibold text-slate-900">Arsip</h1>
                <p class="text-sm text-slate-500">Kumpulan riwayat sublist faktur yang telah selesai.</p>
            </div>

            {{-- Baris filter + tombol Global Export --}}
            <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <form method="GET" action="{{ route('bendahara.arsip.index') }}" class="flex flex-1 flex-col gap-2 md:flex-row md:items-center">
                    <input
                        type="month"
                        name="bulan"
                        value="{{ $filters['bulan'] ?? '' }}"
                        placeholder="Filter Tahun dan Bulan"
                        class="block w-full rounded-md border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600 md:w-52"
                    />
                    <select
                        name="kelas"
                        class="block w-full rounded-md border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600 md:w-52"
                        onchange="this.form.submit()"
                    >
                        <option value="">Filter kelas</option>
                        @foreach (($kelasOptions ?? []) as $kelas)
                            <option value="{{ $kelas }}" @selected(($filters['kelas'] ?? '') === $kelas)>{{ $kelas }}</option>
                        @endforeach
                    </select>
                    <input
                        type="text"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Cari nama faktur..."
                        class="block w-full rounded-md border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600 md:w-72"
                    />
                    <button type="submit" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        Filter
                    </button>
                </form>

                <a
                    href="{{ route('bendahara.arsip.export_global', ['bulan' => $filters['bulan'] ?? '', 'kelas' => $filters['kelas'] ?? '', 'search' => $filters['search'] ?? '']) }}"
                    class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export Seluruh Arsip (CSV)
                </a>
            </div>

            @if(session('error'))
                <div class="mb-4 rounded-md bg-rose-50 p-4 text-sm text-rose-600 shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Tabel daftar faktur hasil filter. --}}
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-4 py-3">Nama Faktur</th>
                                <th class="px-4 py-3">Tipe Target</th>
                                <th class="px-4 py-3">Nama Target</th>
                                <th class="px-4 py-3">Nominal</th>
                                <th class="px-4 py-3">Diarsipkan Pada</th>
                                <th class="px-4 py-3 text-right">Laporan Kas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white text-sm text-slate-700">
                            @forelse ($fakturs as $faktur)
                                @php
                                    $targetLabel = match ($faktur->target_type) {
                                        'angkatan' => 'Per Angkatan',
                                        'kelas' => 'Per Kelas',
                                        'siswa' => 'Per Siswa',
                                        'semua_siswa' => 'Semua Siswa',
                                        default => ucfirst((string) $faktur->target_type),
                                    };

                                    $targetValueText = $faktur->target_value ?: 'Semua Siswa';
                                    if ($faktur->target_type === 'kelas' && str_contains($targetValueText, '|')) {
                                        [$thn, $kls] = explode('|', $targetValueText);
                                        $targetValueText = "Angkatan {$thn} - Kelas {$kls}";
                                    }
                                @endphp
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="h-2 w-2 rounded-full bg-emerald-500"></div>
                                            <span class="font-medium text-slate-900">{{ $faktur->masterFaktur?->nama_faktur ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                            {{ $targetLabel }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">{{ $targetValueText }}</td>
                                    <td class="px-4 py-3">Rp {{ number_format((int) ($faktur->masterFaktur?->nominal ?? 0), 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ optional($faktur->updated_at)->timezone('Asia/Jakarta')->format('d M Y, H:i') ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <form method="POST" action="{{ route('bendahara.arsip.export_sublist', $faktur) }}" class="inline-block">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1 rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                                Export (CSV)
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                        </svg>
                                        <p class="mt-4 text-sm text-slate-500 font-medium">Buku masih kosong.</p>
                                        <p class="mt-1 text-xs text-slate-400">Belum ada faktur yang selesai diverifikasi.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    @if (method_exists($fakturs, 'links'))
                        <div class="mt-4 border-t border-slate-200 px-4 py-3">
                            {{ $fakturs->onEachSide(1)->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

