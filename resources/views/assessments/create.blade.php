@extends('layouts.app')
@section('title', 'New Assessment')
@section('page-title', 'Burnout Assessment')
@section('page-subtitle', 'Complete all sections for accurate prediction')

@section('content')

{{-- Hero Banner --}}
<div class="bg-gradient-to-r from-primary-600 to-purple-600 rounded-xl sm:rounded-2xl p-4 sm:p-6 mb-4 sm:mb-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex-1 min-w-0">
            <h2 class="text-lg sm:text-xl font-bold text-white mb-1">🧠 Burnout Risk Assessment</h2>
            <p class="text-blue-100 text-xs sm:text-sm">Fill in your information below. Our AI model will analyze your data and provide personalized recommendations.</p>
        </div>
        <div class="bg-white/20 backdrop-blur rounded-xl p-3 sm:p-4 text-center text-white flex-shrink-0 w-full sm:w-auto">
            <div class="text-xl sm:text-2xl mb-1">🎯</div>
            <div class="text-xs sm:text-sm font-semibold">25 Questions</div>
        </div>
    </div>
</div>

@if($errors->any())
<div class="mb-4 sm:mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600">
    <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('assessments.store') }}" id="assessmentForm">
@csrf

{{-- ── Section 1: Demographic (from profile) ──────────── --}}
<div class="card p-4 sm:p-5 lg:p-6 mb-4 sm:mb-5">
    <div class="flex items-center gap-3 mb-4 sm:mb-5">
        <span class="w-6 h-6 sm:w-7 sm:h-7 bg-primary-100 text-primary-600 rounded-lg flex items-center justify-center text-xs sm:text-sm font-bold flex-shrink-0">01</span>
        <h3 class="font-semibold text-slate-800 flex items-center gap-2 text-sm sm:text-base">
            👤 Demographic Data
            <span class="text-xs text-slate-400 font-normal hidden sm:inline">(from your profile)</span>
            <span class="text-xs text-slate-400 font-normal sm:hidden">(Profile)</span>
        </h3>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-600 mb-1.5">Age</label>
            <input type="text" value="{{ $user->demographic?->age ?? '—' }}" disabled
                class="w-full px-3 py-2 sm:px-3.5 sm:py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-500 cursor-not-allowed truncate">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-600 mb-1.5">Gender</label>
            <input type="text" value="{{ $user->demographic?->gender ?? '—' }}" disabled
                class="w-full px-3 py-2 sm:px-3.5 sm:py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-500 cursor-not-allowed truncate">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-600 mb-1.5">Job Role</label>
            <input type="text" value="{{ $user->demographic?->job_role ?? '—' }}" disabled
                class="w-full px-3 py-2 sm:px-3.5 sm:py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-500 cursor-not-allowed truncate">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-600 mb-1.5">Experience (Years)</label>
            <input type="text" value="{{ $user->demographic?->experience_years ?? '—' }}" disabled
                class="w-full px-3 py-2 sm:px-3.5 sm:py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-500 cursor-not-allowed truncate">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-600 mb-1.5">Company Size</label>
            <input type="text" value="{{ $user->demographic?->company_size ?? '—' }}" disabled
                class="w-full px-3 py-2 sm:px-3.5 sm:py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-500 cursor-not-allowed truncate">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-600 mb-1.5">Work Mode</label>
            <input type="text" value="{{ $user->demographic?->work_mode ?? '—' }}" disabled
                class="w-full px-3 py-2 sm:px-3.5 sm:py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-500 cursor-not-allowed truncate">
        </div>
    </div>
    <p class="text-xs text-slate-400 mt-3">
        Need to update?
        <a href="{{ route('profile.edit') }}" class="text-primary-600 hover:underline">Edit your profile</a>
    </p>
</div>

{{-- ── Section 2: Work Data ─────────────────────────────── --}}
<div class="card p-4 sm:p-5 lg:p-6 mb-4 sm:mb-5">
    <div class="flex items-center gap-3 mb-4 sm:mb-5">
        <span class="w-6 h-6 sm:w-7 sm:h-7 bg-primary-100 text-primary-600 rounded-lg flex items-center justify-center text-xs sm:text-sm font-bold flex-shrink-0">02</span>
        <h3 class="font-semibold text-slate-800 text-sm sm:text-base">💼 Work Data</h3>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5 lg:gap-6">
        <x-slider name="work_hours_per_week" label="Work Hours Per Week" min="10" max="100" value="40" unit="hrs"/>
        <x-slider name="overtime_hours" label="Overtime Hours / Week" min="0" max="40" value="0" unit="hrs"/>
        <x-slider name="meetings_per_day" label="Meetings Per Day" min="0" max="15" value="3"/>
        <x-slider name="deadlines_missed" label="Deadlines Missed (Monthly)" min="0" max="20" value="0"/>
    </div>
</div>

