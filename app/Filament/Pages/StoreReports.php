<?php

namespace App\Filament\Pages;

use App\Models\FormSubmission;
use App\Models\Store;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class StoreReports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationLabel = 'Report negozi';
    protected static ?string $title = 'Report negozi';
    protected static ?int $navigationSort = 20;
    protected static string $view = 'filament.pages.store-reports';

    public ?string $from = null;
    public ?string $until = null;
    public ?string $storeId = null;

    public function mount(): void
    {
        $this->from = $this->from ?: now()->startOfMonth()->toDateString();
        $this->until = $this->until ?: now()->toDateString();
    }

    public static function canAccess(): bool
    {
        return Auth::check();
    }

    public function getStoresProperty(): Collection
    {
        $user = Auth::user();

        if ($user?->isStoreUser()) {
            return Store::query()
                ->whereKey($user->store_id)
                ->orderBy('name')
                ->get();
        }

        return Store::query()->orderBy('name')->get();
    }

    public function getRowsProperty(): Collection
    {
        $from = Carbon::parse($this->from ?: now()->startOfMonth())->startOfDay();
        $until = Carbon::parse($this->until ?: now())->endOfDay();

        return $this->getStoresProperty()->map(function (Store $store) use ($from, $until): ?array {
            if ($this->storeId && (int) $this->storeId !== $store->id) {
                return null;
            }

            $query = FormSubmission::query()
                ->where('store_id', $store->id)
                ->whereBetween('created_at', [$from, $until]);

            $total = (clone $query)->count();
            $activated = (clone $query)->where('activation_status', 'attivata')->count();
            $rejected = (clone $query)->whereIn('activation_status', ['respinta', 'annullata'])->count();
            $pending = max($total - $activated - $rejected, 0);
            $revenue = (float) (clone $query)->sum('total_amount');
            $commissionsMatured = (float) (clone $query)->whereIn('commission_status', ['maturata', 'confermata', 'liquidata'])->sum('commission_amount');
            $commissionsConfirmed = (float) (clone $query)->whereIn('commission_status', ['confermata', 'liquidata'])->sum('commission_amount');
            $commissionsPaid = (float) (clone $query)->where('commission_status', 'liquidata')->sum('commission_amount');

            $activationHours = (clone $query)
                ->whereNotNull('activated_at')
                ->get(['created_at', 'activated_at'])
                ->map(fn (FormSubmission $submission): int => $submission->created_at->diffInHours($submission->activated_at))
                ->avg();

            return [
                'store' => $store,
                'total' => $total,
                'activated' => $activated,
                'pending' => $pending,
                'rejected' => $rejected,
                'conversion' => $total > 0 ? round(($activated / $total) * 100, 1) : 0,
                'revenue' => $revenue,
                'commissions_matured' => $commissionsMatured,
                'commissions_confirmed' => $commissionsConfirmed,
                'commissions_paid' => $commissionsPaid,
                'activation_hours' => $activationHours !== null ? round($activationHours, 1) : null,
            ];
        })->filter()->values();
    }

    public function formatMoney(float $amount): string
    {
        return '€ ' . number_format($amount, 2, ',', '.');
    }
}
