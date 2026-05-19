@extends('wizard.layout')

@section('content')
<h1>Scegli il tuo numero</h1>

<form method="POST" action="{{ route('wizard.store', 'numero') }}">
    @csrf

    <div style="display:flex; flex-direction:column; gap:20px; max-width:700px; margin:0 auto;">
        <label style="display:block; border:1px solid #ddd; border-radius:16px; padding:24px; cursor:pointer; background:#fff;">
            <input type="radio" name="scelta" value="nuovo"
                {{ old('scelta', $data['numero']['scelta'] ?? 'nuovo') === 'nuovo' ? 'checked' : '' }}>
            <strong style="font-size:22px;">Voglio un nuovo numero</strong><br>
            <span style="color:#6b7280;">Attiva un nuovo numero</span>
        </label>

        <label style="display:block; border:1px solid #ddd; border-radius:16px; padding:24px; cursor:pointer; background:#fff;">
            <input type="radio" name="scelta" value="portabilita"
                {{ old('scelta', $data['numero']['scelta'] ?? '') === 'portabilita' ? 'checked' : '' }}>
            <strong style="font-size:22px;">Voglio mantenere il mio numero</strong><br>
            <span style="color:#6b7280;">Passa da altro operatore mantenendo il tuo numero</span>
        </label>

        @error('scelta')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>

    <div class="actions">
        <a href="{{ route('wizard.show', 'indirizzi') }}" class="btn btn-back">Indietro</a>
        <button type="submit" class="btn btn-next">Avanti</button>
    </div>
</form>
@endsection
