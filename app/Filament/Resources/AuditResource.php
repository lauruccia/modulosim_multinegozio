<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditResource\Pages;
use App\Models\SubmissionEvent;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditResource extends Resource
{
    protected static ?string $model = SubmissionEvent::class;

    protected static ?string $navigationIcon    = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel   = 'Audit Log';
    protected static ?string $modelLabel        = 'Evento';
    protected static ?string $pluralModelLabel  = 'Audit Log';
    protected static ?string $navigationGroup   = 'Superadmin';
    protected static ?int    $navigationSort    = 100;

    public static function shouldRegisterNavigation(): bool { return Auth::user()?->isSuperAdmin() ?? false; }
    public static function canAccess(): bool                { return Auth::user()?->isSuperAdmin() ?? false; }
    public static function canViewAny(): bool               { return Auth::user()?->isSuperAdmin() ?? false; }
    public static function canCreate(): bool                { return false; }
    public static function canEdit(Model $record): bool     { return false; }
    public static function canDelete(Model $record): bool   { return false; }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['submission', 'submission.store', 'performedBy'])
            ->latest('occurred_at');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('occurred_at')
                    ->label('Data / Ora')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),

                Tables\Columns\TextColumn::make('performedBy.name')
                    ->label('Operatore')
                    ->placeholder('Sistema / Wizard')
                    ->searchable(),

                Tables\Columns\TextColumn::make('submission.store.name')
                    ->label('Negozio')
                    ->placeholder('—')
                    ->searchable(),

                Tables\Columns\TextColumn::make('submission.customer_name')
                    ->label('Cliente')
                    ->placeholder('—')
                    ->searchable(),

                Tables\Columns\TextColumn::make('submission_id')
                    ->label('Pratica #')
                    ->url(fn (SubmissionEvent $r): string =>
                        FormSubmissionResource::getUrl('view', ['record' => $r->form_submission_id]))
                    ->openUrlInNewTab()
                    ->sortable(),

                Tables\Columns\TextColumn::make('event_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'ricevuta'       => 'Ricevuta',
                        'in_lavorazione' => 'In lavorazione',
                        'attivata'       => 'Attivata',
                        'respinta'       => 'Respinta',
                        'annullata'      => 'Annullata',
                        'nota'           => 'Nota',
                        default          => ucfirst($state ?? ''),
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'attivata'       => 'success',
                        'respinta',
                        'annullata'      => 'danger',
                        'in_lavorazione' => 'primary',
                        'ricevuta'       => 'info',
                        default          => 'gray',
                    }),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titolo')
                    ->searchable()
                    ->wrap()
                    ->limit(60),

                Tables\Columns\IconColumn::make('is_visible_to_customer')
                    ->label('Visibile cliente')
                    ->boolean(),
            ])
            ->defaultSort('occurred_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('event_type')
                    ->label('Tipo evento')
                    ->options([
                        'ricevuta'       => 'Ricevuta',
                        'in_lavorazione' => 'In lavorazione',
                        'attivata'       => 'Attivata',
                        'respinta'       => 'Respinta',
                        'annullata'      => 'Annullata',
                        'nota'           => 'Nota',
                    ]),

                Tables\Filters\Filter::make('solo_azioni_operative')
                    ->label('Solo azioni manuali')
                    ->query(fn (Builder $query, array $data): Builder =>
                        ($data['isActive'] ?? false)
                            ? $query->whereNotNull('performed_by_user_id')
                            : $query
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('periodo')
                    ->label('Periodo')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dal'),
                        Forms\Components\DatePicker::make('until')->label('Al'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn ($query, $d) => $query->whereDate('occurred_at', '>=', $d))
                        ->when($data['until'] ?? null, fn ($query, $d) => $query->whereDate('occurred_at', '<=', $d))),
            ])
            ->actions([
                Tables\Actions\Action::make('apri_pratica')
                    ->label('Pratica')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (SubmissionEvent $r): string =>
                        FormSubmissionResource::getUrl('view', ['record' => $r->form_submission_id]))
                    ->openUrlInNewTab(),
            ])
            ->searchable()
            ->paginated([25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditEvents::route('/'),
        ];
    }
}
