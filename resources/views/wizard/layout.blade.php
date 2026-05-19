<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Titolo dinamico --}}
    <title>
        @if($store)
            {{ $store->display_name }} &mdash; {{ $store->customText('wizard_title', 'Attivazione') }}
        @else
            Sharers &mdash; Attivazione
        @endif
    </title>

    {{-- Favicon personalizzato --}}
    @if($store?->favicon_url)
        <link rel="icon" type="image/x-icon" href="{{ $store->favicon_url }}">
    @endif

    {{-- Google Fonts (solo se font non di sistema) --}}
    @if($store?->google_font_url)
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="{{ $store->google_font_url }}" rel="stylesheet">
    @endif

    {{-- CSS Variables di branding --}}
    @if($store)
        {!! $store->branding_css !!}
    @else
        <style>
            :root {
                --brand-primary:      #dddc00;
                --brand-primary-dark: #b8b700;
                --brand-secondary:    #1d1d1b;
                --brand-font:         'Arial', sans-serif;
            }
        </style>
    @endif

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: var(--brand-font);
            background: #f3f3f5;
            color: #1f2937;
        }

        /* ── Topbar ── */
        .topbar {
            background: #fff;
            padding: 18px 40px;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
            display: flex;
            align-items: center;
        }

        .logo-link {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .logo-img {
            display: block;
            height: 48px;
            width: auto;
            max-width: 220px;
            object-fit: contain;
        }

        .logo-text {
            font-size: 22px;
            font-weight: 700;
            color: var(--brand-secondary);
            letter-spacing: -.5px;
        }

        /* ── Container ── */
        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* ── Steps indicator ── */
        .steps {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 28px;
            flex-wrap: wrap;
        }

        .step {
            width: 36px;
            height: 36px;
            border-radius: 999px;
            border: 2px solid #d1d5db;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            color: #9ca3af;
            font-weight: 700;
            font-size: 14px;
            transition: all .2s;
        }

        .step.active {
            border-color: var(--brand-primary);
            background: var(--brand-primary);
            color: var(--brand-secondary);
        }

        /* ── Card ── */
        .card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 6px 25px rgba(0,0,0,.06);
            padding: 40px;
        }

        /* ── Typography ── */
        h1 {
            text-align: center;
            margin-bottom: 32px;
            font-size: 32px;
            font-weight: 700;
        }

        /* ── Form ── */
        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .field {
            display: flex;
            flex-direction: column;
        }

        .field label {
            font-size: 13px;
            margin-bottom: 8px;
            color: #6b7280;
            font-weight: 500;
        }

        .field input,
        .field select {
            height: 48px;
            border: none;
            border-bottom: 2px solid #d1d5db;
            background: transparent;
            font-size: 16px;
            font-family: var(--brand-font);
            outline: none;
            color: #1f2937;
            transition: border-color .2s;
        }

        .field input:focus,
        .field select:focus {
            border-bottom-color: var(--brand-primary);
        }

        .full { grid-column: 1 / -1; }

        /* ── Checkboxes ── */
        .checks {
            margin-top: 28px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .check-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 14px;
            line-height: 1.5;
        }

        .check-row input[type="checkbox"] {
            accent-color: var(--brand-primary);
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        /* ── Buttons ── */
        .actions {
            margin-top: 36px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            border: none;
            border-radius: 999px;
            padding: 14px 28px;
            font-size: 15px;
            font-family: var(--brand-font);
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: all .18s;
        }

        .btn-back {
            background: #fff;
            color: #6b7280;
            border: 1.5px solid #d1d5db;
        }

        .btn-back:hover {
            border-color: #9ca3af;
            color: #374151;
        }

        .btn-next {
            background: var(--brand-primary);
            color: var(--brand-secondary);
        }

        .btn-next:hover {
            background: var(--brand-primary-dark);
        }

        /* ── Validation errors ── */
        .error {
            color: #dc2626;
            font-size: 12px;
            margin-top: 5px;
        }

        /* ── Alert ── */
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 14px 18px;
            color: #dc2626;
            font-size: 14px;
            margin-bottom: 20px;
        }

        /* ── Footer ── */
        .wizard-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 12px;
            color: #9ca3af;
        }

        /* ── Responsive ── */
        @media (max-width: 900px) {
            .grid { grid-template-columns: 1fr; }
            h1 { font-size: 24px; }
            .card { padding: 24px; }
            .topbar { padding: 14px 20px; }
        }
    </style>
</head>
<body>

    {{-- ── Topbar con branding dinamico ── --}}
    <div class="topbar">
        <a class="logo-link" href="{{ url('/') }}" aria-label="{{ $store?->display_name ?? 'Sharers' }}">

            @if($store?->logo_url)
                {{-- Logo custom del negozio --}}
                <img class="logo-img" src="{{ $store->logo_url }}" alt="{{ $store->display_name }}">

            @elseif($store?->custom_name)
                {{-- Nessun logo ma nome custom: mostra il nome in stile testo --}}
                <span class="logo-text">{{ $store->display_name }}</span>

            @else
                {{-- Fallback: logo Sharers --}}
                <img class="logo-img" src="{{ asset('images/sharers-logo.jpg') }}" alt="Sharers">
            @endif

        </a>
    </div>

    {{-- ── Corpo pagina ── --}}
    <div class="container">

        {{-- Step indicator --}}
        <div class="steps">
            @foreach($steps as $i => $s)
                @php
                    $currentIndex = array_search($step, $steps);
                    $stepIndex    = array_search($s, $steps);
                @endphp
                <div class="step {{ $stepIndex <= $currentIndex ? 'active' : '' }}">{{ $i + 1 }}</div>
            @endforeach
        </div>

        <div class="card">
            @yield('content')
        </div>

        {{-- Footer con testo personalizzabile --}}
        <div class="wizard-footer">
            @if($store)
                {{ $store->customText('footer_text', $store->display_name . ' — Powered by Sharers') }}
            @else
                Sharers &mdash; Attivazione servizi
            @endif
        </div>

    </div>

</body>
</html>
