<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Conferma richiesta</title>
</head>
<body style="margin:0; padding:0; background:#f3f3f5; font-family:Arial,sans-serif; color:#1f2937;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f3f5; padding:40px 20px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%;">

    {{-- Header --}}
    <tr>
        <td style="background:#fff; border-radius:16px 16px 0 0; padding:28px 36px; border-bottom:1px solid #f0f0f0;">
            @if($submission->store?->logo_url)
                <img src="{{ $submission->store->logo_url }}"
                     alt="{{ $submission->store->display_name }}"
                     style="height:42px; width:auto; max-width:180px; object-fit:contain;">
            @else
                <span style="font-size:20px; font-weight:700; color:#1d1d1b;">
                    {{ $submission->store?->display_name ?? 'Sharers' }}
                </span>
            @endif
        </td>
    </tr>

    {{-- Body --}}
    <tr>
        <td style="background:#fff; padding:32px 36px;">

            <h2 style="margin:0 0 20px; font-size:22px; color:#111827;">
                Richiesta ricevuta!
            </h2>

            <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                Ciao <strong>{{ data_get($submission->payload, 'dati.nome', $submission->customer_name) }}</strong>,
            </p>
            <p style="margin:0 0 24px; font-size:15px; line-height:1.6; color:#374151;">
                la tua richiesta è stata registrata correttamente nel nostro sistema.
                Il nostro team la prenderà in carico al più presto.
            </p>

            {{-- Riepilogo --}}
            <table width="100%" cellpadding="0" cellspacing="0"
                   style="background:#f9fafb; border-radius:10px; padding:20px; margin-bottom:24px;">
                <tr>
                    <td style="font-size:13px; font-weight:600; text-transform:uppercase;
                               letter-spacing:.06em; color:#9ca3af; padding-bottom:14px;">
                        Riepilogo pratica
                    </td>
                </tr>
                <tr>
                    <td style="font-size:14px; padding:6px 0; border-bottom:1px solid #e5e7eb; color:#6b7280;">
                        Pratica n°
                    </td>
                    <td style="font-size:14px; padding:6px 0; border-bottom:1px solid #e5e7eb;
                               text-align:right; font-weight:600; color:#111827;">
                        #{{ $submission->id }}
                    </td>
                </tr>
                <tr>
                    <td style="font-size:14px; padding:6px 0; border-bottom:1px solid #e5e7eb; color:#6b7280;">
                        Servizio
                    </td>
                    <td style="font-size:14px; padding:6px 0; border-bottom:1px solid #e5e7eb;
                               text-align:right; font-weight:600; color:#111827;">
                        {{ \App\Models\CommissionRule::serviceOptions()[$submission->service_type] ?? ucfirst($submission->service_type) }}
                    </td>
                </tr>
                <tr>
                    <td style="font-size:14px; padding:6px 0; border-bottom:1px solid #e5e7eb; color:#6b7280;">
                        Totale
                    </td>
                    <td style="font-size:14px; padding:6px 0; border-bottom:1px solid #e5e7eb;
                               text-align:right; font-weight:600; color:#111827;">
                        € {{ number_format((float)$submission->total_amount, 2, ',', '.') }}
                    </td>
                </tr>
                <tr>
                    <td style="font-size:14px; padding:6px 0; color:#6b7280;">
                        Pagamento
                    </td>
                    <td style="font-size:14px; padding:6px 0; text-align:right; font-weight:600; color:#111827;">
                        @if($submission->payment_method === 'carta')
                            Carta &mdash; confermato
                        @elseif($submission->payment_method === 'negozio')
                            In negozio
                        @else
                            Da definire
                        @endif
                    </td>
                </tr>
            </table>

            {{-- CTA Tracking --}}
            @if($submission->tracking_url)
            <div style="text-align:center; margin-bottom:28px;">
                <p style="font-size:14px; color:#374151; margin:0 0 14px;">
                    Puoi seguire lo stato della tua pratica in tempo reale:
                </p>
                <a href="{{ $submission->tracking_url }}"
                   style="display:inline-block; background:#dddc00; color:#1d1d1b;
                          font-weight:700; font-size:15px; border-radius:999px;
                          padding:14px 32px; text-decoration:none;">
                    Traccia la tua pratica
                </a>
                <p style="font-size:12px; color:#9ca3af; margin:10px 0 0;">
                    oppure copia questo link: <br>
                    <a href="{{ $submission->tracking_url }}" style="color:#6b7280; word-break:break-all;">
                        {{ $submission->tracking_url }}
                    </a>
                </p>
            </div>
            @endif

            <p style="font-size:14px; line-height:1.6; color:#374151; margin:0;">
                Se hai domande o necessiti di assistenza, rispondi a questa email o contatta
                il punto vendita di riferimento.
            </p>

        </td>
    </tr>

    {{-- Footer --}}
    <tr>
        <td style="background:#f9fafb; border-radius:0 0 16px 16px; padding:20px 36px;
                   text-align:center; font-size:12px; color:#9ca3af; border-top:1px solid #f0f0f0;">
            {{ $submission->store?->display_name ?? 'Sharers' }}
            &mdash; Powered by Sharers
            <br>
            Questa email è stata generata automaticamente, non rispondere se non necessario.
        </td>
    </tr>

</table>
</td></tr>
</table>

</body>
</html>
