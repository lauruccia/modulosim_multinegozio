@extends('wizard.layout')

@section('content')
<h1>Inserisci i dati di un tuo documento</h1>

<form method="POST" action="{{ route('wizard.store', 'documento') }}">
    @csrf

    <div class="grid">
        <div class="field">
            <label>Tipologia di documento</label>
            <select name="tipo_documento">
                <option value="">Seleziona</option>
                <option value="carta_identita" {{ old('tipo_documento', data_get($data, 'documento.tipo_documento')) == 'carta_identita' ? 'selected' : '' }}>Carta d'identità</option>
                <option value="passaporto" {{ old('tipo_documento', data_get($data, 'documento.tipo_documento')) == 'passaporto' ? 'selected' : '' }}>Passaporto</option>
                <option value="patente" {{ old('tipo_documento', data_get($data, 'documento.tipo_documento')) == 'patente' ? 'selected' : '' }}>Patente</option>
            </select>
            @error('tipo_documento') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label>Numero documento</label>
            <input type="text" name="numero_documento" value="{{ old('numero_documento', data_get($data, 'documento.numero_documento')) }}">
            @error('numero_documento') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label>Data di scadenza</label>
            <input type="date" name="data_scadenza" value="{{ old('data_scadenza', data_get($data, 'documento.data_scadenza')) }}">
            @error('data_scadenza') <div class="error">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="actions">
        <a href="{{ route('wizard.show', 'dati') }}" class="btn btn-back">Indietro</a>
        <button type="submit" class="btn btn-next">Avanti</button>
    </div>
</form>
@endsection
