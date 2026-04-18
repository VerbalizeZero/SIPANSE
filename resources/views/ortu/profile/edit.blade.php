<x-app-layout>
    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-5">
                <h1 class="text-2xl font-semibold text-slate-900">Profile Orang Tua</h1>
                <p class="text-sm text-slate-500">Kelola informasi profil akun orang tua.</p>
            </div>

            @if (session('success'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">Informasi Siswa</h2>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    {{-- NISN - Tidak bisa diubah --}}
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">NISN</label>
                        <div class="block w-full rounded-md border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-500">
                            {{ $siswa?->nisn ?? '-' }}
                        </div>
                    </div>

                    {{-- Nama Siswa - Tidak bisa diubah --}}
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama Siswa</label>
                        <div class="block w-full rounded-md border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-500">
                            {{ $siswa?->nama_siswa ?? '-' }}
                        </div>
                    </div>

                    {{-- Kelas - Tidak bisa diubah --}}
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Kelas</label>
                        <div class="block w-full rounded-md border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-500">
                            {{ $siswa?->kelas ?? '-' }}
                        </div>
                    </div>

                    {{-- Angkatan - Tidak bisa diubah --}}
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Angkatan</label>
                        <div class="block w-full rounded-md border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-500">
                            {{ $siswa?->tahun_angkatan ?? '-' }}
                        </div>
                    </div>

                    {{-- Level - Tidak bisa diubah --}}
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Level</label>
                        @if ($siswa?->kelas)
                            @php
                                $level = preg_match('/^XII/i', $siswa->kelas) ? '12' : 
                                        (preg_match('/^XI/i', $siswa->kelas) ? '11' : '10');
                            @endphp
                            <div class="block w-full rounded-md border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-500">
                                {{ $level }}
                            </div>
                        @else
                            <div class="block w-full rounded-md border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-500">
                                -
                            </div>
                        @endif
                    </div>

                    {{-- Nama Orang Tua/Wali - Tidak bisa diubah --}}
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama Orang Tua/Wali</label>
                        <div class="block w-full rounded-md border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-500">
                            {{ $siswa?->nama_ortu ?? '-' }}
                        </div>
                    </div>

                    {{-- Email Orang Tua - Tidak bisa diubah --}}
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-700">Email Orang Tua</label>
                        <div class="block w-full rounded-md border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-500">
                            {{ $siswa?->email_ortu ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('ortu.profile.update') }}" class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf
                @method('PUT')

                <h2 class="mb-4 text-lg font-semibold text-slate-900">Informasi Kontak</h2>

                <div class="grid grid-cols-1 gap-4">
                    {{-- Alamat Rumah - Bisa diubah --}}
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Alamat Rumah</label>
                        <textarea 
                            name="alamat" 
                            rows="3" 
                            class="block w-full rounded-md border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600"
                            placeholder="Masukkan alamat rumah"
                        >{{ old('alamat', $siswa?->alamat ?? '') }}</textarea>
                        @error('alamat') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Nomor Kontak - Bisa diubah --}}
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nomor Kontak</label>
                        <input 
                            name="no_hp_ortu" 
                            value="{{ old('no_hp_ortu', $siswa?->no_hp_ortu ?? '') }}" 
                            class="block w-full rounded-md border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600" 
                            placeholder="Masukkan nomor kontak"
                        />
                        @error('no_hp_ortu') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="rounded-md bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-800">
                        Simpan Perubahan
                    </button>
                </div>
            </form>

            <form method="POST" action="{{ route('ortu.profile.password') }}" class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf
                @method('PUT')

                <h2 class="mb-4 text-lg font-semibold text-slate-900">Ganti Password</h2>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Password Saat Ini</label>
                        <input type="password" name="current_password" class="block w-full rounded-md border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600" required />
                        @error('current_password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div></div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Password Baru</label>
                        <input type="password" name="password" class="block w-full rounded-md border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600" required />
                        @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" class="block w-full rounded-md border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600" required />
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="rounded-md bg-slate-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-900">
                        Ganti Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>