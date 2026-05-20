<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormSubmissionResource\Pages;
use App\Models\CommissionRule;
use App\Models\FormSubmission;
use App\Models\Store;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class FormSubmissionResource extends Resource
{
    protected static ?string $model = FormSubmission::class;

    protected static ?string $navigationIcon      = 'heroicon-o-document-text';
    protected static ?string $navigationLabel     = 'Pratiche';
    protected static ?string $modelLabel          = 'Pratica';
    protected static ?string $pluralModelLabel    = 'Pratiche';
    protected static ?string $navigationGroup     = 'Operativo';
    protected static ?int    $navigationSort      = 10;

    /* ── Query base ── */

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['store', 'commissionRule'])
            ->visibleTo(Auth::user());
    }

    /* ── Permessi ── */

    public static function canCreate(): bool    { return Auth::check(); }
    public static function canViewAny(): bool   { return Auth::check(); }

    public static function canView(Model $record): bool
    {
        $user = Auth::user();
        return $user?->isAdmin() || ($user?->store_id && $record->store_id === $user->store_id);
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function canDelete(Model $record): bool   { return Auth::user()?->isSuperAdmin() ?? false; }
    public static function canDeleteAny(): bool             { return Auth::user()?->isSuperAdmin() ?? false; }

    /* ════════════════════════════════════════════════════════
     *  FORM
     * ════════════════════════════════════════════════════════ */

    public static function form(Form $form): Form
    {
        $isStore = Auth::user()?->isStoreUser() ?? false;

        return $isStore
            ? self::storeForm($form)
            : self::adminForm($form);
    }

    /* ── Form semplificato per operatore negozio ── */
    private static function storeForm(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Dati cliente')
                ->icon('heroicon-o-user')
                ->schema([
                    Forms\Components\TextInput::make('customer_name')
                        ->label('Nome e cognome cliente')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('customer_email')
                        ->label('Email')
                        ->email()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('customer_phone')
                        ->label('Telefono / Cellulare')
                        ->maxLength(50),
                ])
                ->columns(2),

            Forms\Components\Section::make('Dettagli richiesta')
                ->icon('heroicon-o-clipboard-document-list')
                ->schema([
                    Forms\Components\Select::make('service_type')
                        ->label('Servizio richiesto')
                        ->options(CommissionRule::serviceOptions())
                        ->default('sim')
                        ->required(),

                    Forms\Components\Select::make('payment_method')
                        ->label('Metodo di pagamento')
                        ->options([
                            'carta'   => 'Carta di credito/debito',
                            'negozio' => 'Pagamento in negozio',
                            'dopo'    => 'Paga dopo',
                        ])
                        ->default('negozio'),
                ])
                ->columns(2),

            Forms\Components\Section::make('Note interne')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->schema([
                    Forms\Components\Textarea::make('admin_notes')
                        ->label('Note')
                        ->placeholder('Eventuali note sulla pratica...')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->collapsible()
                ->collapsed(),
        ]);
    }

    /* ── Form completo per admin/superadmin ── */
    private static function adminForm(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Dati principali')
                ->schema([
                    Forms\Components\Select::make('store_id')
                        ->label('Negozio')
                        ->relationship('store', 'name')
                        ->searchable()
                        ->preload()
                        ->required(fn () => Auth::user()?->isAdmin() ?? false),

                    Forms\Components\Select::make('service_type')
                        ->label('Servizio')
                        ->options(CommissionRule::serviceOptions())
                        ->default('sim')
                        ->required(),

                    Forms\Components\TextInput::make('customer_name')
                        ->label('Nome cliente')
                        ->maxLength(255)
                        ->required(),

                    Forms\Components\TextInput::make('customer_email')
                        ->label('Email')
                        ->email()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('customer_phone')
                        ->label('Telefono')
                        ->maxLength(50),

                    Forms\Components\Select::make('activation_status')
                        ->label('Stato pratica')
                        ->options(self::activationStatusOptions())
                        ->required(),

                    Forms\Components\Select::make('payment_method')
                        ->label('Metodo pagamento')
                        ->options(self::paymentMethodOptions()),

                    Forms\Components\Select::make('payment_status')
                        ->label('Stato pagamento')
                        ->options(self::paymentStatusOptions())
                        ->required(),

                    Forms\Components\DateTimePicker::make('activated_at')
                        ->label('Data attivazione')
                        ->seconds(false),

                    Forms\Components\Textarea::make('admin_notes')
                        ->label('Note interne')
                        ->rows(3)
                        ->columnSpanFull()
                        ->helperText('Visibili solo all\'amministrazione, non al cliente.'),
                ])
                ->columns(2),

            Forms\Components\Section::make('Dati anagrafici cliente')
                ->schema([
                    Forms\Components\TextInput::make('payload.dati.nome')->label('Nome')->maxLength(255),
                    Forms\Components\TextInput::make('payload.dati.cognome')->label('Cognome')->maxLength(255),
                    Forms\Components\TextInput::make('payload.dati.codice_fiscale')->label('Codice fiscale')->maxLength(16),
                ])
                ->columns(3)
                ->collapsed(),

            Forms\Components\Section::make('Documento di identità')
                ->schema([
                    Forms\Components\Select::make('payload.documento.tipo_documento')
                        ->label('Tipo documento')
                        ->options(['carta_identita' => "Carta d'identità", 'passaporto' => 'Passaporto', 'patente' => 'Patente']),
                    Forms\Components\TextInput::make('payload.documento.numero_documento')->label('Numero')->maxLength(50),
                    Forms\Components\DatePicker::make('payload.documento.data_scadenza')->label('Scadenza'),
                ])
                ->columns(3)
                ->collapsed(),

            Forms\Components\Section::make('Indirizzo spedizione')
                ->schema([
                    Forms\Components\TextInput::make('payload.indirizzi.spedizione.destinatario')->label('Destinatario'),
                    Forms\Components\TextInput::make('payload.indirizzi.spedizione.indirizzo')->label('Indirizzo'),
                    Forms\Components\TextInput::make('payload.indirizzi.spedizione.civico')->label('Civico'),
                    Forms\Components\TextInput::make('payload.indirizzi.spedizione.cap')->label('CAP'),
                    Forms\Components\TextInput::make('payload.indirizzi.spedizione.citta')->label('Città'),
                ])
                ->columns(3)
                ->collapsed(),

            Forms\Components\Section::make('Scelte commerciali')
                ->schema([
                    Forms\Components\Select::make('payload.numero.scelta')
                        ->label('Scelta numero')
                        ->options(['nuovo' => 'Nuovo numero', 'portabilita' => 'Mantiene il numero']),
                    Forms\Components\TextInput::make('payload.pagamento.codice_amico')->label('Codice amico'),
                    Forms\Components\Toggle::make('payload.servizi.opzione_5g')->label('5G'),
                    Forms\Components\Toggle::make('payload.servizi.ricarica_automatica')->label('Ricarica automatica'),
                    Forms\Components\Toggle::make('payload.servizi.safe_call')->label('SafeCall'),
                    Forms\Components\Toggle::make('payload.servizi.total_security')->label('Total Security'),
                ])
                ->columns(3)
                ->collapsed(),
        ]);
    }

    /* ════════════════════════════════════════════════════════
     *  INFOLIST (view)
     * ════════════════════════════════════════════════════════ */

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            InfoSection::make('Riepilogo pratica')
                ->schema([
                    TextEntry::make('store.name')->label('Negozio')->placeholder('-')
                        ->visible(fn () => Auth::user()?->isAdmin() ?? false),
                    TextEntry::make('service_type')
                        ->label('Servizio')
                        ->formatStateUsing(fn (?string $state): ?string => CommissionRule::serviceOptions()[$state] ?? $state),
                    TextEntry::make('customer_name')->label('Cliente'),
                    TextEntry::make('customer_email')->label('Email'),
                    TextEntry::make('customer_phone')->label('Telefono'),
                    TextEntry::make('activation_status')
                        ->label('Stato pratica')
                        ->badge()
                        ->formatStateUsing(fn (?string $state) => self::activationStatusOptions()[$state] ?? $state)
                        ->color(fn (?string $state) => self::activationStatusColor($state)),
                    TextEntry::make('payment_method')
                        ->label('Metodo pagamento')
                        ->formatStateUsing(fn (?string $state) => self::paymentMethodOptions()[$state] ?? $state)
                        ->visible(fn () => Auth::user()?->isAdmin() ?? false),
                    TextEntry::make('payment_status')
                        ->label('Stato pagamento')
                        ->badge()
                        ->formatStateUsing(fn (?string $state) => self::paymentStatusOptions()[$state] ?? $state)
                        ->color(fn (?string $state) => match ($state) {
                            'paid'    => 'success',
                            'pending' => 'warning',
                            'failed'  => 'danger',
                            default   => 'gray',
                        })
                        ->visible(fn () => Auth::user()?->isAdmin() ?? false),
                    TextEntry::make('total_amount')->label('Totale')->money('EUR')
                        ->visible(fn () => Auth::user()?->isAdmin() ?? false),
                    TextEntry::make('activated_at')->label('Data attivazione')->dateTime('d/m/Y H:i')->placeholder('-'),
                    TextEntry::make('commission_amount')->label('Commissione')->money('EUR')
                        ->visible(fn () => Auth::user()?->isAdmin() ?? false),
                    TextEntry::make('commission_status')
                        ->label('Stato commissione')
                        ->badge()
                        ->formatStateUsing(fn (?string $state) => self::commissionStatusOptions()[$state] ?? $state)
                        ->color(fn (?string $state) => match ($state) {
                            'maturata'    => 'warning',
                            'confermata'  => 'primary',
                            'liquidata'   => 'success',
                            'stornata'    => 'danger',
                            default       => 'gray',
                        })
                        ->visible(fn () => Auth::user()?->isAdmin() ?? false),
                    TextEntry::make('tracking_url')
                        ->label('Link tracking cliente')
                        ->placeholder('—')
                        ->copyable()
                        ->copyMessage('Link copiato!')
                        ->url(fn (FormSubmission $r): ?string => $r->tracking_url)
                        ->openUrlInNewTab()
                        ->columnSpanFull(),
                    TextEntry::make('admin_notes')
                        ->label('Note')
                        ->placeholder('—')
                        ->columnSpanFull()
                        ->visible(fn () => Auth::user()?->isAdmin() ?? false),
                ])
                ->columns(2),

            InfoSection::make('Anagrafica cliente')
                ->schema([
                    TextEntry::make('payload.dati.nome')->label('Nome'),
                    TextEntry::make('payload.dati.cognome')->label('Cognome'),
                    TextEntry::make('payload.dati.codice_fiscale')->label('Codice fiscale'),
                ])
                ->columns(3)
                ->collapsed(),

            InfoSection::make('Documento')
                ->schema([
                    TextEntry::make('payload.documento.tipo_documento')
                        ->label('Tipo')
                        ->formatStateUsing(fn (?string $state) => match ($state) {
                            'carta_identita' => "Carta d'identità",
                            'passaporto' => 'Passaporto',
                            'patente' => 'Patente',
                            default => $state,
                        }),
                    TextEntry::make('payload.documento.numero_documento')->label('Numero'),
                    TextEntry::make('payload.documento.data_scadenza')->label('Scadenza'),
                ])
                ->columns(3)
                ->collapsed(),

            InfoSection::make('Spedizione')
                ->schema([
                    TextEntry::make('payload.indirizzi.spedizione.destinatario')->label('Destinatario'),
                    TextEntry::make('payload.indirizzi.spedizione.indirizzo')->label('Indirizzo'),
                    TextEntry::make('payload.indirizzi.spedizione.civico')->label('Civico'),
                    TextEntry::make('payload.indirizzi.spedizione.cap')->label('CAP'),
                    TextEntry::make('payload.indirizzi.spedizione.citta')->label('Città'),
                ])
                ->columns(3)
                ->collapsed(),

            InfoSection::make('Servizi scelti')
                ->schema([
                    TextEntry::make('payload.numero.scelta')
                        ->label('Numero')
                        ->formatStateUsing(fn (?string $state) => match ($state) {
                            'nuovo' => 'Nuovo numero', 'portabilita' => 'Mantiene il numero', default => $state,
                        }),
                    TextEntry::make('payload.pagamento.codice_amico')->label('Codice amico')->placeholder('-'),
                    TextEntry::make('payload.servizi.opzione_5g')->label('5G')->formatStateUsing(fn ($state) => $state ? 'Sì' : 'No'),
                    TextEntry::make('payload.servizi.ricarica_automatica')->label('Ricarica automatica')->formatStateUsing(fn ($state) => $state ? 'Sì' : 'No'),
                    TextEntry::make('payload.servizi.safe_call')->label('SafeCall')->formatStateUsing(fn ($state) => $state ? 'Sì' : 'No'),
                    TextEntry::make('payload.servizi.total_security')->label('Total Security')->formatStateUsing(fn ($state) => $state ? 'Sì' : 'No'),
                ])
                ->columns(3)
                ->collapsed(),

            InfoSection::make('Storico pratica')
                ->description('Aggiornamenti visibili al cliente nella pagina di tracking.')
                ->icon('heroicon-o-clock')
                ->schema([
                    RepeatableEntry::make('events')
                        ->label('')
                        ->schema([
                            TextEntry::make('occurred_at')->label('Data')->dateTime('d/m/Y H:i')->weight(\Filament\Support\Enums\FontWeight::Medium),
                            TextEntry::make('title')->label('Titolo')->weight(\Filament\Support\Enums\FontWeight::Bold),
                            TextEntry::make('description')->label('Descrizione')->placeholder('—'),
                            TextEntry::make('performedBy.name')->label('Operatore')->placeholder('Sistema'),
                            TextEntry::make('is_visible_to_customer')
                                ->label('Visibile al cliente')
                                ->badge()
                                ->formatStateUsing(fn (bool $state): string => $state ? 'Sì' : 'Solo interno')
                                ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                        ])
                        ->columns(5),
                ]),
        ]);
    }

    /* ════════════════════════════════════════════════════════
     *  TABLE
     * ════════════════════════════════════════════════════════ */

    public static function table(Table $table): Table
    {
        $isStore = Auth::user()?->isStoreUser() ?? false;
        $isAdmin = Auth::user()?->isAdmin() ?? false;

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('store.name')
                    ->label('Negozio')
                    ->searchable()
                    ->sortable()
                    ->visible($isAdmin),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(30),

                Tables\Columns\TextColumn::make('customer_phone')
                    ->label('Telefono')
                    ->searchable()
                    ->visible($isStore),

                Tables\Columns\TextColumn::make('service_type')
                    ->label('Servizio')
                    ->formatStateUsing(fn (?string $state): ?string => CommissionRule::serviceOptions()[$state] ?? $state)
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('activation_status')
                    ->label('Stato')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => self::activationStatusOptions()[$state] ?? $state)
                    ->color(fn (?string $state) => self::activationStatusColor($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Pagamento')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => self::paymentStatusOptions()[$state] ?? $state)
                    ->color(fn (?string $state) => match ($state) {
                        'paid' => 'success', 'pending' => 'warning', 'failed' => 'danger', default => 'gray',
                    })
                    ->visible($isAdmin),

                Tables\Columns\TextColumn::make('commission_amount')
                    ->label('Commissione')
                    ->money('EUR')
                    ->sortable()
                    ->visible($isAdmin),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters(self::buildFilters($isAdmin))
            ->actions(self::buildActions($isAdmin, $isStore))
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->visible(fn (): bool => Auth::user()?->isSuperAdmin() ?? false),
            ]);
    }

    /* ── Filtri ── */

    private static function buildFilters(bool $isAdmin): array
    {
        $filters = [
            Tables\Filters\Filter::make('solo_attivate')
                ->label('Solo attivate')
                ->toggle()
                ->query(fn (Builder $query, array $data): Builder =>
                    ($data['isActive'] ?? false)
                        ? $query->where('activation_status', 'attivata')
                        : $query
                ),

            Tables\Filters\SelectFilter::make('activation_status')
                ->label('Stato pratica')
                ->options(self::activationStatusOptions()),

            Tables\Filters\SelectFilter::make('service_type')
                ->label('Servizio')
                ->options(CommissionRule::serviceOptions()),
        ];

        if ($isAdmin) {
            $filters[] = Tables\Filters\SelectFilter::make('store_id')
                ->label('Negozio')
                ->options(fn () => Store::query()->orderBy('name')->pluck('name', 'id')->all());

            $filters[] = Tables\Filters\SelectFilter::make('payment_status')
                ->label('Stato pagamento')
                ->options(self::paymentStatusOptions());
        }

        $filters[] = Tables\Filters\Filter::make('created_at')
            ->label('Periodo')
            ->form([
                Forms\Components\DatePicker::make('from')->label('Dal'),
                Forms\Components\DatePicker::make('until')->label('Al'),
            ])
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when($data['from'] ?? null, fn ($query, $d) => $query->whereDate('created_at', '>=', $d))
                ->when($data['until'] ?? null, fn ($query, $d) => $query->whereDate('created_at', '<=', $d)));

        return $filters;
    }

    /* ── Azioni tabella ── */

    private static function buildActions(bool $isAdmin, bool $isStore): array
    {
        $actions = [
            Tables\Actions\ViewAction::make()
                ->label('')
                ->tooltip('Apri')
                ->icon('heroicon-o-eye'),
        ];

        /* Aggiornamento stato — solo admin */
        if ($isAdmin) {
            $actions[] = Tables\Actions\Action::make('aggiorna_stato')
                ->label('')
                ->tooltip('Aggiorna stato')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->form([
                    Forms\Components\Select::make('activation_status')
                        ->label('Nuovo stato pratica')
                        ->options(self::activationStatusOptions())
                        ->required(),

                    Forms\Components\TextInput::make('title')
                        ->label('Titolo aggiornamento')
                        ->required()
                        ->maxLength(200),

                    Forms\Components\Textarea::make('description')
                        ->label('Descrizione (opzionale)')
                        ->rows(3),

                    Forms\Components\Toggle::make('is_visible_to_customer')
                        ->label('Visibile al cliente')
                        ->default(true),
                ])
                ->action(function (FormSubmission $record, array $data): void {
                    $vecchioStato = $record->activation_status;
                    $nuovoStato   = $data['activation_status'];

                    $record->update([
                        'activation_status' => $nuovoStato,
                        'status'            => self::deriveStatus($nuovoStato),
                        'activated_at'      => $nuovoStato === 'attivata' ? ($record->activated_at ?? now()) : $record->activated_at,
                    ]);

                    $record->addEvent(
                        eventType: $nuovoStato,
                        title: $data['title'],
                        description: $data['description'] ?? null,
                        visibleToCustomer: (bool) ($data['is_visible_to_customer'] ?? true),
                    );

                    Notification::make()
                        ->title('Stato aggiornato: ' . (self::activationStatusOptions()[$nuovoStato] ?? $nuovoStato))
                        ->success()
                        ->send();
                });

            /* Nota interna — solo admin */
            $actions[] = Tables\Actions\Action::make('aggiungi_nota')
                ->label('')
                ->tooltip('Aggiungi nota')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->color('gray')
                ->form([
                    Forms\Components\TextInput::make('title')
                        ->label('Titolo nota')
                        ->required()
                        ->maxLength(200),

                    Forms\Components\Textarea::make('description')
                        ->label('Testo')
                        ->rows(3),

                    Forms\Components\Toggle::make('is_visible_to_customer')
                        ->label('Visibile al cliente nella pagina tracking')
                        ->default(false),
                ])
                ->action(function (FormSubmission $record, array $data): void {
                    $record->addEvent(
                        eventType: 'nota',
                        title: $data['title'],
                        description: $data['description'] ?? null,
                        visibleToCustomer: (bool) ($data['is_visible_to_customer'] ?? false),
                    );
                    Notification::make()->title('Nota aggiunta')->success()->send();
                });

            $actions[] = Tables\Actions\EditAction::make()
                ->label('')
                ->tooltip('Modifica');

            /* Correzione stato — solo superadmin, caso eccezionale */
            $actions[] = Tables\Actions\Action::make('correggi_stato')
                ->label('')
                ->tooltip('Correzione eccezionale')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('danger')
                ->visible(fn (FormSubmission $record): bool =>
                    (Auth::user()?->isSuperAdmin() ?? false) &&
                    in_array($record->activation_status, ['attivata', 'annullata', 'respinta'], true)
                )
                ->requiresConfirmation()
                ->modalHeading('Correzione eccezionale stato pratica')
                ->modalDescription('Usa questa funzione SOLO in caso di errore. Ogni modifica viene registrata nel log di audit.')
                ->form([
                    Forms\Components\Select::make('activation_status')
                        ->label('Riporta allo stato')
                        ->options(self::activationStatusOptions())
                        ->required(),

                    Forms\Components\Textarea::make('motivo')
                        ->label('Motivo della correzione (obbligatorio)')
                        ->required()
                        ->rows(3)
                        ->placeholder('Descrivi il motivo della correzione eccezionale...'),
                ])
                ->modalSubmitActionLabel('Applica correzione')
                ->action(function (FormSubmission $record, array $data): void {
                    $vecchio = $record->activation_status;
                    $nuovo   = $data['activation_status'];

                    $record->update([
                        'activation_status' => $nuovo,
                        'status'            => self::deriveStatus($nuovo),
                    ]);

                    $record->addEvent(
                        eventType: 'nota',
                        title: 'Correzione eccezionale stato pratica',
                        description: "Stato modificato da «" . (self::activationStatusOptions()[$vecchio] ?? $vecchio) . "» a «" . (self::activationStatusOptions()[$nuovo] ?? $nuovo) . "».\nMotivo: " . $data['motivo'],
                        visibleToCustomer: false,
                    );

                    Notification::make()
                        ->title('Correzione applicata e registrata nel log')
                        ->warning()
                        ->send();
                });
        }

        return $actions;
    }

    /* ════════════════════════════════════════════════════════
     *  Helpers — opzioni e colori
     * ════════════════════════════════════════════════════════ */

    public static function activationStatusOptions(): array
    {
        return [
            'richiesta'      => 'Nuova richiesta',
            'in_lavorazione' => 'In lavorazione',
            'attivata'       => 'Attivata',
            'respinta'       => 'Respinta',
            'annullata'      => 'Annullata',
        ];
    }

    public static function activationStatusColor(?string $state): string
    {
        return match ($state) {
            'richiesta'      => 'warning',
            'in_lavorazione' => 'primary',
            'attivata'       => 'success',
            'respinta',
            'annullata'      => 'danger',
            default          => 'gray',
        };
    }

    public static function paymentStatusOptions(): array
    {
        return [
            'pending' => 'In attesa',
            'paid'    => 'Pagato',
            'failed'  => 'Non andato a buon fine',
        ];
    }

    public static function paymentMethodOptions(): array
    {
        return [
            'carta'   => 'Carta',
            'negozio' => 'Paga in negozio',
            'dopo'    => 'Paga dopo',
        ];
    }

    public static function commissionStatusOptions(): array
    {
        return [
            'non_maturata' => 'Non maturata',
            'maturata'     => 'Maturata',
            'confermata'   => 'Richiesta liquidazione',
            'liquidata'    => 'Liquidata',
            'stornata'     => 'Stornata',
        ];
    }

    /** Sincronizza status interno con activation_status */
    private static function deriveStatus(string $activationStatus): string
    {
        return match ($activationStatus) {
            'richiesta'      => 'nuova',
            'in_lavorazione' => 'in_lavorazione',
            'attivata'       => 'completata',
            'respinta',
            'annullata'      => 'annullata',
            default          => 'nuova',
        };
    }

    /* ── Pages ── */

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListFormSubmissions::route('/'),
            'create' => Pages\CreateFormSubmission::route('/create'),
            'view'   => Pages\ViewFormSubmission::route('/{record}'),
            'edit'   => Pages\EditFormSubmission::route('/{record}/edit'),
        ];
    }
}
