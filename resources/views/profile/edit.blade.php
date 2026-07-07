@extends('layouts.app')
@section('title','Edit Profile')
@section('page-title','Edit Profile')
@section('page-subtitle','Update your personal and demographic information')

@section('content')
@if($errors->any())
<div class="mb-4 sm:mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600">
    <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('profile.update') }}">
@csrf @method('PUT')

<div class="grid grid-cols-1 xl:grid-cols-2 gap-4 sm:gap-5">

{{-- Account Info --}}
<div class="card p-4 sm:p-5 lg:p-6">
    <h3 class="font-semibold text-slate-800 mb-4 text-sm sm:text-base">Account Information</h3>
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Full Name *</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                class="w-full px-3 py-2 sm:px-3.5 sm:py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Email Address *</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                class="w-full px-3 py-2 sm:px-3.5 sm:py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>
    </div>

    <div class="border-t border-slate-200 my-4 sm:my-5"></div>
    <h3 class="font-semibold text-slate-800 mb-4 text-sm sm:text-base">Change Password <span class="text-xs text-slate-400 font-normal">(leave blank to keep current)</span></h3>
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Current Password</label>
            <input type="password" name="current_password" class="w-full px-3 py-2 sm:px-3.5 sm:py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">New Password</label>
            <input type="password" name="password" class="w-full px-3 py-2 sm:px-3.5 sm:py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Confirm New Password</label>
            <input type="password" name="password_confirmation" class="w-full px-3 py-2 sm:px-3.5 sm:py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>
    </div>
</div>

{{-- Demographic Data --}}
<div class="card p-4 sm:p-5 lg:p-6">
    <h3 class="font-semibold text-slate-800 mb-4 text-sm sm:text-base">Demographic Data</h3>
    <div class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Age</label>
                <input type="number" name="age" value="{{ old('age', $user->demographic?->age) }}" min="16" max="80"
                    class="w-full px-3 py-2 sm:px-3.5 sm:py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Gender</label>
                <select name="gender" class="w-full px-3 py-2 sm:px-3.5 sm:py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Select...</option>
                    @foreach(['Male','Female','Non-binary','Prefer not to say'] as $g)
                    <option value="{{ $g }}" {{ old('gender', $user->demographic?->gender) == $g ? 'selected' : '' }}>{{ $g }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Job Role</label>
            <select name="job_role" class="w-full px-3 py-2 sm:px-3.5 sm:py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Select...</option>
                @foreach(['Software Engineer','Data Scientist','DevOps','Frontend Developer','Backend Developer','Full Stack Developer','Product Manager','Project Manager','UX Designer','QA Engineer','CTO','Other'] as $role)
                <option value="{{ $role }}" {{ old('job_role', $user->demographic?->job_role) == $role ? 'selected' : '' }}>{{ $role }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Experience (Years)</label>
            <input type="number" name="experience_years" step="0.5" value="{{ old('experience_years', $user->demographic?->experience_years) }}" min="0" max="50"
                class="w-full px-3 py-2 sm:px-3.5 sm:py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Company Size</label>
            <select name="company_size" class="w-full px-3 py-2 sm:px-3.5 sm:py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Select...</option>
                @foreach(['Startup (<50)','Mid-size (50-500)','Large (500-5000)','MNC (>5000)'] as $s)
                <option value="{{ $s }}" {{ old('company_size', $user->demographic?->company_size) == $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Work Mode</label>
            <select name="work_mode" class="w-full px-3 py-2 sm:px-3.5 sm:py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Select...</option>
                @foreach(['Remote','Onsite','Hybrid'] as $m)
                <option value="{{ $m }}" {{ old('work_mode', $user->demographic?->work_mode) == $m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
</div>

<div class="flex flex-col sm:flex-row gap-3 mt-4 sm:mt-5">
    <button type="submit" class="btn-primary justify-center sm:justify-start">💾 Save Changes</button>
    <a href="{{ route('profile.show') }}" class="btn-secondary justify-center sm:justify-start">Cancel</a>
</div>
</form>
@endsection
