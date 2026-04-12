<x-app-layout>
    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">Faktur</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Lihat daftar faktur berjalan dan kirim bukti pembayaran melalui unggah berkas.
                    </p>
                </div>
                <button type="button" data-open-modal="tata-cara-modal"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                    Tata Cara
                </button>
            </div>

            @if (session('success'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                @if($fakturs->isEmpty())
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
                        <h3 class="text-base font-semibold text-slate-900">Belum Ada Faktur Berjalan</h3>
                        <p class="mt-2 text-sm text-slate-500">Saat ini belum ada penagihan yang ditujukan ke akun ini.</p>
                    </div>
                @else
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-4 py-3">Nama Faktur</th>
                                    <th class="px-4 py-3">Nominal</th>
                                    <th class="px-4 py-3">Ketersediaan</th>
                                    <th class="px-4 py-3">Jatuh Tempo</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white text-slate-700">
                                @foreach($fakturs as $faktur)
                                    @php
                                        $riwayat = $riwayats->get($faktur->id);
                                        $statusLabel = 'Pending';
                                        $statusBadge = 'bg-slate-100 text-slate-700 border-slate-200';
                                        $rowClass = 'bg-slate-50/40';

                                        if ($riwayat) {
                                            if ($riwayat->status === 'menunggu_verifikasi') {
                                                $statusLabel = 'Menunggu Verifikasi';
                                                $statusBadge = 'bg-amber-100 text-amber-800 border-amber-200';
                                                $rowClass = 'bg-amber-50/60';
                                            } elseif ($riwayat->status === 'diverifikasi') {
                                                $statusLabel = 'Diterima';
                                                $statusBadge = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                                                $rowClass = 'bg-emerald-50/55';
                                            } elseif ($riwayat->status === 'ditolak') {
                                                $statusLabel = 'Ditolak';
                                                $statusBadge = 'bg-rose-100 text-rose-700 border-rose-200';
                                                $rowClass = 'bg-rose-50/60';
                                            }
                                        }
                                    @endphp
                                    <tr class="{{ $rowClass }}">
                                        <td class="px-4 py-3 font-medium text-slate-900">
                                            {{ $faktur->masterFaktur->nama_faktur }}
                                        </td>
                                        <td class="px-4 py-3">Rp {{ number_format($faktur->masterFaktur->nominal, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3">
                                            {{ \Carbon\Carbon::parse($faktur->tersedia_pada)->timezone('Asia/Jakarta')->translatedFormat('d F Y') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ \Carbon\Carbon::parse($faktur->jatuh_tempo)->timezone('Asia/Jakarta')->translatedFormat('d F Y') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $statusBadge }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            @if (($riwayat?->status ?? null) === 'diverifikasi')
                                                <button
                                                    type="button"
                                                    data-open-modal="accepted-modal-{{ $faktur->id }}"
                                                    class="rounded-lg border border-blue-200 bg-white px-4 py-2 text-xs font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-50"
                                                    aria-label="Lihat detail penerimaan">
                                                    Detail
                                                </button>
                                            @else
                                                <button
                                                    type="button"
                                                    data-open-modal="upload-modal-{{ $faktur->id }}"
                                                    class="rounded-lg border border-blue-200 bg-white px-4 py-2 text-xs font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-50">
                                                    Upload Berkas
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $fakturs->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>

    <div id="tata-cara-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4">
        <div class="w-full max-w-2xl rounded-2xl bg-white p-5 shadow-xl">
            <div class="mb-4 flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-xl font-semibold text-slate-900">Tata Cara Faktur</h3>
                    <p class="mt-1 text-sm text-slate-500">Panduan singkat proses kerja pada menu faktur.</p>
                </div>
                <button type="button" data-close-modal="tata-cara-modal" class="text-slate-500 hover:text-slate-700">&times;</button>
            </div>

            <div class="space-y-3 text-sm text-slate-700">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p class="font-semibold text-slate-900">Langkah 1: Pilih faktur yang akan dibayar</p>
                    <p class="mt-1">Periksa nama faktur, nominal, ketersediaan, dan jatuh tempo pada daftar.</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p class="font-semibold text-slate-900">Langkah 2: Upload berkas bukti pembayaran</p>
                    <p class="mt-1">Klik tombol <span class="font-semibold">Upload Berkas</span>, pilih file JPG/PNG/PDF (maks 2MB), lalu kirim.</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p class="font-semibold text-slate-900">Langkah 3: Pantau status verifikasi</p>
                    <ul class="mt-1 list-disc pl-5">
                        <li><span class="font-semibold">Menunggu Verifikasi</span>: berkas sudah masuk dan menunggu pemeriksaan TU.</li>
                        <li><span class="font-semibold">Diterima</span>: berkas disetujui oleh TU, proses selesai.</li>
                        <li><span class="font-semibold">Ditolak</span>: berkas belum sesuai. Lihat catatan TU pada pop-up upload, lalu kirim ulang berkas.</li>
                    </ul>
                </div>
                <div class="rounded-xl border border-blue-200 bg-blue-50 p-3">
                    <p class="font-semibold text-slate-900">Kontak Tata Usaha</p>
                    @php
                        $primaryTu = $tuContacts->first();
                    @endphp
                    <p class="mt-1">PIC TU: <span class="font-semibold">{{ $primaryTu?->name ?? 'Belum tersedia' }}</span></p>
                    <p class="mt-1">Nomor Kontak: <span class="font-semibold">{{ $tuContactPhone }}</span></p>
                    <p class="mt-1">Email TU: <span class="font-semibold">{{ $primaryTu?->email ?? 'Belum tersedia' }}</span></p>
                </div>
            </div>

            <div class="mt-5 flex justify-end">
                <button type="button" data-close-modal="tata-cara-modal"
                    class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    @foreach($fakturs as $faktur)
        @php
            $riwayat = $riwayats->get($faktur->id);
            $existingFile = $riwayat?->berkas_file;
            $existingExt = $existingFile ? strtolower(pathinfo($existingFile, PATHINFO_EXTENSION)) : null;
            $existingIsImage = in_array($existingExt, ['jpg', 'jpeg', 'png', 'webp'], true);
        @endphp
        @if(($riwayat?->status ?? null) === 'diverifikasi')
            <div id="accepted-modal-{{ $faktur->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4">
                <div class="w-full max-w-lg rounded-2xl bg-white p-5 shadow-xl">
                    <div class="mb-4 flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-xl font-semibold text-slate-900">Detail Penerimaan Berkas</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ $faktur->masterFaktur->nama_faktur }}</p>
                        </div>
                        <button type="button" data-close-modal="accepted-modal-{{ $faktur->id }}" class="text-slate-500 hover:text-slate-700">&times;</button>
                    </div>

                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                        Berkas sudah diterima oleh:
                        <span class="font-semibold">{{ $riwayat?->verifiedBy?->name ?? 'Tata Usaha' }}</span>.
                        @if($riwayat?->verified_at)
                            <p class="mt-1 text-xs text-emerald-700">
                                Waktu verifikasi: {{ \Carbon\Carbon::parse($riwayat->verified_at)->timezone('Asia/Jakarta')->translatedFormat('d F Y H:i') }}
                            </p>
                        @endif
                    </div>

                    <div class="mt-5 flex justify-end">
                        <button type="button" data-close-modal="accepted-modal-{{ $faktur->id }}"
                            class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <div id="upload-modal-{{ $faktur->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4">
            <div class="w-full max-w-xl rounded-2xl bg-white p-5 shadow-xl">
                <div class="mb-4 flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-semibold text-slate-900">Upload Bukti Faktur</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ $faktur->masterFaktur->nama_faktur }}</p>
                    </div>
                    <button type="button" data-close-upload-modal="upload-modal-{{ $faktur->id }}" class="text-slate-500 hover:text-slate-700">&times;</button>
                </div>

                <form action="{{ route('ortu.faktur.submit', $faktur->id) }}" method="POST" enctype="multipart/form-data" data-upload-form>
                    @csrf
                    @if(($riwayat?->status ?? null) === 'ditolak' && $riwayat?->catatan_penolakan)
                        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-3">
                            <p class="text-xs font-medium uppercase tracking-wide text-rose-500">Catatan dari Tata Usaha</p>
                            <p class="mt-1 text-sm text-rose-700">{{ $riwayat->catatan_penolakan }}</p>
                        </div>
                    @endif

                    @if($existingFile)
                        <div class="mb-4 rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <p class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-400">Berkas Terakhir</p>

                            @if($existingIsImage)
                                <div class="flex min-h-[180px] flex-col items-center justify-center rounded-lg bg-white p-2">
                                    <a href="{{ Storage::url($existingFile) }}" target="_blank"
                                        class="mb-3 inline-block rounded border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-600 hover:bg-blue-100">
                                        Buka Gambar Penuh
                                    </a>
                                    <img src="{{ Storage::url($existingFile) }}" alt="Bukti terakhir"
                                        class="max-h-56 rounded-lg border border-slate-200 object-contain shadow-sm" />
                                </div>
                            @else
                                <div class="rounded-lg border border-slate-200 bg-white p-3 text-sm text-slate-700">
                                    <p class="font-medium">File PDF: {{ basename($existingFile) }}</p>
                                    <a href="{{ Storage::url($existingFile) }}" target="_blank"
                                        class="mt-2 inline-block rounded border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-600 hover:bg-blue-100">
                                        Buka File PDF
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="mb-1 block text-sm font-medium text-slate-700">Bukti Pembayaran (JPG/PNG/PDF, max 2MB)</label>
                        <input
                            type="file"
                            name="berkas_file"
                            accept="image/*,.pdf"
                            data-proof-input
                            class="block w-full rounded-md border-slate-300 text-sm file:mr-4 file:rounded-md file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:font-semibold file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-600 focus:ring-blue-600">
                        <p class="mt-2 text-xs text-slate-500">Tombol kirim aktif setelah file dipilih.</p>
                    </div>

                    <div class="mt-5 flex flex-wrap justify-end gap-2">
                        <button type="button" data-close-upload-modal="upload-modal-{{ $faktur->id }}"
                            class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" data-submit-upload disabled
                            class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white opacity-50 cursor-not-allowed">
                            Kirim Berkas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <script>
        document.querySelectorAll('[data-open-modal]').forEach((button) => {
            button.addEventListener('click', () => {
                const id = button.getAttribute('data-open-modal');
                const modal = document.getElementById(id);
                if (!modal) return;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });
        });

        document.querySelectorAll('[data-close-upload-modal], [data-close-modal]').forEach((button) => {
            button.addEventListener('click', () => {
                const id = button.getAttribute('data-close-upload-modal') ?? button.getAttribute('data-close-modal');
                const modal = document.getElementById(id);
                if (!modal) return;
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });
        });

        document.querySelectorAll('[data-upload-form]').forEach((form) => {
            const input = form.querySelector('[data-proof-input]');
            const submit = form.querySelector('[data-submit-upload]');
            if (!input || !submit) return;

            input.addEventListener('change', () => {
                const hasFile = (input.files?.length ?? 0) > 0;
                submit.disabled = !hasFile;
                submit.classList.toggle('opacity-50', !hasFile);
                submit.classList.toggle('cursor-not-allowed', !hasFile);
            });
        });
    </script>
</x-app-layout>
