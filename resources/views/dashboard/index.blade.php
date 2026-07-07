@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle')
    Welcome back, {{ Auth::user()->name }}!
@endsection

@section('content')
<div class="space-y-4 sm:space-y-5">

{{-- Profile incomplete warning --}}
@if(!$profileComplete)
<div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-4 bg-amber-50 border border-amber-200 rounded-xl mb-4 sm:mb-6">
    <span class="text-xl flex-shrink-0">⚠️</span>
    <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-amber-800">Complete your profile to take assessments</p>
        <p class="text-xs text-amber-600 mt-0.5">Add your demographic information to get personalized predictions.</p>
    </div>
    <a href="{{ route('profile.edit') }}" class="btn-primary text-xs px-3 py-1.5 flex-shrink-0 w-full sm:w-auto text-center justify-center">Complete Profile</a>
</div>
@endif

{{-- ── Stat Cards ────────────────────────────────────────── --}}
<div class="grid grid-cols-1 xs:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 mb-4 sm:mb-6">

    {{-- Total Assessments --}}
    <div class="card p-4 sm:p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 sm:w-10 sm:h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <span class="text-xs text-slate-400 font-medium bg-slate-100 px-2 py-0.5 rounded-full">All Time</span>
        </div>
        <div class="text-2xl sm:text-3xl font-bold text-slate-800">{{ $totalAssessments }}</div>
        <div class="text-sm text-slate-500 mt-1">Total Assessments</div>
    </div>

    {{-- Current Risk Level --}}
    <div class="card p-4 sm:p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 sm:w-10 sm:h-10 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <span class="text-base sm:text-lg">🔥</span>
            </div>
            @if($latestPrediction)
            <span class="text-xs font-medium px-2 py-0.5 rounded-full whitespace-nowrap
                {{ $latestPrediction->risk_level === 'High' ? 'bg-red-100 text-red-700' : ($latestPrediction->risk_level === 'Moderate' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                {{ $latestPrediction->risk_level }}
            </span>
            @endif
        </div>
        <div class="text-2xl sm:text-3xl font-bold {{ $latestPrediction ? 'risk-'.strtolower($latestPrediction->risk_level) : 'text-slate-400' }}">
            {{ $latestPrediction ? $latestPrediction->risk_level : 'N/A' }}
        </div>
        <div class="text-sm text-slate-500 mt-1">Current Risk Level</div>
    </div>

    {{-- Avg Burnout Probability --}}
    <div class="card p-4 sm:p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 sm:w-10 sm:h-10 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
        </div>
        <div class="text-2xl sm:text-3xl font-bold text-slate-800">{{ number_format($avgProbability, 1) }}%</div>
        <div class="text-sm text-slate-500 mt-1">Avg. Burnout</div>
    </div>

    {{-- Last Assessment Date --}}
    <div class="card p-4 sm:p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 sm:w-10 sm:h-10 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="text-xl sm:text-2xl font-bold text-slate-800 truncate">
            {{ $latestPrediction ? $latestPrediction->created_at->format('d M Y') : '—' }}
        </div>
        <div class="text-sm text-slate-500 mt-1">Last Assessment</div>
    </div>
</div>

{{-- ── Charts Row ────────────────────────────────────────── --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-5 mb-4 sm:mb-6">

    {{-- Burnout Trend --}}
    <div class="card p-4 sm:p-5 xl:col-span-2 overflow-hidden">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 mb-3 sm:mb-4">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">📈 Burnout Trend</h3>
            </div>
            <span class="text-xs text-slate-400">Last {{ count($trendData) }} assessments</span>
        </div>
        @if(count($trendData) > 0)
        <div class="w-full h-48 sm:h-56 lg:h-64">
            <canvas id="trendChart"></canvas>
        </div>
        @else
        <div class="flex flex-col items-center justify-center h-32 sm:h-48 text-slate-400">
            <span class="text-3xl mb-2">📊</span>
            <p class="text-sm">No data yet. Take your first assessment!</p>
        </div>
        @endif
    </div>

    {{-- Risk Distribution --}}
    <div class="card p-4 sm:p-5 overflow-hidden">
        <h3 class="text-sm font-semibold text-slate-800 mb-3 sm:mb-4">🍩 Risk Distribution</h3>
        @if($riskDist->sum() > 0)
        <div class="w-full h-48 sm:h-56 lg:h-64 flex items-center justify-center">
            <canvas id="riskChart"></canvas>
        </div>
        @else
        <div class="flex flex-col items-center justify-center h-32 sm:h-48 text-slate-400">
            <span class="text-3xl mb-2">📊</span>
            <p class="text-sm text-center">No assessments yet</p>
        </div>
        @endif
    </div>
</div>

{{-- ── Stress Trend & Recommendations ──────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-5 mb-4 sm:mb-6">

    {{-- Stress Trend --}}
    <div class="card p-4 sm:p-5 overflow-hidden">
        <h3 class="text-sm font-semibold text-slate-800 mb-3 sm:mb-4">😰 Stress Trend</h3>
        @if(count($trendData) > 0)
        <div class="w-full h-40 sm:h-48 lg:h-52">
            <canvas id="stressChart"></canvas>
        </div>
        @else
        <div class="flex flex-col items-center justify-center h-28 sm:h-40 text-slate-400">
            <p class="text-sm">No data yet</p>
        </div>
        @endif
    </div>

    {{-- Latest Recommendations --}}
    <div class="card p-4 sm:p-5">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 mb-4">
            <h3 class="text-sm font-semibold text-slate-800">💡 Latest Recommendations</h3>
            @if($latestPrediction)
            <a href="{{ route('predictions.show', $latestPrediction->id) }}"
               class="text-xs text-primary-600 hover:underline font-medium whitespace-nowrap">View All</a>
            @endif
        </div>
        @if($latestRecommendations->count() > 0)
        <ul class="space-y-2.5 max-h-40 sm:max-h-48 overflow-y-auto">
            @foreach($latestRecommendations as $rec)
            <li class="flex items-start gap-2.5">
                <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0
                    {{ $rec->priority === 'high' ? 'bg-red-400' : ($rec->priority === 'medium' ? 'bg-amber-400' : 'bg-emerald-400') }}">
                </div>
                <span class="text-sm text-slate-700 leading-snug">{{ $rec->title }}</span>
            </li>
            @endforeach
        </ul>
        @else
        <p class="text-sm text-slate-400">Take an assessment to get personalized recommendations.</p>
        @endif
    </div>
</div>

{{-- ── Action Buttons ────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row gap-3">
    <a href="{{ route('assessments.create') }}" class="btn-primary justify-center sm:justify-start">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Assessment
    </a>
    <a href="{{ route('assessments.history') }}" class="btn-secondary justify-center sm:justify-start">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
        View History
    </a>

    {{-- ML Engine status indicator --}}
    <div class="sm:ml-auto flex items-center gap-2 text-xs text-slate-500 mt-2 sm:mt-0">
        <div class="w-2 h-2 rounded-full {{ $mlHealthy ? 'bg-emerald-400' : 'bg-red-400' }} flex-shrink-0"></div>
        <span class="whitespace-nowrap">ML Engine: {{ $mlHealthy ? 'Online' : 'Offline' }}</span>
    </div>
</div>

</div>

@endsection

@push('scripts')
<script>
const trendData = @json($trendData);
const riskDist  = @json($riskDist);

if (trendData.length > 0) {
    // Burnout Trend Chart
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: trendData.map(d => d.date),
            datasets: [{
                label: 'Burnout %',
                data: trendData.map(d => d.probability),
                borderColor: '#4f63d2',
                backgroundColor: 'rgba(79,99,210,0.08)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: trendData.map(d =>
                    d.risk_level === 'High' ? '#ef4444' :
                    d.risk_level === 'Moderate' ? '#f59e0b' : '#10b981'
                ),
                pointRadius: 5,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { min: 0, max: 100, grid: { color: '#f1f5f9' },
                     ticks: { callback: v => v + '%', font: { size: 11 } } },
                x: { grid: { display: false }, ticks: { font: { size: 11 } } }
            }
        }
    });

    // Stress Trend Chart
    new Chart(document.getElementById('stressChart'), {
        type: 'line',
        data: {
            labels: trendData.map(d => d.date),
            datasets: [{
                label: 'Stress Level',
                data: trendData.map(d => d.stress),
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245,158,11,0.08)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { min: 0, max: 10, grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 } } },
                x: { grid: { display: false }, ticks: { font: { size: 11 } } }
            }
        }
    });
}

if (Object.keys(riskDist).length > 0) {
    new Chart(document.getElementById('riskChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(riskDist),
            datasets: [{
                data: Object.values(riskDist),
                backgroundColor: ['#ef4444','#10b981','#f59e0b'],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 12 } }
            }
        }
    });
}
</script>
@endpush
