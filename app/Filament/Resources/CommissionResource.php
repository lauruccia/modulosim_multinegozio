<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommissionResource\Pages;
use App\Models\CommissionRule;
use App\Models\FormSubmission;
use App\Models\Store;
use Filament\Forms;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CommissionResource extends Resource
{
    protected static ?string $model = FormSubmission::class;

    protected static ?string $navigationIcon     = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel    = 'Commissioni';
    protected static ?string $modelLabel         = 'Commissione';
    protected static ?string $pluralModelLabel   = 'Commissioni';
    protected static ?string $slug               = 'commissions';
    protected static ?string $navigationGroup    = 'Operativo';
    protected static ?int    $navigationSort     = 20;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['store', 'commissionRule'])
            ->visibleTo(Auth::user())
            ->where('commission_status', '!=', 'non_maturata');
    }

    public static function canViewAny(): bool            { return Auth::check(); }
    public static function canCreate(): bool             { return false; }
    public static function canEdit(Model $record): bool  { return false; }

    public static function canView(Model $record): bool
    {
        $user = Auth::user();
        return $user?->isAdmin() || ($user?->store_id && $record->store_id === $user->store_id);
    }

    /* ── Etichette ── */

    private static function commissionStatusOptions(): array
    {
        return [
            'maturata'   => 'Maturata',
            'confermata' => 'Richiesta liquidazione',
            'liquidata'  => 'Liquidata',
            'stornata'   => 'Stornata',
        ];
    }

    private static function commissionStatusColor(?string $state): string
    {
        return match ($state) {
            'maturata'   => 'warning',
            'confermata' => 'primary',
            'liquidata'  => 'success',
            'stornata'   => 'danger',
            default      => 'gray',
        };
    }

    /* ════════════════════════════════════════════════════════
     *  INFOLIST
     * ════════════════════════════════════════════════════════ */

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            InfoSection::make('Commissione')
                ->schema([
                    TextEntry::make('store.name')->label('Negozio')->placeholder('-')
                        ->visible(fn () => Auth::user()?->isAdmin() ?? false),
                    TextEntry::make('service_type')
                        ->label('Servizio')
                        ->formatStateUsing(fn (?string $state): ?string => CommissionRule::serviceOptions()[$state] ?? $state),
                    TextEntry::make('customer_name')->label('Cliente'),
                    TextEntry::make('activation_status')
                        ->label('Stato pratica')
                        ->formatStateUsing(fn (?string $state) => match ($state) {
                            'richiesta' => 'Nuova richiesta', 'in_lavorazione' => 'In lavorazione',
                            'attivata'  => 'Attivata', 'respinta' => 'Respinta', 'annullata' => 'Annullata',
                            default     => $state,
                        }),
                    TextEntry::make('activated_at')->label('Data attivazione')->dateTime('d/m/Y H:i')->placeholder('-'),
                    TextEntry::make('commission_amount')->label('Importo')->money('EUR'),
                    TextEntry::make('commissionRule.name')->label('Regola')->placeholder('-'),
                    TextEntry::make('commission_status')
                        ->label('Stato commissione')
                        ->badge()
                        ->formatStateUsing(fn (?string $state) => self::commissionStatusOptions()[$state] ?? $state)
                        ->color(fn (?string $state) => self::commissionStatusColor($state)),
                    TextEntry::make('commission_confirmed_at')->label('Richiesta il')->dateTime('d/m/Y H:i')->placeholder('-'),
                    TextEntry::make('commission_paid_at')->label('Liquidata il')->dateTime('d/m/Y H:i')->placeholder('-'),
                ])
                ->columns(2),

            InfoSection::make('Coordinate bancarie')
                ->description('IBAN su cui verra accreditato il pagamento.')
                ->icon('heroicon-o-credit-card')
                ->schema([
                    TextEntry::make('store.bank_account_holder')->label('Intestatario')->placeholder('Non impostato'),
                    TextEntry::make('store.iban')->label('IBAN')->placeholder('Non impostato')->copyable()->copyMessage('IBAN copiato!'),
                ])
                ->columns(2)
                ->visible(fn (FormSubmission $record): bool => (bool) $record->store?->iban),
        ]);
    }

    /* ════════════════════════════════════════════════════════
     *  TABLE
     * ════════════════════════════════════════════════════════ */

    public static function table(Table $table): Table
    {
        $isAdmin = Auth::user()?->isAdmin() ?? false;
        $isStore = Auth::user()?->isStoreUser() ?? false;

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable()->visible($isAdmin),

                Tables\Columns\TextColumn::make('store.name')
                    ->label('Negozio')
                    ->visible($isAdmin)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('service_type')
                    ->label('Servizio')
                    ->formatStateUsing(fn (?string $state): ?string => CommissionRule::serviceOptions()[$state] ?? $state)
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('commission_amount')
                    ->label('Importo')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('commission_status')
                    ->label('Stato')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => self::commissionStatusOptions()[$state] ?? $state)
                    ->color(fn (?string $state) => self::commissionStatusColor($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('store.iban')
                    ->label('IBAN')
                    ->placeholder('—')
                    ->copyable()
                    ->copyMessage('Copiato!')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible($isAdmin),

                Tables\Columns\TextColumn::make('commission_paid_at')
                    ->label('Liquidata il')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->defaultSort('commission_confirmed_at', 'desc')
            ->filters([
                /* Filtro rapido: mostra solo commissioni non ancora pagate */
                Tables\Filters\Filter::make('da_pagare')
                    ->label('Da pagare')
                    ->query(fn (Builder $query, array $data): Builder =>
                        ($data['isActive'] ?? false)
                            ? $query->whereIn('commission_status', ['maturata', 'confermata'])
                            : $query
                    )
                    ->toggle(),

                Tables\Filters\SelectFilter::make('commission_status')
                    ->label('Stato commissione')
                    ->options(self::commissionStatusOptions()),

                Tables\Filters\SelectFilter::make('store_id')
                    ->label('Negozio')
                    ->options(fn () => Store::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->visible($isAdmin),

                Tables\Filters\SelectFilter::make('service_type')
                    ->label('Servizio')
                    ->options(CommissionRule::serviceOptions()),

                Tables\Filters\Filter::make('periodo')
                    ->label('Periodo maturazione')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dal'),
                        Forms\Components\DatePicker::make('until')->label('Al'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn ($query, $d) => $query->whereDate('commission_confirmed_at', '>=', $d))
                        ->when($data['until'] ?? null, fn ($query, $d) => $query->whereDate('commission_confirmed_at', '<=', $d))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Apri'),

                /* ─── STORE: richiedi liquidazione singola ─── */
                Tables\Actions\Action::make('richiedi_liquidazione')
                    ->label('Richiedi pagamento')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Richiesta liquidazione commissione')
                    ->modalDescription(fn (FormSubmission $r) =>
                        'Vuoi richiedere il pagamento di ' . number_format((float) $r->commission_amount, 2, ',', '.') .
                        ' EUR? Verifica che l\'IBAN sia aggiornato nel tuo Profilo negozio.')
                    ->modalSubmitActionLabel('Richiedi liquidazione')
                    ->visible(fn (FormSubmission $r): bool =>
                        $isStore && $r->commission_status === 'maturata')
                    ->action(fn (FormSubmission $r) => self::eseguiRichiestaLiquidazione($r)),

                /* ─── ADMIN: segna pagata singola ─── */
                Tables\Actions\Action::make('segna_pagata')
                    ->label('Segna come pagata')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Conferma pagamento commissione')
                    ->modalDescription(fn (FormSubmission $r) =>
                        'Stai per segnare come LIQUIDATA la commissione di EUR ' .
                        number_format((float) $r->commission_amount, 2, ',', '.') .
                        ' — negozio: ' . ($r->store?->name ?? '?'))
                    ->modalSubmitActionLabel('Conferma pagamento')
                    ->visible(fn (FormSubmission $r): bool =>
                        $isAdmin && in_array($r->commission_status, ['maturata', 'confermata'], true))
                    ->action(fn (FormSubmission $r) => self::eseguiPagamento($r)),

                /* ─── SUPERADMIN: storno/correzione eccezionale ─── */
                Tables\Actions\Action::make('storna_commissione')
                    ->label('Storna (eccezionale)')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn (FormSubmission $r): bool =>
                        (Auth::user()?->isSuperAdmin() ?? false) &&
                        $r->commission_status === 'liquidata')
                    ->requiresConfirmation()
                    ->modalHeading('Storno commissione — azione eccezionale')
                    ->modalDescription('Usa questa funzione SOLO in caso di errore. Il motivo viene registrato nel log di audit.')
                    ->form([
                        Forms\Components\Select::make('nuovo_stato')
                            ->label('Riporta allo stato')
                            ->options(['confermata' => 'Richiesta liquidazione (in attesa)', 'maturata' => 'Maturata (da richiedere)'])
                            ->default('confermata')
                            ->required(),
                        Forms\Components\Textarea::make('motivo')
                            ->label('Motivo dello storno (obbligatorio)')
                            ->required()
                            ->rows(3)
                            ->placeholder('Es: pagamento registrato per errore, l\'importo era errato...'),
                    ])
                    ->modalSubmitActionLabel('Applica storno')
                    ->action(function (FormSubmission $record, array $data): void {
                        $record->update([
                            'commission_status' => $data['nuovo_stato'],
                            'commission_paid_at' => null,
                        ]);
                        $record->addEvent(
                            eventType: 'nota',
                            title: 'Storno commissione — correzione eccezionale',
                            description: 'Commissione stornata da «Liquidata» a «' .
                                self::commissionStatusOptions()[$data['nuovo_stato']] . '».' .
                                "\nMotivo: " . $data['motivo'],
                            visibleToCustomer: false,
                        );
                        Notification::make()->title('Storno registrato nel log di audit')->warning()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([

                    /* ─── STORE: richiedi pagamento su selezione multipla ─── */
                    Tables\Actions\BulkAction::make('bulk_richiedi_liquidazione')
                        ->label('Richiedi pagamento selezionate')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Richiesta liquidazione multipla')
                        ->modalDescription('Verranno richieste in pagamento tutte le commissioni selezionate in stato "Maturata". Assicurati di avere l\'IBAN aggiornato nel Profilo negozio.')
                        ->modalSubmitActionLabel('Richiedi pagamento')
                        ->visible(fn (): bool => $isStore)
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $ok = 0;
                            $skip = 0;
                            foreach ($records as $record) {
                                if ($record->commission_status !== 'maturata') { $skip++; continue; }
                                $result = self::eseguiRichiestaLiquidazione($record, notify: false);
                                $result ? $ok++ : $skip++;
                            }
                            Notification::make()
                                ->title("{$ok} richieste inviate" . ($skip ? ", {$skip} saltate (IBAN mancante o stato non valido)" : ''))
                                ->color($ok > 0 ? 'success' : 'warning')
                                ->send();
                        }),

                    /* ─── ADMIN: segna pagate su selezione multipla ─── */
                    Tables\Actions\BulkAction::make('bulk_segna_pagate')
                        ->label('Segna come pagate')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Conferma pagamento multiplo')
                        ->modalDescription('Tutte le commissioni selezionate in stato "Maturata" o "Richiesta liquidazione" verranno segnate come liquidate.')
                        ->modalSubmitActionLabel('Conferma pagamento')
                        ->visible(fn (): bool => $isAdmin)
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $count = 0;
                            foreach ($records as $record) {
                                if (! in_array($record->commission_status, ['maturata', 'confermata'], true)) continue;
                                self::eseguiPagamento($record, notify: false);
                                $count++;
                            }
                            Notification::make()->title("{$count} commissioni segnate come pagate")->success()->send();
                        }),

                ]),
            ]);
    }

    /* ════════════════════════════════════════════════════════
     *  Logica riutilizzabile
     * ════════════════════════════════════════════════════════ */

    private static function eseguiRichiestaLiquidazione(FormSubmission $record, bool $notify = true): bool
    {
        $store = $record->store;

        if (! $store?->iban) {
            if ($notify) {
                Notification::make()
                    ->title('IBAN mancante')
                    ->body('Inserisci l\'IBAN nella sezione "Profilo negozio" prima di richiedere il pagamento.')
                    ->danger()
                    ->persistent()
                    ->send();
            }
            return false;
        }

        $record->update([
            'commission_status'       => 'confermata',
            'commission_confirmed_at' => $record->commission_confirmed_at ?? now(),
        ]);

        $record->addEvent(
            eventType: 'nota',
            title: 'Liquidazione commissione richiesta',
            description: 'Il negozio ha richiesto il pagamento di EUR ' .
                $record->commission_amount . ' sull\'IBAN: ' . $store->iban,
            visibleToCustomer: false,
        );

        if ($notify) {
            Notification::make()->title('Richiesta di liquidazione inviata!')->success()->send();
        }

        return true;
    }

    private static function eseguiPagamento(FormSubmission $record, bool $notify = true): void
    {
        $record->update([
            'commission_status'  => 'liquidata',
            'commission_paid_at' => now(),
        ]);

        $record->addEvent(
            eventType: 'nota',
            title: 'Commissione liquidata',
            description: 'Commissione di EUR ' . $record->commission_amount . ' segnata come pagata.',
            visibleToCustomer: false,
        );

        if ($notify) {
            Notification::make()->title('Commissione segnata come pagata')->success()->send();
        }
    }

    /* ── Pages ── */

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCommissions::route('/'),
            'view'  => Pages\ViewCommission::route('/{record}'),
        ];
    }
}
