<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormSubmissionResource\Pages;
use App\Models\FormSubmission;
use App\Models\Store;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class FormSubmissionResource extends Resource
{
    protected static ?string $model = FormSubmission::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Richieste';
    protected static ?string $modelLabel = 'Richiesta';
    protected static ?string $pluralModelLabel = 'Richieste';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('store')
            ->visibleTo(Auth::user());
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function canViewAny(): bool
    {
        return Auth::check();
    }

    public static function canView(Model $record): bool
    {
        $user = Auth::user();

        return $user?->isAdmin() || ($user?->store_id && $record->store_id === $user->store_id);
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->isSuperAdmin() ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return Auth::user()?->isSuperAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Dati principali')
                ->schema([
                    Forms\Components\Select::make('store_id')
                        ->label('Negozio')
                        ->relationship('store', 'name')
                        ->searchable()
                        ->preload()
                        ->visible(fn () => Auth::user()?->isAdmin() ?? false)
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

                    Forms\Components\Select::make('status')
                        ->label('Stato pratica')
                        ->options([
                            'bozza' => 'Bozza',
                            'nuova' => 'Nuova',
                            'in_lavorazione' => 'In lavorazione',
                            'completata' => 'Completata',
                            'annullata' => 'Annullata',
                        ])
                        ->required(),

                    Forms\Components\Select::make('activation_status')
                        ->label('Stato attivazione')
                        ->options([
                            'richiesta' => 'Richiesta',
                            'in_lavorazione' => 'In lavorazione',
                            'attivata' => 'Attivata',
                            'respinta' => 'Respinta',
                            'annullata' => 'Annullata',
                        ])
                        ->required(),

                    Forms\Components\DateTimePicker::make('activated_at')
                        ->label('Data attivazione')
                        ->seconds(false),

                    Forms\Components\Select::make('payment_method')
                        ->label('Metodo pagamento')
                        ->options([
                            'carta' => 'Carta',
                            'negozio' => 'Paga in negozio',
                            'dopo' => 'Paga dopo',
                        ]),

                    Forms\Components\Select::make('payment_status')
                        ->label('Stato pagamento')
                        ->options([
                            'pending' => 'In attesa',
                            'paid' => 'Pagato',
                            'failed' => 'Fallito',
                        ])
                        ->required(),

                    Forms\Components\TextInput::make('total_amount')
                        ->label('Totale')
                        ->numeric()
                        ->prefix('€'),

                    Forms\Components\TextInput::make('commission_amount')
                        ->label('Commissione')
                        ->numeric()
                        ->prefix('€'),

                    Forms\Components\Select::make('commission_status')
                        ->label('Stato commissione')
                        ->options([
                            'non_maturata' => 'Non maturata',
                            'maturata' => 'Maturata',
                            'confermata' => 'Confermata',
                            'liquidata' => 'Liquidata',
                            'stornata' => 'Stornata',
                        ])
                        ->required(),

                    Forms\Components\DateTimePicker::make('commission_confirmed_at')
                        ->label('Commissione confermata il')
                        ->seconds(false),

                    Forms\Components\DateTimePicker::make('commission_paid_at')
                        ->label('Commissione liquidata il')
                        ->seconds(false),

                    Forms\Components\Textarea::make('admin_notes')
                        ->label('Note amministrazione')
                        ->rows(5)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Modifica dati cliente')
                ->schema([
                    Forms\Components\TextInput::make('payload.dati.nome')
                        ->label('Nome')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('payload.dati.cognome')
                        ->label('Cognome')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('payload.dati.codice_fiscale')
                        ->label('Codice fiscale')
                        ->maxLength(16),

                    Forms\Components\Select::make('payload.documento.tipo_documento')
                        ->label('Tipo documento')
                        ->options([
                            'carta_identita' => 'Carta d’identità',
                            'passaporto' => 'Passaporto',
                            'patente' => 'Patente',
                        ]),

                    Forms\Components\TextInput::make('payload.documento.numero_documento')
                        ->label('Numero documento')
                        ->maxLength(50),

                    Forms\Components\DatePicker::make('payload.documento.data_scadenza')
                        ->label('Scadenza documento'),

                    Forms\Components\TextInput::make('payload.contatti.email')
                        ->label('Email cliente')
                        ->email()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('payload.contatti.cellulare')
                        ->label('Cellulare')
                        ->maxLength(50),
                ])
                ->columns(2)
                ->collapsed(),

            Forms\Components\Section::make('Indirizzo spedizione')
                ->schema([
                    Forms\Components\TextInput::make('payload.indirizzi.spedizione.destinatario')
                        ->label('Destinatario'),

                    Forms\Components\TextInput::make('payload.indirizzi.spedizione.cap')
                        ->label('CAP'),

                    Forms\Components\TextInput::make('payload.indirizzi.spedizione.citta')
                        ->label('Città'),

                    Forms\Components\TextInput::make('payload.indirizzi.spedizione.indirizzo')
                        ->label('Indirizzo'),

                    Forms\Components\TextInput::make('payload.indirizzi.spedizione.civico')
                        ->label('Civico'),
                ])
                ->columns(2)
                ->collapsed(),

            Forms\Components\Section::make('Indirizzo residenza')
                ->schema([
                    Forms\Components\Toggle::make('payload.indirizzi.residenza_diversa')
                        ->label('Residenza diversa dalla spedizione'),

                    Forms\Components\TextInput::make('payload.indirizzi.residenza.cap')
                        ->label('CAP'),

                    Forms\Components\TextInput::make('payload.indirizzi.residenza.citta')
                        ->label('Città'),

                    Forms\Components\TextInput::make('payload.indirizzi.residenza.indirizzo')
                        ->label('Indirizzo'),

                    Forms\Components\TextInput::make('payload.indirizzi.residenza.civico')
                        ->label('Civico'),
                ])
                ->columns(2)
                ->collapsed(),

            Forms\Components\Section::make('Scelte commerciali')
                ->schema([
                    Forms\Components\Select::make('payload.numero.scelta')
                        ->label('Scelta numero')
                        ->options([
                            'nuovo' => 'Nuovo numero',
                            'portabilita' => 'Mantiene il numero',
                        ]),

                    Forms\Components\Select::make('payload.pagamento.metodo')
                        ->label('Metodo scelto dal cliente')
                        ->options([
                            'carta' => 'Carta',
                            'negozio' => 'Paga in negozio',
                            'dopo' => 'Paga dopo',
                        ]),

                    Forms\Components\TextInput::make('payload.pagamento.codice_amico')
                        ->label('Codice amico'),

                    Forms\Components\Toggle::make('payload.servizi.opzione_5g')
                        ->label('Opzione 5G'),

                    Forms\Components\Toggle::make('payload.servizi.ricarica_automatica')
                        ->label('Ricarica automatica'),

                    Forms\Components\Toggle::make('payload.servizi.safe_call')
                        ->label('SafeCall'),

                    Forms\Components\Toggle::make('payload.servizi.total_security')
                        ->label('Total Security'),
                ])
                ->columns(2)
                ->collapsed(),

            Forms\Components\Section::make('JSON completo')
                ->schema([
                    Forms\Components\Textarea::make('payload_json_readonly')
                        ->label('Payload JSON')
                        ->rows(16)
                        ->formatStateUsing(fn ($record) => json_encode($record?->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),
                ])
                ->collapsed(),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
{
    return $infolist->schema([
        InfoSection::make('Dati principali')
            ->schema([
                TextEntry::make('store.name')->label('Negozio')->placeholder('-'),
                TextEntry::make('customer_name')->label('Nome cliente'),
                TextEntry::make('customer_email')->label('Email'),
                TextEntry::make('customer_phone')->label('Telefono'),
                TextEntry::make('status')
                    ->label('Stato pratica')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'bozza' => 'Bozza',
                        'nuova' => 'Nuova',
                        'in_lavorazione' => 'In lavorazione',
                        'completata' => 'Completata',
                        'annullata' => 'Annullata',
                        default => $state,
                    }),
                TextEntry::make('payment_method')
                    ->label('Metodo pagamento')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'carta' => 'Carta',
                        'negozio' => 'Paga in negozio',
                        'dopo' => 'Paga dopo',
                        default => $state,
                    }),
                TextEntry::make('payment_status')
                    ->label('Stato pagamento')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'pending' => 'In attesa',
                        'paid' => 'Pagato',
                        'failed' => 'Fallito',
                        default => $state,
                    }),
                TextEntry::make('total_amount')->label('Totale')->money('EUR'),
                TextEntry::make('activation_status')->label('Stato attivazione'),
                TextEntry::make('activated_at')->label('Data attivazione')->dateTime('d/m/Y H:i')->placeholder('-'),
                TextEntry::make('commission_amount')->label('Commissione')->money('EUR'),
                TextEntry::make('commission_status')->label('Stato commissione'),
                TextEntry::make('commission_confirmed_at')->label('Confermata il')->dateTime('d/m/Y H:i')->placeholder('-'),
                TextEntry::make('commission_paid_at')->label('Liquidata il')->dateTime('d/m/Y H:i')->placeholder('-'),
            ])
            ->columns(2),

        InfoSection::make('Cliente')
            ->schema([
                TextEntry::make('payload.dati.nome')->label('Nome'),
                TextEntry::make('payload.dati.cognome')->label('Cognome'),
                TextEntry::make('payload.dati.codice_fiscale')->label('Codice fiscale'),
            ])
            ->columns(3),

        InfoSection::make('Consensi e autorizzazioni')
            ->schema([
                TextEntry::make('payload.dati.consensi.marketing')
                    ->label('Consenso marketing')
                    ->formatStateUsing(fn ($state) => $state ? 'Sì' : 'No'),

                TextEntry::make('payload.dati.consensi.accetta_condizioni')
                    ->label('Accetta condizioni')
                    ->formatStateUsing(fn ($state) => $state ? 'Sì' : 'No'),

                TextEntry::make('payload.dati.consensi.attivazione_immediata')
                    ->label('Attivazione immediata')
                    ->formatStateUsing(fn ($state) => $state ? 'Sì' : 'No'),
            ])
            ->columns(3),

        InfoSection::make('Documento')
            ->schema([
                TextEntry::make('payload.documento.tipo_documento')
                    ->label('Tipo documento')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'carta_identita' => 'Carta d’identità',
                        'passaporto' => 'Passaporto',
                        'patente' => 'Patente',
                        default => $state,
                    }),

                TextEntry::make('payload.documento.numero_documento')->label('Numero'),
                TextEntry::make('payload.documento.data_scadenza')->label('Scadenza'),
            ])
            ->columns(3),

        InfoSection::make('Contatti')
            ->schema([
                TextEntry::make('payload.contatti.email')->label('Email'),
                TextEntry::make('payload.contatti.cellulare')->label('Cellulare'),
            ])
            ->columns(2),

        InfoSection::make('Spedizione')
            ->schema([
                TextEntry::make('payload.indirizzi.spedizione.destinatario')->label('Destinatario'),
                TextEntry::make('payload.indirizzi.spedizione.cap')->label('CAP'),
                TextEntry::make('payload.indirizzi.spedizione.citta')->label('Città'),
                TextEntry::make('payload.indirizzi.spedizione.indirizzo')->label('Indirizzo'),
                TextEntry::make('payload.indirizzi.spedizione.civico')->label('Civico'),
            ])
            ->columns(2),

        InfoSection::make('Residenza')
            ->schema([
                TextEntry::make('payload.indirizzi.residenza_diversa')
                    ->label('Residenza diversa da spedizione')
                    ->formatStateUsing(fn ($state) => $state ? 'Sì' : 'No'),

                TextEntry::make('payload.indirizzi.residenza.cap')->label('CAP'),
                TextEntry::make('payload.indirizzi.residenza.citta')->label('Città'),
                TextEntry::make('payload.indirizzi.residenza.indirizzo')->label('Indirizzo'),
                TextEntry::make('payload.indirizzi.residenza.civico')->label('Civico'),
            ])
            ->columns(2)
            ->collapsed(),

        InfoSection::make('Servizi scelti')
            ->schema([
                TextEntry::make('payload.numero.scelta')
                    ->label('Numero')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'nuovo' => 'Nuovo numero',
                        'portabilita' => 'Mantiene il numero',
                        default => $state,
                    }),

                TextEntry::make('payload.pagamento.metodo')
                    ->label('Metodo scelto')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'carta' => 'Carta',
                        'negozio' => 'Paga in negozio',
                        'dopo' => 'Paga dopo',
                        default => $state,
                    }),

                TextEntry::make('payload.pagamento.codice_amico')
                    ->label('Codice amico')
                    ->placeholder('-'),

                TextEntry::make('payload.servizi.opzione_5g')
                    ->label('5G')
                    ->formatStateUsing(fn ($state) => $state ? 'Sì' : 'No'),

                TextEntry::make('payload.servizi.ricarica_automatica')
                    ->label('Ricarica automatica')
                    ->formatStateUsing(fn ($state) => $state ? 'Sì' : 'No'),

                TextEntry::make('payload.servizi.safe_call')
                    ->label('SafeCall')
                    ->formatStateUsing(fn ($state) => $state ? 'Sì' : 'No'),

                TextEntry::make('payload.servizi.total_security')
                    ->label('Total Security')
                    ->formatStateUsing(fn ($state) => $state ? 'Sì' : 'No'),
            ])
            ->columns(2),
    ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('store.name')
                    ->label('Negozio')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => Auth::user()?->isAdmin() ?? false)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(30),

                Tables\Columns\TextColumn::make('customer_email')
                    ->label('Email')
                    ->searchable()
                    ->wrap()
                    ->limit(28),

                Tables\Columns\TextColumn::make('customer_phone')
    ->label('Telefono')
    ->searchable()
    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->colors([
                        'gray' => 'bozza',
                        'warning' => 'nuova',
                        'primary' => 'in_lavorazione',
                        'success' => 'completata',
                        'danger' => 'annullata',
                    ]),

                Tables\Columns\TextColumn::make('activation_status')
                    ->label('Attivazione')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'richiesta' => 'Richiesta',
                        'in_lavorazione' => 'In lavorazione',
                        'attivata' => 'Attivata',
                        'respinta' => 'Respinta',
                        'annullata' => 'Annullata',
                        default => $state,
                    })
                    ->colors([
                        'gray' => 'richiesta',
                        'primary' => 'in_lavorazione',
                        'success' => 'attivata',
                        'danger' => ['respinta', 'annullata'],
                    ]),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metodo')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'carta' => 'Carta',
                        'negozio' => 'Negozio',
                        'dopo' => 'Paga dopo',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'carta',
                        'warning' => 'negozio',
                        'gray' => 'dopo',
                    ]),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Pagamento')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'pending' => 'In attesa',
                        'paid' => 'Pagato',
                        'failed' => 'Fallito',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                    ]),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Totale')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('commission_amount')
                    ->label('Commissione')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('commission_status')
                    ->label('Stato comm.')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'non_maturata' => 'Non maturata',
                        'maturata' => 'Maturata',
                        'confermata' => 'Confermata',
                        'liquidata' => 'Liquidata',
                        'stornata' => 'Stornata',
                        default => $state,
                    })
                    ->colors([
                        'gray' => 'non_maturata',
                        'warning' => 'maturata',
                        'primary' => 'confermata',
                        'success' => 'liquidata',
                        'danger' => 'stornata',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('store_id')
                    ->label('Negozio')
                    ->options(fn () => Store::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->visible(fn () => Auth::user()?->isAdmin() ?? false),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Stato pratica')
                    ->options([
                        'bozza' => 'Bozza',
                        'nuova' => 'Nuova',
                        'in_lavorazione' => 'In lavorazione',
                        'completata' => 'Completata',
                        'annullata' => 'Annullata',
                    ]),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Stato pagamento')
                    ->options([
                        'pending' => 'In attesa',
                        'paid' => 'Pagato',
                        'failed' => 'Fallito',
                    ]),

                Tables\Filters\SelectFilter::make('activation_status')
                    ->label('Stato attivazione')
                    ->options([
                        'richiesta' => 'Richiesta',
                        'in_lavorazione' => 'In lavorazione',
                        'attivata' => 'Attivata',
                        'respinta' => 'Respinta',
                        'annullata' => 'Annullata',
                    ]),

                Tables\Filters\SelectFilter::make('commission_status')
                    ->label('Stato commissione')
                    ->options([
                        'non_maturata' => 'Non maturata',
                        'maturata' => 'Maturata',
                        'confermata' => 'Confermata',
                        'liquidata' => 'Liquidata',
                        'stornata' => 'Stornata',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Apri'),
                Tables\Actions\EditAction::make()
                    ->label('Modifica')
                    ->visible(fn () => Auth::user()?->isAdmin() ?? false),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()?->isSuperAdmin() ?? false),
                ])->visible(fn () => Auth::user()?->isSuperAdmin() ?? false),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFormSubmissions::route('/'),
            'create' => Pages\CreateFormSubmission::route('/create'),
            'view' => Pages\ViewFormSubmission::route('/{record}'),
            'edit' => Pages\EditFormSubmission::route('/{record}/edit'),
        ];
    }
}
