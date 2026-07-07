<?php

namespace App\Filament\Widgets;

use App\Models\PredictionResult;
use Filament\Widgets\ChartWidget;

class RiskDistributionWidget extends ChartWidget
{
    protected static ?string $heading = '🍩 Risk Distribution';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $dist = PredictionResult::selectRaw('risk_level, count(*) as count')
            ->groupBy('risk_level')
            ->pluck('count', 'risk_level');

        return [
            'datasets' => [
                [
                    'data'            => [
                        $dist['High'] ?? 0,
                        $dist['Moderate'] ?? 0,
                        $dist['Low'] ?? 0,
                    ],
                    'backgroundColor' => ['#ef4444', '#f59e0b', '#10b981'],
                    'borderWidth'     => 2,
                    'borderColor'     => '#ffffff',
                ],
            ],
            'labels' => ['High Risk', 'Moderate Risk', 'Low Risk'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'bottom'],
            ],
            'cutout' => '65%',
        ];
    }
}
