<?php

namespace App\Filament\Widgets;

use App\Models\FormSubmission;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class RequestStats extends BaseWidget
{
    protected function getStats(): array
    {
        $query = FormSubmission::query()->visibleTo(Auth::user());

        return [
            Stat::make('Richieste totali', (clone $query)->count())
                ->description('Richieste visibili per il tuo ruolo')
                ->color('primary'),

            Stat::make('Nuove richieste', (clone $query)->where('status', 'nuova')->count())
                ->description('Da prendere in carico')
                ->color('warning'),

            Stat::make('Attivazioni', (clone $query)->where('activation_status', 'attivata')->count())
                ->description('Richieste attivate')
                ->color('success'),

            Stat::make('Commissioni maturate', '€ ' . number_format((float) (clone $query)
                ->whereIn('commission_status', ['maturata', 'confermata', 'liquidata'])
                ->sum('commission_amount'), 2, ',', '.'))
                ->description('Totale commissionabile visibile')
                ->color('primary'),
        ];
    }
}
