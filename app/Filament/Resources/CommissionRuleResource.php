<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommissionRuleResource\Pages;
use App\Models\CommissionRule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CommissionRuleResource extends Resource
{
    protected static ?string $model = CommissionRule::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-euro';
    protected static ?string $navigationGroup = 'Gestione';
    protected static ?string $navigationLabel = 'Regole commissioni';
    protected static ?string $modelLabel = 'Regola commissione';
    protected static ?string $pluralModelLabel = 'Regole commissioni';

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
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->isSuperAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Regola')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nome')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Select::make('service_type')
                        ->label('Servizio')
                        ->options(CommissionRule::serviceOptions())
                        ->required()
                        ->default('sim'),

                    Forms\Components\Select::make('trigger_status')
                        ->label('Quando matura')
                        ->options(CommissionRule::triggerOptions())
                        ->required()
                        ->default('attivata'),

                    Forms\Components\TextInput::make('amount')
                        ->label('Commissione')
                        ->numeric()
                        ->prefix('€')
                        ->required()
                        ->default(5),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Attiva')
                        ->default(true),

                    Forms\Components\Textarea::make('notes')
                        ->label('Note')
                        ->rows(4)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('service_type')
                    ->label('Servizio')
                    ->formatStateUsing(fn (?string $state): ?string => CommissionRule::serviceOptions()[$state] ?? $state)
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('trigger_status')
                    ->label('Evento')
                    ->formatStateUsing(fn (?string $state): ?string => CommissionRule::triggerOptions()[$state] ?? $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Importo')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Attiva')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('service_type')
                    ->label('Servizio')
                    ->options(CommissionRule::serviceOptions()),

                Tables\Filters\SelectFilter::make('trigger_status')
                    ->label('Evento')
                    ->options(CommissionRule::triggerOptions()),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Attiva'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Modifica'),
                Tables\Actions\DeleteAction::make()
                    ->label('Elimina')
                    ->visible(fn () => Auth::user()?->isSuperAdmin() ?? false),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->visible(fn () => Auth::user()?->isSuperAdmin() ?? false),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCommissionRules::route('/'),
            'create' => Pages\CreateCommissionRule::route('/create'),
            'edit' => Pages\EditCommissionRule::route('/{record}/edit'),
        ];
    }
}
