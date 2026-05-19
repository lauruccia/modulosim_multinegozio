@extends('wizard.layout')

@section('content')
<h1>Quale metodo di pagamento preferisci?</h1>

<form method="POST" action="{{ route('wizard.store', 'pagamento') }}">
    @csrf

    <div style="max-width:800px; margin:0 auto; display:flex; flex-direction:column; gap:20px;">
        <label style="display:block; border:1px solid #ddd; border-radius:16px; padding:24px; cursor:pointer; background:#fff;">
            <input type="radio" name="metodo" value="carta"
                {{ old('metodo', $data['pagamento']['metodo'] ?? 'carta') === 'carta' ? 'checked' : '' }}>
            <strong style="font-size:22px;">Carta di credito</strong><br>
            <span style="color:#6b7280;">Pagamento online immediato con Stripe</span>
        </label>

        <label style="display:block; border:1px solid #ddd; border-radius:16px; padding:24px; cursor:pointer; background:#fff;">
            <input type="radio" name="metodo" value="negozio"
                {{ old('metodo', $data['pagamento']['metodo'] ?? '') === 'negozio' ? 'checked' : '' }}>
            <strong style="font-size:22px;">Paga in negozio</strong><br>
            <span style="color:#6b7280;">La richiesta viene inviata e il pagamento sarà effettuato in negozio</span>
        </label>

        <label style="display:block; border:1px solid #ddd; border-radius:16px; padding:24px; cursor:pointer; background:#fff;">
            <input type="radio" name="metodo" value="dopo"
                {{ old('metodo', $data['pagamento']['metodo'] ?? '') === 'dopo' ? 'checked' : '' }}>
            <strong style="font-size:22px;">Paga dopo</strong><br>
            <span style="color:#6b7280;">Invia la richiesta ora e gestirai il pagamento successivamente</span>
        </label>

        @error('metodo')
            <div class="error">{{ $message }}</div>
        @enderror

        <div style="margin-top:10px;">
            <label>Codice amico</label>
            <input type="text" name="codice_amico" value="{{ old('codice_amico', $data['pagamento']['codice_amico'] ?? '') }}">
        </div>
    </div>

    <div class="actions">
        <a href="{{ route('wizard.show', 'servizi') }}" class="btn btn-back">Indietro</a>
        <button type="submit" class="btn btn-next">Avanti</button>
    </div>
</form>
@endsection
