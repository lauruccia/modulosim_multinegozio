@extends('wizard.layout')

@section('content')
<h1>Inserisci i dati dell’intestatario della SIM</h1>

<form method="POST" action="{{ route('wizard.store', 'dati') }}">
    @csrf

    <div class="grid">
        <div class="field">
            <label>Nome</label>
            <input type="text" name="nome" value="{{ old('nome', data_get($data, 'dati.nome')) }}">
            @error('nome') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label>Cognome</label>
            <input type="text" name="cognome" value="{{ old('cognome', data_get($data, 'dati.cognome')) }}">
            @error('cognome') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label>Codice fiscale</label>
            <input type="text" name="codice_fiscale" value="{{ old('codice_fiscale', data_get($data, 'dati.codice_fiscale')) }}">
            @error('codice_fiscale') <div class="error">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="checks">
        <label>
            <input type="checkbox" name="consensi[marketing]" value="1"
                {{ old('consensi.marketing', data_get($data, 'dati.consensi.marketing')) ? 'checked' : '' }}>
            Desidero ricevere aggiornamenti su offerte esclusive e promozioni.
        </label>

        <label>
            <input type="checkbox" name="consensi[accetta_condizioni]" value="1"
                {{ old('consensi.accetta_condizioni', data_get($data, 'dati.consensi.accetta_condizioni')) ? 'checked' : '' }}>
            Ho letto e accetto le condizioni generali di contratto, la sintesi contrattuale e l'informativa privacy.
        </label>
        @error('consensi.accetta_condizioni') <div class="error">{{ $message }}</div> @enderror

        <label>
            <input type="checkbox" name="consensi[attivazione_immediata]" value="1"
                {{ old('consensi.attivazione_immediata', data_get($data, 'dati.consensi.attivazione_immediata')) ? 'checked' : '' }}>
            Chiedo l’attivazione immediata del servizio.
        </label>
        @error('consensi.attivazione_immediata') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div class="actions">
        <span></span>
        <button type="submit" class="btn btn-next">Avanti</button>
    </div>
</form>
@endsection
