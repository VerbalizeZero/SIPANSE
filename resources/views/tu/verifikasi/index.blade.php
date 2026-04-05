<x-app-layout>
    <div class="py-8">
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
                                                class="group block rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-slate-300 hover:shadow-md">
                                                <div class="flex flex-col gap-4">
                                                    <div class="space-y-3">
                                                        <div class="flex flex-wrap items-center gap-3">
                                                            <span data-role="sublist-status-badge" class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-semibold {{ $statusMeta['badge'] }}">
                                                                <span data-role="sublist-status-dot" class="h-2.5 w-2.5 rounded-full {{ $statusMeta['dot'] }}"></span>
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
                                                                <p>{{ $faktur->tersedia_pada }}</p>
                                                            </div>
                                                            <div>
                                                                <p class="text-xs uppercase tracking-wide text-slate-400">Jatuh Tempo</p>
                                                                <p>{{ $faktur->jatuh_tempo }}</p>
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

    <script>
        // Konfigurasi ini menjadi sumber tunggal untuk tampilan status sublist di halaman index.
        // Setiap status menyimpan label, warna badge, warna titik indikator, dan hint singkat yang
        // bisa dipakai ulang saat JavaScript memperbarui kartu setelah kembali dari halaman detail.
        const indexStatusConfig = {
            pending: {
                label: 'Verifikasi Berlangsung',
                badge: 'bg-slate-100 text-slate-700 border-slate-200',
                dot: 'bg-slate-400',
                hint: 'Masih ada siswa yang belum selesai diverifikasi.',
            },
            completed: {
                label: 'Selesai, Menunggu Export',
                badge: 'bg-amber-100 text-amber-800 border-amber-200',
                dot: 'bg-amber-400',
                hint: 'Semua siswa sudah selesai diverifikasi. Export dapat dilakukan kapan pun; arsip 7 hari aktif setelah export.',
            },
        };

        // Blok ini berjalan saat halaman selesai dirender.
        // Tugasnya adalah memeriksa setiap kartu faktur, lalu menyamakan badge status di halaman index
        // dengan state terakhir yang mungkin sudah berubah saat proses verifikasi dilakukan di halaman detail.
        document.querySelectorAll('[data-faktur-id]').forEach((card) => {
            // Setiap kartu menyimpan id faktur pada atribut data-faktur-id.
            // Id ini dipakai untuk membentuk key localStorage yang spesifik untuk satu sublist faktur.
            const fakturId = card.getAttribute('data-faktur-id');
            const storageKey = `verifikasi:faktur:${fakturId}`;
            const rawState = localStorage.getItem(storageKey);
            if (!rawState) return;

            // State yang sudah tersimpan dibaca kembali agar halaman index tidak selalu kembali ke tampilan awal.
            // Dari state ini dipilih config status mana yang harus dipakai.
            const state = JSON.parse(rawState);
            const nextStatus = state.sublistStatus === 'completed'
                ? indexStatusConfig.completed
                : indexStatusConfig.pending;

            // Elemen target dicari lewat data-role agar JavaScript bisa memperbarui bagian tertentu
            // tanpa harus membangun ulang seluruh kartu HTML.
            const badge = card.querySelector('[data-role="sublist-status-badge"]');
            const dot = card.querySelector('[data-role="sublist-status-dot"]');
            const label = card.querySelector('[data-role="sublist-status-label"]');
            const hint = card.querySelector('[data-role="sublist-status-hint"]');

            if (!badge || !dot || !label || !hint) return;

            // Setelah config ditemukan, class dan teks pada badge ditimpa agar tampilannya
            // mencerminkan kondisi sublist terbaru.
            badge.className = `inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-semibold ${nextStatus.badge}`;
            dot.className = `h-2.5 w-2.5 rounded-full ${nextStatus.dot}`;
            label.textContent = nextStatus.label;
            hint.textContent = nextStatus.hint;
        });
    </script>
</x-app-layout>
