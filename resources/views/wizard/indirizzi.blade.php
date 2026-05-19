@extends('wizard.layout')

@section('content')
<h1>Dove possiamo inviare la tua nuova SIM?</h1>

@php
    $residenzaDiversa = old('residenza_diversa', $data['indirizzi']['residenza_diversa'] ?? false);
@endphp

<form method="POST" action="{{ route('wizard.store', 'indirizzi') }}">
    @csrf

    <div class="grid">
        <div class="field full">
            <label>Destinatario</label>
            <input type="text" name="spedizione[destinatario]" value="{{ old('spedizione.destinatario', $data['indirizzi']['spedizione']['destinatario'] ?? '') }}">
            @error('spedizione.destinatario') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label>CAP</label>
            <input type="text" name="spedizione[cap]" value="{{ old('spedizione.cap', $data['indirizzi']['spedizione']['cap'] ?? '') }}">
            @error('spedizione.cap') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label>Città</label>
            <input type="text" name="spedizione[citta]" value="{{ old('spedizione.citta', $data['indirizzi']['spedizione']['citta'] ?? '') }}">
            @error('spedizione.citta') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label>Indirizzo</label>
            <input type="text" name="spedizione[indirizzo]" value="{{ old('spedizione.indirizzo', $data['indirizzi']['spedizione']['indirizzo'] ?? '') }}">
            @error('spedizione.indirizzo') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label>Civico</label>
            <input type="text" name="spedizione[civico]" value="{{ old('spedizione.civico', $data['indirizzi']['spedizione']['civico'] ?? '') }}">
            @error('spedizione.civico') <div class="error">{{ $message }}</div> @enderror
        </div>
    </div>

    <div style="margin-top: 30px;">
        <label style="display:flex; align-items:center; gap:10px;">
            <input
                type="checkbox"
                id="residenza_diversa"
                name="residenza_diversa"
                value="1"
                {{ $residenzaDiversa ? 'checked' : '' }}
            >
            L'indirizzo di residenza è diverso da quello di spedizione
        </label>
    </div>

    <div
        id="residenza_section"
        style="margin-top: 35px; padding-top: 20px; border-top: 1px solid #e5e7eb; {{ $residenzaDiversa ? '' : 'display:none;' }}"
    >
        <h2 style="font-size: 24px; margin-bottom: 20px;">Indirizzo di residenza</h2>

        <div class="grid">
            <div class="field">
                <label>CAP</label>
                <input type="text" name="residenza[cap]" value="{{ old('residenza.cap', $data['indirizzi']['residenza']['cap'] ?? '') }}">
                @error('residenza.cap') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label>Città</label>
                <input type="text" name="residenza[citta]" value="{{ old('residenza.citta', $data['indirizzi']['residenza']['citta'] ?? '') }}">
                @error('residenza.citta') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label>Indirizzo</label>
                <input type="text" name="residenza[indirizzo]" value="{{ old('residenza.indirizzo', $data['indirizzi']['residenza']['indirizzo'] ?? '') }}">
                @error('residenza.indirizzo') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label>Civico</label>
                <input type="text" name="residenza[civico]" value="{{ old('residenza.civico', $data['indirizzi']['residenza']['civico'] ?? '') }}">
                @error('residenza.civico') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <div class="actions">
        <a href="{{ route('wizard.show', 'contatti') }}" class="btn btn-back">Indietro</a>
        <button type="submit" class="btn btn-next">Avanti</button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkbox = document.getElementById('residenza_diversa');
        const section = document.getElementById('residenza_section');

        function toggleResidenza() {
            section.style.display = checkbox.checked ? 'block' : 'none';
        }

        checkbox.addEventListener('change', toggleResidenza);
        toggleResidenza();
    });
</script>
@endsection
