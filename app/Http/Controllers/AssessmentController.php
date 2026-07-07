<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\PredictionResult;
use App\Services\MLPredictionService;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller
{
    public function __construct(
        private MLPredictionService $mlService,
        private GeminiService $geminiService
    ) {}

    public function create()
    {
        $user = Auth::user()->load('demographic');

        if (! $user->demographic || ! $user->demographic->age) {
            return redirect()->route('profile.edit')
                ->with('warning', 'Please complete your demographic profile before taking an assessment.');
        }

        return view('assessments.create', compact('user'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'work_hours_per_week'     => 'required|numeric|min:1|max:168',
            'overtime_hours'          => 'required|numeric|min:0|max:80',
            'meetings_per_day'        => 'required|integer|min:0|max:20',
            'deadlines_missed'        => 'required|integer|min:0|max:30',
            'job_satisfaction'        => 'required|integer|min:1|max:10',
            'manager_support'         => 'required|integer|min:1|max:10',
            'work_life_balance'       => 'required|integer|min:1|max:10',
            'sleep_hours'             => 'required|numeric|min:1|max:12',
            'physical_activity_days'  => 'required|integer|min:0|max:7',
            'screen_time_hours'       => 'required|numeric|min:0|max:24',
            'caffeine_intake'         => 'required|integer|min:0|max:20',
            'social_support_score'    => 'required|integer|min:1|max:10',
            'has_therapy'             => 'boolean',
            'seeks_professional_help' => 'boolean',
            'stress_level'            => 'required|integer|min:1|max:10',
            'anxiety_score'           => 'required|integer|min:1|max:10',
            'depression_score'        => 'required|integer|min:1|max:10',
        ]);

        $user = Auth::user()->load('demographic');

        try {
            DB::beginTransaction();

            // Create assessment
            $assessment = Assessment::create(array_merge(
                $validated,
                [
                    'user_id'     => $user->id,
                    'has_therapy' => $request->boolean('has_therapy'),
                    'seeks_professional_help' => $request->boolean('seeks_professional_help'),
                ]
            ));

            // Run ML prediction
            $prediction = $this->mlService->predict($assessment, $user);

            // Generate AI recommendations
            $this->geminiService->generateRecommendations($prediction, $user);

            DB::commit();

            return redirect()->route('predictions.show', $prediction->id)
                ->with('success', 'Assessment completed! Your burnout risk has been analyzed.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Analysis failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function history()
    {
        $user = Auth::user();
        $assessments = Assessment::where('user_id', $user->id)
            ->with('prediction')
            ->latest()
            ->paginate(10);

        return view('history.index', compact('assessments'));
    }
}
