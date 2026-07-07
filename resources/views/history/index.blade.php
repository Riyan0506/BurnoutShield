@extends('layouts.app')
@section('title','Assessment History')
@section('page-title','Assessment History')
@section('page-subtitle','All your previous burnout assessments')

@section('content')
<div class="card overflow-hidden">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 sm:p-5 border-b border-slate-200 gap-3">
        <h3 class="font-semibold text-slate-800 text-sm sm:text-base">All Assessments</h3>
        <a href="{{ route('assessments.create') }}" class="btn-primary text-sm py-1.5 px-3 w-full sm:w-auto text-center justify-center">+ New Assessment</a>
    </div>
    
    {{-- Mobile Card View --}}
    <div class="block lg:hidden">
        @forelse($assessments as $a)
        <div class="p-4 border-b border-slate-100 last:border-b-0">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <div class="text-sm font-medium text-slate-800">{{ $a->created_at->format('d M Y') }}</div>
                    <div class="text-xs text-slate-500 mt-0.5">{{ $a->work_hours_per_week }}h/week</div>
                </div>
                @if($a->prediction)
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium {{ $a->prediction->risk_level === 'High' ? 'bg-red-100 text-red-700' : ($a->prediction->risk_level === 'Moderate' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                    {{ $a->prediction->risk_level }}
                </span>
                @endif
            </div>
            <div class="flex items-center gap-3 mb-3">
                <div class="flex-1 bg-slate-200 rounded-full h-1.5">
                    <div class="h-1.5 rounded-full {{ $a->stress_level >= 7 ? 'bg-red-500' : ($a->stress_level >= 5 ? 'bg-amber-400' : 'bg-emerald-400') }}" style="width:{{ ($a->stress_level/10)*100 }}%"></div>
                </div>
                <span class="text-xs text-slate-600">Stress: {{ $a->stress_level }}/10</span>
            </div>
            @if($a->prediction)
            <div class="flex items-center justify-between">
                <span class="{{ $a->prediction->risk_level === 'High' ? 'text-red-600' : ($a->prediction->risk_level === 'Moderate' ? 'text-amber-600' : 'text-emerald-600') }} font-semibold text-sm">
                    {{ number_format($a->prediction->burnout_probability, 1) }}% Burnout
                </span>
                <a href="{{ route('predictions.show', $a->prediction->id) }}" class="text-xs border border-slate-300 hover:border-primary-400 hover:text-primary-600 text-slate-600 px-3 py-1.5 rounded-lg transition-colors">
                    View
                </a>
            </div>
            @endif
        </div>
        @empty
        <div class="p-8 text-center text-slate-400">
            <span class="text-4xl block mb-2">📋</span>
            <span class="text-sm">No assessments yet.</span>
            <a href="{{ route('assessments.create') }}" class="text-primary-600 hover:underline text-sm block mt-1">Take your first one!</a>
        </div>
        @endforelse
    </div>
    
    {{-- Desktop Table View --}}
    <div class="hidden lg:block">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">#</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Date</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Job Role</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Hrs</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Stress</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Risk</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Burnout %</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($assessments as $a)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-4 text-sm text-slate-500 whitespace-nowrap">{{ $loop->index + 1 }}</td>
                        <td class="px-5 py-4 text-sm text-slate-700 whitespace-nowrap">{{ $a->created_at->format('d M Y') }}</td>
                        <td class="px-5 py-4 text-sm text-slate-700 whitespace-nowrap">{{ Auth::user()->demographic?->job_role ?? '—' }}</td>
                        <td class="px-5 py-4 text-sm text-slate-700 whitespace-nowrap">{{ $a->work_hours_per_week }}h</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <div class="bg-slate-200 rounded-full h-1.5 w-16">
                                    <div class="h-1.5 rounded-full {{ $a->stress_level >= 7 ? 'bg-red-500' : ($a->stress_level >= 5 ? 'bg-amber-400' : 'bg-emerald-400') }}" style="width:{{ ($a->stress_level/10)*100 }}%"></div>
                                </div>
                                <span class="text-sm text-slate-700 whitespace-nowrap">{{ $a->stress_level }}.0</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            @if($a->prediction)
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium {{ $a->prediction->risk_level === 'High' ? 'bg-red-100 text-red-700' : ($a->prediction->risk_level === 'Moderate' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $a->prediction->risk_level === 'High' ? 'bg-red-500' : ($a->prediction->risk_level === 'Moderate' ? 'bg-amber-500' : 'bg-emerald-500') }}"></span>
                                {{ $a->prediction->risk_level }}
                            </span>
                            @else
                            <span class="text-xs text-slate-400">Processing...</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            @if($a->prediction)
                            <span class="{{ $a->prediction->risk_level === 'High' ? 'text-red-600' : ($a->prediction->risk_level === 'Moderate' ? 'text-amber-600' : 'text-emerald-600') }} font-semibold text-sm">
                                {{ number_format($a->prediction->burnout_probability, 1) }}%
                            </span>
                            @else
                            <span class="text-slate-400 text-sm">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            @if($a->prediction)
                            <a href="{{ route('predictions.show', $a->prediction->id) }}" class="text-xs border border-slate-300 hover:border-primary-400 hover:text-primary-600 text-slate-600 px-3 py-1.5 rounded-lg transition-colors inline-block">
                                View
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                            <span class="text-4xl block mb-2">📋</span>
                            <span class="text-sm">No assessments yet.</span>
                            <a href="{{ route('assessments.create') }}" class="text-primary-600 hover:underline text-sm block mt-1">Take your first one!</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($assessments->hasPages())
    <div class="p-4 border-t border-slate-200 flex justify-center">{{ $assessments->links() }}</div>
    @endif
</div>
@endsection
