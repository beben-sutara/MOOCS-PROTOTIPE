<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $role = trim((string) $request->query('role', ''));

        $usersQuery = User::query()
            ->withCount(['instructedCourses', 'enrollments'])
            ->latest();

        if ($search !== '') {
            $usersQuery->where(function ($query) use ($search) {
                $query
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        if (in_array($role, ['user', 'instructor', 'admin'], true)) {
            $usersQuery->where('role', $role);
        }

        $users = $usersQuery->limit(50)->get();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'role' => $role,
            'totalUsers' => User::count(),
            'totalStudents' => User::where('role', 'user')->count(),
            'totalInstructors' => User::where('role', 'instructor')->count(),
            'totalAdmins' => User::where('role', 'admin')->count(),
        ]);
    }

    public function edit(User $user)
    {
        $instructedCourses = $user->instructedCourses()
            ->withCount(['modules', 'enrollments'])
            ->latest()
            ->get();

        return view('admin.users.edit', [
            'managedUser' => $user,
            'userRank' => $user->getRank(),
            'courseCount' => $instructedCourses->count(),
            'enrollmentCount' => $user->enrollments()->count(),
            'completedEnrollmentCount' => $user->enrollments()->where('status', 'completed')->count(),
            'recentXpLogs' => $user->xpLogs()->latest()->take(6)->get(),
            'instructedCourses' => $instructedCourses,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in(['user', 'instructor', 'admin'])],
        ]);

        $requestedRole = $validated['role'];
        $currentUser = $request->user();

        if ($currentUser && $currentUser->id === $user->id && $requestedRole !== 'admin') {
            return back()
                ->withErrors(['role' => 'Admin tidak bisa menghapus role admin dari akunnya sendiri.'])
                ->withInput();
        }

        if ($user->role === 'admin' && $requestedRole !== 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()
                ->withErrors(['role' => 'Sistem harus memiliki minimal satu admin aktif.'])
                ->withInput();
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?: null,
            'role' => $requestedRole,
        ]);

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('success', 'Data user berhasil diperbarui.');
    }
}
