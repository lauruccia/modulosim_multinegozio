<?php

namespace App\Filament\Pages;

use App\Models\Store;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class StoreProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon    = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel   = 'Profilo negozio';
    protected static ?string $title             = 'Profilo negozio';
    protected static ?int    $navigationSort    = 90;
    protected static string  $view              = 'filament.pages.store-profile';

    /* Visibile solo agli operatori negozio */
    public static function canAccess(): bool
    {
        return Auth::user()?->isStoreUser() ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->isStoreUser() ?? false;
    }

    /* ── Stato form ── */

    public ?array $data = [];

    public function mount(): void
    {
        $store = $this->store();

        $this->form->fill([
            'contact_name'        => $store?->contact_name,
            'email'               => $store?->email,
            'phone'               => $store?->phone,
            'iban'                => $store?->iban,
            'bank_account_holder' => $store?->bank_account_holder,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dati di contatto')
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
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Conto bancario per commissioni')
                    ->description('Inserisci le coordinate bancarie su cui vuoi ricevere il pagamento delle commissioni maturate.')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Forms\Components\TextInput::make('bank_account_holder')
                            ->label('Intestatario del conto')
                            ->maxLength(120)
                            ->placeholder('Es: Mario Rossi / Negozio XYZ srl'),

                        Forms\Components\TextInput::make('iban')
                            ->label('IBAN')
                            ->maxLength(34)
                            ->placeholder('IT60 X054 2811 1010 0000 0123 456')
                            ->helperText('Inserisci l\'IBAN senza spazi oppure con spazi come da estratto conto.'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $store = $this->store();

        if (! $store) {
            Notification::make()
                ->title('Negozio non trovato')
                ->danger()
                ->send();
            return;
        }

        $store->update([
            'contact_name'        => $data['contact_name'] ?? null,
            'email'               => $data['email'] ?? null,
            'phone'               => $data['phone'] ?? null,
            'iban'                => $data['iban'] ? strtoupper(str_replace(' ', '', $data['iban'])) : null,
            'bank_account_holder' => $data['bank_account_holder'] ?? null,
        ]);

        Notification::make()
            ->title('Profilo aggiornato!')
            ->success()
            ->send();
    }

    private function store(): ?Store
    {
        $user = Auth::user();
        return $user?->store_id ? Store::find($user->store_id) : null;
    }
}
