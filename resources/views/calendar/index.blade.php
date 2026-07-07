@extends('layouts.app')
@section('title','Google Calendar')
@section('page-title','Google Calendar')
@section('page-subtitle','Sync your wellness reminders to Google Calendar')

@section('content')

{{-- Connection Status --}}
<div class="card p-4 sm:p-6 mb-4 sm:mb-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3 sm:gap-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-xl flex items-center justify-center text-xl sm:text-2xl flex-shrink-0">📅</div>
            <div class="min-w-0">
                <h3 class="font-semibold text-slate-800 text-sm sm:text-base">Google Calendar Integration</h3>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    @if($user->hasCalendarConnected())
                        <span class="text-emerald-600 font-medium">✅ Connected</span> — Your calendar is synced
                    @else
                        <span class="text-slate-400">Not connected</span> — Connect to sync recommendations as reminders
                    @endif
                </p>
            </div>
        </div>
        @if($user->hasCalendarConnected())
        <form method="POST" action="{{ route('calendar.disconnect') }}"
              onsubmit="return confirm('Disconnect Google Calendar?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-secondary text-red-600 border-red-200 hover:bg-red-50 text-xs sm:text-sm py-1.5 px-3 w-full sm:w-auto text-center justify-center">
                Disconnect
            </button>
        </form>
        @else
        <a href="{{ route('calendar.connect') }}" class="btn-primary w-full sm:w-auto text-center justify-center text-sm">
            🔗 Connect Google Calendar
        </a>
        @endif
    </div>
</div>

@if($user->hasCalendarConnected())
{{-- Synced Events --}}
<div class="card overflow-hidden">
    <div class="p-4 sm:p-5 border-b border-slate-200">
        <h3 class="font-semibold text-slate-800 text-sm sm:text-base">Synced Events</h3>
    </div>
    
    {{-- Mobile Card View --}}
    <div class="block lg:hidden">
        @forelse($events as $event)
        <div class="p-4 border-b border-slate-100 last:border-b-0">
            <div class="flex items-start justify-between mb-2">
                <div class="min-w-0 flex-1 pr-2">
                    <div class="text-sm font-medium text-slate-800 truncate">{{ Str::limit($event->title, 30) }}</div>
                    <div class="text-xs text-slate-500 capitalize mt-0.5">{{ $event->recommendation?->category ?? '—' }}</div>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full flex-shrink-0
                    {{ $event->status === 'synced' ? 'bg-emerald-100 text-emerald-700' : ($event->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600') }}">
                    {{ ucfirst($event->status) }}
                </span>
            </div>
            <div class="flex items-center justify-between mt-2">
                <span class="text-xs text-slate-600">{{ $event->event_date->format('d M Y') }}</span>
                <span class="text-xs text-slate-400 font-mono truncate max-w-[100px]">{{ Str::limit($event->google_event_id, 12) }}</span>
            </div>
        </div>
        @empty
        <div class="p-8 text-center text-slate-400">
            <span class="text-4xl block mb-2">📅</span>
            <span class="text-sm">No events synced yet. Go to a prediction result and sync recommendations.</span>
        </div>
        @endforelse
    </div>
    
    {{-- Desktop Table View --}}
    <div class="hidden lg:block">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Title</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Category</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Event Date</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Event ID</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($events as $event)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-4 text-sm text-slate-700 font-medium">{{ Str::limit($event->title, 30) }}</td>
                        <td class="px-5 py-4 text-sm text-slate-500 capitalize whitespace-nowrap">{{ $event->recommendation?->category ?? '—' }}</td>
                        <td class="px-5 py-4 text-sm text-slate-700 whitespace-nowrap">{{ $event->event_date->format('d M Y') }}</td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="text-xs px-2 py-0.5 rounded-full
                                {{ $event->status === 'synced' ? 'bg-emerald-100 text-emerald-700' : ($event->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600') }}">
                                {{ ucfirst($event->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-xs text-slate-400 font-mono whitespace-nowrap">{{ Str::limit($event->google_event_id, 15) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-slate-400">
                            <span class="text-4xl block mb-2">📅</span>
                            <span class="text-sm">No events synced yet. Go to a prediction result and sync recommendations.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@else
<div class="card p-6 sm:p-8 text-center text-slate-400">
    <span class="text-4xl sm:text-5xl block mb-3">📅</span>
    <h3 class="text-base sm:text-lg font-medium text-slate-600 mb-1">Connect Google Calendar</h3>
    <p class="text-xs sm:text-sm">After connecting, you can sync your wellness recommendations as calendar reminders.</p>
    <p class="text-xs mt-3 text-slate-400">Note: You need to configure GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in your .env file.</p>
</div>
@endif
@endsection
