<?php

namespace App\Filament\Widgets;

use App\Models\CommissionRule;
use App\Models\FormSubmission;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class CommissionsByService extends Widget
{
    protected static string $view = 'filament.widgets.commissions-by-service';

    // Polling ogni 30 secondi per aggiornamenti in tempo reale
    protected static ?string $pollingInterval = '30s';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public function getRows(): array
    {
        $user  = Auth::user();
        $query = FormSubmission::query()->visibleTo($user);

        $serviceTypes = array_keys(CommissionRule::serviceOptions());
        $rows = [];

        foreach ($serviceTypes as $type) {
            $q = (clone $query)->where('service_type', $type);

            $total      = (clone $q)->count();
            $activated  = (clone $q)->where('activation_status', 'attivata')->count();
            $pending    = (clone $q)->whereIn('activation_status', ['richiesta', 'in_lavorazione'])->count();

            $matured    = (float)(clone $q)->whereIn('commission_status', ['maturata', 'confermata', 'liquidata'])->sum('commission_amount');
            $confirmed  = (float)(clone $q)->whereIn('commission_status', ['confermata', 'liquidata'])->sum('commission_amount');
            $paid       = (float)(clone $q)->where('commission_status', 'liquidata')->sum('commission_amount');

            if ($total === 0) {
                continue; // Nascondi servizi senza dati
            }

            $rows[] = [
                'type'      => $type,
                'label'     => CommissionRule::serviceOptions()[$type] ?? $type,
                'total'     => $total,
                'activated' => $activated,
                'pending'   => $pending,
                'matured'   => $matured,
                'confirmed' => $confirmed,
                'paid'      => $paid,
            ];
        }

        return $rows;
    }

    public function formatMoney(float $amount): string
    {
        return '€ ' . number_format($amount, 2, ',', '.');
    }

    public static function canView(): bool
    {
        return Auth::check();
    }
}
