<x-app-layout>
    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
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
        </div>
    </div>
</x-app-layout>

