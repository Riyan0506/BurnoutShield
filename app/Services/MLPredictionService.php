<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\DemographicData;
use App\Models\PredictionResult;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MLPredictionService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.ml.url', 'http://127.0.0.1:8001'), '/');
        $this->apiKey  = config('services.ml.key', '');
    }

    /**
     * Run prediction for a given assessment.
     */
    public function predict(Assessment $assessment, User $user): PredictionResult
    {
        $demo = $user->demographic;

        if (! $demo) {
            throw new \RuntimeException('User has no demographic data. Complete your profile first.');
        }

        $payload = $assessment->toMLArray($demo);

        // Call Python ML engine
        try {
            $response = Http::timeout(30)
                ->withHeaders(['X-ML-API-Key' => $this->apiKey])
                ->post("{$this->baseUrl}/predict", $payload);

            if (! $response->successful()) {
                throw new \RuntimeException('ML engine returned error: ' . $response->body());
            }

            $result = $response->json();
        } catch (\Exception $e) {
            Log::error('ML engine call failed: ' . $e->getMessage());
            // Fallback: simple rule-based prediction
            $result = $this->fallbackPredict($payload);
        }

        // Persist result
        $prediction = PredictionResult::create([
            'assessment_id'      => $assessment->id,
            'user_id'            => $user->id,
            'risk_level'         => $result['risk_level'] ?? 'Moderate',
            'burnout_probability' => $result['burnout_probability'] ?? 50,
            'model_used'         => $result['model_used'] ?? 'Fallback',
            'probabilities'      => $result['probabilities'] ?? [],
            'feature_importance' => $result['feature_importance'] ?? [],
            'top_risk_factors'   => $result['top_risk_factors'] ?? [],
        ]);

        return $prediction;
    }

    /**
     * Fallback rule-based prediction when ML engine is unreachable.
     */
    private function fallbackPredict(array $data): array
    {
        $score = 0;
        $score += ($data['stress_level'] ?? 5) * 3;
        $score += ($data['anxiety_score'] ?? 4) * 2;
        $score += ($data['depression_score'] ?? 3) * 2;
        $score += max(0, ($data['work_hours_per_week'] ?? 40) - 40) * 0.5;
        $score += ($data['overtime_hours'] ?? 0) * 1;
        $score -= ($data['sleep_hours'] ?? 7) * 1;
        $score -= ($data['job_satisfaction'] ?? 5) * 0.5;
        $score -= ($data['work_life_balance'] ?? 5) * 0.5;

        $prob = min(100, max(0, $score));

        if ($prob >= 60) {
            $level = 'High';
        } elseif ($prob >= 35) {
            $level = 'Moderate';
        } else {
            $level = 'Low';
        }

        return [
            'risk_level'          => $level,
            'burnout_probability' => round($prob, 2),
            'model_used'          => 'Fallback (Rule-Based)',
            'probabilities'       => ['Low' => 100 - $prob, 'Moderate' => 0, 'High' => $prob],
            'feature_importance'  => [],
            'top_risk_factors'    => ['stress_level', 'work_hours_per_week', 'overtime_hours'],
        ];
    }

    /**
     * Get model performance metrics from ML engine.
     */
    public function getModelPerformance(): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['X-ML-API-Key' => $this->apiKey])
                ->get("{$this->baseUrl}/model/performance");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::warning('Could not fetch model performance: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Health check for ML engine.
     */
    public function isHealthy(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
