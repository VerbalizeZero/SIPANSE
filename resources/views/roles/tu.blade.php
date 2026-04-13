<x-app-layout>
    <div class="py-4 sm:py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-slate-900">Dashboard TU</h1>
                <p class="text-sm text-slate-500">Ringkasan aktivitas faktur dan verifikasi Tata Usaha.</p>
            </div>

            <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-600">
                    Selamat datang, <span class="font-semibold text-slate-900">{{ auth()->user()->name }}</span>.
                    Gunakan menu di atas untuk melanjutkan proses faktur, verifikasi, dan arsip.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-slate-400">Total Faktur Dibuat</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $stats['total_faktur_dibuat'] ?? 0 }}</p>
                    <p class="mt-1 text-xs text-slate-500">Semua sublist yang dibuat oleh akun TU ini.</p>
                </section>

                <section class="rounded-2xl border border-amber-200 bg-amber-50/40 p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-amber-600">Faktur Berjalan</p>
                    <p class="mt-3 text-3xl font-semibold text-amber-700">{{ $stats['total_faktur_berjalan'] ?? 0 }}</p>
                    <p class="mt-1 text-xs text-amber-700/80">Sublist yang masih aktif diproses.</p>
                </section>

                <section class="rounded-2xl border border-emerald-200 bg-emerald-50/40 p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-emerald-600">Total Faktur Selesai</p>
                    <p class="mt-3 text-3xl font-semibold text-emerald-700">{{ $stats['total_faktur_selesai'] ?? 0 }}</p>
                    <p class="mt-1 text-xs text-emerald-700/80">Sublist yang telah masuk fase selesai/arsip.</p>
                </section>

                <section class="rounded-2xl border border-blue-200 bg-blue-50/40 p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-blue-600">Total Diterima</p>
                    <p class="mt-3 text-3xl font-semibold text-blue-700">{{ $stats['total_diterima'] ?? 0 }}</p>
                    <p class="mt-1 text-xs text-blue-700/80">Jumlah verifikasi siswa dengan status diterima.</p>
                </section>

                <section class="rounded-2xl border border-rose-200 bg-rose-50/40 p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-rose-600">Total Ditolak</p>
                    <p class="mt-3 text-3xl font-semibold text-rose-700">{{ $stats['total_ditolak'] ?? 0 }}</p>
                    <p class="mt-1 text-xs text-rose-700/80">Jumlah verifikasi siswa dengan status ditolak.</p>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
