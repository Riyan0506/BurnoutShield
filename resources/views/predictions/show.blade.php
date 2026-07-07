@extends('layouts.app')
@section('title', 'Prediction Result')
@section('page-title', 'Assessment Result')
@section('page-subtitle', 'AI-powered burnout risk analysis')

@section('content')

@php
    $riskColor = match($prediction->risk_level) {
        'High'     => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'text' => 'text-red-700', 'badge' => 'bg-red-100 text-red-700', 'hex' => '#ef4444'],
        'Moderate' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'text' => 'text-amber-700', 'badge' => 'bg-amber-100 text-amber-700', 'hex' => '#f59e0b'],
        default    => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-700', 'badge' => 'bg-emerald-100 text-emerald-700', 'hex' => '#10b981'],
    };
    $riskIcon = match($prediction->risk_level) {
        'High' => '🚨', 'Moderate' => '⚠️', default => '✅'
    };
@endphp

{{-- ── Risk Summary Card ────────────────────────────────── --}}
<div class="card p-4 sm:p-6 lg:p-8 mb-4 sm:mb-6 {{ $riskColor['bg'] }} {{ $riskColor['border'] }} border-2">
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 lg:gap-6">
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-2">
                <span class="text-3xl sm:text-4xl">{{ $riskIcon }}</span>
                <div>
                    <p class="text-xs sm:text-sm font-medium text-slate-600">Burnout Risk Level</p>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold {{ $riskColor['text'] }}">{{ $prediction->risk_level }} Risk</h2>
                </div>
            </div>
            <p class="text-slate-600 text-xs sm:text-sm mt-2 sm:mt-3 max-w-xl">
                @if($prediction->risk_level === 'High')
                    Your assessment indicates a <strong>high burnout risk</strong>. Immediate action is recommended. Please review the personalized recommendations below and consider speaking with a professional.
                @elseif($prediction->risk_level === 'Moderate')
                    Your assessment indicates a <strong>moderate burnout risk</strong>. Some lifestyle adjustments are recommended to prevent escalation. Review the recommendations below.
                @else
                    Your assessment indicates a <strong>low burnout risk</strong>. Keep maintaining your current healthy habits! The recommendations below can help you stay well.
                @endif
            </p>
            <div class="flex flex-wrap items-center gap-3 sm:gap-4 mt-3 sm:mt-4 text-xs sm:text-sm text-slate-500">
                <span>📅 {{ $prediction->created_at->format('d M Y, H:i') }}</span>
                <span>🤖 {{ $prediction->model_used }}</span>
            </div>
        </div>

        {{-- Probability Gauge --}}
        <div class="flex-shrink-0 text-center w-full lg:w-auto flex lg:block justify-center">
            <div class="relative w-28 h-28 sm:w-36 sm:h-36">
                <canvas id="gaugeChart" class="w-full h-full"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-2xl sm:text-3xl font-bold {{ $riskColor['text'] }}">{{ number_format($prediction->burnout_probability, 1) }}%</span>
                    <span class="text-xs text-slate-500">Burnout Prob.</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Charts Row ────────────────────────────────────────── --}}
<div class="grid grid-cols-1 xl:grid-cols-2 gap-4 sm:gap-5 mb-4 sm:mb-6">

    {{-- Risk Probabilities --}}
    <div class="card p-4 sm:p-5 overflow-hidden">
        <h3 class="text-sm font-semibold text-slate-800 mb-3 sm:mb-4">📊 Risk Probabilities</h3>
        @if($probabilities->count() > 0)
        <div class="w-full h-48 sm:h-56 lg:h-64">
            <canvas id="probChart"></canvas>
        </div>
        @endif
    </div>

    {{-- Feature Importance --}}
    <div class="card p-4 sm:p-5 overflow-hidden">
        <h3 class="text-sm font-semibold text-slate-800 mb-3 sm:mb-4">📈 Top Risk Factors</h3>
        @if($featureImportance->count() > 0)
        <div class="w-full h-48 sm:h-56 lg:h-64">
            <canvas id="importanceChart"></canvas>
        </div>
        @else
        <div class="h-48 sm:h-56 lg:h-64 flex items-center justify-center">
            <p class="text-sm text-slate-400">Feature importance not available for this model.</p>
        </div>
        @endif
    </div>
</div>

