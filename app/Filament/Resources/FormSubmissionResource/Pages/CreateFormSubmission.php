<?php

namespace App\Filament\Resources\FormSubmissionResource\Pages;

use App\Filament\Resources\FormSubmissionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateFormSubmission extends CreateRecord
{
    protected static string $resource = FormSubmissionResource::class;

    public function getTitle(): string
    {
        return 'Nuova pratica';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        $data['submitted_by_user_id'] = $user?->id;
        $data['source']               = 'admin_panel';

        /* Operatore negozio: lega automaticamente al proprio store */
        if ($user?->isStoreUser()) {
            $data['store_id'] = $user->store_id;
        }

        /* Default stati per nuova pratica creata da backend */
        $data['activation_status'] = $data['activation_status'] ?? 'richiesta';
        $data['status']            = 'nuova';
        $data['payment_status']    = $data['payment_status'] ?? 'pending';

        /* Sincronizza customer_name dai campi payload se presenti */
        $payload = $data['payload'] ?? [];
        if (empty($data['customer_name']) && (! empty($payload['dati']['nome'] ?? null) || ! empty($payload['dati']['cognome'] ?? null))) {
            $data['customer_name'] = trim(($payload['dati']['nome'] ?? '') . ' ' . ($payload['dati']['cognome'] ?? ''));
        }
        if (empty($data['customer_email']) && ! empty($payload['contatti']['email'] ?? null)) {
            $data['customer_email'] = $payload['contatti']['email'];
        }
        if (empty($data['customer_phone']) && ! empty($payload['contatti']['cellulare'] ?? null)) {
            $data['customer_phone'] = $payload['contatti']['cellulare'];
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->addEvent(
            eventType: 'ricevuta',
            title: 'Pratica ricevuta',
            description: 'Nuova pratica creata dal pannello di gestione.',
            visibleToCustomer: true,
        );
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
