<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserXpLog;
use App\Services\XpRewardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XpAndLevelingTest extends TestCase
{
    use RefreshDatabase;

    protected XpRewardService $rewardService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rewardService = app(XpRewardService::class);
    }

    // =========================================================================
    // Test Cases for Basic XP System
    // =========================================================================

    public function test_user_starts_with_zero_xp_and_level_one()
    {
        $user = User::factory()->create();

        $this->assertEquals(0, $user->xp);
        $this->assertEquals(1, $user->level);
    }

    public function test_user_can_add_xp()
    {
        $user = User::factory()->create();

        $result = $user->addXp(100, 'test');

        $this->assertEquals(100, $user->fresh()->xp);
        $this->assertArrayHasKey('current_xp', $result);
        $this->assertEquals(100, $result['current_xp']);
    }

    public function test_xp_transaction_is_logged()
    {
        $user = User::factory()->create();

        $user->addXp(100, 'module_completed', ['module_id' => 5]);

        $log = UserXpLog::where('user_id', $user->id)->first();

        $this->assertNotNull($log);
        $this->assertEquals(100, $log->amount);
        $this->assertEquals('module_completed', $log->source);
        $this->assertEquals(5, $log->metadata['module_id']);
    }

    public function test_user_can_add_multiple_xp_from_different_sources()
    {
        $user = User::factory()->create();

        $user->addMultipleXp([
            'module_completed' => 100,
            'quiz_passed' => 50,
            'discussion_post' => 10,
        ]);

        $this->assertEquals(160, $user->fresh()->xp);
    }

    // =========================================================================
    // Test Cases for Leveling System
    // =========================================================================

    public function test_user_levels_up_when_reaching_required_xp()
    {
        $user = User::factory()->create();

        $user->addXp(100, 'test'); // Level 1 requires 0 XP, level 2 requires 100 XP

        $user->refresh();
        $this->assertEquals(2, $user->level);
    }

    public function test_user_can_level_up_multiple_times()
    {
        $user = User::factory()->create();

        // Get total XP needed for level 5
        $totalXpForLevel5 = $user->getXpRequiredForLevel(5);

        $user->addXp($totalXpForLevel5, 'test');

        $user->refresh();
        $this->assertGreaterThanOrEqual(5, $user->level);
    }

    public function test_leveled_up_flag_is_set_correctly()
    {
        $user = User::factory()->create();

        $result = $user->addXp(100, 'test');

        $this->assertTrue($result['leveled_up']);

        // Second XP add without leveling up
        $result2 = $user->addXp(5, 'test');
        $this->assertFalse($result2['leveled_up']);
    }

    public function test_next_level_xp_is_calculated_correctly()
    {
        $user = User::factory()->create();

        $user->addXp(100, 'test'); // Reach level 2

        $expectedXpForLevel3 = $user->getXpRequiredForLevel(3);

        $this->assertEquals($expectedXpForLevel3, $user->fresh()->next_level_xp);
    }

    // =========================================================================
    // Test Cases for XP Progress Calculation
    // =========================================================================

    public function test_xp_progress_is_calculated_correctly()
    {
        $user = User::factory()->create();

        $user->addXp(100, 'test'); // Reach level 2, start at 0 XP in level 2

        // Progress should be 0% since we just leveled up
        $progress = $user->getXpProgress();

        $this->assertEquals(0.0, $progress);
    }

    public function test_xp_progress_increases_correctly()
    {
        $user = User::factory()->create();

        $user->addXp(100, 'test');    // Reach level 2
        $user->addXp(55, 'test');     // 50% progress toward level 3

        $progress = $user->fresh()->getXpProgress();

        $this->assertGreaterThan(0, $progress);
        $this->assertLessThan(100, $progress);
    }

    public function test_xp_until_next_level_calculation()
    {
        $user = User::factory()->create();

        $user->addXp(100, 'test');

        $remaining = $user->fresh()->getXpUntilNextLevel();

        $this->assertGreaterThan(0, $remaining);
        $this->assertEquals($user->next_level_xp - $user->xp, $remaining);
    }

    public function test_get_xp_in_current_level()
    {
        $user = User::factory()->create();

        $user->addXp(100, 'test');  // Level 2, 0 XP in level
        $user->addXp(50, 'test');   // 50 XP in level 2

        $xpInLevel = $user->fresh()->getXpInCurrentLevel();

        $this->assertEquals(50, $xpInLevel);
    }

    // =========================================================================
    // Test Cases for Level Information
    // =========================================================================

    public function test_get_xp_required_for_level()
    {
        $user = User::factory()->create();

        $xpForLevel1 = $user->getXpRequiredForLevel(1);
        $xpForLevel2 = $user->getXpRequiredForLevel(2);
        $xpForLevel3 = $user->getXpRequiredForLevel(3);

        $this->assertEquals(0, $xpForLevel1);
        $this->assertEquals(100, $xpForLevel2);
        $this->assertGreaterThan($xpForLevel2, $xpForLevel3);
    }

    public function test_is_max_level()
    {
        $user = User::factory()->create();

        $this->assertFalse($user->isMaxLevel());

        // Set user to max level
        $user->update(['level' => 100]);

        $this->assertTrue($user->fresh()->isMaxLevel());
    }

    // =========================================================================
    // Test Cases for Ranking System
    // =========================================================================

    public function test_user_ranking_calculation()
    {
        User::factory()->create(['xp' => 5000]);
        User::factory()->create(['xp' => 3000]);
        User::factory()->create(['xp' => 1000]);

        $topUser = User::query()->orderByDesc('xp')->first();
        $rank = $topUser->getRank();

        $this->assertEquals(1, $rank['rank']);
        $this->assertEquals(3, $rank['total_users']);
    }

    // =========================================================================
    // Test Cases for XpRewardService
    // =========================================================================

    public function test_award_module_completion()
    {
        $user = User::factory()->create();
        $moduleId = 5;

        $result = $this->rewardService->awardModuleCompletion($user, $moduleId);

        $this->assertEquals(100, $result['current_xp']);
        $this->assertNotNull(UserXpLog::where('user_id', $user->id)
            ->where('source', 'module_completed')->first());
    }

    public function test_award_quiz_passed_with_perfect_score()
    {
        $user = User::factory()->create();
        $quizId = 3;

        $result = $this->rewardService->awardQuizPassed($user, $quizId, 100);

        // Perfect score should award 150 XP
        $this->assertEquals(150, $result['current_xp']);
    }

    public function test_award_quiz_passed_with_normal_score()
    {
        $user = User::factory()->create();
        $quizId = 3;

        $result = $this->rewardService->awardQuizPassed($user, $quizId, 75);

        // Normal score should award 50 XP
        $this->assertEquals(50, $result['current_xp']);
    }

    public function test_award_course_completion()
    {
        $user = User::factory()->create();
        $courseId = 1;

        $result = $this->rewardService->awardCourseCompletion($user, $courseId);

        // Course completion is 500 XP
        $this->assertEquals(500, $result['current_xp']);
    }

    public function test_award_streak()
    {
        $user = User::factory()->create();

        $result5Day = $this->rewardService->awardStreak($user, 5);
        $this->assertEquals(200, $result5Day['current_xp']);

        $result30Day = $this->rewardService->awardStreak($user, 30);
        $this->assertEquals(1200, $result30Day['current_xp']);
    }

    public function test_get_leaderboard()
    {
        User::factory()->create(['xp' => 5000, 'level' => 10]);
        User::factory()->create(['xp' => 3000, 'level' => 7]);
        User::factory()->create(['xp' => 1000, 'level' => 3]);

        $leaderboard = $this->rewardService->getLeaderboard(10);

        $this->assertCount(3, $leaderboard);
        $this->assertEquals(1, $leaderboard[0]->rank);
        $this->assertEquals(2, $leaderboard[1]->rank);
        $this->assertEquals(3, $leaderboard[2]->rank);
    }

    public function test_get_top_users_by_xp()
    {
        User::factory()->create(['xp' => 5000]);
        User::factory()->create(['xp' => 3000]);
        User::factory()->create(['xp' => 1000]);

        $topUsers = $this->rewardService->getTopUsersByXp(2);

        $this->assertCount(2, $topUsers);
        $this->assertEquals(5000, $topUsers[0]->xp);
        $this->assertEquals(3000, $topUsers[1]->xp);
    }

    public function test_get_user_rank()
    {
        User::factory()->create(['xp' => 5000]);
        User::factory()->create(['xp' => 3000]);
        $userToRank = User::factory()->create(['xp' => 1000]);

        $rank = $this->rewardService->getUserRank($userToRank);

        $this->assertEquals(3, $rank);
    }

    // =========================================================================
    // Test Cases for Admin Functions
    // =========================================================================

    public function test_reset_xp_and_level()
    {
        $user = User::factory()->create(['xp' => 500, 'level' => 5]);

        $user->resetXpAndLevel();

        $user->refresh();
        $this->assertEquals(0, $user->xp);
        $this->assertEquals(1, $user->level);
    }

    public function test_set_xp_directly()
    {
        $user = User::factory()->create();

        $user->setXp(5000);

        $this->assertEquals(5000, $user->fresh()->xp);
        $this->assertGreaterThan(1, $user->fresh()->level);
    }

    // =========================================================================
    // Test Cases for XP Summary
    // =========================================================================

    public function test_get_xp_summary()
    {
        $user = User::factory()->create();
        $user->addXp(100, 'test');

        $summary = $user->fresh()->getXpSummary();

        $this->assertArrayHasKey('current_xp', $summary);
        $this->assertArrayHasKey('current_level', $summary);
        $this->assertArrayHasKey('next_level_xp', $summary);
        $this->assertArrayHasKey('xp_progress_percentage', $summary);
        $this->assertArrayHasKey('rank', $summary);
        $this->assertEquals(100, $summary['current_xp']);
    }

    // =========================================================================
    // Test Cases for Analytics
    // =========================================================================

    public function test_get_user_xp_analytics()
    {
        $user = User::factory()->create();
        
        $user->addXp(100, 'module_completed');
        $user->addXp(50, 'quiz_passed');
        $user->addXp(25, 'discussion_post');

        $analytics = $this->rewardService->getUserXpAnalytics($user);

        $this->assertEquals(175, $analytics['total_xp_earned']);
        $this->assertEquals(2, $analytics['current_level']);
        $this->assertGreaterThan(0, $analytics['average_xp_per_day']);
        $this->assertNotNull($analytics['most_common_source']);
    }

    public function test_bulk_award_xp()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $results = $this->rewardService->bulkAwardXp(
            [$user1->id, $user2->id, $user3->id],
            100,
            'bulk_award'
        );

        $this->assertCount(3, $results);
        $this->assertEquals(100, $user1->fresh()->xp);
        $this->assertEquals(100, $user2->fresh()->xp);
        $this->assertEquals(100, $user3->fresh()->xp);
    }
}
