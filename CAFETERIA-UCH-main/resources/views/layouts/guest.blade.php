<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Cafetería UCH') }} · Acceso</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/uch-isotipo.svg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .guest-wrap {
            min-height: 100vh;
            display: flex;
            font-family: 'DM Sans', system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        /* Panel izquierdo — imagen de fondo */
        .guest-side {
            display: none;
            position: relative;
            flex: 1;
            background: #1C1C1C;
            overflow: hidden;
        }
        @media (min-width: 900px) { .guest-side { display: flex; flex-direction: column; justify-content: flex-end; } }
        .guest-side-bg {
            position: absolute; inset: 0;
            background-image: url('https://images.unsplash.com/photo-1567521464027-f127ff144326?w=1200&q=70');
            background-size: cover;
            background-position: center;
            opacity: 0.35;
        }
        .guest-side-content {
            position: relative; z-index: 1;
            padding: 2.5rem 2.5rem 3rem;
            color: #fff;
        }
        .guest-side-logo {
            display: flex; align-items: center; gap: 0.65rem;
            margin-bottom: 2rem;
        }
        .guest-side-logo-icon {
            width: 38px; height: 38px;
            background: var(--hasp-brand, #C8511B);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
        }
        .guest-side-logo-text strong {
            display: block;
            font-size: 0.9rem;
            font-weight: 700;
            letter-spacing: -0.01em;
        }
        .guest-side-logo-text span {
            font-size: 0.65rem;
            font-weight: 400;
            opacity: 0.7;
        }
        .guest-side-heading {
            font-family: 'DM Serif Display', serif;
            font-size: 2.2rem;
            font-weight: 400;
            line-height: 1.1;
            margin-bottom: 1rem;
        }
        .guest-side-heading em { font-style: italic; color: #F97316; }
        .guest-side-desc {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.6);
            line-height: 1.6;
            max-width: 320px;
        }
        .guest-side-chips {
            display: flex; flex-wrap: wrap; gap: 0.5rem;
            margin-top: 1.75rem;
        }
        .guest-side-chip {
            display: inline-flex; align-items: center; gap: 0.3rem;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 999px;
            padding: 0.28rem 0.75rem;
            font-size: 0.72rem;
            color: rgba(255,255,255,0.75);
        }

        /* Panel derecho — formulario */
        .guest-form-side {
            width: 100%;
            max-width: 460px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 2.5rem 2rem;
            background: var(--hasp-bg, #F9F9F7);
        }
        @media (min-width: 900px) { .guest-form-side { padding: 3rem 3rem; } }
        .guest-form-header { margin-bottom: 2rem; }
        .guest-form-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--hasp-text, #1C1C1C);
            margin-bottom: 0.3rem;
        }
        .guest-form-header p {
            font-size: 0.85rem;
            color: var(--hasp-muted, #6E6E6E);
        }
        .guest-card {
            background: #fff;
            border: 1px solid var(--hasp-border, #EBEBEB);
            border-radius: 16px;
            padding: 1.75rem;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
        }
        /* Override Tailwind form inputs dentro del guest layout */
        .guest-card input[type="email"],
        .guest-card input[type="text"],
        .guest-card input[type="password"] {
            border-radius: 8px !important;
            border-color: var(--hasp-border, #EBEBEB) !important;
            font-family: 'DM Sans', system-ui, sans-serif !important;
            font-size: 0.875rem !important;
        }
        .guest-card input:focus {
            border-color: var(--hasp-brand, #C8511B) !important;
            box-shadow: 0 0 0 2px rgba(200,81,27,0.12) !important;
            outline: none !important;
        }
        .guest-card label {
            font-size: 0.8rem !important;
            font-weight: 600 !important;
            color: var(--hasp-text, #1C1C1C) !important;
            font-family: 'DM Sans', system-ui, sans-serif !important;
        }
        .guest-card .guest-submit {
            width: 100%;
            background: var(--hasp-brand, #C8511B);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            font-family: 'DM Sans', system-ui, sans-serif;
            transition: opacity 0.15s;
            margin-top: 1.25rem;
        }
        .guest-card .guest-submit:hover { opacity: 0.88; }
        .guest-divider {
            display: flex; align-items: center; gap: 0.75rem;
            margin: 1.25rem 0;
            font-size: 0.72rem;
            color: var(--hasp-subtle, #ABABAB);
            font-weight: 500;
        }
        .guest-divider::before, .guest-divider::after {
            content: ''; flex: 1;
            height: 1px; background: var(--hasp-border, #EBEBEB);
        }
        .guest-google-btn {
            display: flex; align-items: center; justify-content: center; gap: 0.6rem;
            width: 100%;
            border: 1px solid var(--hasp-border, #EBEBEB);
            border-radius: 8px;
            padding: 0.65rem;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--hasp-text, #1C1C1C);
            background: #fff;
            cursor: pointer;
            font-family: 'DM Sans', system-ui, sans-serif;
            text-decoration: none;
            transition: background 0.15s;
        }
        .guest-google-btn:hover { background: var(--hasp-bg, #F9F9F7); }
        .guest-footer-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.82rem;
            color: var(--hasp-muted, #6E6E6E);
        }
        .guest-footer-link a {
            color: var(--hasp-brand, #C8511B);
            font-weight: 600;
            text-decoration: none;
        }
        .guest-forgot {
            font-size: 0.78rem;
            color: var(--hasp-muted, #6E6E6E);
            text-decoration: none;
        }
        .guest-forgot:hover { color: var(--hasp-brand, #C8511B); }
        .guest-remember {
            display: flex; align-items: center; gap: 0.5rem;
            font-size: 0.8rem;
            color: var(--hasp-muted, #6E6E6E);
        }
        .guest-remember input[type="checkbox"] {
            accent-color: var(--hasp-brand, #C8511B);
            width: 15px; height: 15px;
        }
    </style>
</head>
<body>
<div class="guest-wrap">

    <!-- Lado izquierdo decorativo -->
    <div class="guest-side">
        <div class="guest-side-bg"></div>
        <div class="guest-side-content">
            <div class="guest-side-logo">
                <div class="guest-side-logo-icon">☕</div>
                <div class="guest-side-logo-text">
                    <strong>GRUPO HASP SERVICE</strong>
                    <span>Cafetería UCH</span>
                </div>
            </div>
            <h2 class="guest-side-heading">Tu comedor<br><em>universitario</em></h2>
            <p class="guest-side-desc">Pide tu almuerzo en línea, elige tu menú del día y recógelo en caja sin hacer cola.</p>
            <div class="guest-side-chips">
                <span class="guest-side-chip">🕐 Lun–Vie 7am–5pm · Sáb 7am–3pm</span>
                <span class="guest-side-chip">📍 Comedor UCH · Pabellón C 1er Piso</span>
                <span class="guest-side-chip">💳 Efectivo, Tarjeta, Yape, Plin</span>
            </div>
        </div>
    </div>

    <!-- Lado derecho — formulario -->
    <div class="guest-form-side">
        {{ $slot }}
    </div>

</div>
</body>
</html>
