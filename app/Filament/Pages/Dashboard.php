<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\LatestRequests;
use App\Filament\Widgets\RequestStats;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Panoramica';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?int $navigationSort = -2;

    public function getWidgets(): array
    {
        return [
            RequestStats::class,
            LatestRequests::class,
        ];
    }
}
