<?php

namespace App\Services;

use App\Models\CalendarEvent;
use App\Models\CalendarToken;
use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;
    private string $tokenUrl   = 'https://oauth2.googleapis.com/token';
    private string $calendarUrl = 'https://www.googleapis.com/calendar/v3';

    public function __construct()
    {
        $this->clientId     = config('services.google.client_id', '');
        $this->clientSecret = config('services.google.client_secret', '');
        $this->redirectUri  = config('services.google.redirect', url('/user/calendar/callback'));
    }

    /**
     * Build the Google OAuth authorization URL.
     * Uses user_id in state parameter to recover session after OAuth callback.
     */
    public function getAuthUrl(User $user): string
    {
        // Encode user_id in state parameter (base64 encoded to handle special chars)
        $state = base64_encode(json_encode(['user_id' => $user->id, 'csrf' => csrf_token()]));

        $params = http_build_query([
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->redirectUri,
            'response_type' => 'code',
            'scope'         => 'https://www.googleapis.com/auth/calendar.events https://www.googleapis.com/auth/userinfo.email',
            'access_type'   => 'offline',
            'prompt'        => 'consent',
            'state'         => $state,
        ]);

        return "https://accounts.google.com/o/oauth2/v2/auth?{$params}";
    }

    /**
     * Decode state parameter from OAuth callback.
     */
    public function decodeState(string $state): ?array
    {
        $decoded = base64_decode($state, true);
        if ($decoded === false) {
            return null;
        }
        $data = json_decode($decoded, true);
        return is_array($data) ? $data : null;
    }

    /**
     * Exchange authorization code for access + refresh tokens.
     */
    public function exchangeCode(string $code, User $user): CalendarToken
    {
        $response = Http::post($this->tokenUrl, [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
            'grant_type'    => 'authorization_code',
            'code'          => $code,
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Google OAuth token exchange failed: ' . $response->body());
        }

        $data = $response->json();

        // Upsert token for this user
        return CalendarToken::updateOrCreate(
            ['user_id' => $user->id],
            [
                'access_token'  => $data['access_token'],
                'refresh_token' => $data['refresh_token'] ?? null,
                'expires_at'    => now()->addSeconds($data['expires_in'] ?? 3600),
                'scope'         => $data['scope'] ?? '',
            ]
        );
    }

    /**
     * Refresh the access token using the refresh token.
     */
    public function refreshToken(CalendarToken $token): CalendarToken
    {
        if (! $token->refresh_token) {
            throw new \RuntimeException('No refresh token stored. User must re-authorize.');
        }

        $response = Http::post($this->tokenUrl, [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $token->refresh_token,
            'grant_type'    => 'refresh_token',
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Token refresh failed: ' . $response->body());
        }

        $data = $response->json();

        $token->update([
            'access_token' => $data['access_token'],
            'expires_at'   => now()->addSeconds($data['expires_in'] ?? 3600),
        ]);

        return $token->fresh();
    }

    /**
     * Get a valid access token, refreshing if needed.
     */
    private function getValidToken(User $user): string
    {
        $token = $user->calendarToken;

        if (! $token) {
            throw new \RuntimeException('User has not connected Google Calendar.');
        }

        if ($token->isExpired() || $token->isExpiringSoon()) {
            $token = $this->refreshToken($token);
        }

        return $token->access_token;
    }

    /**
     * Sync a recommendation as a Google Calendar event.
     */
    public function syncRecommendation(Recommendation $recommendation, User $user, \Carbon\Carbon $eventDate): CalendarEvent
    {
        $accessToken = $this->getValidToken($user);

        $event = [
            'summary'     => "🔥 " . $recommendation->title,
            'description' => $recommendation->description . "\n\n— BurnoutShield Wellness Reminder",
            'start'       => ['dateTime' => $eventDate->toIso8601String(), 'timeZone' => 'Asia/Jakarta'],
            'end'         => ['dateTime' => $eventDate->addHour()->toIso8601String(), 'timeZone' => 'Asia/Jakarta'],
            'reminders'   => [
                'useDefault' => false,
                'overrides'  => [['method' => 'popup', 'minutes' => 30]],
            ],
            'colorId' => match($recommendation->priority) {
                'high'   => '11', // Red
                'medium' => '5',  // Yellow
                'low'    => '2',  // Sage
                default  => '7',  // Peacock
            },
        ];

        $response = Http::withToken($accessToken)
            ->post("{$this->calendarUrl}/calendars/primary/events", $event);

        if (! $response->successful()) {
            Log::error('Google Calendar event creation failed: ' . $response->body());
            throw new \RuntimeException('Could not create calendar event: ' . $response->json('error.message', 'Unknown error'));
        }

        $eventData = $response->json();

        return CalendarEvent::updateOrCreate(
            ['user_id' => $user->id, 'recommendation_id' => $recommendation->id],
            [
                'google_event_id' => $eventData['id'],
                'title'           => $recommendation->title,
                'description'     => $recommendation->description,
                'event_date'      => $eventDate,
                'status'          => 'synced',
            ]
        );
    }

    /**
     * Revoke access and delete stored token.
     */
    public function disconnect(User $user): void
    {
        $token = $user->calendarToken;
        if ($token) {
            Http::post('https://oauth2.googleapis.com/revoke', [
                'token' => $token->access_token
            ]);
            $token->delete();
        }
    }

    public function isConfigured(): bool
    {
        return ! empty($this->clientId) && ! empty($this->clientSecret);
    }
}
