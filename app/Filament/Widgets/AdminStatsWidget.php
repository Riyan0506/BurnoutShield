<?php

namespace App\Filament\Widgets;

use App\Models\Assessment;
use App\Models\PredictionResult;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalUsers       = User::where('role', 'employee')->count();
        $totalAssessments = Assessment::count();
        $totalPredictions = PredictionResult::count();
        $highRiskCount    = PredictionResult::where('risk_level', 'High')->count();
        $moderateCount    = PredictionResult::where('risk_level', 'Moderate')->count();
        $avgProbability   = PredictionResult::avg('burnout_probability') ?? 0;

        // Trend: assessments this week vs last week
        $thisWeek = Assessment::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $lastWeek = Assessment::whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])->count();
        $trend    = $thisWeek >= $lastWeek ? 'up' : 'down';

        return [
            Stat::make('Total Employees', $totalUsers)
                ->description('Registered employees')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Total Assessments', $totalAssessments)
                ->description($thisWeek . ' this week ' . ($trend === 'up' ? '↑' : '↓'))
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('info'),

            Stat::make('Total Predictions', $totalPredictions)
                ->description('AI predictions run')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color('success'),

            Stat::make('High Risk Cases', $highRiskCount)
                ->description($moderateCount . ' moderate risk')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make('Avg Burnout Probability', number_format($avgProbability, 1) . '%')
                ->description('Across all predictions')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($avgProbability >= 60 ? 'danger' : ($avgProbability >= 35 ? 'warning' : 'success')),

            Stat::make('Risk Distribution', "H:{$highRiskCount} M:{$moderateCount}")
                ->description('High / Moderate cases')
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color('warning'),
        ];
    }
}
