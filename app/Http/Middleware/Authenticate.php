<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Log authentication failure for debugging
        Log::warning('Authenticate Middleware: User not authenticated, redirecting to login', [
            'route' => $request->path(),
            'method' => $request->method(),
            'session_id' => session()->getId(),
            'has_session_cookie' => $request->hasCookie(config('session.cookie')),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
            'session_data_keys' => array_keys(session()->all()),
        ]);

        return $request->expectsJson() ? null : route('login');
    }
}
