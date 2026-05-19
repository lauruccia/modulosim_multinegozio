<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Conferma richiesta</title>
</head>
<body style="font-family: Arial, sans-serif; color: #222; line-height: 1.5;">
    <h2>Abbiamo ricevuto la tua richiesta</h2>

    <p>Ciao {{ data_get($submission->payload, 'dati.nome', $submission->customer_name) }},</p>

    <p>la tua richiesta è stata registrata correttamente.</p>

    <p><strong>ID richiesta:</strong> {{ $submission->id }}</p>
    <p><strong>Totale:</strong> € {{ number_format((float) $submission->total_amount, 2, ',', '.') }}</p>
    <p><strong>Metodo pagamento:</strong> {{ $submission->payment_method }}</p>
    <p><strong>Stato pagamento:</strong> {{ $submission->payment_status }}</p>

    @if($submission->payment_method === 'carta')
        <p>Il pagamento risulta registrato correttamente.</p>
    @else
        <p>Il pagamento sarà gestito secondo la modalità da te scelta.</p>
    @endif

    <p>Ti contatteremo presto per i passaggi successivi.</p>

    <p>Grazie,<br>Sharers</p>
</body>
</html>
