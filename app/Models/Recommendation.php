<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model
{
    protected $fillable = [
        'prediction_id','user_id','title','description','category','priority','is_completed','gemini_context'
    ];
    protected $casts = ['is_completed'=>'boolean'];

    public function prediction() { return $this->belongsTo(PredictionResult::class); }
    public function user()       { return $this->belongsTo(User::class); }
    public function calendarEvents() { return $this->hasMany(CalendarEvent::class); }

    public function categoryIcon(): string
    {
        return match($this->category) {
            'sleep'    => '😴', 'exercise' => '🏃', 'work'     => '💼',
            'mental'   => '🧠', 'social'   => '👥', 'nutrition'=> '🥗',
            default    => '💡',
        };
    }
}
