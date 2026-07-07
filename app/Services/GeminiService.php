<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\PredictionResult;
use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key', '');
        $this->model  = config('services.gemini.model', 'gemini-1.5-flash');
    }

    /**
     * Generate personalized recommendations based on prediction result.
     */
    public function generateRecommendations(PredictionResult $prediction, User $user): array
    {
        $assessment = $prediction->assessment;
        $demo       = $user->demographic;

        $prompt = $this->buildPrompt($prediction, $assessment, $demo, $user);

        try {
            $response = Http::timeout(30)
                ->post("{$this->baseUrl}/{$this->model}:generateContent?key={$this->apiKey}", [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ],
                    'generationConfig' => [
                        'temperature'     => 0.7,
                        'maxOutputTokens' => 1500,
                    ],
                ]);

            if (! $response->successful()) {
                Log::warning('Gemini API error: ' . $response->body());
                return $this->fallbackRecommendations($prediction);
            }

            $text = $response->json('candidates.0.content.parts.0.text', '');
            return $this->parseRecommendations($text, $prediction, $user);

        } catch (\Exception $e) {
            Log::error('Gemini service error: ' . $e->getMessage());
            return $this->fallbackRecommendations($prediction);
        }
    }

    private function buildPrompt(PredictionResult $prediction, $assessment, $demo, User $user): string
    {
        $riskLevel   = $prediction->risk_level;
        $burnoutProb = $prediction->burnout_probability;
        $topFactors  = implode(', ', $prediction->top_risk_factors ?? []);

        return <<<PROMPT
You are a professional workplace wellness consultant. Based on the following employee data, generate 6 personalized burnout prevention recommendations in JSON format.

EMPLOYEE PROFILE:
- Name: {$user->name}
- Age: {$demo?->age}, Gender: {$demo?->gender}
- Job Role: {$demo?->job_role}, Experience: {$demo?->experience_years} years
- Company Size: {$demo?->company_size}, Work Mode: {$demo?->work_mode}

ASSESSMENT DATA:
- Work Hours/Week: {$assessment->work_hours_per_week}, Overtime: {$assessment->overtime_hours}h
- Stress Level: {$assessment->stress_level}/10, Anxiety: {$assessment->anxiety_score}/10, Depression: {$assessment->depression_score}/10
- Job Satisfaction: {$assessment->job_satisfaction}/10, Work-Life Balance: {$assessment->work_life_balance}/10
- Sleep Hours/Night: {$assessment->sleep_hours}, Physical Activity: {$assessment->physical_activity_days} days/week
- Manager Support: {$assessment->manager_support}/10, Social Support: {$assessment->social_support_score}/10
- In Therapy: {$assessment->has_therapy}, Seeking Help: {$assessment->seeks_professional_help}

PREDICTION RESULT:
- Burnout Risk Level: {$riskLevel}
- Burnout Probability: {$burnoutProb}%
- Top Risk Factors: {$topFactors}

Generate exactly 6 recommendations. Return ONLY valid JSON array, no markdown, no explanation:
[
  {
    "title": "Short action title (max 60 chars)",
    "description": "Detailed, practical advice specific to this person (2-3 sentences)",
    "category": "one of: sleep|exercise|work|mental|social|nutrition",
    "priority": "one of: high|medium|low"
  }
]

Rules:
- Prioritize based on the risk level ({$riskLevel})
- Be specific and actionable, not generic
- Categories must cover at least 3 different types
- High risk = 2 high priority, 3 medium, 1 low
- Moderate risk = 1 high priority, 3 medium, 2 low
- Low risk = 0 high priority, 3 medium, 3 low
PROMPT;
    }

    private function parseRecommendations(string $text, PredictionResult $prediction, User $user): array
    {
        // Strip markdown code blocks if present
        $text = preg_replace('/```json\s*|\s*```/', '', $text);
        $text = trim($text);

        $items = json_decode($text, true);

        if (! is_array($items)) {
            Log::warning('Failed to parse Gemini JSON response, using fallback');
            return $this->fallbackRecommendations($prediction);
        }

        $saved = [];
        foreach ($items as $item) {
            $rec = Recommendation::create([
                'prediction_id'  => $prediction->id,
                'user_id'        => $user->id,
                'title'          => $item['title'] ?? 'Wellness Tip',
                'description'    => $item['description'] ?? '',
                'category'       => $item['category'] ?? 'general',
                'priority'       => $item['priority'] ?? 'medium',
                'gemini_context' => 'Generated by Gemini AI',
            ]);
            $saved[] = $rec;
        }

        return $saved;
    }

    private function fallbackRecommendations(PredictionResult $prediction): array
    {
        $riskLevel = $prediction->risk_level;

        $defaults = [
            'High' => [
                ['title' => 'Strictly limit work to contracted hours only', 'description' => 'Stop all work activities once your contracted hours are done. This boundary is critical for recovery and preventing further burnout escalation.', 'category' => 'work', 'priority' => 'high'],
                ['title' => 'Mandatory 8-hour sleep schedule', 'description' => 'Set a consistent bedtime and wake time to achieve at least 8 hours of sleep. Poor sleep dramatically worsens burnout symptoms.', 'category' => 'sleep', 'priority' => 'high'],
                ['title' => 'Daily 20-minute physical activity minimum', 'description' => 'Even a short daily walk can reduce cortisol levels. Start small and build gradually to 30+ minutes per day.', 'category' => 'exercise', 'priority' => 'medium'],
                ['title' => 'Avoid work communications after hours', 'description' => 'Turn off work notifications after office hours. Psychological detachment from work is essential for mental recovery.', 'category' => 'mental', 'priority' => 'medium'],
                ['title' => 'Schedule weekly social activities', 'description' => 'Connect with friends or family at least once per week outside of work. Social support is a key buffer against burnout.', 'category' => 'social', 'priority' => 'medium'],
                ['title' => 'Reduce caffeine intake gradually', 'description' => 'High caffeine consumption increases anxiety and disrupts sleep. Aim to reduce by one cup per day each week.', 'category' => 'nutrition', 'priority' => 'low'],
            ],
            'Moderate' => [
                ['title' => 'Practice the 52/17 work-break method', 'description' => 'Work focused for 52 minutes then take a 17-minute break. This rhythm maintains productivity while preventing mental fatigue.', 'category' => 'work', 'priority' => 'high'],
                ['title' => 'Establish a wind-down bedtime routine', 'description' => 'Spend the last 30 minutes before sleep screen-free. Read, meditate, or do light stretching to signal your body to rest.', 'category' => 'sleep', 'priority' => 'medium'],
                ['title' => 'Exercise 3-4 times per week', 'description' => 'Moderate aerobic exercise significantly reduces stress hormones. Mix activities you enjoy to maintain consistency.', 'category' => 'exercise', 'priority' => 'medium'],
                ['title' => '5-minute mindfulness check-ins', 'description' => 'Set 3 daily reminders for 5-minute breathing exercises. Brief mindfulness practices reduce cumulative stress effectively.', 'category' => 'mental', 'priority' => 'medium'],
                ['title' => 'Weekly catch-up with a supportive colleague', 'description' => 'Build positive workplace relationships to increase your sense of belonging and reduce isolation at work.', 'category' => 'social', 'priority' => 'low'],
                ['title' => 'Optimize your workspace ergonomics', 'description' => 'Ensure your workstation supports good posture. Physical discomfort contributes to mental fatigue over time.', 'category' => 'work', 'priority' => 'low'],
            ],
            'Low' => [
                ['title' => 'Maintain your current healthy work rhythm', 'description' => 'Your current balance is working well. Continue protecting your boundaries and prioritizing recovery time.', 'category' => 'work', 'priority' => 'medium'],
                ['title' => 'Optimize sleep quality further', 'description' => 'Experiment with keeping your bedroom cooler and darker. Even small sleep quality improvements benefit cognitive performance.', 'category' => 'sleep', 'priority' => 'medium'],
                ['title' => 'Try a new physical activity', 'description' => 'Introducing variety in exercise — yoga, swimming, cycling — keeps motivation high and works different muscle groups.', 'category' => 'exercise', 'priority' => 'medium'],
                ['title' => 'Build a personal learning habit', 'description' => 'Spend 20 minutes per day learning something unrelated to work. This builds resilience and sense of personal growth.', 'category' => 'mental', 'priority' => 'low'],
                ['title' => 'Strengthen your social connections', 'description' => 'Invest in relationships outside work. Strong social networks are your best long-term protection against burnout.', 'category' => 'social', 'priority' => 'low'],
                ['title' => 'Review nutrition and hydration habits', 'description' => 'Stay well-hydrated and aim for balanced meals. Good nutrition sustains energy and mood throughout the workday.', 'category' => 'nutrition', 'priority' => 'low'],
            ],
        ];

        $recs   = $defaults[$riskLevel] ?? $defaults['Moderate'];
        $saved  = [];
        foreach ($recs as $item) {
            $rec = Recommendation::create([
                'prediction_id'  => $prediction->id,
                'user_id'        => $prediction->user_id,
                'title'          => $item['title'],
                'description'    => $item['description'],
                'category'       => $item['category'],
                'priority'       => $item['priority'],
                'gemini_context' => 'Fallback recommendation (Gemini API not configured)',
            ]);
            $saved[] = $rec;
        }

        return $saved;
    }
}
