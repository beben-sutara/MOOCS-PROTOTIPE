# 🎮 XP & Leveling System - Quick Start Guide

Panduan cepat untuk mengintegrasikan sistem XP dan leveling otomatis ke aplikasi MOOC Anda.

---

## ⚡ 5-Minute Setup

### Step 1: Run Migrations

```bash
php artisan migrate
```

Ini akan membuat 2 table:

- `users` - tambah kolom: `xp`, `level`, `next_level_xp`, `last_xp_earned_at`
- `user_xp_logs` - track setiap XP transaction

✅ **DONE!** Trait sudah otomatis terintegrasi ke User model.

---

## 📝 Usage Examples

### Example 1: Award XP Ketika Module Completed

Edit file: `app/Http/Controllers/ModuleController.php`

```php
public function complete(Request $request, Course $course, Module $module)
{
    $user = auth()->user();

    // ... existing completion logic ...

    // Award XP! 🎉
    $result = $user->addXp(100, 'module_completed', [
        'module_id' => $module->id,
        'completion_time' => $completionTime
    ]);

    return response()->json([
        'message' => 'Module completed successfully!',
        'xp_awarded' => $result['xp_awarded'] ?? 100,
        'new_level' => $result['current_level'],
        'leveled_up' => $result['leveled_up'],
        'user_summary' => $user->fresh()->getXpSummary()
    ]);
}
```

### Example 2: Display User Level & Progress

**Di Blade template** (e.g., `resources/views/layouts/header.blade.php`):

```blade
<!-- User Level Card -->
<div class="user-level">
    <div class="level-display">
        <span class="level-badge">{{ auth()->user()->level }}</span>
    </div>

    <div class="xp-progress">
        <progress
            value="{{ auth()->user()->getXpProgress() }}"
            max="100"
            class="progress-bar">
        </progress>
        <small>{{ auth()->user()->getXpProgress() }}% ke level {{ auth()->user()->level + 1 }}</small>
    </div>

    <p class="xp-text">
        {{ auth()->user()->getXpInCurrentLevel() }} /
        {{ auth()->user()->next_level_xp - auth()->user()->getTotalXpForCurrentLevel() }} XP
    </p>
</div>
```

### Example 3: Award XP Menggunakan Service

```php
use App\Services\XpRewardService;

class QuizController extends Controller
{
    public function __construct(private XpRewardService $rewardService)
    {}

    public function submitAnswers(Request $request, Quiz $quiz)
    {
        $user = auth()->user();
        $score = calculateScore($request->answers);

        // Award XP based on score
        // Score 100 = 150 XP (perfect bonus)
        // Score < 100 = 50 XP
        $xpResult = $this->rewardService->awardQuizPassed(
            $user,
            $quiz->id,
            $score
        );

        return response()->json([
            'score' => $score,
            'xp_awarded' => $xpResult['current_xp'],
            'user_data' => $user->fresh()->getXpSummary()
        ]);
    }
}
```

### Example 4: Display Leaderboard

**Controller:**

```php
use App\Services\XpRewardService;

class LeaderboardController extends Controller
{
    public function index(XpRewardService $rewardService)
    {
        return view('leaderboard', [
            'leaderboard' => $rewardService->getLeaderboard(100),
            'userRank' => $rewardService->getUserRank(auth()->user()),
            'userStats' => $rewardService->getUserXpAnalytics(auth()->user())
        ]);
    }
}
```

**View** (`resources/views/leaderboard.blade.php`):

```blade
<table class="leaderboard">
    <thead>
        <tr>
            <th>Rank</th>
            <th>User</th>
            <th>Level</th>
            <th>XP</th>
        </tr>
    </thead>
    <tbody>
        @foreach($leaderboard as $entry)
            <tr class="@if($entry->id === auth()->id()) highlight @endif">
                <td><strong>#{{ $entry->rank }}</strong></td>
                <td>{{ $entry->name }}</td>
                <td>
                    <span class="level-badge level-{{ $entry->level }}">
                        {{ $entry->level }}
                    </span>
                </td>
                <td>{{ number_format($entry->xp) }} XP</td>
            </tr>
        @endforeach
    </tbody>
</table>

<!-- User's own stats -->
<div class="user-stats">
    <p>Your Rank: #{{ $userRank }}</p>
    <p>Your Level: {{ $userStats['current_level'] }}</p>
    <p>Your XP: {{ number_format($userStats['current_xp']) }}</p>
</div>
```

---

## 🔌 Integration Checklist

- [ ] Run migrations: `php artisan migrate`
- [ ] Award XP di ModuleController.complete()
- [ ] Award XP di QuizController.submit()
- [ ] Award XP di CourseController.complete() (jika ada)
- [ ] Display user level di header/navigation
- [ ] Create leaderboard view
- [ ] Setup notifications untuk level up
- [ ] (Optional) Create achievement system

