@extends('wizard.layout')

@section('content')
<h1>Aggiungi altri servizi</h1>

<form method="POST" action="{{ route('wizard.store', 'servizi') }}">
    @csrf

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">
        <label style="border:1px solid #ddd; border-radius:16px; padding:24px; display:block; cursor:pointer;">
            <input type="checkbox" name="opzione_5g" value="1"
                {{ old('opzione_5g', $data['servizi']['opzione_5g'] ?? '') ? 'checked' : '' }}>
            <div style="font-size:24px; font-weight:700; margin-top:12px;">Opzione 5G</div>
            <div style="color:#6b7280;">+ 1,95 € / mese</div>
        </label>

        <label style="border:1px solid #ddd; border-radius:16px; padding:24px; display:block; cursor:pointer;">
            <input type="checkbox" name="ricarica_automatica" value="1"
                {{ old('ricarica_automatica', $data['servizi']['ricarica_automatica'] ?? '') ? 'checked' : '' }}>
            <div style="font-size:24px; font-weight:700; margin-top:12px;">Ricarica automatica</div>
            <div style="color:#6b7280;">Addebito automatico del rinnovo</div>
        </label>

        <label style="border:1px solid #ddd; border-radius:16px; padding:24px; display:block; cursor:pointer;">
            <input type="checkbox" name="safe_call" value="1"
                {{ old('safe_call', $data['servizi']['safe_call'] ?? '') ? 'checked' : '' }}>
            <div style="font-size:24px; font-weight:700; margin-top:12px;">SafeCall</div>
            <div style="color:#6b7280;">+ 0,95 € / mese</div>
        </label>

        <label style="border:1px solid #ddd; border-radius:16px; padding:24px; display:block; cursor:pointer;">
            <input type="checkbox" name="total_security" value="1"
                {{ old('total_security', $data['servizi']['total_security'] ?? '') ? 'checked' : '' }}>
            <div style="font-size:24px; font-weight:700; margin-top:12px;">Total Security</div>
            <div style="color:#6b7280;">+ 1,95 € / mese</div>
        </label>
    </div>

    <div class="actions">
        <a href="{{ route('wizard.show', 'numero') }}" class="btn btn-back">Indietro</a>
        <button type="submit" class="btn btn-next">Avanti</button>
    </div>
</form>
@endsection
