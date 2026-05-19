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
        return 'Nuova richiesta';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        $payload = $data['payload'] ?? [];

        $data['submitted_by_user_id'] = $user?->id;
        $data['source'] = 'admin_panel';

        if ($user?->isStoreUser()) {
            $data['store_id'] = $user->store_id;
        }

        if (! empty($payload['dati']['nome'] ?? null) || ! empty($payload['dati']['cognome'] ?? null)) {
            $data['customer_name'] = trim(($payload['dati']['nome'] ?? '') . ' ' . ($payload['dati']['cognome'] ?? ''));
        }

        if (! empty($payload['contatti']['email'] ?? null)) {
            $data['customer_email'] = $payload['contatti']['email'];
        }

        if (! empty($payload['contatti']['cellulare'] ?? null)) {
            $data['customer_phone'] = $payload['contatti']['cellulare'];
        }

        return $data;
    }
}
