<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Service untuk mengelola XP awards dan leveling events
 */
class XpRewardService
{
    /**
     * XP rewards untuk berbagai aksi
     */
    const REWARDS = [
        'module_completed' => 100,
        'quiz_passed' => 50,
        'quiz_perfect_score' => 150,
        'course_completed' => 500,
        'streak_5_days' => 200,
        'streak_30_days' => 1000,
        'discussion_post' => 10,
        'helpful_answer' => 25,
        'achievement_unlocked' => 100,
    ];

    /**
     * Award XP untuk user completion modul
     *
     * @param  User  $user
     * @param  int  $moduleId
     * @param  array  $metadata
     * @return array
     */
    public function awardModuleCompletion(User $user, int $moduleId, array $metadata = []): array
    {
        $xpAmount = self::REWARDS['module_completed'];

        return $user->addXp($xpAmount, 'module_completed', array_merge([
            'module_id' => $moduleId,
        ], $metadata));
    }

    /**
     * Award XP untuk quiz passed
     *
     * @param  User  $user
     * @param  int  $quizId
     * @param  float  $score
     * @param  array  $metadata
     * @return array
     */
    public function awardQuizPassed(User $user, int $quizId, float $score, array $metadata = []): array
    {
        $xpAmount = self::REWARDS['quiz_passed'];

        // Bonus untuk perfect score
        if ($score >= 100) {
            $xpAmount = self::REWARDS['quiz_perfect_score'];
            $source = 'quiz_perfect_score';
        } else {
            $source = 'quiz_passed';
        }

        return $user->addXp($xpAmount, $source, array_merge([
            'quiz_id' => $quizId,
            'score' => $score,
        ], $metadata));
    }

    /**
     * Award XP untuk course completion
     *
     * @param  User  $user
     * @param  int  $courseId
     * @param  array  $metadata
     * @return array
     */
    public function awardCourseCompletion(User $user, int $courseId, array $metadata = []): array
    {
        $xpAmount = self::REWARDS['course_completed'];

        return $user->addXp($xpAmount, 'course_completed', array_merge([
            'course_id' => $courseId,
        ], $metadata));
    }

    /**
     * Award XP untuk streak
     *
     * @param  User  $user
     * @param  int  $days
     * @param  array  $metadata
     * @return array
     */
    public function awardStreak(User $user, int $days, array $metadata = []): array
    {
        if ($days === 5) {
            $xpAmount = self::REWARDS['streak_5_days'];
            $source = 'streak_5_days';
        } elseif ($days === 30) {
            $xpAmount = self::REWARDS['streak_30_days'];
            $source = 'streak_30_days';
        } else {
            return ['message' => 'Streak not recognized'];
        }

        return $user->addXp($xpAmount, $source, array_merge([
            'streak_days' => $days,
        ], $metadata));
    }

    /**
     * Award XP untuk discussion activity
     *
     * @param  User  $user
     * @param  int  $discussionId
     * @param  string  $type  'post', 'answer'
     * @param  array  $metadata
     * @return array
     */
    public function awardDiscussionActivity(
        User $user,
        int $discussionId,
        string $type = 'post',
        array $metadata = []
    ): array {
        $source = match ($type) {
            'post' => 'discussion_post',
            'helpful_answer' => 'helpful_answer',
            default => 'discussion_post',
        };

        $xpAmount = self::REWARDS[$source];

        return $user->addXp($xpAmount, $source, array_merge([
            'discussion_id' => $discussionId,
            'type' => $type,
        ], $metadata));
    }

    /**
     * Award XP untuk achievement
     *
     * @param  User  $user
     * @param  string  $achievementName
     * @param  array  $metadata
     * @return array
     */
    public function awardAchievement(User $user, string $achievementName, array $metadata = []): array
    {
        $xpAmount = self::REWARDS['achievement_unlocked'];

        return $user->addXp($xpAmount, 'achievement_unlocked', array_merge([
            'achievement' => $achievementName,
        ], $metadata));
    }

