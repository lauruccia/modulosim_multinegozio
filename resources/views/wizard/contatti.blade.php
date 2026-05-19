@extends('wizard.layout')

@section('content')
<h1>Inserisci la tua email e il tuo numero</h1>

<form method="POST" action="{{ route('wizard.store', 'contatti') }}">
    @csrf

    <div class="grid">
        <div class="field">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email', data_get($data, 'contatti.email')) }}">
            @error('email') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label>Conferma email</label>
            <input type="email" name="email_confirm" value="{{ old('email_confirm', data_get($data, 'contatti.email_confirm')) }}">
            @error('email_confirm') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label>Numero di cellulare</label>
            <input type="text" name="cellulare" value="{{ old('cellulare', data_get($data, 'contatti.cellulare')) }}">
            @error('cellulare') <div class="error">{{ $message }}</div> @enderror
        </div>
    </div>

    <div style="margin-top: 30px; background: #f8fafc; border-radius: 14px; padding: 20px; border: 1px solid #e5e7eb;">
        Questi contatti saranno utilizzati per tutte le comunicazioni legate all'acquisto che stai effettuando.
    </div>

    <div class="actions">
        <a href="{{ route('wizard.show', 'documento') }}" class="btn btn-back">Indietro</a>
        <button type="submit" class="btn btn-next">Avanti</button>
    </div>
</form>
@endsection