---

## 📊 Available Methods

### Get User Stats

```php
$user = auth()->user();

// Get all info dalam satu call
$summary = $user->getXpSummary();

// Or individual methods:
$user->xp                           // Current XP (bigInteger)
$user->level                        // Current level (1-100)
$user->next_level_xp                // XP untuk next level
$user->getXpProgress()              // % progress (0-100)
$user->getXpUntilNextLevel()        // XP lagi sampai level up
$user->getXpInCurrentLevel()        // XP sudah di level ini
$user->getRank()                    // Rank info (rank, total, percentage)
$user->isMaxLevel()                 // Sudah max level?
```

### Award XP

```php
// Simple add
$user->addXp(100, 'module_completed');

// With metadata
$user->addXp(100, 'module_completed', ['module_id' => 5]);

// Multiple sources
$user->addMultipleXp([
    'module_completed' => 100,
    'quiz_passed' => 50
]);

// Using service (pre-configured amounts)
$rewardService = app(XpRewardService::class);
$rewardService->awardModuleCompletion($user, $moduleId);
$rewardService->awardQuizPassed($user, $quizId, $score);
$rewardService->awardCourseCompletion($user, $courseId);
$rewardService->awardStreak($user, 5);  // 5-day atau 30-day
```

### Get Rankings & Leaderboard

```php
$rewardService = app(XpRewardService::class);

// Get leaderboard
$leaderboard = $rewardService->getLeaderboard(100);  // Top 100

// Get top users
$topByXp = $rewardService->getTopUsersByXp(10);
$topByLevel = $rewardService->getTopUsersByLevel(10);

// Get user rank
$rank = $rewardService->getUserRank($user);

// Get analytics
$analytics = $rewardService->getUserXpAnalytics($user);
// Returns: total_xp_earned, avg_xp_per_day, most_common_source, etc
```

---

## 🎯 XP Reward Defaults

| Action                    | XP   |
| ------------------------- | ---- |
| Quiz Passed               | 50   |
| Quiz Perfect Score (100%) | 150  |
| Module Completed          | 100  |
| Course Completed          | 500  |
| 5-Day Streak              | 200  |
| 30-Day Streak             | 1000 |
| Discussion Post           | 10   |
| Helpful Answer            | 25   |
| Achievement               | 100  |

### Customize Rewards

Edit di `app/Services/XpRewardService.php`:

```php
const REWARDS = [
    'module_completed' => 100,      // ← Change this
    'quiz_passed' => 50,            // ← Or this
    // ...
];
```

---

## 🎮 Level Progression

**Formula:**

```
XP_Required_For_Level = cumulative sum of 100 * (1.1 ^ (n - 2)) for n = 2..level
```

| Level | XP Required | Cumulative |
| ----- | ----------- | ---------- |
| 1     | 0           | 0          |
| 2     | 100         | 100        |
| 3     | 110         | 210        |
| 4     | 121         | 331        |
| 5     | 133         | 464        |
| 10    | 214         | 1,357      |
| 20    | 556         | 5,114      |
| 50    | 9,702       | 105,715    |
| 100   | MAX         | 12,526,824 |

**Customize levels:** Edit `HasXpAndLeveling` trait constants

---

## 📈 Real-World Flow

```
1. User completes module
   ↓
2. ModuleController awards 100 XP
   ↓
3. User's XP increases from 250 → 350
   ↓
4. Level remains 4 (threshold level 4 = 331, threshold level 5 = 464)
   ↓
5. Progress shows ~14% toward level 5
   ↓
6. XP log created tracking the transaction
   ↓
7. Response sent with updated summary

---

Later: User completes more activities
   ↓
Total XP reaches 464
   ↓
 Level automatically increases: 4 → 5 ✨
   ↓
 Notification sent: "Level Up! Now Level 5"
   ↓
next_level_xp updated to 610
```

---

## 🔔 Handle Level Up Events

**Option 1: In Controller**

```php
$result = $user->addXp(100, 'module_completed');

if ($result['leveled_up']) {
    // Send notification
    notification()->send($user, new LevelUpNotification($result['current_level']));

    // Log event
    activity()
        ->causedBy($user)
        ->log("Leveled up to {$result['current_level']}");
}
```

**Option 2: Using Model Observer**

```php
// app/Observers/UserObserver.php

public function updated(User $user)
{
    if ($user->wasChanged('level')) {
        // Level changed!
        Notification::send($user, new LevelUpNotification($user->level));
    }
}
```

**Register observer in:**

```php
// app/Providers/AppServiceProvider.php
use App\Models\User;
use App\Observers\UserObserver;

public function boot()
{
    User::observe(UserObserver::class);
}
```

---

## 🧪 Testing

