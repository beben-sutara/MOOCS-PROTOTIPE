<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\XpRewardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeaderboardWebController extends Controller
{
    protected XpRewardService $rewardService;

    public function __construct(XpRewardService $rewardService)
    {
        $this->rewardService = $rewardService;
    }

    /**
     * Show leaderboard page
     */
    public function index()
    {
        // Get leaderboards
        $xpLeaderboard = $this->rewardService->getLeaderboard(100);
        $levelLeaderboard = $this->rewardService->getTopUsersByLevel(100);

        // Weekly leaderboard: users with XP logs in last 7 days
        $weeklyLeaderboard = User::whereHas('xpLogs', function ($query) {
            $query->where('created_at', '>=', now()->subDays(7));
        })
            ->select([
                'id',
                'name',
                'email',
                'level',
                'xp'
            ])
            ->withSum([
                'xpLogs' => function ($query) {
                    $query->where('created_at', '>=', now()->subDays(7));
                }
            ], 'amount')
            ->orderByDesc('xp_logs_sum_amount')
            ->take(100)
            ->get()
            ->map(function ($user, $index) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'level' => $user->level,
                    'xp' => $user->xp,
                    'weekly_xp' => $user->xp_logs_sum_amount ?? 0,
                    'rank' => $index + 1,
                ];
            })
            ->toArray();

        $userRank = null;
        if (Auth::check()) {
            $userRank = Auth::user()->getRank();
        }

        return view('leaderboard', [
            'xpLeaderboard' => $xpLeaderboard,
            'levelLeaderboard' => $levelLeaderboard,
            'weeklyLeaderboard' => $weeklyLeaderboard,
            'userRank' => $userRank,
        ]);
    }
}
