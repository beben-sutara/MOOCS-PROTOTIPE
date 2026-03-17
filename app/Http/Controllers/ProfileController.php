<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show user profile
     */
    public function show()
    {
        $userRank = Auth::user()->getRank();

        return view('profile', [
            'userRank' => $userRank,
            'latestInstructorApplication' => Auth::user()->instructorApplications()->latest()->first(),
        ]);
    }

    /**
     * Update user profile information
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'phone' => 'nullable|string|max:20',
            'current_password' => 'required|current_password',
        ]);

        Auth::user()->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        return redirect('/profile')->with('success', 'Profile updated successfully');
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'old_password' => 'required|current_password',
            'new_password' => 'required|min:6|confirmed',
        ]);

        Auth::user()->update([
            'password' => Hash::make($validated['new_password'])
        ]);

        return redirect('/profile')->with('success', 'Password changed successfully');
    }
}
