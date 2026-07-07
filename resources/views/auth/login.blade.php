@extends('layouts.auth')
@section('title', 'Login')
@section('content')
<div class="mb-5 sm:mb-6">
    <h2 class="text-lg sm:text-xl font-bold text-slate-800">Welcome back</h2>
    <p class="text-slate-500 text-sm mt-1">Sign in to your account</p>
</div>

@if($errors->any())
<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600">
    {{ $errors->first() }}
</div>
@endif

<form method="POST" action="{{ route('login') }}" class="space-y-4">
    @csrf
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required
            class="w-full px-3 py-2.5 sm:px-3.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            placeholder="you@example.com">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
        <input type="password" name="password" required
            class="w-full px-3 py-2.5 sm:px-3.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            placeholder="••••••••">
    </div>
    <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
            <input type="checkbox" name="remember" class="rounded border-slate-300">
            Remember me
        </label>
    </div>
    <button type="submit"
        class="w-full py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg text-sm transition-colors">
        Sign In
    </button>
</form>

<p class="text-center text-sm text-slate-500 mt-5 sm:mt-6">
    Don't have an account?
    <a href="{{ route('register') }}" class="text-primary-600 hover:underline font-medium">Create one</a>
</p>

<!-- Demo credentials -->
<div class="mt-4 p-3 bg-slate-50 rounded-lg border border-slate-200 text-xs text-slate-500">
    <strong class="text-slate-700">Demo:</strong> employee@burnoutshield.id / password
</div>
@endsection
