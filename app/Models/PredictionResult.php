<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PredictionResult extends Model
{
    protected $fillable = [
        'assessment_id','user_id','risk_level','burnout_probability',
        'model_used','probabilities','feature_importance','top_risk_factors'
    ];
    protected $casts = [
        'burnout_probability'=>'float','probabilities'=>'array',
        'feature_importance'=>'array','top_risk_factors'=>'array'
    ];

    public function assessment() { return $this->belongsTo(Assessment::class); }
    public function user()       { return $this->belongsTo(User::class); }
    public function recommendations() { return $this->hasMany(Recommendation::class, 'prediction_id'); }

    public function riskColor(): string
    {
        return match($this->risk_level) {
            'High'     => 'red',
            'Moderate' => 'yellow',
            'Low'      => 'green',
            default    => 'gray',
        };
    }
}
