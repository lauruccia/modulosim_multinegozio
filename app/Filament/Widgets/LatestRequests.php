<?php

namespace App\Filament\Widgets;

use App\Models\FormSubmission;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class LatestRequests extends BaseWidget
{
    protected static ?string $heading = 'Ultime pratiche';
    protected static ?int    $sort    = 3;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $isAdmin = Auth::user()?->isAdmin() ?? false;

        return $table
            ->query(FormSubmission::query()->with('store')->visibleTo(Auth::user())->latest()->limit(10))
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->visible($isAdmin),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable(),

                Tables\Columns\TextColumn::make('store.name')
                    ->label('Negozio')
                    ->visible($isAdmin),

                Tables\Columns\TextColumn::make('customer_email')
                    ->label('Email')
                    ->searchable()
                    ->visible($isAdmin),

                Tables\Columns\TextColumn::make('customer_phone')
                    ->label('Telefono')
                    ->searchable(),

                Tables\Columns\TextColumn::make('service_type')
                    ->label('Servizio')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'sim'       => 'SIM',
                        'luce'      => 'Luce',
                        'gas'       => 'Gas',
                        'luce_gas'  => 'Luce + Gas',
                        default     => ucfirst($state ?? ''),
                    })
                    ->color('gray'),

                Tables\Columns\TextColumn::make('activation_status')
                    ->label('Stato pratica')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'richiesta'      => 'Nuova richiesta',
                        'in_lavorazione' => 'In lavorazione',
                        'attivata'       => 'Attivata',
                        'respinta'       => 'Respinta',
                        'annullata'      => 'Annullata',
                        default          => $state,
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'richiesta'      => 'warning',
                        'in_lavorazione' => 'primary',
                        'attivata'       => 'success',
                        'respinta',
                        'annullata'      => 'danger',
                        default          => 'gray',
                    }),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Pagamento')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'pending' => 'In attesa',
                        'paid'    => 'Pagato',
                        'failed'  => 'Non riuscito',
                        default   => $state,
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'paid'    => 'success',
                        'pending' => 'warning',
                        'failed'  => 'danger',
                        default   => 'gray',
                    })
                    ->visible($isAdmin),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('apri')
                    ->label('Apri')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (FormSubmission $record): string =>
                        \App\Filament\Resources\FormSubmissionResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(),
            ])
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5);
    }
}
