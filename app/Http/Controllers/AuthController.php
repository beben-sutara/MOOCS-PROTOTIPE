<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected function getSelectedCourse(?string $courseId): ?Course
    {
        if (!$courseId || !ctype_digit($courseId)) {
            return null;
        }

        return Course::where('status', 'published')->find((int) $courseId);
    }

    protected function redirectAfterAuthentication(User $user, ?Course $selectedCourse)
    {
        if ($selectedCourse) {
            Enrollment::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'course_id' => $selectedCourse->id,
                ],
                [
                    'status' => 'active',
                    'enrolled_at' => now(),
                ]
            );

            return redirect()
                ->route('courses.show', $selectedCourse)
                ->with('success', 'Akun siap digunakan. Anda langsung terdaftar ke course pilihan.');
        }

        return redirect('/dashboard');
    }

    /**
     * Show login form
     */
    public function showLogin(Request $request)
    {
        return view('auth.login', [
            'selectedCourse' => $this->getSelectedCourse($request->query('course')),
        ]);
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $selectedCourse = $this->getSelectedCourse($request->input('course'));

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            /** @var User $user */
            $user = Auth::user();

            return $this->redirectAfterAuthentication($user, $selectedCourse);
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    /**
     * Show register form
     */
    public function showRegister(Request $request)
    {
        return view('auth.register', [
            'selectedCourse' => $this->getSelectedCourse($request->query('course')),
        ]);
    }

    /**
     * Handle register request
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $selectedCourse = $this->getSelectedCourse($request->input('course'));

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
            'xp' => 0,
            'level' => 1,
            'next_level_xp' => 100,
        ]);

        Auth::login($user);

        return $this->redirectAfterAuthentication($user, $selectedCourse)
            ->with('success', $selectedCourse
                ? 'Akun berhasil dibuat dan Anda langsung terdaftar ke course pilihan.'
                : 'Akun berhasil dibuat!');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Logout berhasil');
    }
}
