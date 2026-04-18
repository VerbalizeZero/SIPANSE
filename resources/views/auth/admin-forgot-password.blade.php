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

            .toggle {
                display: flex;
                gap: 8px;
                margin-bottom: 20px;
            }

            .toggle button {
                flex: 1;
                padding: 10px 16px;
                border: 1px solid var(--border);
                border-radius: 8px;
                background: #ffffff;
                color: var(--muted);
                font-size: 13px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s ease;
            }

            .toggle button:hover {
                border-color: rgba(27, 58, 138, 0.4);
                color: var(--primary);
            }

            .toggle button.active {
                background: var(--primary);
                border-color: var(--primary);
                color: #ffffff;
            }

            .field {
                margin-bottom: 14px;
                text-align: left;
                display: none;
            }

            .field.active {
                display: block;
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

            .submit:disabled {
                background: #94a3b8;
                cursor: not-allowed;
                transform: none;
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
                    <path d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5Z"/>
                    <path d="M4 21a8 8 0 0 1 16 0"/>
                </svg>
            </div>

            <h1>Lupa Password Admin</h1>
            <p>Pilih metode untuk mengirim link reset password ke email yang terdaftar.</p>

            <div id="email-display" class="status status-success" style="display: none;">
                Link reset password akan dikirim ke email: <strong id="email-text"></strong>
            </div>

            @if (session('status'))
                <div class="status status-success">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.admin.email') }}" id="forgot-form">
                @csrf
                <input type="hidden" name="option" id="option-input" value="username">

                <div class="toggle">
                    <button type="button" class="toggle-btn active" data-mode="username">Via Username</button>
                    <button type="button" class="toggle-btn" data-mode="email">Via Email</button>
                </div>

                <div class="field active" id="field-username">
                    <label class="label" for="username">Username</label>
                    <input 
                        class="input" 
                        id="username" 
                        type="text" 
                        name="username" 
                        placeholder="Masukkan username" 
                        value="{{ old('username') }}" 
                    >
                    <div class="help">Username untuk akun Tata Usaha atau Bendahara</div>
                    @error('username')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field" id="field-email">
                    <label class="label" for="email">Email</label>
                    <input 
                        class="input" 
                        id="email" 
                        type="email" 
                        name="email" 
                        placeholder="Masukkan email terdaftar" 
                        value="{{ old('email') }}" 
                    >
                    <div class="help">Email yang terdaftar pada akun admin</div>
                    @error('email')
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
            const toggleBtns = document.querySelectorAll('.toggle-btn');
            const fieldUsername = document.getElementById('field-username');
            const fieldEmail = document.getElementById('field-email');
            const usernameInput = document.getElementById('username');
            const emailInput = document.getElementById('email');
            const emailDisplay = document.getElementById('email-display');
            const emailText = document.getElementById('email-text');
            const submitBtn = document.getElementById('submit-btn');
            const form = document.getElementById('forgot-form');
            const optionInput = document.getElementById('option-input');

            let currentMode = 'username';

            // Toggle mode
            toggleBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    const mode = btn.dataset.mode;
                    currentMode = mode;

                    toggleBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');

                    fieldUsername.classList.toggle('active', mode === 'username');
                    fieldEmail.classList.toggle('active', mode === 'email');

                    // Update option input value
                    optionInput.value = mode;

                    // Reset state
                    emailDisplay.style.display = 'none';
                    submitBtn.disabled = mode === 'email' ? false : true;
                });
            });

            // Username mode - check email when username is entered
            let timeout;
            if (usernameInput) {
                usernameInput.addEventListener('input', (event) => {
                    clearTimeout(timeout);
                    const username = event.target.value;
                    
                    // Reset state
                    emailDisplay.style.display = 'none';
                    submitBtn.disabled = true;
                    
                    if (username.length >= 3 && currentMode === 'username') {
                        timeout = setTimeout(() => checkUsername(username), 500);
                    }
                });
            }

            // Email mode - enable submit immediately when email is valid
            if (emailInput) {
                emailInput.addEventListener('input', (event) => {
                    const email = event.target.value;
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    submitBtn.disabled = !emailRegex.test(email);
                });
            }

            // Form submission
            form.addEventListener('submit', (event) => {
                // Remove field that is not active
                if (currentMode === 'username') {
                    emailInput.name = '';
                    emailInput.disabled = true;
                    usernameInput.name = 'username';
                    usernameInput.disabled = false;
                } else {
                    usernameInput.name = '';
                    usernameInput.disabled = true;
                    emailInput.name = 'email';
                    emailInput.disabled = false;
                }
            });

            async function checkUsername(username) {
                try {
                    const response = await fetch('/api/check-admin-username', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ username })
                    });

                    const data = await response.json();

                    if (data.success && data.email) {
                        const maskedEmail = maskEmail(data.email);
                        emailText.textContent = maskedEmail;
                        emailDisplay.style.display = 'block';
                        submitBtn.disabled = false;
                    } else {
                        emailDisplay.style.display = 'none';
                        submitBtn.disabled = true;
                    }
                } catch (error) {
                    console.error('Error checking username:', error);
                    emailDisplay.style.display = 'none';
                    submitBtn.disabled = true;
                }
            }

            function maskEmail(email) {
                const [localPart, domain] = email.split('@');
                if (localPart.length <= 3) {
                    return email;
                }
                const maskedLocal = localPart.substring(0, 3) + '***';
                return maskedLocal + '@' + domain;
            }
        </script>
    </body>
</html>