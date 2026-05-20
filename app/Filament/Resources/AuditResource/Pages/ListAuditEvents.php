<?php

namespace App\Filament\Resources\AuditResource\Pages;

use App\Filament\Resources\AuditResource;
use Filament\Resources\Pages\ListRecords;

class ListAuditEvents extends ListRecords
{
    protected static string $resource = AuditResource::class;

    public function getTitle(): string
    {
        return 'Audit Log — Tutte le azioni';
    }
}