{{-- ── AI Recommendations ────────────────────────────────── --}}
<div class="card p-4 sm:p-6 mb-4 sm:mb-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 sm:mb-5 gap-3">
        <div>
            <h3 class="font-semibold text-slate-800 text-sm sm:text-base">💡 AI-Powered Recommendations</h3>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Personalized advice based on your assessment data</p>
        </div>
        @if(Auth::user()->hasCalendarConnected())
        <span class="text-xs text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200 flex-shrink-0">
            🗓 Calendar connected
        </span>
        @endif
    </div>

    @if($prediction->recommendations->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach($prediction->recommendations as $rec)
        <div class="border border-slate-200 rounded-xl p-4 hover:shadow-sm transition-shadow">
            <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
                <div class="flex items-center gap-2">
                    <span class="text-base sm:text-lg">{{ $rec->categoryIcon() }}</span>
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full
                        {{ $rec->priority === 'high' ? 'bg-red-100 text-red-700' : ($rec->priority === 'medium' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                        {{ ucfirst($rec->priority) }}
                    </span>
                </div>
                <span class="text-xs text-slate-400 capitalize bg-slate-100 px-2 py-0.5 rounded-full">{{ $rec->category }}</span>
            </div>
            <h4 class="text-sm font-semibold text-slate-800 mb-1.5">{{ $rec->title }}</h4>
            <p class="text-xs text-slate-600 leading-relaxed line-clamp-3">{{ $rec->description }}</p>

            @if(Auth::user()->hasCalendarConnected())
            <form method="POST" action="{{ route('calendar.sync') }}" class="mt-3">
                @csrf
                <input type="hidden" name="recommendation_id" value="{{ $rec->id }}">
                <div class="flex flex-col sm:flex-row gap-2">
                    <input type="datetime-local" name="event_date" min="{{ now()->addHour()->format('Y-m-d\TH:i') }}"
                        class="flex-1 text-xs border border-slate-300 rounded-lg px-2 py-1.5 sm:py-2 focus:outline-none focus:ring-1 focus:ring-primary-500 w-full sm:w-auto">
                    <button type="submit" class="text-xs bg-primary-600 text-white px-3 py-1.5 sm:py-2 rounded-lg hover:bg-primary-700 whitespace-nowrap">
                        📅 Add
                    </button>
                </div>
            </form>
            @endif
        </div>
        @endforeach
    </div>
    @else
    <p class="text-sm text-slate-400">No recommendations generated yet.</p>
    @endif

    @if(!Auth::user()->hasCalendarConnected())
    <div class="mt-4 p-3 bg-slate-50 border border-slate-200 rounded-lg flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div class="text-sm text-slate-600">
            🗓 Connect Google Calendar to schedule these recommendations as reminders
        </div>
        <a href="{{ route('calendar.connect') }}" class="btn-secondary text-xs py-1.5 px-3 flex-shrink-0 w-full sm:w-auto text-center justify-center">
            Connect Calendar
        </a>
    </div>
    @endif
</div>

{{-- ── Assessment Details ────────────────────────────────── --}}
<div class="card p-4 sm:p-6 mb-4 sm:mb-6">
    <h3 class="font-semibold text-slate-800 mb-4 text-sm sm:text-base">📋 Assessment Details</h3>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 text-sm">
        @php $a = $prediction->assessment; @endphp
        @foreach([
            ['Work Hours/Week', $a->work_hours_per_week . ' hrs'],
            ['Overtime/Week', $a->overtime_hours . ' hrs'],
            ['Meetings/Day', $a->meetings_per_day],
            ['Deadlines Missed', $a->deadlines_missed],
            ['Stress Level', $a->stress_level . '/10'],
            ['Anxiety Score', $a->anxiety_score . '/10'],
            ['Depression Score', $a->depression_score . '/10'],
            ['Sleep Hours', $a->sleep_hours . 'h/night'],
            ['Job Satisfaction', $a->job_satisfaction . '/10'],
            ['Work-Life Balance', $a->work_life_balance . '/10'],
            ['Manager Support', $a->manager_support . '/10'],
            ['Physical Activity', $a->physical_activity_days . ' days/wk'],
        ] as [$label, $val])
        <div class="bg-slate-50 rounded-lg p-2 sm:p-3">
            <div class="text-xs text-slate-500 mb-0.5 truncate">{{ $label }}</div>
            <div class="font-semibold text-slate-800 truncate">{{ $val }}</div>
        </div>
        @endforeach
    </div>
</div>

{{-- ── Actions ──────────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row gap-3">
    <a href="{{ route('assessments.create') }}" class="btn-primary justify-center sm:justify-start">
        🔄 Take New Assessment
    </a>
    <a href="{{ route('assessments.history') }}" class="btn-secondary justify-center sm:justify-start">
        📋 View History
    </a>
    <a href="{{ route('dashboard') }}" class="btn-secondary justify-center sm:justify-start">
        🏠 Dashboard
    </a>
</div>

@endsection

@push('scripts')
<script>
const riskHex    = '{{ $riskColor["hex"] }}';
const burnoutProb = {{ $prediction->burnout_probability }};
const probData   = @json($probabilities);
const importData = @json($featureImportance);

// Gauge / Doughnut for burnout probability
new Chart(document.getElementById('gaugeChart'), {
    type: 'doughnut',
    data: {
        datasets: [{
            data: [burnoutProb, 100 - burnoutProb],
            backgroundColor: [riskHex, '#f1f5f9'],
            borderWidth: 0,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '75%',
        rotation: -90,
        circumference: 180,
        plugins: { legend: { display: false }, tooltip: { enabled: false } },
    }
});

// Probabilities bar chart
if (probData.length > 0) {
    new Chart(document.getElementById('probChart'), {
        type: 'bar',
        data: {
            labels: probData.map(d => d.label),
            datasets: [{
                label: 'Probability %',
                data: probData.map(d => d.value),
                backgroundColor: probData.map(d =>
                    d.label === 'High' ? '#ef4444' :
                    d.label === 'Moderate' ? '#f59e0b' : '#10b981'
                ),
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { min: 0, max: 100, ticks: { callback: v => v + '%', font: { size: 11 } }, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false }, ticks: { font: { size: 10 } } }
            }
        }
    });
}

// Feature importance horizontal bar
if (importData.length > 0) {
    new Chart(document.getElementById('importanceChart'), {
        type: 'bar',
        data: {
            labels: importData.map(d => d.feature),
            datasets: [{
                data: importData.map(d => d.value),
                backgroundColor: '#4f63d2',
                borderRadius: 4,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { callback: v => v + '%', font: { size: 10 } }, grid: { color: '#f1f5f9' } },
                y: { ticks: { font: { size: 10 } }, grid: { display: false } }
            }
        }
    });
}
</script>
@endpush
