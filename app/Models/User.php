<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];

    // ─── Filament access ───────────────────────────────────
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === 'admin';
    }

    // ─── Relations ─────────────────────────────────────────
    public function demographic(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(DemographicData::class);
    }

    public function assessments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Assessment::class)->latest();
    }

    public function predictions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PredictionResult::class)->latest();
    }

    public function calendarToken(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CalendarToken::class);
    }

    public function calendarEvents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    // ─── Helpers ───────────────────────────────────────────
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function latestPrediction(): ?PredictionResult
    {
        return $this->predictions()->first();
    }

    public function hasCalendarConnected(): bool
    {
        $token = $this->calendarToken;
        return $token && $token->access_token;
    }
}
