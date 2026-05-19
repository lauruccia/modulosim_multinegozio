@extends('wizard.layout')

@section('content')
<h1>Operazione completata</h1>

<div style="max-width:700px; margin:0 auto; text-align:center;">
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:32px;">
        <p style="font-size:20px; margin:0;">
            {{ $message ?? 'Operazione completata correttamente.' }}
        </p>
    </div>

    <div class="actions" style="justify-content:center; margin-top:30px;">
        <a href="{{ route('wizard.show', 'dati') }}" class="btn btn-next">Nuova richiesta</a>
    </div>
</div>
@endsection