    /**
     * Custom XP award
     *
     * @param  User  $user
     * @param  int  $amount
     * @param  string  $source
     * @param  array  $metadata
     * @return array
     */
    public function awardCustom(User $user, int $amount, string $source, array $metadata = []): array
    {
        return $user->addXp($amount, $source, $metadata);
    }

    /**
     * Get top users by XP
     *
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTopUsersByXp(int $limit = 10)
    {
        return User::orderByDesc('xp')
            ->limit($limit)
            ->get(['id', 'name', 'email', 'xp', 'level']);
    }

    /**
     * Get top users by level
     *
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTopUsersByLevel(int $limit = 10)
    {
        return User::orderByDesc('level')
            ->orderByDesc('xp')
            ->limit($limit)
            ->get(['id', 'name', 'email', 'xp', 'level']);
    }

    /**
     * Get leaderboard dengan rank
     *
     * @param  int  $limit
     * @return \Illuminate\Support\Collection
     */
    public function getLeaderboard(int $limit = 100)
    {
        return User::orderByDesc('level')
            ->orderByDesc('xp')
            ->limit($limit)
            ->get(['id', 'name', 'xp', 'level'])
            ->values()
            ->map(function ($user, $index) {
                $user->rank = $index + 1;
                return $user;
            });
    }

    /**
     * Get user rank dalam leaderboard
     *
     * @param  User  $user
     * @return int
     */
    public function getUserRank(User $user): int
    {
        return User::where(function ($query) use ($user) {
            $query->where('level', '>', $user->level)
                ->orWhere(function ($q) use ($user) {
                    $q->where('level', $user->level)->where('xp', '>', $user->xp);
                });
        })->count() + 1;
    }

    /**
     * Get XP analytics untuk user
     *
     * @param  User  $user
     * @return array
     */
    public function getUserXpAnalytics(User $user): array
    {
        $totalXpEarned = $user->xpLogs()->sum('amount');
        $averageXpPerDay = $this->getAverageXpPerDay($user);
        $mostCommonSource = $this->getMostCommonXpSource($user);
        $levelUpDates = $this->getLevelUpDates($user);

        return [
            'total_xp_earned' => $totalXpEarned,
            'current_xp' => $user->xp,
            'current_level' => $user->level,
            'average_xp_per_day' => $averageXpPerDay,
            'most_common_source' => $mostCommonSource,
            'level_up_count' => count($levelUpDates),
            'recent_level_up' => $levelUpDates[0] ?? null,
            'rank' => $this->getUserRank($user),
        ];
    }

    /**
     * Get average XP earned per hari
     *
     * @param  User  $user
     * @return float
     */
    private function getAverageXpPerDay(User $user): float
    {
        if (!$user->created_at) {
            return 0;
        }

        $daysActive = $user->created_at->diffInDays(now()) ?: 1;
        $totalXp = $user->xpLogs()->sum('amount');

        return round($totalXp / $daysActive, 2);
    }

    /**
     * Get sumber XP yang paling sering
     *
     * @param  User  $user
     * @return string|null
     */
    private function getMostCommonXpSource(User $user): ?string
    {
        return DB::table('user_xp_logs')
            ->where('user_id', $user->id)
            ->select('source')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('source')
            ->orderByDesc('count')
            ->first()?->source;
    }

    /**
     * Get tanggal-tanggal level up
     *
     * @param  User  $user
     * @return array
     */
    private function getLevelUpDates(User $user): array
    {
        return $user->xpLogs()
            ->where('leveled_up', true)
            ->orderByDesc('created_at')
            ->get(['current_level', 'created_at'])
            ->map(fn($log) => [
                'level' => $log->current_level,
                'date' => $log->created_at,
            ])
            ->toArray();
    }

    /**
     * Bulk award XP ke multiple users
     *
     * @param  array  $userIds
     * @param  int  $amount
     * @param  string  $source
     * @param  array  $metadata
     * @return array
     */
    public function bulkAwardXp(array $userIds, int $amount, string $source, array $metadata = []): array
    {
        $results = [];

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user) {
                $results[$userId] = $user->addXp($amount, $source, $metadata);
            }
        }

        return $results;
    }
}
