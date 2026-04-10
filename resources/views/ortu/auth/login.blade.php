<x-guest-layout>
    <!-- Menampilkan pesan error validasi form jika ada (misal NISN tidak ditemukan) -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('ortu.login.submit') }}">
        @csrf

        <!-- Username (NISN) -->
        <div>
            <x-input-label for="nisn" value="Masukkan NISN Anak Anda" />
            
            <x-text-input id="nisn" class="block mt-1 w-full" type="text" name="nisn" :value="old('nisn')" required autofocus autocomplete="username" />
            
            <x-input-error :messages="$errors->get('nisn')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button class="ml-3">
                {{ __('Masuk ke Dasbor') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
