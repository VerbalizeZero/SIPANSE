<x-app-layout>
    <div class="py-4 sm:py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">Verifikasi</h1>
                    <p class="max-w-3xl text-sm leading-6 text-slate-500">
                        Menu untuk memproses verifikasi faktur yang masuk.
                    </p>
                </div>
                <div class="grid grid-cols-1 gap-2 text-xs text-slate-600 sm:grid-cols-3">
                    <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
                        <div class="mb-1 flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-slate-400"></span>
                            <span class="font-semibold text-slate-700">Berlangsung</span>
                        </div>
                        <p>Masih ada proses verifikasi yang harus ditinjau.</p>
                    </div>
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 shadow-sm">
                        <div class="mb-1 flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span>
                            <span class="font-semibold text-amber-800">Selesai</span>
                        </div>
                        <p>Fase verifikasi selesai, tetapi laporan belum diexport.</p>
                    </div>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 shadow-sm">
                        <div class="mb-1 flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                            <span class="font-semibold text-emerald-800">Aman</span>
                        </div>
                        <p>Faktur dan laporan selesai, siap untuk diarsip.</p>
                    </div>
                </div>
            </div>

            <!-- SEARCH AND FILTER BARS -->
            <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <form method="GET" action="{{ route('tu.verifikasi.index') }}" class="flex flex-1 flex-col gap-2 md:flex-row md:items-center">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari nama faktur..."
                        class="block w-full rounded-md border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600 md:w-72"
                    />
                    <select
                        name="status"
                        class="block w-full rounded-md border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600 md:w-36 lg:w-48"
                        onchange="this.form.submit()"
                    >
                        <option value="aktif" {{ request('status', 'aktif') === 'aktif' ? 'selected' : '' }}>Filter Status</option>
                        <option value="semua" {{ request('status') === 'semua' ? 'selected' : '' }}>Semua Status</option>
                        <option value="berlangsung" {{ request('status') === 'berlangsung' ? 'selected' : '' }}>Berlangsung</option>
                        <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="diarsipkan" {{ request('status') === 'diarsipkan' ? 'selected' : '' }}>Aman</option>
                    </select>
                    <input
                        type="month"
                        name="bulan"
                        value="{{ request('bulan') }}"
                        placeholder="Filter Tahun dan Bulan"
                        class="block w-full rounded-md border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600 md:w-48"
                    />
                    <button type="submit" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                        Search
                    </button>
                    @if(request()->hasAny(['search', 'bulan']) || (request()->has('status') && request('status') !== 'berlangsung'))
                    <a href="{{ route('tu.verifikasi.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 text-center">
                        Reset
                    </a>
                    @endif
                </form>
            </div>

            @if (session('rejection_note'))
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    Catatan penolakan tersimpan. Alasan terakhir: {{ session('rejection_note') }}
                </div>
            @endif

            @if (session('exported_faktur_id'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    Export laporan untuk sublist faktur berhasil dipicu. Timer auto-delete 7 hari nantinya akan mengikuti metadata export.
                </div>
            @endif

            <div class="space-y-6">
                @forelse ($groupedFakturs as $monthGroup)
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Periode Faktur</p>
                                <h2 class="text-xl font-semibold text-slate-900">{{ $monthGroup['month_label'] }}</h2>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                {{ collect($monthGroup['dates'])->sum(fn ($dateGroup) => count($dateGroup['items'])) }} sublist
                            </span>
                        </div>

                        <div class="space-y-5">
                            @foreach ($monthGroup['dates'] as $dateGroup)
                                <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                                    <div class="mb-4 flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900">{{ $dateGroup['date_label'] }}</p>
                                            <p class="text-xs text-slate-500">Sublist faktur yang dibuat pada tanggal ini.</p>
                                        </div>
                                        <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs text-slate-500">
                                            {{ count($dateGroup['items']) }} faktur
                                        </span>
                                    </div>

                                    <div class="space-y-3">
                                        @foreach ($dateGroup['items'] as $item)
                                            @php($faktur = $item['model'])
                                            @php($statusMeta = $item['status_meta'])
                                            {{-- Kartu sublist faktur. JS di bawah membaca data-faktur-id untuk sinkron status dari localStorage. --}}
                                            <a href="{{ route('tu.verifikasi.show', $faktur) }}"
                                                data-faktur-id="{{ $faktur->id }}"
                                                class="group relative block rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-slate-300 hover:shadow-md">
                                                
                                                {{-- Red Ping Unread Notification --}}
                                                @if(
                                                    strtolower((string)$faktur->status) !== 'selesai' && 
                                                    strtolower((string)$faktur->status) !== 'diarsipkan' && 
                                                    \App\Models\PenyerahanFaktur::where('tu_faktur_id', $faktur->id)
                                                        ->whereNotNull('berkas_file')
                                                        ->whereNotIn('status', ['diverifikasi', 'ditolak'])
                                                        ->count() > 0
                                                )
                                                <div data-role="sublist-unread-ping" class="absolute -right-1.5 -top-1.5 flex h-3.5 w-3.5">
                                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-rose-400 opacity-75"></span>
                                                    <span class="relative inline-flex h-3.5 w-3.5 rounded-full border-2 border-white bg-rose-500"></span>
                                                </div>
                                                @endif

                                                <div class="flex flex-col gap-4">
                                                    <div class="space-y-3">
                                                        <div class="flex flex-wrap items-center gap-3">
                                                            <span data-role="sublist-status-badge" class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $statusMeta['badge'] }}">
                                                                <span data-role="sublist-status-label">{{ $statusMeta['label'] }}</span>
                                                            </span>
                                                            <span class="text-xs text-slate-400">Dibuat {{ optional($faktur->created_at)?->timezone('Asia/Jakarta')->format('H:i') ?? '-' }}</span>
                                                        </div>

                                                        <div>
                                                            <h3 class="text-lg font-semibold text-slate-900 group-hover:text-blue-700">
                                                                {{ $faktur->masterFaktur?->nama_faktur ?? '-' }}
                                                            </h3>
                                                            <p class="text-sm text-slate-500">{{ $item['target_summary'] }}</p>
                                                        </div>

                                                        <div class="grid grid-cols-1 gap-3 text-sm text-slate-600 sm:grid-cols-3">
                                                            <div>
                                                                <p class="text-xs uppercase tracking-wide text-slate-400">Dibuat Oleh</p>
                                                                <p>{{ $item['creator_name'] }}</p>
                                                            </div>
                                                            <div>
                                                                <p class="text-xs uppercase tracking-wide text-slate-400">Ketersediaan</p>
                                                                <p>{{ \Carbon\Carbon::parse($faktur->tersedia_pada)->format('d F Y') }}</p>
                                                            </div>
                                                            <div>
                                                                <p class="text-xs uppercase tracking-wide text-slate-400">Jatuh Tempo</p>
                                                                <p>{{ \Carbon\Carbon::parse($faktur->jatuh_tempo)->format('d F Y') }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    {{-- Hint status tetap dipertahankan untuk dibaca JS, tetapi tidak ditampilkan pada layout kartu. --}}
                                                    <span data-role="sublist-status-hint" class="hidden">{{ $statusMeta['hint'] }}</span>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-900">Belum Ada Antrean Verifikasi</h2>
                        <p class="mt-2 text-sm text-slate-500">
                            Daftar ini akan terisi setelah faktur masuk ke fase verifikasi. Untuk sementara, belum ada sublist yang perlu diproses.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Halaman index kini telah terintegrasi dengan backend.
         JavaScript localStorage telah dihapus penuh sehingga status ditarik murni dari database. --}}
</x-app-layout>
