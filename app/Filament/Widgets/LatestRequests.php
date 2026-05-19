<?php

namespace App\Filament\Widgets;

use App\Models\FormSubmission;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class LatestRequests extends BaseWidget
{
    protected static ?string $heading = 'Ultime richieste';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(FormSubmission::query()->with('store')->visibleTo(Auth::user())->latest())
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable(),

                Tables\Columns\TextColumn::make('store.name')
                    ->label('Negozio')
                    ->visible(fn () => Auth::user()?->isAdmin() ?? false),

                Tables\Columns\TextColumn::make('customer_email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer_phone')
                    ->label('Telefono')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Stato pratica')
                    ->colors([
                        'gray' => 'bozza',
                        'warning' => 'nuova',
                        'primary' => 'in_lavorazione',
                        'success' => 'completata',
                        'danger' => 'annullata',
                    ]),

                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Pagamento')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                    ]),

                Tables\Columns\BadgeColumn::make('activation_status')
                    ->label('Attivazione')
                    ->colors([
                        'gray' => 'richiesta',
                        'primary' => 'in_lavorazione',
                        'success' => 'attivata',
                        'danger' => ['respinta', 'annullata'],
                    ]),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Totale')
                    ->money('EUR'),

                Tables\Columns\TextColumn::make('commission_amount')
                    ->label('Commissione')
                    ->money('EUR'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('apri')
                    ->label('Apri')
                    ->url(fn (FormSubmission $record): string => route('filament.admin.resources.form-submissions.edit', ['record' => $record])),
            ])
            ->paginated([5]);
    }
}
