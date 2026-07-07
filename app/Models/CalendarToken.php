<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CalendarToken extends Model
{
    protected $fillable = ['user_id','access_token','refresh_token','expires_at','scope'];
    protected $casts = ['expires_at'=>'datetime'];

    public function user() { return $this->belongsTo(User::class); }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isExpiringSoon(): bool
    {
        return $this->expires_at && $this->expires_at->diffInMinutes(now()) <= 5;
    }
}
