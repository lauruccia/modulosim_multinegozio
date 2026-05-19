@extends('wizard.layout')

@section('content')
<h1>Concludi ordine</h1>

@php
    $metodo = $data['pagamento']['metodo'] ?? 'carta';
@endphp

<div style="display:grid; grid-template-columns:2fr 1fr; gap:30px;">
    <div>
        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:24px; margin-bottom:20px;">
            <h2 style="margin-top:0;">Riepilogo cliente</h2>
            <p><strong>Nome:</strong> {{ $data['dati']['nome'] ?? '' }}</p>
            <p><strong>Cognome:</strong> {{ $data['dati']['cognome'] ?? '' }}</p>
            <p><strong>Codice fiscale:</strong> {{ $data['dati']['codice_fiscale'] ?? '' }}</p>
            <p><strong>Email:</strong> {{ $data['contatti']['email'] ?? '' }}</p>
            <p><strong>Cellulare:</strong> {{ $data['contatti']['cellulare'] ?? '' }}</p>
        </div>

        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:24px;">
            <h2 style="margin-top:0;">Riepilogo servizi</h2>
            <p><strong>Numero:</strong> {{ ($data['numero']['scelta'] ?? '') === 'portabilita' ? 'Mantengo il mio numero' : 'Nuovo numero' }}</p>

            <p>
                <strong>Metodo di pagamento:</strong>
                @if($metodo === 'carta')
                    Carta di credito
                @elseif($metodo === 'negozio')
                    Paga in negozio
                @else
                    Paga dopo
                @endif
            </p>

            <ul>
                <li>Piano base: € 14,85</li>
                @if(!empty($data['servizi']['opzione_5g']))
                    <li>Opzione 5G: € 1,95</li>
                @endif
                @if(!empty($data['servizi']['safe_call']))
                    <li>SafeCall: € 0,95</li>
                @endif
                @if(!empty($data['servizi']['total_security']))
                    <li>Total Security: € 1,95</li>
                @endif
            </ul>
        </div>
    </div>

    <div>
        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:24px;">
            <h2 style="margin-top:0;">Totale da pagare</h2>
            <div style="font-size:42px; font-weight:700; color:#2ca9e1; margin:20px 0;">
                € {{ number_format($totale, 2, ',', '.') }}
            </div>

            @if($metodo === 'carta')
                <form method="POST" action="{{ route('wizard.pay') }}">
                    @csrf
                    <button type="submit" class="btn btn-next" style="width:100%;">
                        Paga con carta
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('wizard.completeOffline') }}">
                    @csrf
                    <button type="submit" class="btn btn-next" style="width:100%;">
                        Concludi ordine
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

<div class="actions">
    <a href="{{ route('wizard.show', 'pagamento') }}" class="btn btn-back">Indietro</a>
</div>
@endsection