{{-- ── Section 3: Wellness & Lifestyle ─────────────────── --}}
<div class="card p-4 sm:p-6 mb-4 sm:mb-5">
    <div class="flex items-center gap-3 mb-4 sm:mb-5">
        <span class="w-6 h-6 sm:w-7 sm:h-7 bg-primary-100 text-primary-600 rounded-lg flex items-center justify-center text-xs sm:text-sm font-bold">03</span>
        <h3 class="font-semibold text-slate-800 text-sm sm:text-base">🌿 Wellness & Lifestyle</h3>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
        <x-slider name="job_satisfaction" label="Job Satisfaction" min="1" max="10" value="5" suffix="/10"/>
        <x-slider name="manager_support" label="Manager Support" min="1" max="10" value="5" suffix="/10"/>
        <x-slider name="work_life_balance" label="Work-Life Balance" min="1" max="10" value="5" suffix="/10"/>
        <x-slider name="sleep_hours" label="Sleep Hours / Night" min="2" max="12" value="7" step="0.5" suffix="/12"/>
        <x-slider name="social_support_score" label="Social Support Score" min="1" max="10" value="5" suffix="/10"/>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Physical Activity (days/week)</label>
            <input type="number" name="physical_activity_days" value="{{ old('physical_activity_days', 3) }}" min="0" max="7"
                class="w-full px-3 py-2 sm:px-3.5 sm:py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>
        <x-slider name="screen_time_hours" label="Screen Time (hours/day)" min="0" max="16" value="8" step="0.5"/>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Daily Caffeine Intake (cups)</label>
            <input type="number" name="caffeine_intake" value="{{ old('caffeine_intake', 2) }}" min="0" max="15"
                class="w-full px-3 py-2 sm:px-3.5 sm:py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-3">Currently in Therapy?</label>
            <div class="flex gap-4 sm:gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="has_therapy" value="1" {{ old('has_therapy') == '1' ? 'checked' : '' }}
                        class="text-primary-600">
                    <span class="text-sm text-slate-700">Yes</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="has_therapy" value="0" {{ old('has_therapy', '0') == '0' ? 'checked' : '' }}
                        class="text-primary-600">
                    <span class="text-sm text-slate-700">No</span>
                </label>
            </div>
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-3">Would Seek Professional Help?</label>
            <div class="flex gap-4 sm:gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="seeks_professional_help" value="1" {{ old('seeks_professional_help') == '1' ? 'checked' : '' }}
                        class="text-primary-600">
                    <span class="text-sm text-slate-700">Yes</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="seeks_professional_help" value="0" {{ old('seeks_professional_help', '0') == '0' ? 'checked' : '' }}
                        class="text-primary-600">
                    <span class="text-sm text-slate-700">No</span>
                </label>
            </div>
        </div>
    </div>
</div>

{{-- ── Section 4: Psychological Indicators ─────────────── --}}
<div class="card p-4 sm:p-6 mb-4 sm:mb-6">
    <div class="flex items-center gap-3 mb-3 sm:mb-4">
        <span class="w-6 h-6 sm:w-7 sm:h-7 bg-primary-100 text-primary-600 rounded-lg flex items-center justify-center text-xs sm:text-sm font-bold">04</span>
        <h3 class="font-semibold text-slate-800 text-sm sm:text-base">🧠 Psychological Indicators</h3>
    </div>
    <div class="flex items-center gap-2 p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs sm:text-sm text-blue-700 mb-4 sm:mb-5">
        ⚠️ Please answer honestly. Your data is private and only used for analysis.
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        <x-slider name="stress_level" label="Stress Level" min="1" max="10" value="5" suffix="/10" hint="1 (Very Low) → 10 (Extreme)"/>
        <x-slider name="anxiety_score" label="Anxiety Score" min="1" max="10" value="4" suffix="/10" hint="1 (Very Low) → 10 (Extreme)"/>
        <x-slider name="depression_score" label="Depression Score" min="1" max="10" value="3" suffix="/10" hint="1 (Very Low) → 10 (Extreme)"/>
    </div>
</div>

{{-- ── Action Buttons ────────────────────────────────────── --}}
<div class="flex flex-col-reverse sm:flex-row items-center justify-between gap-3">
    <a href="{{ route('dashboard') }}" class="btn-secondary w-full sm:w-auto text-center justify-center">
        ← Back
    </a>
    <button type="submit" id="submitBtn"
        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 w-full sm:w-auto bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        Run AI Analysis
    </button>
</div>

</form>
@endsection

@push('scripts')
<script>
// Slider component inline logic
document.querySelectorAll('.bs-slider').forEach(slider => {
    const input   = slider.querySelector('input[type=range]');
    const display = slider.querySelector('.slider-val');
    input.addEventListener('input', () => { display.textContent = input.value; });
});

// Submit loading state
document.getElementById('assessmentForm').addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Analyzing...';
});
</script>
@endpush
