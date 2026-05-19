<?php

namespace App\Observers;

use App\Models\FormSubmission;

class SubmissionObserver
{
    /**
     * Evento automatico alla creazione della pratica.
     */
    public function created(FormSubmission $submission): void
    {
        $submission->addEvent(
            eventType: 'ricevuta',
            title: 'Richiesta ricevuta',
            description: 'La tua richiesta è stata registrata correttamente nel nostro sistema.',
            visibleToCustomer: true,
        );
    }

    /**
     * Evento automatico quando cambia activation_status.
     */
    public function updated(FormSubmission $submission): void
    {
        if (! $submission->wasChanged('activation_status')) {
            return;
        }

        $newStatus = $submission->activation_status;

        $map = [
            'in_lavorazione' => [
                'type'        => 'in_lavorazione',
                'title'       => 'Pratica presa in carico',
                'description' => 'Il nostro team ha preso in carico la tua richiesta e sta procedendo con la verifica dei dati.',
                'visible'     => true,
            ],
            'attivata' => [
                'type'        => 'attivata',
                'title'       => 'Attivazione completata!',
                'description' => 'La tua pratica è stata attivata con successo. Grazie per aver scelto i nostri servizi.',
                'visible'     => true,
            ],
            'respinta' => [
                'type'        => 'respinta',
                'title'       => 'Pratica respinta',
                'description' => 'Purtroppo la tua pratica non ha potuto essere completata. Ti contatteremo per fornirti maggiori dettagli.',
                'visible'     => true,
            ],
            'annullata' => [
                'type'        => 'annullata',
                'title'       => 'Pratica annullata',
                'description' => 'La pratica è stata annullata.',
                'visible'     => true,
            ],
        ];

        if (! isset($map[$newStatus])) {
            return;
        }

        $data = $map[$newStatus];

        $submission->addEvent(
            eventType: $data['type'],
            title: $data['title'],
            description: $data['description'],
            visibleToCustomer: $data['visible'],
        );
    }
}