```bash
# Run XP tests
php artisan test tests/Feature/XpAndLevelingTest.php

# Run specific test
php artisan test tests/Feature/XpAndLevelingTest.php --filter=test_user_levels_up

# With coverage
php artisan test tests/Feature/XpAndLevelingTest.php --coverage
```

---

## 📱 Frontend Examples

### Vue.js Component

```vue
<template>
    <div class="user-level-widget">
        <div class="level-number">{{ user.level }}</div>
        <div class="progress-bar">
            <div class="fill" :style="{ width: user.xpProgress + '%' }"></div>
        </div>
        <p class="progress-text">
            {{ userXpInLevel }} / {{ xpNeededForLevel }} XP
        </p>
        <small v-if="!isMaxLevel">
            {{ xpUntilNextLevel }} XP to Level {{ user.level + 1 }}
        </small>
        <small v-else class="max-level">Max Level!</small>
    </div>
</template>

<script>
export default {
    props: ["user"],
    computed: {
        isMaxLevel() {
            return this.user.level >= 100;
        },
        xpProgress() {
            return this.user.xp_progress || 0;
        },
        userXpInLevel() {
            return this.user.xp_in_current_level || 0;
        },
        xpNeededForLevel() {
            return (
                this.user.next_level_xp - this.user.total_xp_for_current_level
            );
        },
        xpUntilNextLevel() {
            return this.user.xp_until_next_level || 0;
        },
    },
};
</script>

<style scoped>
.level-number {
    font-size: 24px;
    font-weight: bold;
}

.progress-bar {
    height: 20px;
    background: #ddd;
    border-radius: 10px;
    overflow: hidden;
}

.fill {
    height: 100%;
    background: linear-gradient(90deg, #4caf50, #8bc34a);
    transition: width 0.3s ease;
}

.progress-text {
    margin: 8px 0 0 0;
    font-size: 14px;
}

.max-level {
    color: #ffd700;
    font-weight: bold;
}
</style>
```

### React Component

```jsx
function UserLevelWidget({ user }) {
    const isMaxLevel = user.level >= 100;

    return (
        <div className="user-level-widget">
            <div className="level-number">{user.level}</div>
            <div className="progress-bar">
                <div
                    className="fill"
                    style={{ width: (user.xp_progress || 0) + "%" }}
                ></div>
            </div>
            <p className="progress-text">
                {user.xp_in_current_level} /{" "}
                {user.next_level_xp - user.total_xp_for_current_level} XP
            </p>
            {!isMaxLevel ? (
                <small>
                    {user.xp_until_next_level} XP to Level {user.level + 1}
                </small>
            ) : (
                <small className="max-level">Max Level!</small>
            )}
        </div>
    );
}
```

---

## 📚 API Endpoints (Example)

```php
// routes/api.php

Route::middleware('auth:sanctum')->group(function () {
    // Get user stats
    Route::get('/user/xp-summary', fn() =>
        response()->json(auth()->user()->getXpSummary())
    );

    // Get leaderboard
    Route::get('/leaderboard', function () {
        $service = app(XpRewardService::class);
        return response()->json($service->getLeaderboard(100));
    });

    // Get user rank
    Route::get('/user/rank', function () {
        $service = app(XpRewardService::class);
        return response()->json([
            'rank' => $service->getUserRank(auth()->user())
        ]);
    });
});
```

---

## ⚙️ Config & Customization

### Change Reward Amounts

```php
// app/Services/XpRewardService.php
const REWARDS = [
    'module_completed' => 150,      // Increased from 100
    'quiz_passed' => 75,            // Increased from 50
    'course_completed' => 1000,     // Doubled
];
```

### Change Level Progression

```php
// app/Traits/HasXpAndLeveling.php
const BASE_XP_FOR_LEVEL = 150;    // Higher base
const XP_MULTIPLIER = 1.15;       // Increases by 15% per level
const MAX_LEVEL = 50;             // Lower max
```

---

## 🚀 What's Next?

1. **Achievements** - Add badge system untuk milestones
2. **Streaks** - Track login/activity streaks
3. **Challenges** - Weekly challenges dengan XP bonuses
4. **Seasons** - Leaderboard resets setiap season
5. **Events** - Double XP event weekends
6. **Missions** - Daily/weekly missions dengan rewards

---

## ✅ Verification

```bash
# Test XP system
php artisan tinker

$user = User::first();
$user->addXp(100, 'test');
echo $user->getXpSummary();  # Should show updates
```

---

## 📞 Documentation

- Detailed: [XP_AND_LEVELING_DOCS.md](XP_AND_LEVELING_DOCS.md)
- Tests: [tests/Feature/XpAndLevelingTest.php](tests/Feature/XpAndLevelingTest.php)
- Implementation: [app/Traits/HasXpAndLeveling.php](app/Traits/HasXpAndLeveling.php)

---

**You're all set! 🎉 Start awarding XP and watch your users level up!**
