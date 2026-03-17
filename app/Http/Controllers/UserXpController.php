<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\XpRewardService;
use Illuminate\Http\Request;

class UserXpController extends Controller
{
    protected XpRewardService $rewardService;

    public function __construct(XpRewardService $rewardService)
    {
        $this->rewardService = $rewardService;
        $this->middleware('auth');
    }

    /**
     * Get XP summary untuk authenticated user
     *
     * GET /api/user/xp-summary
     */
    public function getSummary(Request $request)
    {
        $user = auth()->user();
        
        return response()->json([
            'success' => true,
            'data' => $user->getXpSummary()
        ]);
    }

    /**
     * Get XP history/logs untuk user
     *
     * GET /api/user/xp-logs
     */
    public function getHistory(Request $request)
    {
        $request->validate([
            'limit' => 'integer|min:1|max:100',
            'offset' => 'integer|min:0',
        ]);

        $user = auth()->user();
        $limit = $request->get('limit', 50);
        $offset = $request->get('offset', 0);

        $logs = $user->xpLogs()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->offset($offset)
            ->get();

        $total = $user->xpLogs()->count();

        return response()->json([
            'success' => true,
            'data' => $logs,
            'pagination' => [
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
            ]
        ]);
    }

    /**
     * Get user XP analytics
     *
     * GET /api/user/xp-analytics
     */
    public function getAnalytics(Request $request)
    {
        $user = auth()->user();
        $analytics = $this->rewardService->getUserXpAnalytics($user);

        return response()->json([
            'success' => true,
            'data' => $analytics
        ]);
    }

    /**
     * Award XP ke user (admin/instructor only)
     *
     * POST /api/user/{userId}/award-xp
     */
    public function awardXp(Request $request, User $user)
    {
        // Check authorization (instructor/admin)
        if (auth()->user()->role === 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk memberikan XP'
            ], 403);
        }

        $request->validate([
            'amount' => 'required|integer|min:1',
            'source' => 'required|string|max:100',
            'metadata' => 'array',
        ]);

        $result = $user->addXp(
            $request->get('amount'),
            $request->get('source'),
            $request->get('metadata', [])
        );

        return response()->json([
            'success' => true,
            'message' => 'XP berhasil diberikan',
            'data' => $result,
            'user_summary' => $user->fresh()->getXpSummary()
        ]);
    }

    /**
     * Get user rank
     *
     * GET /api/user/rank
     */
    public function getRank(Request $request)
    {
        $user = auth()->user();
        $rank = $this->rewardService->getUserRank($user);

        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $user->id,
                'name' => $user->name,
                'level' => $user->level,
                'xp' => $user->xp,
                'rank' => $rank,
            ]
        ]);
    }

    /**
     * Get user XP by user ID
     *
     * GET /api/users/{userId}/xp
     */
    public function getUserXp(User $user)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $user->id,
                'name' => $user->name,
                'level' => $user->level,
                'xp' => $user->xp,
                'next_level_xp' => $user->next_level_xp,
                'progress' => $user->getXpProgress(),
            ]
        ]);
    }
}
