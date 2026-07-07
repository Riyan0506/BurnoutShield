<?php

namespace App\Http\Controllers;

use App\Models\Recommendation;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GoogleCalendarController extends Controller
{
    public function __construct(private GoogleCalendarService $calendarService) {}

    public function index()
    {
        Log::info('Calendar Index: Request received', [
            'auth_user' => Auth::check() ? Auth::id() : null,
            'session_id' => session()->getId(),
            'session_data_keys' => array_keys(session()->all()),
            'session_has_user' => session()->has('_token'),
        ]);

        $user   = Auth::user()->load('calendarToken', 'calendarEvents.recommendation');
        $events = $user->calendarEvents()->with('recommendation')->latest()->get();
        return view('calendar.index', compact('user', 'events'));
    }

    public function connect()
    {
        if (! $this->calendarService->isConfigured()) {
            return back()->with('error', 'Google Calendar is not configured. Add GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET to your .env file.');
        }

        $user = Auth::user();

        // Store user_id in session before redirecting to Google
        Log::info('Google OAuth: User starting OAuth flow', [
            'user_id' => $user->id,
            'session_id' => session()->getId(),
        ]);

        // Pass user to getAuthUrl to include user_id in state
        return redirect($this->calendarService->getAuthUrl($user));
    }

    public function callback(Request $request)
    {
        Log::info('Google OAuth Callback: Request received', [
            'has_error' => $request->has('error'),
            'has_code' => $request->has('code'),
            'session_id' => session()->getId(),
            'auth_user' => Auth::check() ? Auth::id() : null,
        ]);

        if ($request->has('error')) {
            Log::warning('Google OAuth Callback: Error from Google', ['error' => $request->get('error')]);
            return redirect()->route('calendar.index')
                ->with('error', 'Google authorization was denied: ' . $request->get('error'));
        }

        if (! $request->has('code')) {
            Log::warning('Google OAuth Callback: No authorization code received');
            return redirect()->route('calendar.index')->with('error', 'No authorization code received.');
        }

        // Try to get user from session, or from state parameter
        $user = Auth::user();
        $stateData = null;

        if (!$user && $request->has('state')) {
            // Decode state to get user_id (session was lost, recover user)
            $stateData = $this->calendarService->decodeState($request->get('state'));
            if ($stateData && isset($stateData['user_id'])) {
                $user = \App\Models\User::find($stateData['user_id']);
                if ($user) {
                    Log::info('Google OAuth Callback: Recovered user from state parameter', ['user_id' => $user->id]);
                    // Manually set the user as authenticated
                    Auth::login($user);
                }
            }
        }

        if (!$user) {
            Log::error('Google OAuth Callback: User not authenticated and could not recover from state.', [
                'session_id' => session()->getId(),
                'state_data' => $stateData,
            ]);
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        try {
            Log::info('Google OAuth Callback: Exchanging code for token', ['user_id' => $user->id]);
            $this->calendarService->exchangeCode($request->get('code'), $user);
            Log::info('Google OAuth Callback: Token exchange successful', ['user_id' => $user->id]);

            // Redirect to calendar.index
            return redirect()->route('calendar.index')->with('success', 'Google Calendar connected successfully!');
        } catch (\Exception $e) {
            Log::error('Google OAuth Callback: Token exchange failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            return redirect()->route('calendar.index')->with('error', 'Connection failed: ' . $e->getMessage());
        }
    }

    public function sync(Request $request)
    {
        $request->validate([
            'recommendation_id' => 'required|exists:recommendations,id',
            'event_date'        => 'required|date|after:now',
        ]);

        $user           = Auth::user();
        $recommendation = Recommendation::where('user_id', $user->id)
            ->findOrFail($request->recommendation_id);

        try {
            $this->calendarService->syncRecommendation(
                $recommendation,
                $user,
                \Carbon\Carbon::parse($request->event_date)
            );

            return back()->with('success', '"' . $recommendation->title . '" synced to Google Calendar!');
        } catch (\Exception $e) {
            return back()->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    public function disconnect()
    {
        $this->calendarService->disconnect(Auth::user());
        return redirect()->route('calendar.index')->with('success', 'Google Calendar disconnected.');
    }
}
