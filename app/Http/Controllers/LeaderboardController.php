<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\XpRewardService;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    protected XpRewardService $rewardService;

    public function __construct(XpRewardService $rewardService)
    {
        $this->rewardService = $rewardService;
    }

    /**
     * Get top users by XP (Global Leaderboard)
     *
     * GET /api/leaderboard/xp
     */
    public function topByXp(Request $request)
    {
        $request->validate([
            'limit' => 'integer|min:1|max:100',
        ]);

        $limit = $request->get('limit', 100);
        $leaderboard = $this->rewardService->getLeaderboard($limit);

        // Add user's own rank if authenticated
        $userRank = null;
        if (auth()->check()) {
            $userRank = $this->rewardService->getUserRank(auth()->user());
        }

        return response()->json([
            'success' => true,
            'data' => $leaderboard,
            'user_rank' => $userRank,
            'total_users' => User::where('role', '!=', 'admin')->count(),
        ]);
    }

    /**
     * Get top users by Level
     *
     * GET /api/leaderboard/level
     */
    public function topByLevel(Request $request)
    {
        $request->validate([
            'limit' => 'integer|min:1|max:100',
        ]);

        $limit = $request->get('limit', 100);
        $leaderboard = $this->rewardService->getTopUsersByLevel($limit);

        return response()->json([
            'success' => true,
            'data' => $leaderboard,
            'total_users' => User::where('role', '!=', 'admin')->count(),
        ]);
    }

    /**
     * Get weekly leaderboard
     *
     * GET /api/leaderboard/weekly
     */
    public function weekly(Request $request)
    {
        $weekAgo = now()->subWeek();

        $leaderboard = User::where('role', '!=', 'admin')
            ->where('last_xp_earned_at', '>=', $weekAgo)
            ->select('id', 'name', 'level', 'xp', 'last_xp_earned_at')
            ->orderByDesc('xp')
            ->take(100)
            ->get()
            ->map(function ($user, $index) {
                return array_merge($user->toArray(), [
                    'rank' => $index + 1,
                    'progress' => $user->getXpProgress(),
                ]);
            });

        return response()->json([
            'success' => true,
            'data' => $leaderboard,
            'title' => 'Weekly Leaderboard (Last 7 days)',
        ]);
    }

    /**
     * Get leaderboard by course
     *
     * GET /api/leaderboard/course/{courseId}
     */
    public function byCourse(Request $request, $courseId)
    {
        $request->validate([
            'limit' => 'integer|min:1|max:100',
        ]);

        $limit = $request->get('limit', 100);

        // Get users enrolled in this course, sorted by XP
        $leaderboard = User::whereHas('enrollments', function ($query) use ($courseId) {
            $query->where('course_id', $courseId);
        })
            ->where('role', '!=', 'admin')
            ->select('id', 'name', 'level', 'xp')
            ->orderByDesc('xp')
            ->take($limit)
            ->get()
            ->map(function ($user, $index) {
                return array_merge($user->toArray(), [
                    'rank' => $index + 1,
                    'progress' => $user->getXpProgress(),
                ]);
            });

        return response()->json([
            'success' => true,
            'data' => $leaderboard,
            'course_id' => $courseId,
        ]);
    }

    /**
     * Get leaderboard stats summary
     *
     * GET /api/leaderboard/stats
     */
    public function stats(Request $request)
    {
        $topUser = User::where('role', '!=', 'admin')
            ->orderByDesc('xp')
            ->first();

        $stats = [
            'total_users' => User::where('role', '!=', 'admin')->count(),
            'total_instructors' => User::where('role', 'instructor')->count(),
            'top_user' => [
                'name' => $topUser?->name,
                'level' => $topUser?->level,
                'xp' => $topUser?->xp,
            ],
            'average_level' => User::where('role', '!=', 'admin')->avg('level'),
            'average_xp' => User::where('role', '!=', 'admin')->avg('xp'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get leaderboard dengan filter by level
     *
     * GET /api/leaderboard/by-level/{level}
     */
    public function filterByLevel(Request $request, $level)
    {
        $request->validate([
            'limit' => 'integer|min:1|max:100',
        ]);

        $limit = $request->get('limit', 100);

        $leaderboard = User::where('role', '!=', 'admin')
            ->where('level', $level)
            ->select('id', 'name', 'level', 'xp')
            ->orderByDesc('xp')
            ->take($limit)
            ->get()
            ->map(function ($user, $index) {
                return array_merge($user->toArray(), [
                    'rank' => $index + 1,
                ]);
            });

        return response()->json([
            'success' => true,
            'data' => $leaderboard,
            'level' => $level,
            'count' => count($leaderboard),
        ]);
    }
}
