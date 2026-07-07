<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    protected $fillable = ['user_id','recommendation_id','google_event_id','title','description','event_date','status'];
    protected $casts = ['event_date'=>'datetime'];

    public function user()           { return $this->belongsTo(User::class); }
    public function recommendation() { return $this->belongsTo(Recommendation::class); }
}
