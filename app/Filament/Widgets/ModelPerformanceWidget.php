<?php

namespace App\Filament\Widgets;

use App\Models\ModelPerformance;
use Filament\Widgets\ChartWidget;

class ModelPerformanceWidget extends ChartWidget
{
    protected static ?string $heading = '🤖 ML Model Performance Comparison';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $models = ModelPerformance::all();

        if ($models->isEmpty()) {
            // Load from metrics.json if DB is empty
            $metricsPath = base_path('../ml-engine/models/metrics.json');
            if (file_exists($metricsPath)) {
                $metrics = json_decode(file_get_contents($metricsPath), true);
                $modelData = $metrics['models'] ?? [];
            } else {
                // Return placeholder data with empty message
                return [
                    'datasets' => [
                        [
                            'label' => 'No ML model data available',
                            'data' => [0],
                            'backgroundColor' => 'rgba(156, 163, 175, 0.5)',
                            'borderColor' => '#9ca3af',
                            'borderWidth' => 1,
                            'borderRadius' => 4,
                        ],
                    ],
                    'labels' => ['No Data'],
                ];
            }

            $labels   = array_keys($modelData);
            $accuracy = array_column(array_values($modelData), 'accuracy');
            $f1       = array_column(array_values($modelData), 'f1');
            $auc      = array_column(array_values($modelData), 'roc_auc');
        } else {
            $labels   = $models->pluck('model_name')->toArray();
            $accuracy = $models->pluck('accuracy')->toArray();
            $f1       = $models->pluck('f1_score')->toArray();
            $auc      = $models->pluck('roc_auc')->toArray();
        }

        // Scale to percentage
        $toPercent = fn($arr) => array_map(fn($v) => round($v * 100, 2), $arr);

        return [
            'datasets' => [
                [
                    'label'           => 'Accuracy (%)',
                    'data'            => $toPercent($accuracy),
                    'backgroundColor' => 'rgba(79,99,210,0.7)',
                    'borderColor'     => '#4f63d2',
                    'borderWidth'     => 1,
                    'borderRadius'    => 4,
                ],
                [
                    'label'           => 'F1 Score (%)',
                    'data'            => $toPercent($f1),
                    'backgroundColor' => 'rgba(16,185,129,0.7)',
                    'borderColor'     => '#10b981',
                    'borderWidth'     => 1,
                    'borderRadius'    => 4,
                ],
                [
                    'label'           => 'ROC AUC (%)',
                    'data'            => $toPercent($auc),
                    'backgroundColor' => 'rgba(245,158,11,0.7)',
                    'borderColor'     => '#f59e0b',
                    'borderWidth'     => 1,
                    'borderRadius'    => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'top'],
            ],
            'scales' => [
                'y' => [
                    'min'   => 0,
                    'max'   => 100,
                    'ticks' => ['callback' => "function(v){return v+'%'}"],
                ],
            ],
        ];
    }
}
