<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Nuova richiesta</title>
</head>
<body style="font-family: Arial, sans-serif; color: #222; line-height: 1.5;">
    <h2>Nuova richiesta ricevuta</h2>

    <p><strong>ID richiesta:</strong> {{ $submission->id }}</p>
    <p><strong>Cliente:</strong> {{ $submission->customer_name }}</p>
    <p><strong>Email:</strong> {{ $submission->customer_email }}</p>
    <p><strong>Telefono:</strong> {{ $submission->customer_phone }}</p>
    <p><strong>Metodo pagamento:</strong> {{ $submission->payment_method }}</p>
    <p><strong>Stato pagamento:</strong> {{ $submission->payment_status }}</p>
    <p><strong>Totale:</strong> € {{ number_format((float) $submission->total_amount, 2, ',', '.') }}</p>

    <hr>

    <h3>Dati cliente</h3>
    <p><strong>Nome:</strong> {{ data_get($submission->payload, 'dati.nome') }}</p>
    <p><strong>Cognome:</strong> {{ data_get($submission->payload, 'dati.cognome') }}</p>
    <p><strong>Codice fiscale:</strong> {{ data_get($submission->payload, 'dati.codice_fiscale') }}</p>

    <h3>Documento</h3>
    <p><strong>Tipo:</strong> {{ data_get($submission->payload, 'documento.tipo_documento') }}</p>
    <p><strong>Numero:</strong> {{ data_get($submission->payload, 'documento.numero_documento') }}</p>
    <p><strong>Scadenza:</strong> {{ data_get($submission->payload, 'documento.data_scadenza') }}</p>

    <h3>Contatti</h3>
    <p><strong>Email:</strong> {{ data_get($submission->payload, 'contatti.email') }}</p>
    <p><strong>Cellulare:</strong> {{ data_get($submission->payload, 'contatti.cellulare') }}</p>

    <h3>Spedizione</h3>
    <p><strong>Destinatario:</strong> {{ data_get($submission->payload, 'indirizzi.spedizione.destinatario') }}</p>
    <p><strong>CAP:</strong> {{ data_get($submission->payload, 'indirizzi.spedizione.cap') }}</p>
    <p><strong>Città:</strong> {{ data_get($submission->payload, 'indirizzi.spedizione.citta') }}</p>
    <p><strong>Indirizzo:</strong> {{ data_get($submission->payload, 'indirizzi.spedizione.indirizzo') }} {{ data_get($submission->payload, 'indirizzi.spedizione.civico') }}</p>

    <h3>Scelte</h3>
    <p><strong>Numero:</strong> {{ data_get($submission->payload, 'numero.scelta') }}</p>
    <p><strong>Metodo pagamento scelto:</strong> {{ data_get($submission->payload, 'pagamento.metodo') }}</p>
    <p><strong>Codice amico:</strong> {{ data_get($submission->payload, 'pagamento.codice_amico') ?: '-' }}</p>

    <h3>Servizi</h3>
    <p><strong>5G:</strong> {{ data_get($submission->payload, 'servizi.opzione_5g') ? 'Sì' : 'No' }}</p>
    <p><strong>Ricarica automatica:</strong> {{ data_get($submission->payload, 'servizi.ricarica_automatica') ? 'Sì' : 'No' }}</p>
    <p><strong>SafeCall:</strong> {{ data_get($submission->payload, 'servizi.safe_call') ? 'Sì' : 'No' }}</p>
    <p><strong>Total Security:</strong> {{ data_get($submission->payload, 'servizi.total_security') ? 'Sì' : 'No' }}</p>
</body>
</html>
