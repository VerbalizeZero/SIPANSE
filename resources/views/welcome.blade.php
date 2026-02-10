<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'SIPANSE') }}</title>

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
            }

            .page {
                min-height: 100vh;
                display: grid;
                grid-template-columns: minmax(0, 1.3fr) minmax(0, 1fr);
            }

            .hero {
                position: relative;
                overflow: hidden;
                background: #e9f0fb;
            }

            .hero-image {
                position: absolute;
                inset: 0;
                background-image: url("{{ asset('images/landing/WhatsApp-Image-2021-05-03-at-11.36.19-PM-2.jpeg') }}");
                background-size: cover;
                background-position: center;
            }

            .hero-overlay {
                position: absolute;
                inset: 0;
                background: linear-gradient(180deg, rgba(10, 25, 51, 0.05) 0%, rgba(10, 25, 51, 0.6) 100%);
            }

            .hero-caption {
                position: absolute;
                left: 48px;
                bottom: 36px;
                color: #ffffff;
                text-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
            }

            .hero-caption h1 {
                margin: 0 0 6px;
                font-size: 28px;
                letter-spacing: 0.3px;
            }

            .hero-caption p {
                margin: 0;
                font-size: 14px;
                opacity: 0.9;
            }

            .panel {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 48px 32px;
                background: radial-gradient(circle at top, #f7f9ff 0%, #ffffff 55%);
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

            .card h2 {
                margin: 0 0 8px;
                font-size: 26px;
                color: var(--primary);
            }

            .card p {
                margin: 0 0 24px;
                color: var(--muted);
                font-size: 14px;
            }

            .choice {
                display: grid;
                gap: 12px;
                margin-bottom: 18px;
            }

            .choice button {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                padding: 12px 16px;
                border-radius: 10px;
                border: 1px solid var(--border);
                background: #ffffff;
                color: var(--primary);
                font-weight: 600;
                cursor: pointer;
                transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            }

            .choice button:hover {
                border-color: rgba(27, 58, 138, 0.4);
                box-shadow: 0 10px 24px rgba(27, 58, 138, 0.15);
                transform: translateY(-2px);
            }

            .choice span {
                display: block;
                font-size: 12px;
                font-weight: 500;
                color: var(--muted);
                margin-top: 2px;
            }

            .choice button.active {
                border-color: rgba(27, 58, 138, 0.6);
                box-shadow: 0 12px 26px rgba(27, 58, 138, 0.18);
                transform: translateY(-2px);
            }

            .form-shell {
                margin-top: 6px;
                border-top: 1px solid var(--border);
                padding-top: 18px;
                text-align: left;
                position: relative;
            }

            .form-viewport {
                height: var(--form-height, 0px);
                transition: height 0.35s ease;
                overflow: hidden;
            }

            .form-panel {
                opacity: 0;
                transform: scale(0.98);
                transform-origin: top center;
                transition: opacity 0.35s ease, transform 0.35s ease;
                pointer-events: none;
                position: absolute;
                inset: 0;
            }

            .form-panel.active {
                opacity: 1;
                transform: scale(1);
                pointer-events: auto;
                position: relative;
            }

            .form-panel.collapsing {
                opacity: 1;
                transform: scale(1);
                pointer-events: none;
            }

            .form-panel.hidden {
                opacity: 0;
                transform: scale(0.98);
                pointer-events: none;
                visibility: hidden;
            }

            .form-title {
                font-size: 13px;
                font-weight: 600;
                margin: 0 0 12px;
                color: var(--muted);
                text-align: center;
            }

            .field {
                margin-bottom: 14px;
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
                justify-content: flex-end;
                margin: 6px 0 14px;
            }

            .link {
                color: var(--primary);
                font-size: 12px;
                text-decoration: none;
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
            }

            .submit:hover {
                background: var(--primary-strong);
                transform: translateY(-1px);
            }

            .card-footer {
                font-size: 12px;
                color: var(--muted);
            }

            .icon {
                width: 18px;
                height: 18px;
                color: var(--primary);
            }

            @media (max-width: 1024px) {
                .page {
                    grid-template-columns: 1fr;
                }

                .hero {
                    min-height: 52vh;
                }

                .hero-caption {
                    left: 28px;
                    bottom: 24px;
                }
            }

            @media (max-width: 640px) {
                .panel {
                    padding: 32px 20px;
                }

                .card {
                    padding: 28px 22px 22px;
                }
            }
        </style>
    </head>
    <body>
        <main class="page">
            <section class="hero" aria-label="Foto sekolah">
                <div class="hero-image" role="img" aria-label="SMAN 15 Surabaya"></div>
                <div class="hero-overlay"></div>
                <div class="hero-caption">
                    <h1>SMAN 15 Surabaya</h1>
                    <p>Portal Sistem Informasi Sekolah</p>
                </div>
            </section>

            <section class="panel">
                <div class="card">
                    <h2>Selamat Datang</h2>
                    <p>Pilih jenis akun Anda</p>

                    <div class="choice">
                        <button type="button" class="role-button" data-role="admin">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5Z"/>
                                <path d="M4 21a8 8 0 0 1 16 0"/>
                            </svg>
                            <div>
                                Administration
                                <span>Tata Usaha &amp; Bendahara</span>
                            </div>
                        </button>
                        <button type="button" class="role-button" data-role="ortu">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M5 21h14"/>
                                <path d="M7 21V7a5 5 0 0 1 10 0v14"/>
                                <path d="M9 11h6"/>
                            </svg>
                            <div>
                                Orang Tua
                                <span>Login dengan NISN siswa</span>
                            </div>
                        </button>
                    </div>

                    <div class="form-shell">
                        <div class="form-viewport" id="formViewport">
                            <div class="form-panel hidden" id="formAdmin" data-height-target>
                                <div class="form-title">Login sebagai Administrasi</div>
                                <form method="POST" action="{{ route('login') }}">
                                    @csrf
                                    <div class="field">
                                        <label class="label" for="email">Email atau Username</label>
                                        <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="Masukkan email atau username">
                                    </div>
                                    <div class="field">
                                        <label class="label" for="password">Password</label>
                                        <input class="input" id="password" type="password" name="password" required autocomplete="current-password" placeholder="Masukkan password">
                                    </div>
                                    <div class="actions">
                                        @if (Route::has('password.request'))
                                            <a class="link" href="{{ route('password.request') }}">Lupa Password?</a>
                                        @endif
                                    </div>
                                    <button class="submit" type="submit">Login</button>
                                </form>
                            </div>

                            <div class="form-panel hidden" id="formOrtu" data-height-target>
                                <div class="form-title">Login sebagai Orang Tua</div>
                                <form method="POST" action="{{ route('login.ortu') }}">
                                    @csrf
                                    <div class="field">
                                        <label class="label" for="nisn">NISN Siswa</label>
                                        <input class="input" id="nisn" type="text" name="nisn" inputmode="numeric" pattern="[0-9]*" maxlength="20" placeholder="Masukkan NISN siswa" value="{{ old('nisn') }}" required>
                                        <div class="help">Masukkan 10 digit Nomor Induk Siswa Nasional</div>
                                        @if ($errors->get('nisn'))
                                            <div class="error">{{ $errors->first('nisn') }}</div>
                                        @endif
                                    </div>
                                    <button class="submit" type="submit">Masuk</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">© 2026 SMAN 15 Surabaya. All rights reserved.</div>
                </div>
            </section>
        </main>

        <script>
            const buttons = document.querySelectorAll('.role-button');
            const formAdmin = document.getElementById('formAdmin');
            const formOrtu = document.getElementById('formOrtu');
            const viewport = document.getElementById('formViewport');

            const panels = {
                admin: formAdmin,
                ortu: formOrtu,
            };

            let switchTimer = null;
            const transitionDelay = 350;

            const setViewportHeight = (panel) => {
                const height = panel ? panel.offsetHeight : 0;
                viewport.style.setProperty('--form-height', `${height}px`);
            };

            const switchRole = (role) => {
                if (switchTimer) {
                    clearTimeout(switchTimer);
                    switchTimer = null;
                }
                buttons.forEach((button) => {
                    button.classList.toggle('active', button.dataset.role === role);
                });

                const current = document.querySelector('.form-panel.active');
                const target = panels[role];
                if (!target) {
                    return;
                }

                if (current) {
                    current.classList.add('collapsing');
                    setViewportHeight(current);
                    requestAnimationFrame(() => {
                        setViewportHeight(null);
                    });
                    setTimeout(() => {
                        current.classList.remove('active', 'collapsing');
                        current.classList.add('hidden');
                    }, transitionDelay);
                }

                switchTimer = setTimeout(() => {
                    Object.values(panels).forEach((panel) => {
                        panel.classList.remove('active', 'collapsing');
                        panel.classList.add('hidden');
                    });

                    target.classList.remove('hidden');
                    target.classList.add('active');
                    setViewportHeight(target);
                }, transitionDelay);
            };

            window.addEventListener('load', () => setViewportHeight(null));
            const initialRole = @json($errors->has('nisn') || old('nisn')) ? 'ortu' : null;
            if (initialRole) {
                const target = panels[initialRole];
                if (target) {
                    buttons.forEach((button) => {
                        button.classList.toggle('active', button.dataset.role === initialRole);
                    });
                    target.classList.remove('hidden');
                    target.classList.add('active');
                    setViewportHeight(target);
                }
            }
            window.addEventListener('resize', () => {
                const active = document.querySelector('.form-panel.active');
                setViewportHeight(active);
            });

            buttons.forEach((button) => {
                button.addEventListener('click', () => switchRole(button.dataset.role));
            });

            const nisnInput = document.getElementById('nisn');
            if (nisnInput) {
                nisnInput.addEventListener('input', (event) => {
                    const target = event.target;
                    target.value = target.value.replace(/\D/g, '').slice(0, 20);
                });
            }
        </script>
    </body>
</html>
