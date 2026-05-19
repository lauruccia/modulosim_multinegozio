<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        @if($store){{ $store->display_name }} &mdash; @endif
        Stato pratica #{{ $submission->id }}
    </title>

    {{-- Favicon personalizzato --}}
    @if($store?->favicon_url)
        <link rel="icon" type="image/x-icon" href="{{ $store->favicon_url }}">
    @endif

    {{-- Google Fonts --}}
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
            padding: 16px 40px;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
        }

        .logo-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .logo-img {
            height: 42px;
            width: auto;
            max-width: 200px;
            object-fit: contain;
        }

        .logo-text {
            font-size: 20px;
            font-weight: 700;
            color: var(--brand-secondary);
        }

        /* ── Layout ── */
        .container {
            max-width: 700px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* ── Hero status card ── */
        .status-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 6px 25px rgba(0,0,0,.06);
            padding: 36px;
            margin-bottom: 24px;
            text-align: center;
        }

        .status-icon {
            font-size: 52px;
            margin-bottom: 12px;
            line-height: 1;
        }

        .status-label {
            display: inline-block;
            padding: 6px 20px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 15px;
            margin-bottom: 14px;
        }

        .status-attivata   { background: #dcfce7; color: #166534; }
        .status-in_lavorazione { background: #ede9fe; color: #5b21b6; }
        .status-richiesta  { background: #dbeafe; color: #1d4ed8; }
        .status-respinta   { background: #fee2e2; color: #991b1b; }
        .status-annullata  { background: #f3f4f6; color: #374151; }

        .status-title {
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 6px;
        }

        .status-subtitle {
            color: #6b7280;
            font-size: 15px;
            margin: 0;
        }

        /* ── Info box ── */
        .info-box {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,.05);
            padding: 24px 28px;
            margin-bottom: 24px;
        }

        .info-box h2 {
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #9ca3af;
            margin: 0 0 16px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
        }

        .info-row:last-child { border-bottom: none; }

        .info-row .label { color: #6b7280; }
        .info-row .value { font-weight: 600; color: #111827; }

        /* ── Timeline ── */
        .timeline-box {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,.05);
            padding: 24px 28px;
        }

        .timeline-box h2 {
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #9ca3af;
            margin: 0 0 20px;
        }

        .timeline {
            position: relative;
            padding-left: 28px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 7px;
            top: 8px;
            bottom: 8px;
            width: 2px;
            background: #e5e7eb;
        }

        .tl-item {
            position: relative;
            margin-bottom: 24px;
        }

        .tl-item:last-child { margin-bottom: 0; }

        .tl-dot {
            position: absolute;
            left: -25px;
            top: 4px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2.5px solid #fff;
            box-shadow: 0 0 0 2px currentColor;
        }

        .tl-item:last-child .tl-dot {
            box-shadow: 0 0 0 2px var(--brand-primary);
            background: var(--brand-primary) !important;
        }

        .tl-title {
            font-weight: 600;
            font-size: 15px;
            color: #111827;
            margin: 0 0 4px;
        }

        .tl-desc {
            font-size: 13px;
            color: #6b7280;
            margin: 0 0 4px;
            line-height: 1.5;
        }

        .tl-date {
            font-size: 12px;
            color: #9ca3af;
        }

        /* ── Empty state ── */
        .empty {
            text-align: center;
            color: #9ca3af;
            padding: 20px 0;
            font-size: 14px;
        }

        /* ── Footer ── */
        .page-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 12px;
            color: #9ca3af;
            padding-bottom: 40px;
        }

        /* ── Responsive ── */
        @media (max-width: 600px) {
            .topbar { padding: 14px 20px; }
            .status-card { padding: 24px 20px; }
            .info-box, .timeline-box { padding: 20px; }
        }
    </style>
</head>
<body>

    {{-- Topbar --}}
    <div class="topbar">
        <a class="logo-link" href="{{ url('/') }}">
            @if($store?->logo_url)
                <img class="logo-img" src="{{ $store->logo_url }}" alt="{{ $store->display_name }}">
            @elseif($store?->custom_name)
                <span class="logo-text">{{ $store->display_name }}</span>
            @else
                <img class="logo-img" src="{{ asset('images/sharers-logo.jpg') }}" alt="Sharers">
            @endif
        </a>
    </div>

    <div class="container">

        {{-- ── Hero: stato attuale ── --}}
        @php
            $statusIcons = [
                'richiesta'      => '📬',
                'in_lavorazione' => '⚙️',
                'attivata'       => '✅',
                'respinta'       => '❌',
                'annullata'      => '🚫',
            ];
            $statusIcon = $statusIcons[$submission->activation_status] ?? '📋';
        @endphp

        <div class="status-card">
            <div class="status-icon">{{ $statusIcon }}</div>

            <div class="status-label status-{{ $submission->activation_status }}">
                {{ $submission->activation_status_label }}
            </div>

            <h1 class="status-title">
                Ciao, {{ data_get($submission->payload, 'dati.nome', $submission->customer_name) }}!
            </h1>
            <p class="status-subtitle">
                Pratica #{{ $submission->id }} &mdash; Ricevuta il {{ $submission->created_at->format('d/m/Y \a\l\l\e H:i') }}
            </p>
        </div>

        {{-- ── Riepilogo pratica ── --}}
        <div class="info-box">
            <h2>Riepilogo</h2>

            <div class="info-row">
                <span class="label">Servizio</span>
                <span class="value">
                    {{ \App\Models\CommissionRule::serviceOptions()[$submission->service_type] ?? ucfirst($submission->service_type) }}
                </span>
            </div>

            @if($submission->customer_phone)
            <div class="info-row">
                <span class="label">Telefono</span>
                <span class="value">{{ $submission->customer_phone }}</span>
            </div>
            @endif

            <div class="info-row">
                <span class="label">Metodo di pagamento</span>
                <span class="value">
                    {{ match($submission->payment_method) {
                        'carta'  => 'Carta di credito',
                        'negozio' => 'In negozio',
                        'dopo'   => 'Pagamento differito',
                        default  => ucfirst($submission->payment_method),
                    } }}
                </span>
            </div>

            <div class="info-row">
                <span class="label">Importo totale</span>
                <span class="value">€ {{ number_format((float)$submission->total_amount, 2, ',', '.') }}</span>
            </div>

            @if($submission->activated_at)
            <div class="info-row">
                <span class="label">Data attivazione</span>
                <span class="value">{{ $submission->activated_at->format('d/m/Y') }}</span>
            </div>
            @endif
        </div>

        {{-- ── Timeline eventi ── --}}
        <div class="timeline-box">
            <h2>Storico pratica</h2>

            @if($events->isEmpty())
                <div class="empty">Nessun aggiornamento disponibile al momento.</div>
            @else
                <div class="timeline">
                    @foreach($events as $event)
                        <div class="tl-item">
                            <div class="tl-dot" style="background: {{ $event->color }}; color: {{ $event->color }};"></div>
                            <div class="tl-title">{{ $event->title }}</div>
                            @if($event->description)
                                <div class="tl-desc">{{ $event->description }}</div>
                            @endif
                            <div class="tl-date">{{ $event->occurred_at->format('d/m/Y \a\l\l\e H:i') }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Footer --}}
        <div class="page-footer">
            @if($store)
                {{ $store->customText('footer_text', $store->display_name . ' — Powered by Sharers') }}
            @else
                Sharers &mdash; Tracking pratica
            @endif
        </div>

    </div>
</body>
</html>
