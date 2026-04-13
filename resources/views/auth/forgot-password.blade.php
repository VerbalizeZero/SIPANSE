<x-guest-layout>
    <div class="mb-4 text-sm text-slate-600">
        Lupa password? Masukkan alamat email Anda yang dipakai dan kami akan mengirimkan link reset password ke email Anda.
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="'Email'" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <button type="submit" class="rounded-md bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-800">
                Kirim Link Reset Password
            </button>
        </div>
    </form>
</x-guest-layout>
