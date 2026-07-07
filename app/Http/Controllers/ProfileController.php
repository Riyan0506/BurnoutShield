<?php

namespace App\Http\Controllers;

use App\Models\DemographicData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user()->load('demographic');
        return view('profile.index', compact('user'));
    }

    public function edit()
    {
        $user = Auth::user()->load('demographic');
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email,' . $user->id,
            'age'              => 'nullable|integer|min:16|max:80',
            'gender'           => 'nullable|in:Male,Female,Non-binary,Prefer not to say',
            'job_role'         => 'nullable|string',
            'experience_years' => 'nullable|numeric|min:0|max:50',
            'company_size'     => 'nullable|string',
            'work_mode'        => 'nullable|in:Remote,Onsite,Hybrid',
            'current_password' => 'nullable|string',
            'password'         => 'nullable|string|min:8|confirmed',
        ]);

        // Update user core info
        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        // Update password if provided
        if ($request->filled('current_password')) {
            if (! Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            $user->update(['password' => Hash::make($request->password)]);
        }

        // Update or create demographic data (single source of truth)
        DemographicData::updateOrCreate(
            ['user_id' => $user->id],
            [
                'age'              => $request->age,
                'gender'           => $request->gender,
                'job_role'         => $request->job_role,
                'experience_years' => $request->experience_years,
                'company_size'     => $request->company_size,
                'work_mode'        => $request->work_mode,
            ]
        );

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully!');
    }
}
