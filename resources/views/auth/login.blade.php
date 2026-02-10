<x-guest-layout>
    <h1 class="auth-title">Selamat Datang</h1>
    <p class="auth-subtitle">Login sebagai Administrasi</p>

    <a class="auth-back" href="{{ url('/') }}">
        <span>←</span>
        <span>Kembali</span>
    </a>

    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="auth-field">
            <label for="email" class="auth-label">Email atau Username</label>
            <input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="Masukkan email atau username" />
            <x-input-error :messages="$errors->get('email')" class="auth-error" />
        </div>

        <div class="auth-field">
            <label for="password" class="auth-label">Password</label>
            <input id="password" class="auth-input" type="password" name="password" required autocomplete="current-password" placeholder="Masukkan password" />
            <x-input-error :messages="$errors->get('password')" class="auth-error" />
        </div>

        <div class="auth-actions">
            @if (Route::has('password.request'))
                <a class="auth-link" href="{{ route('password.request') }}">
                    Lupa Password?
                </a>
            @endif
        </div>

        <button class="auth-button" type="submit">Login</button>
    </form>

    <div class="auth-footer">© 2026 SMAN 15 Surabaya. All rights reserved.</div>
</x-guest-layout>
