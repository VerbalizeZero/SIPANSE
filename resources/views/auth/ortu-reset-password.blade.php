<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Reset Password - {{ config('app.name', 'SIPANSE') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700" rel="stylesheet" />

        <style>
            :root {
                --ink: #0f1b2d;
                --muted: #5b6b86;
                --primary: #1b3a8a;
                --primary-strong: #17327a;
                --card: #ffffff;
                --border: #dfe6f3;
                --shadow: 0 20px 40px rgba(11, 26, 60, 0.12);
                --radius: 16px;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                font-family: "Poppins", system-ui, -apple-system, "Segoe UI", sans-serif;
                color: var(--ink);
                background: #ffffff;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 24px;
            }

            .card {
                width: min(420px, 100%);
                background: var(--card);
                border: 1px solid var(--border);
                border-radius: var(--radius);
                box-shadow: var(--shadow);
                padding: 36px 32px 28px;
                text-align: center;
            }

            .logo {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 64px;
                height: 64px;
                background: var(--primary);
                border-radius: 50%;
                margin-bottom: 20px;
            }

            .logo svg {
                color: #ffffff;
                width: 32px;
                height: 32px;
            }

            .card h1 {
                margin: 0 0 8px;
                font-size: 26px;
                color: var(--primary);
            }

            .card p {
                margin: 0 0 24px;
                color: var(--muted);
                font-size: 14px;
                line-height: 1.6;
            }

            .field {
                margin-bottom: 14px;
                text-align: left;
            }

            .label {
                display: block;
                font-size: 13px;
                font-weight: 500;
                margin-bottom: 6px;
                color: var(--ink);
            }

            .input {
                width: 100%;
                border: 1px solid var(--border);
                border-radius: 8px;
                padding: 10px 12px;
                font-size: 13px;
                outline: none;
                transition: border-color 0.2s ease, box-shadow 0.2s ease;
                background: #ffffff;
            }

            .input:focus {
                border-color: rgba(27, 58, 138, 0.6);
                box-shadow: 0 0 0 3px rgba(27, 58, 138, 0.1);
            }

            .help {
                margin-top: 6px;
                font-size: 11px;
                color: var(--muted);
            }

            .error {
                margin-top: 6px;
                font-size: 11px;
                color: #c0392b;
            }

            .actions {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin: 16px 0;
            }

            .link {
                color: var(--primary);
                font-size: 13px;
                text-decoration: none;
            }

            .link:hover {
                text-decoration: underline;
            }

            .submit {
                width: 100%;
                border: none;
                background: var(--primary);
                color: #ffffff;
                padding: 10px 16px;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                transition: background 0.2s ease, transform 0.2s ease;
                margin-top: 8px;
            }

            .submit:hover {
                background: var(--primary-strong);
                transform: translateY(-1px);
            }
        </style>
    </head>
    <body>
        <div class="card">
            <div class="logo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </div>

            <h1>Reset Password Orang Tua</h1>
            <p>Buat password baru untuk akun Orang Tua Anda.</p>

            <form method="POST" action="{{ route('password.ortu.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="field">
                    <label class="label" for="email">Email</label>
                    <input 
                        class="input" 
                        id="email" 
                        type="email" 
                        name="email" 
                        value="{{ $email ?? old('email') }}" 
                        required
                        readonly
                    >
                    <div class="help">Email yang terdaftar untuk reset password</div>
                    @error('email')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label class="label" for="password">Password Baru</label>
                    <input 
                        class="input" 
                        id="password" 
                        type="password" 
                        name="password" 
                        required
                        autocomplete="new-password"
                        placeholder="Masukkan password baru"
                    >
                    <div class="help">Minimal 8 karakter</div>
                    @error('password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label class="label" for="password_confirmation">Konfirmasi Password</label>
                    <input 
                        class="input" 
                        id="password_confirmation" 
                        type="password" 
                        name="password_confirmation" 
                        required
                        autocomplete="new-password"
                        placeholder="Ulangi password baru"
                    >
                    @error('password_confirmation')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <button class="submit" type="submit">Reset Password</button>

                <div class="actions">
                    <a class="link" href="{{ route('login') }}">
                        ← Kembali ke Login
                    </a>
                </div>
            </form>
        </div>
    </body>
</html>