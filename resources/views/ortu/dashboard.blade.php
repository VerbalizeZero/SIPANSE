<x-app-layout>
    <div class="py-4 sm:py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-slate-900">Dashboard Ortu</h1>
                <p class="text-sm text-slate-500">Ringkasan status faktur untuk akun Orang Tua.</p>
            </div>

            <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Profil Siswa</h3>
                <p class="mt-1 text-sm text-slate-500">Ringkasan data siswa yang terhubung dengan akun Orang Tua.</p>

                <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <div class="grid grid-cols-1 gap-4 text-sm text-slate-700 md:grid-cols-3">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-400">Nama Siswa</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $siswa->nama_siswa }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-400">NISN</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $siswa->nisn }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-400">Kelas</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $siswa->kelas ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-slate-400">Total Faktur</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $stats['total_faktur'] ?? 0 }}</p>
                    <p class="mt-1 text-xs text-slate-500">Semua faktur yang ditujukan ke siswa ini.</p>
                </section>

                <section class="rounded-2xl border border-amber-200 bg-amber-50/40 p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-amber-600">Faktur Berjalan</p>
                    <p class="mt-3 text-3xl font-semibold text-amber-700">{{ $stats['total_berjalan'] ?? 0 }}</p>
                    <p class="mt-1 text-xs text-amber-700/80">Masih menunggu proses verifikasi.</p>
                </section>

                <section class="rounded-2xl border border-emerald-200 bg-emerald-50/40 p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-emerald-600">Diterima</p>
                    <p class="mt-3 text-3xl font-semibold text-emerald-700">{{ $stats['total_diterima'] ?? 0 }}</p>
                    <p class="mt-1 text-xs text-emerald-700/80">Pengajuan berkas yang sudah disetujui.</p>
                </section>

                <section class="rounded-2xl border border-rose-200 bg-rose-50/40 p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-rose-600">Ditolak</p>
                    <p class="mt-3 text-3xl font-semibold text-rose-700">{{ $stats['total_ditolak'] ?? 0 }}</p>
                    <p class="mt-1 text-xs text-rose-700/80">Pengajuan yang perlu perbaikan berkas.</p>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
