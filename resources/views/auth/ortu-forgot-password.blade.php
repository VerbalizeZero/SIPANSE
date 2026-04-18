<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Lupa Password - {{ config('app.name', 'SIPANSE') }}</title>

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

            .status {
                padding: 12px;
                border-radius: 8px;
                font-size: 13px;
                margin-bottom: 16px;
                text-align: left;
            }

            .status-success {
                background: #d1fae5;
                color: #065f46;
                border: 1px solid #a7f3d0;
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

            .icon {
                width: 18px;
                height: 18px;
                color: var(--primary);
            }
        </style>
    </head>
    <body>
        <div class="card">
            <div class="logo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 21h14"/>
                    <path d="M7 21V7a5 5 0 0 1 10 0v14"/>
                    <path d="M9 11h6"/>
                </svg>
            </div>

            <h1>Lupa Password Orang Tua</h1>
            <p>Masukkan NISN siswa untuk mengirim link reset password ke email yang terdaftar.</p>

            <div id="email-display" class="status status-success" style="display: none;">
                Link reset password akan dikirim ke email: <strong id="email-text"></strong>
            </div>

            @if (session('status'))
                <div class="status status-success">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.ortu.email') }}" id="forgot-form">
                @csrf
                <div class="field">
                    <label class="label" for="nisn">NISN Siswa</label>
                    <input 
                        class="input" 
                        id="nisn" 
                        type="text" 
                        name="nisn" 
                        inputmode="numeric" 
                        pattern="[0-9]*" 
                        maxlength="20" 
                        placeholder="Masukkan NISN siswa" 
                        value="{{ old('nisn') }}" 
                        required
                    >
                    <div class="help">Masukkan 10 digit Nomor Induk Siswa Nasional</div>
                    @error('nisn')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <button class="submit" type="submit" id="submit-btn" disabled>Cek Email & Kirim Link Reset</button>

                <div class="actions">
                    <a class="link" href="{{ route('login') }}">
                        ← Kembali ke Login
                    </a>
                </div>
            </form>
        </div>

        <script>
            const nisnInput = document.getElementById('nisn');
            const emailDisplay = document.getElementById('email-display');
            const emailText = document.getElementById('email-text');
            const submitBtn = document.getElementById('submit-btn');
            const form = document.getElementById('forgot-form');

            if (nisnInput) {
                // Hanya angka
                nisnInput.addEventListener('input', (event) => {
                    const target = event.target;
                    target.value = target.value.replace(/\D/g, '').slice(0, 20);
                });

                // Cek email saat NISN berubah (debounce)
                let timeout;
                nisnInput.addEventListener('input', (event) => {
                    clearTimeout(timeout);
                    const nisn = event.target.value;
                    
                    // Reset state
                    emailDisplay.style.display = 'none';
                    submitBtn.disabled = true;
                    
                    if (nisn.length >= 10) {
                        timeout = setTimeout(() => checkEmail(nisn), 500);
                    }
                });
            }

            async function checkEmail(nisn) {
                try {
                    const response = await fetch('/api/check-ortu-email', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ nisn })
                    });

                    const data = await response.json();

                    if (data.success && data.email) {
                        // Mask email: deflect***@gmail.com
                        const maskedEmail = maskEmail(data.email);
                        emailText.textContent = maskedEmail;
                        emailDisplay.style.display = 'block';
                        submitBtn.disabled = false;
                    } else {
                        emailDisplay.style.display = 'none';
                        submitBtn.disabled = true;
                    }
                } catch (error) {
                    console.error('Error checking email:', error);
                    emailDisplay.style.display = 'none';
                    submitBtn.disabled = true;
                }
            }

            function maskEmail(email) {
                const [localPart, domain] = email.split('@');
                if (localPart.length <= 3) {
                    return email;
                }
                // Tampilkan 3 karakter pertama, sisanya ***
                const maskedLocal = localPart.substring(0, 3) + '***';
                return maskedLocal + '@' + domain;
            }
        </script>
    </body>
</html>