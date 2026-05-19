<?php

namespace App\Filament\Resources\FormSubmissionResource\Pages;

use App\Filament\Resources\FormSubmissionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditFormSubmission extends EditRecord
{
    protected static string $resource = FormSubmissionResource::class;

    public function getTitle(): string
    {
        return 'Modifica richiesta';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()->label('Apri'),
            Actions\DeleteAction::make()
                ->label('Elimina')
                ->visible(fn () => Auth::user()?->isSuperAdmin() ?? false),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $payload = $data['payload'] ?? [];

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
