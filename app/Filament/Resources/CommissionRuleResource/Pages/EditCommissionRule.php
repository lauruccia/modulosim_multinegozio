<?php

namespace App\Filament\Resources\CommissionRuleResource\Pages;

use App\Filament\Resources\CommissionRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditCommissionRule extends EditRecord
{
    protected static string $resource = CommissionRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Elimina')
                ->visible(fn () => Auth::user()?->isSuperAdmin() ?? false),
        ];
    }
}
