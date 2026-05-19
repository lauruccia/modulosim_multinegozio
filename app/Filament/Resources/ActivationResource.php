<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivationResource\Pages;
use App\Models\CommissionRule;
use App\Models\FormSubmission;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivationResource extends Resource
{
    protected static ?string $model = FormSubmission::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static ?string $navigationLabel = 'Attivazioni';
    protected static ?string $modelLabel = 'Attivazione';
    protected static ?string $pluralModelLabel = 'Attivazioni';
    protected static ?string $slug = 'activations';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('store')
            ->with('commissionRule')
            ->visibleTo(Auth::user())
            ->where('activation_status', 'attivata');
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

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            InfoSection::make('Attivazione')
                ->schema([
                    TextEntry::make('store.name')->label('Negozio')->placeholder('-'),
                    TextEntry::make('service_type')
                        ->label('Servizio')
                        ->formatStateUsing(fn (?string $state): ?string => CommissionRule::serviceOptions()[$state] ?? $state),
                    TextEntry::make('customer_name')->label('Cliente'),
                    TextEntry::make('customer_email')->label('Email'),
                    TextEntry::make('customer_phone')->label('Telefono'),
                    TextEntry::make('activated_at')->label('Data attivazione')->dateTime('d/m/Y H:i')->placeholder('-'),
                    TextEntry::make('total_amount')->label('Totale')->money('EUR'),
                    TextEntry::make('commission_amount')->label('Commissione')->money('EUR'),
                    TextEntry::make('commissionRule.name')->label('Regola commissione')->placeholder('-'),
                    TextEntry::make('commission_status')->label('Stato commissione'),
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
                    ->sortable(),

                Tables\Columns\TextColumn::make('store.name')
                    ->label('Negozio')
                    ->visible(fn () => Auth::user()?->isAdmin() ?? false)
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
                    ->toggleable(),

                Tables\Columns\TextColumn::make('activated_at')
                    ->label('Attivata il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Totale')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('commission_amount')
                    ->label('Commissione')
                    ->money('EUR')
                    ->sortable(),
            ])
            ->defaultSort('activated_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make()->label('Apri'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivations::route('/'),
            'view' => Pages\ViewActivation::route('/{record}'),
        ];
    }
}
