<x-app-layout>
    <div class="py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            {{-- Hidden input ini menjadi jembatan data Blade -> JavaScript. --}}
            <input type="hidden" id="verifikasi-faktur-id" value="{{ $faktur->id }}">
            <input type="hidden" id="verifikasi-actor-name" value="{{ auth()->user()?->name ?? 'TU' }}">
            <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">{{ $faktur->masterFaktur?->nama_faktur ?? '-' }}</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ ucfirst($faktur->target_type) }} - {{ $faktur->target_value ?? 'Semua Siswa' }}
                    </p>
                </div>
                <a href="{{ route('tu.verifikasi.index') }}"
                    class="rounded-md border border-slate-300 px-4 py-2 text-sm bg-white text-slate-700 hover:bg-slate-50 font-medium shadow-sm transition-colors">
                    Kembali ke Verifikasi
                </a>
            </div>

            <div class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-[1.25fr_0.75fr]">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex flex-wrap items-center gap-3">
                        <span id="sublist-status-badge"
                            class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $statusMeta['badge'] }}">
                            <span id="sublist-status-label">{{ $statusMeta['label'] }}</span>
                        </span>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600">
                            <span id="sublist-target-count">{{ $targetSiswas->count() }}</span> siswa target
                        </span>
                    </div>

                    <div class="grid grid-cols-1 gap-4 text-sm text-slate-600 md:grid-cols-2 xl:grid-cols-4">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-400">Dibuat Oleh</p>
                            <p class="mt-1 font-medium text-slate-800">{{ $faktur->creator?->name ?? 'Belum tercatat' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-400">Tanggal Dibuat</p>
                            <p class="mt-1 font-medium text-slate-800">
                                {{ optional($faktur->created_at)?->timezone('Asia/Jakarta')->format('d M Y H:i') ?? '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-400">Ketersediaan</p>
                            <p class="mt-1 font-medium text-slate-800">{{ $faktur->tersedia_pada }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-400">Jatuh Tempo</p>
                            <p class="mt-1 font-medium text-slate-800">{{ $faktur->jatuh_tempo }}</p>
                        </div>
                    </div>

                    <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Ringkasan Verifikasi</p>
                        <p id="sublist-status-hint" class="mt-2 text-sm leading-6 text-slate-600">
                            Halaman ini menampilkan seluruh siswa yang termasuk target faktur. Proses verifikasi
                            dilakukan per siswa melalui aksi masing-masing.
                        </p>
                    </div>
                </section>

                <aside class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">Laporan Sublist</h2>
                    <p class="mt-1 text-sm text-slate-500">Export dapat dilakukan kapan pun. Hitung mundur arsip 7 hari
                        akan aktif jika semua siswa sudah selesai diverifikasi dan laporan sudah diexport.</p>

                    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Status Arsip</p>
                        <p id="export-status-hint" class="mt-2 text-sm leading-6 text-slate-600">
                            Menunggu semua sublist selesai diverifikasi.
                        </p>
                        <div class="mt-3 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600">
                            <p class="text-xs uppercase tracking-wide text-slate-400">Audit Trail Export</p>
                            @php($lastExport = \App\Models\TuFaktur::find($faktur->id)?->updated_at)
                            <p id="export-audit-trail" class="mt-1">
                                {{ strtolower((string)$faktur->status) === 'diarsipkan' ? 'Diexport pada ' . $lastExport?->timezone('Asia/Jakarta')->format('d M Y H:i') : 'Belum pernah diexport.' }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-col gap-2">
                        <!-- Tombol Export Sementara -->
                        <form method="POST" action="{{ route('tu.verifikasi.export', $faktur) }}">
                            @csrf
                            <button type="submit" id="btn-export-sementara"
                                @if(in_array(strtolower((string)$faktur->status), ['selesai', 'diarsipkan'])) disabled class="hidden" @else class="w-full rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700" @endif>
                                Export Laporan
                            </button>
                        </form>

                        <!-- Tombol Export Arsip -->
                        <form method="POST" action="{{ route('tu.verifikasi.export', $faktur) }}">
                            @csrf
                            <button type="submit" id="btn-export-arsip"
                                @if(!in_array(strtolower((string)$faktur->status), ['selesai', 'diarsipkan'])) disabled class="hidden" @else class="w-full rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors" @endif>
                                {{ strtolower((string)$faktur->status) === 'diarsipkan' ? 'Unduh Ulang Laporan' : 'Laporan Final' }}
                            </button>
                        </form>
                    </div>
                </aside>
            </div>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Daftar Siswa Target</h2>
                        <p class="text-sm text-slate-500">Rincian siswa yang masuk ke dalam target faktur ini.</p>
                    </div>
                    <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs text-slate-500">
                        Total {{ $targetSiswas->total() }} siswa target
                    </span>
                </div>

                <!-- FILTER DAN SEARCH BAR UNTUK DAFTAR SISWA -->
                <form method="GET" action="{{ route('tu.verifikasi.show', $faktur) }}" class="mb-5 flex flex-col gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 sm:flex-row sm:items-center">
                    <div class="flex-1">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NISN siswa..." class="w-full rounded-lg border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="sm:w-56">
                        <select name="status" class="w-full rounded-lg border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="semua" {{ request('status') === 'semua' ? 'selected' : '' }}>Semua Status</option>
                            <option value="belum_ada_tindakan" {{ request('status') === 'belum_ada_tindakan' ? 'selected' : '' }}>Belum Ada Tindakan</option>
                            <option value="menunggu_verifikasi" {{ request('status') === 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                            <option value="diverifikasi" {{ request('status') === 'diverifikasi' ? 'selected' : '' }}>Disetujui</option>
                            <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="submit" class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 border border-slate-300 shadow-sm transition hover:bg-slate-100">
                            Search
                        </button>
                        @if(request()->hasAny(['search', 'status']) && (request('search') || (request('status') && request('status') !== 'semua')))
                        <a href="{{ route('tu.verifikasi.show', $faktur) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 text-center">
                            Reset
                        </a>
                        @endif
                    </div>
                </form>

                <div class="space-y-3">
                    @forelse ($targetSiswas as $item)
                    @php($siswa = $item['model'])
                    <article id="student-card-{{ $siswa->id }}" data-student-id="{{ $siswa->id }}"
                        data-verification-state="pending"
                        data-proof-state="{{ $item['berkas_file'] ? 'uploaded' : 'empty' }}"
                        data-original-verif="{{ $item['verification_status']['label'] }}"
                        class="rounded-xl border border-slate-200 bg-slate-50/60 p-4 transition-colors duration-300">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <h3 class="text-base font-semibold text-slate-900">{{ $siswa->nama_siswa }}</h3>
                                <p class="text-sm text-slate-500">NISN {{ $siswa->nisn }} | Kelas
                                    {{ $siswa->kelas ?: '-' }} | Angkatan {{ $siswa->tahun_angkatan ?: '-' }}</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span data-role="verification-badge"
                                    class="rounded-full border px-3 py-1 text-xs {{ $item['verification_status']['badge'] }}">
                                    {{ $item['verification_status']['label'] }}
                                </span>
                                <span data-role="proof-badge"
                                    class="rounded-full border px-3 py-1 text-xs {{ $item['proof_status']['badge'] }}">
                                    {{ $item['proof_status']['label'] }}
                                </span>
                            </div>
                        </div>
                        <div
                            class="mt-3 grid grid-cols-1 gap-3 text-sm text-slate-600 md:grid-cols-[1fr_1fr_1fr_240px] md:items-end">
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">Nama Ortu</p>
                                <p>{{ $siswa->nama_ortu ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">Kontak</p>
                                <p>{{ $siswa->no_hp_ortu ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">Diverifikasi Oleh</p>
                                <p data-role="verification-actor" class="text-sm text-slate-600">
                                    {{ in_array($item['penyerahan_status'] ?? '', ['diverifikasi', 'ditolak']) ? 'Tata Usaha' : 'Belum diverifikasi' }}
                                </p>
                            </div>
                            <div class="md:flex md:justify-end">
                                <button type="button" data-modal-open="kelola-verifikasi-{{ $siswa->id }}"
                                    class="inline-flex w-full items-center justify-center rounded-xl border border-blue-200 bg-white px-4 py-3 text-sm font-medium text-blue-700 shadow-sm transition hover:border-blue-300 hover:bg-blue-50 md:w-auto md:min-w-[220px]">
                                    Kelola Verifikasi
                                </button>
                            </div>
                        </div>
                    </article>

                    <div id="kelola-verifikasi-{{ $siswa->id }}"
                        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4">
                        <div class="w-full max-w-2xl rounded-2xl bg-white p-5 shadow-xl">
                            <div class="mb-4 flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-xl font-semibold text-slate-900">Kelola Verifikasi Siswa</h3>
                                    <p class="mt-1 text-sm text-slate-500">{{ $siswa->nama_siswa }} | NISN
                                        {{ $siswa->nisn }}</p>
                                </div>
                                <button type="button" data-modal-close="kelola-verifikasi-{{ $siswa->id }}"
                                    class="text-slate-500 hover:text-slate-700">&times;</button>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-slate-700">Status
                                        Pengajuan</label>
                                    <div data-role="verification-display"
                                        class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                                        {{ $item['verification_status']['label'] }}
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-slate-700">Status Bukti</label>
                                    <div data-role="proof-display"
                                        class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                                        {{ $item['proof_status']['label'] }}
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-medium text-slate-700">Bukti
                                        Verifikasi</label>

                                    @if($item['berkas_file'])
                                        <div class="mb-4 rounded-xl border border-slate-200 bg-white p-3">
                                            <p class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-400">
                                                Diunggah Oleh Orang Tua</p>
                                            <div
                                                class="flex min-h-[160px] flex-col items-center justify-center rounded-lg bg-slate-50 p-2">
                                                <a href="{{ Storage::url($item['berkas_file']) }}" target="_blank"
                                                    class="mb-3 inline-block rounded border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-600 hover:bg-blue-100">
                                                    Buka Gambar Penuh </a>
                                                <img src="{{ Storage::url($item['berkas_file']) }}"
                                                    alt="Preview bukti submission"
                                                    class="max-h-56 rounded-lg border border-slate-200 object-contain shadow-sm" />
                                            </div>
                                        </div>
                                    @else
                                        <div class="mb-2 rounded text-sm text-amber-600 italic">Belum ada bukti / submission
                                            dari pihak Orang Tua.</div>
                                    @endif

                                    <p class="mb-2 mt-4 text-xs font-medium uppercase tracking-wide text-slate-400">Atau
                                        Upload Bukti Susulan TU</p>
                                    <input type="file" accept="image/*,.pdf" data-role="proof-input"
                                        data-student-id="{{ $siswa->id }}"
                                        class="block w-full rounded-md border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600" />
                                    <div data-role="proof-preview"
                                        class="mt-3 hidden rounded-xl border border-slate-200 bg-slate-50 p-3">
                                        <p class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-400">
                                            Preview Bukti Baru</p>
                                        <div class="flex min-h-[240px] items-center justify-center">
                                            <img data-role="proof-preview-image" alt="Preview bukti"
                                                class="hidden max-h-56 rounded-lg border border-slate-200 object-contain" />
                                            <div data-role="proof-preview-file"
                                                class="hidden rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-medium text-slate-700">Catatan Verifikasi</label>
                                    @php($catatan = \App\Models\PenyerahanFaktur::where('tu_faktur_id', $faktur->id)->where('siswa_id', $siswa->id)->first()?->catatan_penolakan)
                                    <textarea data-role="note-input" data-student-id="{{ $siswa->id }}" rows="4"
                                        class="block w-full rounded-md border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600 placeholder:text-slate-400"
                                        placeholder="Catatan sangat penting. Wajib bila Ditolak, Opsional bila Disetujui.">{{ $catatan }}</textarea>
                                    <p data-role="note-error" class="mt-2 hidden text-xs font-medium text-rose-600">Catatan wajib diisi bila ingin Menolak verifikasi.</p>
                                </div>
                            </div>

                            <div class="mt-5 flex flex-wrap justify-end gap-2">
                                <button type="button" data-modal-close="kelola-verifikasi-{{ $siswa->id }}"
                                    class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                    Tutup
                                </button>
                                <button type="button" data-action-reject="{{ $siswa->id }}"
                                    class="rounded-md border border-rose-300 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50">
                                    Tolak Verifikasi
                                </button>
                                <button type="button" data-action-accept="{{ $siswa->id }}"
                                    class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                                    Terima Verifikasi
                                </button>
                            </div>
                        </div>
                    </div>


                    @empty
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
                        <h3 class="text-base font-semibold text-slate-900">Belum Ada Siswa pada Target Ini</h3>
                        <p class="mt-2 text-sm text-slate-500">Data siswa target akan muncul di sini sesuai angkatan,
                            kelas, seluruh siswa, atau siswa spesifik yang dipilih saat faktur dibuat.</p>
                    </div>
                    @endforelse
                </div>

                <!-- NAVIGATION PAGING -->
                @if($targetSiswas->hasPages())
                <div class="mt-6 border-t border-slate-200 pt-4">
                    {{ $targetSiswas->links() }}
                </div>
                @endif
            </section>
        </div>
    </div>

    <script>
        const fakturId = document.getElementById('verifikasi-faktur-id')?.value ?? '';
        const actorName = document.getElementById('verifikasi-actor-name')?.value ?? 'TU';
        const csrfToken = '{{ csrf_token() }}';

        // Fungsi ini membuka modal secara visual
        const openModal = (id) => {
            const modal = document.getElementById(id);
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        };

        // Fungsi penutup modal
        const closeModal = (id) => {
            const modal = document.getElementById(id);
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        // Pemanggilan API ke Laravel Backend untuk merekam pilihan verifikasi.
        // Konsep AJAX yang membuat UX "snappy" karena page tidak berkedip.
        const updateBackend = async (studentId, status, note) => {
            const endpoint = `/tu/verifikasi/${fakturId}/siswa/${studentId}/status`;
            const payload = {
                status: status,
                catatan_penolakan: note
            };

            const modal = document.getElementById(`kelola-verifikasi-${studentId}`);
            const buttons = modal.querySelectorAll('button');
            buttons.forEach(btn => btn.disabled = true);

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        // [SECURITY: CSRF PROTECTION]
                        // Token ini menjamin bahwa request yang dilayangkan berasal murni dari aplikasi SIPANSE, bukan
                        // dari script Phishing pihak ketiga yang mencoba meniru sesi Staf TU.
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                    },
                    body: JSON.stringify(payload)
                });
                
                const data = await response.json();
                if(data.success) {
                    return data.sublist_status; // (misal: "berlangsung", "selesai")
                }
            } catch(e) {
                console.error(e);
                alert('Gagal menyambung ke server sinkronisasi. Cek jaringan.');
            } finally {
                buttons.forEach(btn => btn.disabled = false);
            }
            return null;
        };

        // Konfigurasi status sublist untuk update header dinamis tanpa refresh.
        const statusConfig = {
            pending: {
                label: 'Verifikasi Berlangsung',
                badge: 'bg-slate-100 text-slate-700 border-slate-200',
                hint: 'Masih menunggu verifikasi untuk para siswa.',
            },
            completed: {
                label: 'Selesai, Menunggu Export',
                badge: 'bg-amber-100 text-amber-800 border-amber-200',
                hint: 'Semua siswa pada sublist ini sudah selesai diverifikasi.',
            },
            archived: {
                label: 'Aman',
                badge: 'bg-emerald-100 text-emerald-800 border-emerald-200',
                hint: 'Faktur dan laporan selesai, siap untuk diarsip.',
            }
        };

        const applyBadgeClasses = (element, classes) => {
            element.className = `rounded-full border px-3 py-1 text-xs font-semibold ${classes}`;
        };

        // Rubah widget Export Arsip berdasarkan feedback langsung dari backend
        const updateExportAudit = (serverStatus) => {
            const exportStatusHint = document.getElementById('export-status-hint');
            if (!exportStatusHint) return;

            if (serverStatus === 'selesai') {
                exportStatusHint.innerHTML = '<span class="text-amber-600 font-medium">Semua siswa selesai diverifikasi!</span> Laporan sublist sudah siap untuk diexport.';
            } else if (serverStatus === 'diarsipkan') {
                exportStatusHint.innerHTML = '<span class="text-emerald-600 font-medium">Laporan sudah diamankan.</span> Sublist ini akan segera diarsipkan sesuai jadwal batas waktu.';
            } else {
                exportStatusHint.innerHTML = 'Menunggu semua sublist selesai diverifikasi.';
            }
        };

        // Hanya manipulasi DOM (karena data sudah diamankan di Database via backend)
        const updateStudentCard = (studentId, nextState) => {
            const card = document.getElementById(`student-card-${studentId}`);
            if (!card) return;

            card.dataset.verificationState = nextState;

            const verificationBadge = card.querySelector('[data-role="verification-badge"]');
            const proofBadge = card.querySelector('[data-role="proof-badge"]');
            const verificationActor = card.querySelector('[data-role="verification-actor"]');
            const manageModal = document.getElementById(`kelola-verifikasi-${studentId}`);

            if (nextState === 'approved') {
                applyBadgeClasses(verificationBadge, 'bg-emerald-50 text-emerald-700 border-emerald-200');
                verificationBadge.textContent = 'Disetujui';
                proofBadge.classList.add('hidden');
                card.className = 'rounded-xl border border-emerald-200 bg-emerald-50/30 p-4 transition-colors duration-300';
            } else if (nextState === 'rejected') {
                applyBadgeClasses(verificationBadge, 'bg-rose-100 text-rose-700 border-rose-200');
                verificationBadge.textContent = 'Ditolak';
                proofBadge.classList.add('hidden');
                card.className = 'rounded-xl border border-rose-200 bg-rose-50/40 p-4 transition-colors duration-300';
            }

            if (verificationActor) {
                verificationActor.textContent = actorName;
            }

            if (manageModal) {
                const verificationDisplay = manageModal.querySelector('[data-role="verification-display"]');
                const proofDisplay = manageModal.querySelector('[data-role="proof-display"]');

                if (verificationDisplay) {
                    verificationDisplay.innerHTML = verificationBadge.innerHTML;
                    verificationDisplay.className = `rounded-xl border px-3 py-2 text-sm flex font-semibold items-center ${verificationBadge.className.replace('rounded-full', '').replace('px-3 py-1 text-xs', '')}`;
                }
                if (proofDisplay && !proofBadge.classList.contains('hidden')) {
                    proofDisplay.textContent = proofBadge.textContent;
                    proofDisplay.className = `rounded-xl border px-3 py-2 text-sm font-semibold ${proofBadge.className.replace('rounded-full', '').replace('px-3 py-1 text-xs', '')}`;
                } else if (proofDisplay) {
                    proofDisplay.textContent = "Bukti Tersimpan";
                }
            }
        };

        // Meregangkan atau mengecilkan UI badge sublist di atas dokumen.
        const updateSublistSummary = (serverStatus) => {
            let nextStatusKey = 'pending';
            if (serverStatus === 'selesai') nextStatusKey = 'completed';
            if (serverStatus === 'diarsipkan') nextStatusKey = 'archived';
            
            const nextStatus = statusConfig[nextStatusKey];

            const badge = document.getElementById('sublist-status-badge');
            const label = document.getElementById('sublist-status-label');
            const hint = document.getElementById('sublist-status-hint');

            badge.className = `inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold ${nextStatus.badge}`;
            label.textContent = nextStatus.label;
            hint.textContent = nextStatus.hint;
            
            const btnSementara = document.getElementById('btn-export-sementara');
            const btnArsip = document.getElementById('btn-export-arsip');
            
            if (serverStatus === 'selesai' || serverStatus === 'diarsipkan') {
                if (btnSementara) {
                    btnSementara.disabled = true;
                    btnSementara.className = "hidden";
                }
                if (btnArsip) {
                    btnArsip.disabled = false;
                    btnArsip.className = "w-full rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors";
                    btnArsip.textContent = serverStatus === 'diarsipkan' ? 'Unduh Ulang Laporan' : 'Laporan Final';
                }
            } else {
                if (btnSementara) {
                    btnSementara.disabled = false;
                    btnSementara.className = "w-full rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700";
                }
                if (btnArsip) {
                    btnArsip.disabled = true;
                    btnArsip.className = "hidden";
                }
            }

            updateExportAudit(serverStatus);
        };

        document.querySelectorAll('[data-modal-open]').forEach((button) => {
            button.addEventListener('click', () => openModal(button.getAttribute('data-modal-open')));
        });

        document.querySelectorAll('[data-modal-close]').forEach((button) => {
            button.addEventListener('click', () => closeModal(button.getAttribute('data-modal-close')));
        });

        // Trigger input file bukti susulan
        document.querySelectorAll('[data-role="proof-input"]').forEach((input) => {
            input.addEventListener('change', (event) => {
                const studentId = input.getAttribute('data-student-id');
                const file = event.target.files?.[0];
                const modal = document.getElementById(`kelola-verifikasi-${studentId}`);
                if (!modal) return;

                const previewWrapper = modal.querySelector('[data-role="proof-preview"]');
                const previewImage = modal.querySelector('[data-role="proof-preview-image"]');
                const previewFile = modal.querySelector('[data-role="proof-preview-file"]');

                if (!file) {
                    previewWrapper.classList.add('hidden');
                    return;
                }

                previewWrapper.classList.remove('hidden');

                if (file.type.startsWith('image/')) {
                    previewImage.src = URL.createObjectURL(file);
                    previewImage.classList.remove('hidden');
                    previewFile.classList.add('hidden');
                } else {
                    previewFile.textContent = `File terpilih: ${file.name}`;
                    previewFile.classList.remove('hidden');
                    previewImage.classList.add('hidden');
                }
            });
        });

        // Aksi AJAX Terima
        document.querySelectorAll('[data-action-accept]').forEach((button) => {
            button.addEventListener('click', async () => {
                const studentId = button.getAttribute('data-action-accept');
                const modal = document.getElementById(`kelola-verifikasi-${studentId}`);
                const noteInput = modal?.querySelector('[data-role="note-input"]');
                const noteError = modal?.querySelector('[data-role="note-error"]');
                
                if (noteError) noteError.classList.add('hidden');

                const newSublistStatus = await updateBackend(studentId, 'diverifikasi', noteInput?.value.trim() ?? '');
                
                if (newSublistStatus) {
                    updateStudentCard(studentId, 'approved');
                    updateSublistSummary(newSublistStatus);
                    closeModal(`kelola-verifikasi-${studentId}`);
                }
            });
        });

        // Aksi AJAX Tolak
        document.querySelectorAll('[data-action-reject]').forEach((button) => {
            button.addEventListener('click', async () => {
                const studentId = button.getAttribute('data-action-reject');
                const modal = document.getElementById(`kelola-verifikasi-${studentId}`);
                const noteInput = modal?.querySelector('[data-role="note-input"]');
                const noteError = modal?.querySelector('[data-role="note-error"]');
                const note = noteInput?.value.trim() ?? '';

                if (note === '') {
                    noteError?.classList.remove('hidden');
                    noteInput?.focus();
                    return;
                }
                noteError?.classList.add('hidden');

                const newSublistStatus = await updateBackend(studentId, 'ditolak', note);
                
                if (newSublistStatus) {
                    updateStudentCard(studentId, 'rejected');
                    updateSublistSummary(newSublistStatus);
                    closeModal(`kelola-verifikasi-${studentId}`);
                }
            });
        });

        // Memuat status inisial dari variable PHP $faktur
        updateSublistSummary('{{ strtolower($faktur->status) }}');
    </script>
</x-app-layout>