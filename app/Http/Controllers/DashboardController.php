<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show dashboard
     */
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        $userRank = $user->getRank();

        return view('dashboard', [
            'userRank' => $userRank,
            'latestInstructorApplication' => $user->instructorApplications()->latest()->first(),
        ]);
    }
}
