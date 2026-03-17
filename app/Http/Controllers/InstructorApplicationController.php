<?php

namespace App\Http\Controllers;

use App\Models\InstructorApplication;
use Illuminate\Http\Request;

class InstructorApplicationController extends Controller
{
    public function create(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'user') {
            return redirect()->route('dashboard')
                ->with('error', 'Pengajuan instructor hanya tersedia untuk pengguna biasa.');
        }

        return view('instructor.apply', [
            'latestApplication' => $user->instructorApplications()->latest()->first(),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'user') {
            return redirect()->route('dashboard')
                ->with('error', 'Pengajuan instructor hanya tersedia untuk pengguna biasa.');
        }

        $existingPendingApplication = $user->instructorApplications()
            ->where('status', 'pending')
            ->first();

        if ($existingPendingApplication) {
            return redirect()->route('instructor.apply')
                ->with('error', 'Anda masih memiliki pengajuan instructor yang sedang direview admin.');
        }

        $validated = $request->validate([
            'expertise' => ['required', 'string', 'max:255'],
            'motivation' => ['required', 'string', 'min:20', 'max:2000'],
            'experience' => ['nullable', 'string', 'max:2000'],
        ]);

        InstructorApplication::create([
            'user_id' => $user->id,
            'expertise' => $validated['expertise'],
            'motivation' => $validated['motivation'],
            'experience' => $validated['experience'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('instructor.apply')
            ->with('success', 'Pengajuan instructor berhasil dikirim dan sedang menunggu review admin.');
    }
}
