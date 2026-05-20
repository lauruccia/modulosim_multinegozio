<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StoreResource\Pages;
use App\Models\Store;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class StoreResource extends Resource
{
    protected static ?string $model = Store::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Gestione';
    protected static ?int    $navigationSort    = 10;
    protected static ?string $navigationLabel = 'Negozi';
    protected static ?string $modelLabel = 'Negozio';
    protected static ?string $pluralModelLabel = 'Negozi';

    /* ── Permissions ── */

    public static function shouldRegisterNavigation(): bool { return Auth::user()?->isAdmin() ?? false; }
    public static function canAccess(): bool                { return Auth::user()?->isAdmin() ?? false; }
    public static function canViewAny(): bool               { return Auth::user()?->isAdmin() ?? false; }
    public static function canCreate(): bool                { return Auth::user()?->isAdmin() ?? false; }
    public static function canEdit(Model $record): bool     { return Auth::user()?->isAdmin() ?? false; }
    public static function canDelete(Model $record): bool   { return Auth::user()?->isSuperAdmin() ?? false; }
    public static function canDeleteAny(): bool             { return Auth::user()?->isSuperAdmin() ?? false; }

    /* ── Form ── */

    public static function form(Form $form): Form
    {
        return $form->schema([

            /* ── Dati base ── */
            Forms\Components\Section::make('Dati negozio')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nome negozio')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('slug')
                        ->label('Slug link pubblico')
                        ->helperText('Es: centro-roma → link /negozi/centro-roma/attivazione/dati')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    Forms\Components\TextInput::make('code')
                        ->label('Codice negozio')
                        ->unique(ignoreRecord: true)
                        ->maxLength(50),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Attivo')
                        ->default(true),
                ])
                ->columns(2),

            /* ── Referente ── */
            Forms\Components\Section::make('Referente')
                ->schema([
                    Forms\Components\TextInput::make('contact_name')
                        ->label('Nominativo referente')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('phone')
                        ->label('Telefono')
                        ->maxLength(50),

                    Forms\Components\TextInput::make('commission_rate')
                        ->label('Commissione default (%)')
                        ->numeric()
                        ->suffix('%')
                        ->default(0),
                ])
                ->columns(2),

            /* ── Dati bancari ── */
            Forms\Components\Section::make('Coordinate bancarie')
                ->description('IBAN su cui il negozio riceve il pagamento delle commissioni. Il negozio può aggiornare questi dati autonomamente dal Profilo negozio.')
                ->icon('heroicon-o-credit-card')
                ->schema([
                    Forms\Components\TextInput::make('bank_account_holder')
                        ->label('Intestatario del conto')
                        ->maxLength(120),

                    Forms\Components\TextInput::make('iban')
                        ->label('IBAN')
                        ->maxLength(34),
                ])
                ->columns(2)
                ->collapsible()
                ->collapsed(),

            /* ── Branding & White-label ── */
            Forms\Components\Section::make('Branding & White-label')
                ->description('Personalizza l\'aspetto del wizard per questo negozio. Ogni campo è opzionale.')
                ->icon('heroicon-o-paint-brush')
                ->schema([

                    Forms\Components\FileUpload::make('logo_path')
                        ->label('Logo negozio')
                        ->helperText('PNG o SVG con sfondo trasparente. Altezza consigliata: 80-120px. Max 2MB.')
                        ->image()
                        ->disk('public')
                        ->directory('stores/logos')
                        ->imagePreviewHeight('80')
                        ->acceptedFileTypes(['image/png', 'image/svg+xml', 'image/jpeg', 'image/webp'])
                        ->maxSize(2048),

                    Forms\Components\FileUpload::make('favicon_path')
                        ->label('Favicon (.ico / .png)')
                        ->helperText('Icona tab browser. Dimensione ideale: 32×32px. Max 512KB.')
                        ->image()
                        ->disk('public')
                        ->directory('stores/favicons')
                        ->imagePreviewHeight('32')
                        ->acceptedFileTypes(['image/x-icon', 'image/png'])
                        ->maxSize(512),

                    Forms\Components\ColorPicker::make('primary_color')
                        ->label('Colore primario')
                        ->helperText('Bottoni, step attivi, focus campi.')
                        ->default('#dddc00'),

                    Forms\Components\ColorPicker::make('secondary_color')
                        ->label('Colore secondario')
                        ->helperText('Testo su sfondo primario, logo testuale.')
                        ->default('#1d1d1b'),

                    Forms\Components\Select::make('font_family')
                        ->label('Font')
                        ->options(Store::fontOptions())
                        ->default('Arial')
                        ->helperText('Font diversi da Arial vengono caricati da Google Fonts.'),

                    Forms\Components\TextInput::make('custom_name')
                        ->label('Nome visualizzato (custom)')
                        ->helperText('Sostituisce il nome negozio nel wizard e nelle email. Lascia vuoto per usare il nome standard.')
                        ->maxLength(120),

                    Forms\Components\TextInput::make('custom_domain')
                        ->label('Dominio custom')
                        ->helperText('Es: attivazioni.mionegozio.it — Il DNS deve puntare a questo server.')
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->prefix('https://'),

                    Forms\Components\Section::make('Testi personalizzabili')
                        ->description('Lascia vuoto per usare i testi predefiniti.')
                        ->schema([
                            Forms\Components\TextInput::make('custom_texts.wizard_title')
                                ->label('Titolo browser del wizard')
                                ->placeholder('Attivazione')
                                ->maxLength(80),

                            Forms\Components\Textarea::make('custom_texts.footer_text')
                                ->label('Testo footer wizard & email')
                                ->placeholder('Nome Negozio — Powered by Sharers')
                                ->rows(2)
                                ->maxLength(200),
                        ])
                        ->columns(1)
                        ->collapsible()
                        ->collapsed(),
                ])
                ->columns(2)
                ->collapsible(),

        ]);
    }

    /* ── Table ── */

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo_path')
                    ->label('')
                    ->disk('public')
                    ->height(32)
                    ->width(80)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Negozio')
                    ->description(fn (Store $record): string => $record->custom_name ? 'custom: ' . $record->custom_name : '')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Slug copiato!'),

                Tables\Columns\TextColumn::make('custom_domain')
                    ->label('Dominio custom')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Attivo')
                    ->boolean(),

                Tables\Columns\TextColumn::make('form_submissions_count')
                    ->label('Pratiche')
                    ->counts('formSubmissions')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Attivo'),
            ])
            ->actions([
                Tables\Actions\Action::make('copy_link')
                    ->label('Copia link')
                    ->icon('heroicon-o-clipboard-document')
                    ->color('gray')
                    ->action(function (Store $record, \Livewire\Component $livewire): void {
                        $url = url("/negozi/{$record->slug}/attivazione/dati");
                        $livewire->js("navigator.clipboard.writeText('{$url}').catch(()=>{})");
                        Notification::make()
                            ->title('Link copiato!')
                            ->body($url)
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make()->label('Modifica'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /* ── Pages ── */

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStores::route('/'),
            'create' => Pages\CreateStore::route('/create'),
            'edit'   => Pages\EditStore::route('/{record}/edit'),
        ];
    }
}
