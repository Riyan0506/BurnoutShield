@extends('layouts.app')
@section('title','My Profile')
@section('page-title','My Profile')
@section('page-subtitle','Account information')

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6">
    {{-- Avatar Card --}}
    <div class="card p-5 sm:p-6 lg:p-8 flex flex-col items-center text-center">
        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-primary-600 flex items-center justify-center text-white text-2xl sm:text-3xl font-bold mb-3">
            {{ strtoupper(substr($user->name,0,1)) }}
        </div>
        <h2 class="font-semibold text-slate-800 text-base sm:text-lg">{{ $user->name }}</h2>
        <p class="text-slate-500 text-sm break-all">{{ $user->email }}</p>
        <p class="mt-2 text-xs font-medium text-primary-600 bg-primary-50 px-3 py-1 rounded-full">
            {{ $user->demographic?->job_role ?? 'No Role Set' }}
        </p>
        <div class="mt-4 text-xs text-slate-400">Member since {{ $user->created_at->format('M Y') }}</div>
    </div>

    {{-- Demographic Info --}}
    <div class="card p-4 sm:p-5 lg:p-6 xl:col-span-2">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 sm:mb-5 gap-3">
            <h3 class="font-semibold text-slate-800 text-sm sm:text-base">Demographic Data</h3>
            <a href="{{ route('profile.edit') }}" class="btn-primary text-xs py-1.5 px-3 w-full sm:w-auto text-center justify-center">✏️ Edit Profile</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 sm:gap-y-5 gap-x-4 sm:gap-x-6">
            @foreach([
                ['Name','name',$user->name],
                ['Email','email',$user->email],
                ['Age','age',$user->demographic?->age],
                ['Gender','gender',$user->demographic?->gender],
                ['Job Role','job_role',$user->demographic?->job_role],
                ['Experience (Years)','exp',$user->demographic?->experience_years ? $user->demographic->experience_years.' yrs' : null],
                ['Company Size','company_size',$user->demographic?->company_size],
                ['Work Mode','work_mode',$user->demographic?->work_mode],
            ] as [$label,$key,$val])
            <div>
                <div class="text-xs text-slate-500 mb-1">{{ $label }}</div>
                <div class="text-sm font-medium text-slate-800 truncate">{{ $val ?? '—' }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
