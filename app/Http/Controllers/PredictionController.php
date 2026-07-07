<?php

namespace App\Http\Controllers;

use App\Models\PredictionResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PredictionController extends Controller
{
    public function show(PredictionResult $prediction)
    {
        // Authorize: only owner can view
        if ($prediction->user_id !== Auth::id()) {
            abort(403);
        }

        $prediction->load('assessment', 'recommendations');

        // Build chart data for feature importance
        $featureImportance = collect($prediction->feature_importance ?? [])
            ->take(10)
            ->map(fn($val, $key) => [
                'feature' => ucwords(str_replace('_', ' ', $key)),
                'value'   => round($val * 100, 2),
            ])->values();

        // Probability chart data
        $probabilities = collect($prediction->probabilities ?? [])
            ->map(fn($val, $key) => ['label' => $key, 'value' => $val])
            ->values();

        return view('predictions.show', compact('prediction', 'featureImportance', 'probabilities'));
    }
}
