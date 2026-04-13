<x-app-layout>
    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-5">
                <h1 class="text-2xl font-semibold text-slate-900">Profile Bendahara</h1>
                <p class="text-sm text-slate-500">Kelola identitas akun Bendahara untuk sistem.</p>
            </div>

            @if (session('success'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('bendahara.profile.update') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Username</label>
                        <input name="username" value="{{ old('username', $user->username) }}" class="block w-full rounded-md border-slate-300 bg-slate-100 text-sm text-slate-500 cursor-not-allowed" disabled />
                        @error('username') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama Profile</label>
                        <input name="name" value="{{ old('name', $user->name) }}" class="block w-full rounded-md border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600" required />
                        @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="block w-full rounded-md border-slate-300 bg-slate-100 text-sm text-slate-500 cursor-not-allowed" disabled />
                        @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Kontak</label>
                        <input name="contact" value="{{ old('contact', $user->contact) }}" class="block w-full rounded-md border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600" />
                        @error('contact') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="rounded-md bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-800">
                        Simpan Profile
                    </button>
                </div>
            </form>

            <form method="POST" action="{{ route('bendahara.profile.password') }}" class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
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

