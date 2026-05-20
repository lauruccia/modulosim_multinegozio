<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Gestione';
    protected static ?string $navigationLabel = 'Utenti';
    protected static ?string $modelLabel = 'Utente';
    protected static ?string $pluralModelLabel = 'Utenti';

    /* Nasconde i super_admin a chi non è superadmin */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (! (Auth::user()?->isSuperAdmin() ?? false)) {
            $query->where('role', '!=', 'super_admin');
        }

        return $query;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();

        if (! $user?->isAdmin()) {
            return false;
        }

        return $user->isSuperAdmin() || $record->role !== 'super_admin';
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
            Forms\Components\TextInput::make('name')
                ->label('Nome')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),

            Forms\Components\TextInput::make('password')
                ->label('Password')
                ->password()
                ->required(fn (string $operation): bool => $operation === 'create')
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->maxLength(255),

            Forms\Components\Select::make('role')
                ->label('Ruolo')
                ->options(function (): array {
                    $options = ['admin' => 'Amministratore', 'store' => 'Negozio'];
                    if (Auth::user()?->isSuperAdmin()) {
                        $options = ['super_admin' => 'Superamministratore'] + $options;
                    }
                    return $options;
                })
                ->required()
                ->live(),

            Forms\Components\Select::make('store_id')
                ->label('Negozio associato')
                ->relationship('store', 'name')
                ->searchable()
                ->preload()
                ->visible(fn (Forms\Get $get): bool => $get('role') === 'store')
                ->required(fn (Forms\Get $get): bool => $get('role') === 'store'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('role')
                    ->label('Ruolo')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'super_admin' => 'Superamministratore',
                        'admin' => 'Amministratore',
                        'store' => 'Negozio',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('store.name')
                    ->label('Negozio')
                    ->placeholder('-')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Ruolo')
                    ->options(function (): array {
                        $options = ['admin' => 'Amministratore', 'store' => 'Negozio'];
                        if (Auth::user()?->isSuperAdmin()) {
                            $options = ['super_admin' => 'Superamministratore'] + $options;
                        }
                        return $options;
                    }),

                Tables\Filters\SelectFilter::make('store_id')
                    ->label('Negozio')
                    ->relationship('store', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Modifica'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
