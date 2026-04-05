<x-app-layout>
    <div class="py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            {{-- Hidden input ini menjadi jembatan data Blade -> JavaScript. --}}
            <input type="hidden" id="verifikasi-faktur-id" value="{{ $faktur->id }}">
            <input type="hidden" id="verifikasi-actor-name" value="{{ auth()->user()?->name ?? 'TU' }}">
            <div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <div class="mb-2 inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-500 shadow-sm">
                        Detail Sublist Verifikasi
                    </div>
                    <h1 class="text-2xl font-semibold text-slate-900">{{ $faktur->masterFaktur?->nama_faktur ?? '-' }}</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ ucfirst($faktur->target_type) }} - {{ $faktur->target_value ?? 'Semua Siswa' }}
                    </p>
                </div>
                <a href="{{ route('tu.verifikasi.index') }}"
                    class="rounded-md border border-slate-300 px-4 py-2 text-sm bg-white text-slate-700 hover:bg-slate-50">
                    Kembali ke Verifikasi
                </a>
            </div>

            <div class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-[1.25fr_0.75fr]">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex flex-wrap items-center gap-3">
                        <span id="sublist-status-badge" class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-semibold {{ $statusMeta['badge'] }}">
                            <span id="sublist-status-dot" class="h-2.5 w-2.5 rounded-full {{ $statusMeta['dot'] }}"></span>
                            <span id="sublist-status-label">{{ $statusMeta['label'] }}</span>
                        </span>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600">
                            <span id="sublist-target-count">{{ $targetSiswas->count() }}</span> siswa target
                        </span>
                    </div>

                    <div class="grid grid-cols-1 gap-4 text-sm text-slate-600 md:grid-cols-2 xl:grid-cols-4">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-400">Dibuat Oleh</p>
                            <p class="mt-1 font-medium text-slate-800">{{ $faktur->creator?->name ?? 'Belum tercatat' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-400">Tanggal Dibuat</p>
                            <p class="mt-1 font-medium text-slate-800">{{ optional($faktur->created_at)?->timezone('Asia/Jakarta')->format('d M Y H:i') ?? '-' }}</p>
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
                            Halaman ini menampilkan seluruh siswa yang termasuk target faktur. Proses verifikasi dilakukan per siswa melalui aksi masing-masing.
                        </p>
                    </div>
                </section>

                <aside class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">Laporan Sublist</h2>
                    <p class="mt-1 text-sm text-slate-500">Export dapat dilakukan kapan pun. Hitung mundur arsip 7 hari akan aktif jika semua siswa sudah selesai diverifikasi dan laporan sudah diexport.</p>

                    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Status Arsip</p>
                        <p id="export-status-hint" class="mt-2 text-sm leading-6 text-slate-600">
                            Saat ini export berfungsi sebagai pengaman laporan.
                        </p>
                        <div class="mt-3 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600">
                            <p class="text-xs uppercase tracking-wide text-slate-400">Audit Trail Export</p>
                            <p id="export-audit-trail" class="mt-1">Belum pernah diexport.</p>
                        </div>
                    </div>

                    <form id="export-sublist-form" method="POST" action="{{ route('tu.verifikasi.export', $faktur) }}" class="mt-4">
                        @csrf
                        <button type="submit"
                            class="w-full rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                            Export Laporan Sublist
                        </button>
                    </form>
                </aside>
            </div>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Daftar Siswa Target</h2>
                        <p class="text-sm text-slate-500">Rincian siswa yang masuk ke dalam target faktur ini.</p>
                    </div>
                    <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs text-slate-500">
                        {{ $targetSiswas->count() }} siswa
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse ($targetSiswas as $item)
                        @php($siswa = $item['model'])
                        <article
                            id="student-card-{{ $siswa->id }}"
                            data-student-id="{{ $siswa->id }}"
                            data-verification-state="pending"
                            data-proof-state="empty"
                            class="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <h3 class="text-base font-semibold text-slate-900">{{ $siswa->nama_siswa }}</h3>
                                    <p class="text-sm text-slate-500">NISN {{ $siswa->nisn }} | Kelas {{ $siswa->kelas ?: '-' }} | Angkatan {{ $siswa->tahun_angkatan ?: '-' }}</p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span data-role="verification-badge" class="rounded-full border px-3 py-1 text-xs {{ $item['verification_status']['badge'] }}">
                                        {{ $item['verification_status']['label'] }}
                                    </span>
                                    <span data-role="proof-badge" class="rounded-full border px-3 py-1 text-xs {{ $item['proof_status']['badge'] }}">
                                        {{ $item['proof_status']['label'] }}
                                    </span>
                                </div>
                            </div>
                            <div class="mt-3 grid grid-cols-1 gap-3 text-sm text-slate-600 md:grid-cols-[1fr_1fr_1fr_240px] md:items-end">
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
                                    <p data-role="verification-actor" class="text-sm text-slate-600">Belum diverifikasi</p>
                                </div>
                                <div class="md:flex md:justify-end">
                                    <button type="button"
                                        data-modal-open="kelola-verifikasi-{{ $siswa->id }}"
                                        class="inline-flex w-full items-center justify-center rounded-xl border border-blue-200 bg-white px-4 py-3 text-sm font-medium text-blue-700 shadow-sm transition hover:border-blue-300 hover:bg-blue-50 md:w-auto md:min-w-[220px]">
                                        Kelola Verifikasi
                                    </button>
                                </div>
                            </div>
                        </article>

                        <div id="kelola-verifikasi-{{ $siswa->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4">
                            <div class="w-full max-w-2xl rounded-2xl bg-white p-5 shadow-xl">
                                <div class="mb-4 flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="text-xl font-semibold text-slate-900">Kelola Verifikasi Siswa</h3>
                                        <p class="mt-1 text-sm text-slate-500">{{ $siswa->nama_siswa }} | NISN {{ $siswa->nisn }}</p>
                                    </div>
                                    <button type="button" data-modal-close="kelola-verifikasi-{{ $siswa->id }}" class="text-slate-500 hover:text-slate-700">&times;</button>
                                </div>

                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-slate-700">Status Pengajuan</label>
                                        <div data-role="verification-display" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                                            {{ $item['verification_status']['label'] }}
                                        </div>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-slate-700">Status Bukti</label>
                                        <div data-role="proof-display" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                                            {{ $item['proof_status']['label'] }}
                                        </div>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="mb-1 block text-sm font-medium text-slate-700">Bukti Verifikasi</label>
                                        <input
                                            type="file"
                                            accept="image/*,.pdf"
                                            data-role="proof-input"
                                            data-student-id="{{ $siswa->id }}"
                                            class="block w-full rounded-md border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600" />
                                        <div data-role="proof-preview" class="mt-3 hidden rounded-xl border border-slate-200 bg-slate-50 p-3">
                                            <p class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-400">Preview Bukti</p>
                                            <div class="flex min-h-[240px] items-center justify-center">
                                                <img data-role="proof-preview-image" alt="Preview bukti" class="hidden max-h-56 rounded-lg border border-slate-200 object-contain" />
                                                <div data-role="proof-preview-file" class="hidden rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="mb-1 block text-sm font-medium text-slate-700">Catatan (Opsional)</label>
                                        <textarea
                                            data-role="note-input"
                                            rows="4"
                                            class="block w-full rounded-md border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600"
                                            placeholder="Tambahkan catatan bila diperlukan..."></textarea>
                                    </div>
                                </div>

                                <div class="mt-5 flex flex-wrap justify-end gap-2">
                                    <button type="button" data-modal-close="kelola-verifikasi-{{ $siswa->id }}"
                                        class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                        Tutup
                                    </button>
                                    <button
                                        type="button"
                                        data-open-reject-flow="{{ $siswa->id }}"
                                        class="rounded-md border border-rose-300 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50">
                                        Tolak Verifikasi
                                    </button>
                                    <button
                                        type="button"
                                        data-accept-verification="{{ $siswa->id }}"
                                        class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                                        Terima Verifikasi
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div id="reject-note-modal-{{ $siswa->id }}" class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-900/60 px-4">
                            <div class="w-full max-w-xl rounded-2xl bg-white p-5 shadow-xl">
                                <div class="mb-4 flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="text-xl font-semibold text-slate-900">Catatan Penolakan</h3>
                                        <p class="mt-1 text-sm text-slate-500">Alasan penolakan wajib diisi sebelum proses ditolak.</p>
                                    </div>
                                    <button type="button" data-modal-close="reject-note-modal-{{ $siswa->id }}" class="text-slate-500 hover:text-slate-700">&times;</button>
                                </div>

                                <label class="mb-1 block text-sm font-medium text-slate-700">Alasan Penolakan</label>
                                <textarea
                                    data-role="reject-note-input"
                                    data-student-id="{{ $siswa->id }}"
                                    rows="5"
                                    class="block w-full rounded-md border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600"
                                    placeholder="Alasan mengapa ditolak..."></textarea>
                                <p data-role="reject-note-error" class="mt-2 hidden text-xs text-rose-600">Catatan penolakan wajib diisi.</p>

                                <div class="mt-5 flex flex-wrap justify-end gap-2">
                                    <button type="button" data-modal-close="reject-note-modal-{{ $siswa->id }}"
                                        class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                        Batal
                                    </button>
                                    <button
                                        type="button"
                                        data-open-reject-confirm="{{ $siswa->id }}"
                                        class="rounded-md border border-rose-300 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50">
                                        Lanjutkan Penolakan
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div id="reject-confirm-modal-{{ $siswa->id }}" class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-900/60 px-4">
                            <div class="w-full max-w-lg rounded-2xl bg-white p-5 shadow-xl">
                                <div class="mb-4 flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="text-xl font-semibold text-slate-900">Konfirmasi Penolakan</h3>
                                        <p class="mt-1 text-sm text-slate-500">Pastikan alasan penolakan sudah sesuai sebelum disimpan.</p>
                                    </div>
                                    <button type="button" data-modal-close="reject-confirm-modal-{{ $siswa->id }}" class="text-slate-500 hover:text-slate-700">&times;</button>
                                </div>

                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Catatan Penolakan</p>
                                    <p data-role="reject-note-preview" class="mt-2 text-sm leading-6 text-slate-700"></p>
                                </div>

                                <div class="mt-5 flex flex-wrap justify-end gap-2">
                                    <button type="button" data-modal-close="reject-confirm-modal-{{ $siswa->id }}"
                                        class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                        Kembali
                                    </button>
                                    <button
                                        type="button"
                                        data-confirm-reject="{{ $siswa->id }}"
                                        class="rounded-md bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">
                                        Ya, Tolak Verifikasi
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
                            <h3 class="text-base font-semibold text-slate-900">Belum Ada Siswa pada Target Ini</h3>
                            <p class="mt-2 text-sm text-slate-500">Data siswa target akan muncul di sini sesuai angkatan, kelas, seluruh siswa, atau siswa spesifik yang dipilih saat faktur dibuat.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    <script>
        // Nilai ini dibaca sekali di awal agar seluruh fungsi JavaScript mengetahui konteks halaman:
        // faktur mana yang sedang dibuka dan nama aktor yang melakukan aksi verifikasi pada halaman ini.
        const fakturId = document.getElementById('verifikasi-faktur-id')?.value ?? '';
        const actorName = document.getElementById('verifikasi-actor-name')?.value ?? 'TU';
        const storageKey = `verifikasi:faktur:${fakturId}`;

        // Fungsi ini membuka modal berdasarkan id elemen modal yang dikirim oleh tombol aksi.
        // Saat modal dibuka, class `hidden` dilepas dan diganti menjadi `flex` agar overlay tampil.
        const openModal = (id) => {
            const modal = document.getElementById(id);
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        };

        // Fungsi ini menutup modal dan mengembalikan class tampilannya ke kondisi tersembunyi.
        // Pola ini dipakai ulang oleh modal kelola verifikasi, modal catatan penolakan, dan modal konfirmasi.
        const closeModal = (id) => {
            const modal = document.getElementById(id);
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        // Fungsi ini membaca state sementara dari localStorage untuk satu sublist faktur.
        // Jika storage kosong atau format JSON rusak, fungsi mengembalikan objek kosong agar script tetap aman.
        const readState = () => {
            try {
                return JSON.parse(localStorage.getItem(storageKey) ?? '{}');
            } catch (error) {
                return {};
            }
        };

        // Fungsi ini menyimpan ulang state ke localStorage setiap kali ada perubahan:
        // misalnya status verifikasi, status bukti, audit trail aktor, atau metadata export.
        const writeState = (state) => {
            localStorage.setItem(storageKey, JSON.stringify(state));
        };

        // Konfigurasi ini menjadi peta visual status sublist di halaman detail.
        // Saat seluruh siswa selesai diproses, fungsi ringkasan sublist akan mengambil konfigurasi `completed`.
        const statusConfig = {
            pending: {
                label: 'Verifikasi Berlangsung',
                badge: 'bg-slate-100 text-slate-700 border-slate-200',
                dot: 'bg-slate-400',
                hint: 'Masih menunggu verifikasi untuk para siswa.',
            },
            completed: {
                label: 'Selesai, Menunggu Export',
                badge: 'bg-amber-100 text-amber-800 border-amber-200',
                dot: 'bg-amber-400',
                hint: 'Semua siswa pada sublist ini sudah selesai diverifikasi, hitung mundur arsip akan aktif setelah diexport.',
            },
        };

        // Fungsi kecil ini dipakai untuk mengganti warna badge tanpa menyalin ulang class dasar di banyak tempat.
        // Class dasar tetap dipertahankan, lalu hanya bagian warna/status yang ditimpa.
        const applyBadgeClasses = (element, classes) => {
            element.className = `rounded-full border px-3 py-1 text-xs ${classes}`;
        };

        // Fungsi ini memperbarui panel export di sisi kanan.
        // Jika sudah ada jejak export pada state, panel audit trail dan hint arsip akan disesuaikan.
        const updateExportAudit = () => {
            const state = readState();
            const exportAuditTrail = document.getElementById('export-audit-trail');
            const exportStatusHint = document.getElementById('export-status-hint');

            if (state.exportedBy && exportAuditTrail) {
                exportAuditTrail.textContent = `${state.exportedBy} pada ${state.exportedAt}`;
            }

            if (exportStatusHint && state.sublistStatus === 'completed' && state.exportedBy) {
                exportStatusHint.textContent = 'Laporan sudah diamankan dan bisa masuk ke fase arsip berikutnya.';
            }
        };

        // Fungsi ini memperbarui tampilan satu kartu siswa berdasarkan state verifikasi dan state bukti.
        // Perubahan yang diatur di sini mencakup badge status, badge bukti, audit trail verifikator,
        // dan tampilan ringkas yang muncul di dalam modal kelola verifikasi.
        const updateStudentCard = (studentId, nextState) => {
            const card = document.getElementById(`student-card-${studentId}`);
            if (!card) return;

            card.dataset.verificationState = nextState;

            // State siswa dibaca dari storage agar perubahan sebelumnya tetap dipakai sebagai sumber data.
            // Dari sini elemen-elemen kartu diambil untuk diperbarui satu per satu.
            const state = readState();
            const studentState = (state.students ?? {})[studentId] ?? {};
            const verificationBadge = card.querySelector('[data-role="verification-badge"]');
            const proofBadge = card.querySelector('[data-role="proof-badge"]');
            const verificationActor = card.querySelector('[data-role="verification-actor"]');
            const manageModal = document.getElementById(`kelola-verifikasi-${studentId}`);

            // Bagian ini menentukan tampilan badge verifikasi.
            // Nilai `approved`, `rejected`, atau `pending` diterjemahkan menjadi label dan warna yang berbeda.
            if (nextState === 'approved') {
                applyBadgeClasses(verificationBadge, 'bg-emerald-50 text-emerald-700 border-emerald-200');
                verificationBadge.textContent = 'Disetujui';
            } else if (nextState === 'rejected') {
                applyBadgeClasses(verificationBadge, 'bg-rose-100 text-rose-700 border-rose-200');
                verificationBadge.textContent = 'Ditolak';
            } else {
                applyBadgeClasses(verificationBadge, 'bg-slate-100 text-slate-700 border-slate-200');
                verificationBadge.textContent = 'Belum Ada Pengajuan';
            }

            // Bagian ini memisahkan status bukti dari status keputusan verifikasi.
            // Dengan cara ini, kartu masih bisa menunjukkan apakah file bukti sudah ada walau keputusan belum dibuat.
            if (card.dataset.proofState === 'uploaded') {
                applyBadgeClasses(proofBadge, 'bg-emerald-50 text-emerald-700 border-emerald-200');
                proofBadge.textContent = 'Bukti Tersimpan';
            } else {
                applyBadgeClasses(proofBadge, 'bg-amber-50 text-amber-700 border-amber-200');
                proofBadge.textContent = 'Bukti Belum Diunggah';
            }

            // Audit trail verifikasi diisi dari state siswa.
            // Jika belum pernah diproses, kolom ini tetap menampilkan informasi default.
            if (verificationActor) {
                verificationActor.textContent = studentState.verifiedBy
                    ? `${studentState.verifiedBy} (${studentState.decisionLabel ?? 'Diproses'})`
                    : 'Belum diverifikasi';
            }

            // Jika modal masih terbuka saat status berubah, tampilan di modal ikut diperbarui agar konsisten
            // dengan badge yang sudah berubah di kartu utama.
            if (manageModal) {
                const verificationDisplay = manageModal.querySelector('[data-role="verification-display"]');
                const proofDisplay = manageModal.querySelector('[data-role="proof-display"]');
                if (verificationDisplay) verificationDisplay.textContent = verificationBadge.textContent;
                if (proofDisplay) proofDisplay.textContent = proofBadge.textContent;
            }
        };

        // Fungsi ini menghitung status sublist berdasarkan seluruh kartu siswa yang tampil di halaman.
        // Jika semua siswa sudah approved atau rejected, sublist dianggap selesai dan badge header diperbarui.
        const updateSublistSummary = () => {
            const cards = Array.from(document.querySelectorAll('[id^="student-card-"]'));
            const allCompleted = cards.length > 0 && cards.every((card) => ['approved', 'rejected'].includes(card.dataset.verificationState));
            const nextStatusKey = allCompleted ? 'completed' : 'pending';
            const nextStatus = statusConfig[nextStatusKey];

            // Hasil perhitungan disimpan ke storage agar halaman index bisa membaca status yang sama
            // ketika kembali ke daftar sublist.
            const state = readState();
            state.sublistStatus = nextStatusKey;
            writeState(state);

            const badge = document.getElementById('sublist-status-badge');
            const dot = document.getElementById('sublist-status-dot');
            const label = document.getElementById('sublist-status-label');
            const hint = document.getElementById('sublist-status-hint');

            badge.className = `inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-semibold ${nextStatus.badge}`;
            dot.className = `h-2.5 w-2.5 rounded-full ${nextStatus.dot}`;
            label.textContent = nextStatus.label;
            hint.textContent = nextStatus.hint;
        };

        // Fungsi ini memulihkan state yang pernah disimpan sebelumnya.
        // Saat halaman detail dibuka ulang, setiap kartu siswa dan panel export dikembalikan ke kondisi terakhir.
        const restoreState = () => {
            const state = readState();
            const students = state.students ?? {};

            Object.entries(students).forEach(([studentId, studentState]) => {
                const card = document.getElementById(`student-card-${studentId}`);
                if (!card) return;

                card.dataset.verificationState = studentState.verificationState ?? 'pending';
                card.dataset.proofState = studentState.proofState ?? 'empty';
                updateStudentCard(studentId, card.dataset.verificationState);
            });

            updateExportAudit();
        };

        // Listener ini menangani tombol pembuka modal.
        // Nilai atribut data-modal-open berisi id modal yang harus ditampilkan.
        document.querySelectorAll('[data-modal-open]').forEach((button) => {
            button.addEventListener('click', () => openModal(button.getAttribute('data-modal-open')));
        });

        // Listener ini menangani seluruh tombol penutup modal agar semua modal memakai pola yang sama.
        document.querySelectorAll('[data-modal-close]').forEach((button) => {
            button.addEventListener('click', () => closeModal(button.getAttribute('data-modal-close')));
        });

        // Listener ini menangani perubahan file bukti.
        // Jika file berupa gambar, preview visual ditampilkan. Jika bukan gambar, nama file ditampilkan sebagai informasi.
        // Pada saat yang sama, status bukti siswa disimpan ke storage agar kartu dan modal tetap sinkron.
        document.querySelectorAll('[data-role="proof-input"]').forEach((input) => {
            input.addEventListener('change', (event) => {
                const studentId = input.getAttribute('data-student-id');
                const file = event.target.files?.[0];
                const card = document.getElementById(`student-card-${studentId}`);
                const modal = document.getElementById(`kelola-verifikasi-${studentId}`);
                if (!card || !modal) return;

                const previewWrapper = modal.querySelector('[data-role="proof-preview"]');
                const previewImage = modal.querySelector('[data-role="proof-preview-image"]');
                const previewFile = modal.querySelector('[data-role="proof-preview-file"]');
                const state = readState();
                state.students = state.students ?? {};
                state.students[studentId] = state.students[studentId] ?? {};

                if (!file) {
                    card.dataset.proofState = 'empty';
                    state.students[studentId].proofState = 'empty';
                    delete state.students[studentId].proofName;
                    writeState(state);
                    previewWrapper.classList.add('hidden');
                    previewImage.classList.add('hidden');
                    previewFile.classList.add('hidden');
                    updateStudentCard(studentId, card.dataset.verificationState);
                    return;
                }

                card.dataset.proofState = 'uploaded';
                state.students[studentId].proofState = 'uploaded';
                state.students[studentId].proofName = file.name;
                writeState(state);
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

                updateStudentCard(studentId, card.dataset.verificationState);
            });
        });

        // Listener ini menangani keputusan setuju.
        // Saat tombol ditekan, state siswa diperbarui, audit trail aktor disimpan, lalu tampilan kartu dan sublist dihitung ulang.
        document.querySelectorAll('[data-accept-verification]').forEach((button) => {
            button.addEventListener('click', () => {
                const studentId = button.getAttribute('data-accept-verification');
                const state = readState();
                state.students = state.students ?? {};
                state.students[studentId] = {
                    ...(state.students[studentId] ?? {}),
                    verificationState: 'approved',
                    verifiedBy: actorName,
                    decisionLabel: 'Disetujui',
                };
                writeState(state);
                updateStudentCard(studentId, 'approved');
                updateSublistSummary();
                closeModal(`kelola-verifikasi-${studentId}`);
            });
        });

        // Listener ini memulai alur penolakan dengan memindahkan tampilan dari modal utama ke modal catatan penolakan.
        document.querySelectorAll('[data-open-reject-flow]').forEach((button) => {
            button.addEventListener('click', () => {
                const studentId = button.getAttribute('data-open-reject-flow');
                closeModal(`kelola-verifikasi-${studentId}`);
                openModal(`reject-note-modal-${studentId}`);
            });
        });

        // Listener ini memvalidasi bahwa alasan penolakan terisi sebelum membuka modal konfirmasi.
        // Jika textarea kosong, pesan error ditampilkan dan proses tidak dilanjutkan.
        document.querySelectorAll('[data-open-reject-confirm]').forEach((button) => {
            button.addEventListener('click', () => {
                const studentId = button.getAttribute('data-open-reject-confirm');
                const noteModal = document.getElementById(`reject-note-modal-${studentId}`);
                const noteInput = noteModal?.querySelector('[data-role="reject-note-input"]');
                const error = noteModal?.querySelector('[data-role="reject-note-error"]');
                const preview = document.getElementById(`reject-confirm-modal-${studentId}`)?.querySelector('[data-role="reject-note-preview"]');
                const note = noteInput?.value.trim() ?? '';

                if (note === '') {
                    error?.classList.remove('hidden');
                    return;
                }

                error?.classList.add('hidden');
                if (preview) preview.textContent = note;
                closeModal(`reject-note-modal-${studentId}`);
                openModal(`reject-confirm-modal-${studentId}`);
            });
        });

        // Listener ini menyimpan keputusan tolak ke state siswa setelah konfirmasi terakhir diberikan.
        // Catatan penolakan ikut disimpan sebagai bagian dari audit keputusan.
        document.querySelectorAll('[data-confirm-reject]').forEach((button) => {
            button.addEventListener('click', () => {
                const studentId = button.getAttribute('data-confirm-reject');
                const noteInput = document.querySelector(`[data-role="reject-note-input"][data-student-id="${studentId}"]`);
                const state = readState();
                state.students = state.students ?? {};
                state.students[studentId] = {
                    ...(state.students[studentId] ?? {}),
                    verificationState: 'rejected',
                    verifiedBy: actorName,
                    decisionLabel: 'Ditolak',
                    rejectNote: noteInput?.value.trim() ?? '',
                };
                writeState(state);
                updateStudentCard(studentId, 'rejected');
                updateSublistSummary();
                closeModal(`reject-confirm-modal-${studentId}`);
            });
        });

        // Listener form export ini hanya menyimpan metadata export lokal sebelum request form dikirim.
        // Informasi ini dipakai untuk memperbarui panel audit trail export pada tampilan berikutnya.
        document.getElementById('export-sublist-form')?.addEventListener('submit', () => {
            const state = readState();
            state.exportedBy = actorName;
            state.exportedAt = new Date().toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' });
            writeState(state);
        });

        // Inisialisasi halaman dilakukan di akhir agar state yang pernah tersimpan langsung diterapkan
        // sebelum pengguna mulai berinteraksi kembali dengan daftar siswa target.
        restoreState();
        updateSublistSummary();
    </script>
</x-app-layout>
