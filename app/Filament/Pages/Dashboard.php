<?php

namespace App\Filament\Pages;

use App\Models\Assessment;
use App\Models\ModelPerformance;
use App\Models\PredictionResult;
use App\Models\User;
use App\Services\MLPredictionService;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $routePath = '/';
    protected static ?int $navigationSort = -2;

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\AdminStatsWidget::class,
            \App\Filament\Widgets\RiskDistributionWidget::class,
            \App\Filament\Widgets\ModelPerformanceWidget::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return 3;
    }

    public function getHeaderWidgets(): array
    {
        return [\App\Filament\Widgets\AdminStatsWidget::class];
    }
}
