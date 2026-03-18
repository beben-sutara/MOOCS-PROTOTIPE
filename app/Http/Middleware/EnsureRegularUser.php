<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRegularUser
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'user') {
            return redirect()->route('dashboard')
                ->with('error', 'Leaderboard hanya tersedia untuk pengguna biasa.');
        }

        return $next($request);
    }
}
