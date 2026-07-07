@extends('layouts.auth')
@section('title', 'Register')
@section('content')
<div class="mb-5 sm:mb-6">
    <h2 class="text-lg sm:text-xl font-bold text-slate-800">Create account</h2>
    <p class="text-slate-500 text-sm mt-1">Start monitoring your wellness today</p>
</div>

@if($errors->any())
<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600">
    <ul class="space-y-0.5">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('register') }}" class="space-y-4">
    @csrf
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Full Name</label>
        <input type="text" name="name" value="{{ old('name') }}" required
            class="w-full px-3 py-2.5 sm:px-3.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            placeholder="John Doe">
    </div>
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
            placeholder="Min. 8 characters">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Confirm Password</label>
        <input type="password" name="password_confirmation" required
            class="w-full px-3 py-2.5 sm:px-3.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            placeholder="Repeat password">
    </div>
    <button type="submit"
        class="w-full py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg text-sm transition-colors">
        Create Account
    </button>
</form>

<p class="text-center text-sm text-slate-500 mt-5 sm:mt-6">
    Already have an account?
    <a href="{{ route('login') }}" class="text-primary-600 hover:underline font-medium">Sign in</a>
</p>
@endsection
