<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Module;
use App\Models\User;

class HomeController extends Controller
{
    /**
     * Display the home/welcome page
     */
    public function index()
    {
        $stats = [
            'total_users' => User::where('role', '!=', 'admin')->count(),
            'total_courses' => Course::where('status', 'published')->count(),
            'total_modules' => Module::count(),
        ];

        $featuredCourses = Course::where('status', 'published')
            ->with(['modules', 'instructor'])
            ->withCount('enrollments')
            ->latest()
            ->take(6)
            ->get();

        return view('home', [
            'stats' => $stats,
            'featuredCourses' => $featuredCourses,
        ]);
    }
}
