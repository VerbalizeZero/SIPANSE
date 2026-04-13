<x-app-layout>
    <div class="py-4 sm:py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-slate-900">Dashboard Bendahara</h1>
                <p class="text-sm text-slate-500">Ringkasan master faktur dan progres sublist dari Tata Usaha.</p>
            </div>

            <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-600">
                    Selamat datang, <span class="font-semibold text-slate-900">{{ auth()->user()->name }}</span>.
                    Gunakan menu "Membuat Faktur" dan "Arsip Faktur" untuk monitoring data kas.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-slate-400">Total Master Faktur</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $stats['total_master_faktur'] ?? 0 }}</p>
                    <p class="mt-1 text-xs text-slate-500">Data master faktur yang sudah tersedia.</p>
                </section>

                <section class="rounded-2xl border border-amber-200 bg-amber-50/40 p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-amber-600">Faktur Berjalan</p>
                    <p class="mt-3 text-3xl font-semibold text-amber-700">{{ $stats['total_faktur_berjalan'] ?? 0 }}</p>
                    <p class="mt-1 text-xs text-amber-700/80">Sublist yang masih aktif diproses.</p>
                </section>

                <section class="rounded-2xl border border-emerald-200 bg-emerald-50/40 p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-emerald-600">Faktur Selesai</p>
                    <p class="mt-3 text-3xl font-semibold text-emerald-700">{{ $stats['total_faktur_selesai'] ?? 0 }}</p>
                    <p class="mt-1 text-xs text-emerald-700/80">Sublist pada status selesai/arsip.</p>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
