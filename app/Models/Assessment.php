<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $fillable = [
        'user_id','work_hours_per_week','overtime_hours','meetings_per_day','deadlines_missed',
        'job_satisfaction','manager_support','work_life_balance','sleep_hours',
        'physical_activity_days','screen_time_hours','caffeine_intake','social_support_score',
        'has_therapy','seeks_professional_help','stress_level','anxiety_score','depression_score'
    ];
    protected $casts = [
        'has_therapy'=>'boolean','seeks_professional_help'=>'boolean',
        'work_hours_per_week'=>'float','overtime_hours'=>'float','sleep_hours'=>'float','screen_time_hours'=>'float',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function prediction() { return $this->hasOne(PredictionResult::class); }

    public function toMLArray(DemographicData $demo): array
    {
        return array_merge($demo->toMLArray(), [
            'work_hours_per_week'    => $this->work_hours_per_week,
            'overtime_hours'         => $this->overtime_hours,
            'meetings_per_day'       => $this->meetings_per_day,
            'deadlines_missed'       => $this->deadlines_missed,
            'job_satisfaction'       => $this->job_satisfaction,
            'manager_support'        => $this->manager_support,
            'work_life_balance'      => $this->work_life_balance,
            'sleep_hours'            => $this->sleep_hours,
            'physical_activity_days' => $this->physical_activity_days,
            'screen_time_hours'      => $this->screen_time_hours,
            'caffeine_intake'        => $this->caffeine_intake,
            'social_support_score'   => $this->social_support_score,
            'has_therapy'            => $this->has_therapy ? 1 : 0,
            'stress_level'           => $this->stress_level,
            'anxiety_score'          => $this->anxiety_score,
            'depression_score'       => $this->depression_score,
            'seeks_professional_help'=> $this->seeks_professional_help ? 1 : 0,
        ]);
    }
}
