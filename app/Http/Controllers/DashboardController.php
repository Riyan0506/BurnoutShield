<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\PredictionResult;
use App\Services\MLPredictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load('demographic', 'predictions', 'assessments');

        // Stats
        $totalAssessments = $user->assessments()->count();
        $latestPrediction = $user->predictions()->with('recommendations')->first();
        $avgProbability   = $user->predictions()->avg('burnout_probability') ?? 0;

        // Trend data (last 10 assessments)
        $trendData = PredictionResult::where('user_id', $user->id)
            ->with('assessment')
            ->latest()
            ->take(10)
            ->get()
            ->reverse()
            ->map(fn($p) => [
                'date'        => $p->created_at->format('d M'),
                'probability' => round($p->burnout_probability, 1),
                'stress'      => $p->assessment->stress_level ?? 0,
                'risk_level'  => $p->risk_level,
            ])->values();

        // Risk distribution
        $riskDist = PredictionResult::where('user_id', $user->id)
            ->selectRaw('risk_level, count(*) as count')
            ->groupBy('risk_level')
            ->pluck('count', 'risk_level');

        // Latest recommendations
        $latestRecommendations = $latestPrediction
            ? $latestPrediction->recommendations()->take(4)->get()
            : collect();

        // ML engine status
        $mlService  = app(MLPredictionService::class);
        $mlHealthy  = $mlService->isHealthy();

        // Profile completeness
        $demo              = $user->demographic;
        $profileComplete   = $demo && $demo->age && $demo->job_role && $demo->gender;

        return view('dashboard.index', compact(
            'user', 'totalAssessments', 'latestPrediction',
            'avgProbability', 'trendData', 'riskDist',
            'latestRecommendations', 'mlHealthy', 'profileComplete'
        ));
    }
}
