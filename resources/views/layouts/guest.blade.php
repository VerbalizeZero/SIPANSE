<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

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

            .auth-page {
                min-height: 100vh;
                display: grid;
                grid-template-columns: minmax(0, 1.3fr) minmax(0, 1fr);
            }

            .auth-hero {
                position: relative;
                overflow: hidden;
                background: #e9f0fb;
            }

            .auth-hero-image {
                position: absolute;
                inset: 0;
                background-image: url("{{ asset('images/landing/WhatsApp-Image-2021-05-03-at-11.36.19-PM-2.jpeg') }}");
                background-size: cover;
                background-position: center;
            }

            .auth-hero-overlay {
                position: absolute;
                inset: 0;
                background: linear-gradient(180deg, rgba(10, 25, 51, 0.05) 0%, rgba(10, 25, 51, 0.6) 100%);
            }

            .auth-hero-caption {
                position: absolute;
                left: 48px;
                bottom: 36px;
                color: #ffffff;
                text-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
            }

            .auth-hero-caption h1 {
                margin: 0 0 6px;
                font-size: 28px;
                letter-spacing: 0.3px;
            }

            .auth-hero-caption p {
                margin: 0;
                font-size: 14px;
                opacity: 0.9;
            }

            .auth-panel {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 48px 32px;
                background: radial-gradient(circle at top, #f7f9ff 0%, #ffffff 55%);
            }

            .auth-card {
                width: min(420px, 100%);
                background: var(--card);
                border: 1px solid var(--border);
                border-radius: var(--radius);
                box-shadow: var(--shadow);
                padding: 36px 32px 28px;
            }

            .auth-title {
                margin: 0 0 6px;
                font-size: 22px;
                font-weight: 700;
                color: var(--primary);
                text-align: center;
            }

            .auth-subtitle {
                margin: 0 0 22px;
                color: var(--muted);
                font-size: 13px;
                text-align: center;
            }

            .auth-back {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                color: var(--muted);
                font-size: 13px;
                text-decoration: none;
                margin-bottom: 18px;
            }

            .auth-field {
                margin-bottom: 16px;
            }

            .auth-label {
                display: block;
                font-weight: 500;
                font-size: 13px;
                margin-bottom: 6px;
                color: var(--ink);
            }

            .auth-input {
                width: 100%;
                border: 1px solid var(--border);
                border-radius: 8px;
                padding: 10px 12px;
                font-size: 13px;
                outline: none;
                transition: border-color 0.2s ease, box-shadow 0.2s ease;
                background: #ffffff;
            }

            .auth-input:focus {
                border-color: rgba(27, 58, 138, 0.6);
                box-shadow: 0 0 0 3px rgba(27, 58, 138, 0.1);
            }

            .auth-actions {
                display: flex;
                justify-content: flex-end;
                margin: 6px 0 18px;
            }

            .auth-link {
                color: var(--primary);
                font-size: 12px;
                text-decoration: none;
            }

            .auth-button {
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

            .auth-button:hover {
                background: var(--primary-strong);
                transform: translateY(-1px);
            }

            .auth-footer {
                margin-top: 18px;
                text-align: center;
                color: var(--muted);
                font-size: 11px;
            }

            .auth-error {
                margin-top: 6px;
                font-size: 12px;
                color: #c0392b;
            }

            @media (max-width: 1024px) {
                .auth-page {
                    grid-template-columns: 1fr;
                }

                .auth-hero {
                    min-height: 52vh;
                }

                .auth-hero-caption {
                    left: 28px;
                    bottom: 24px;
                }
            }

            @media (max-width: 640px) {
                .auth-panel {
                    padding: 32px 20px;
                }

                .auth-card {
                    padding: 28px 22px 22px;
                }
            }
        </style>
    </head>
    <body>
        <main class="auth-page">
            <section class="auth-hero" aria-label="Foto sekolah">
                <div class="auth-hero-image" role="img" aria-label="SMAN 15 Surabaya"></div>
                <div class="auth-hero-overlay"></div>
                <div class="auth-hero-caption">
                    <h1>SMAN 15 Surabaya</h1>
                    <p>Portal Sistem Informasi Sekolah</p>
                </div>
            </section>

            <section class="auth-panel">
                <div class="auth-card">
                    {{ $slot }}
                </div>
            </section>
        </main>
    </body>
</html>
