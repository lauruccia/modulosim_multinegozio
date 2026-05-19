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
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CommissionResource extends Resource
{
    protected static ?string $model = FormSubmission::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Commissioni';
    protected static ?string $modelLabel = 'Commissione';
    protected static ?string $pluralModelLabel = 'Commissioni';
    protected static ?string $slug = 'commissions';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('store')
            ->with('commissionRule')
            ->visibleTo(Auth::user())
            ->where('commission_status', '!=', 'non_maturata');
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
            InfoSection::make('Commissione')
                ->schema([
                    TextEntry::make('store.name')->label('Negozio')->placeholder('-'),
                    TextEntry::make('service_type')
                        ->label('Servizio')
                        ->formatStateUsing(fn (?string $state): ?string => CommissionRule::serviceOptions()[$state] ?? $state),
                    TextEntry::make('customer_name')->label('Cliente'),
                    TextEntry::make('activation_status')->label('Stato attivazione'),
                    TextEntry::make('activated_at')->label('Data attivazione')->dateTime('d/m/Y H:i')->placeholder('-'),
                    TextEntry::make('commission_amount')->label('Importo')->money('EUR'),
                    TextEntry::make('commissionRule.name')->label('Regola')->placeholder('-'),
                    TextEntry::make('commission_status')->label('Stato'),
                    TextEntry::make('commission_confirmed_at')->label('Confermata il')->dateTime('d/m/Y H:i')->placeholder('-'),
                    TextEntry::make('commission_paid_at')->label('Liquidata il')->dateTime('d/m/Y H:i')->placeholder('-'),
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

                Tables\Columns\TextColumn::make('commission_amount')
                    ->label('Importo')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('commission_status')
                    ->label('Stato')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('commission_paid_at')
                    ->label('Liquidata il')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->defaultSort('commission_confirmed_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('store_id')
                    ->label('Negozio')
                    ->options(fn () => Store::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->visible(fn () => Auth::user()?->isAdmin() ?? false),

                Tables\Filters\SelectFilter::make('service_type')
                    ->label('Servizio')
                    ->options(CommissionRule::serviceOptions()),

                Tables\Filters\SelectFilter::make('commission_status')
                    ->label('Stato')
                    ->options([
                        'maturata' => 'Maturata',
                        'confermata' => 'Confermata',
                        'liquidata' => 'Liquidata',
                        'stornata' => 'Stornata',
                    ]),

                Tables\Filters\Filter::make('commission_confirmed_at')
                    ->label('Periodo maturazione')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dal'),
                        Forms\Components\DatePicker::make('until')->label('Al'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('commission_confirmed_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('commission_confirmed_at', '<=', $date))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Apri'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCommissions::route('/'),
            'view' => Pages\ViewCommission::route('/{record}'),
        ];
    }
}
