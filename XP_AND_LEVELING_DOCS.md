# 🎮 XP dan Leveling System Documentation

## Overview

Sistem XP dan leveling otomatis untuk MOOC platform yang menggunakan trait pada User model. Sistem ini menyediakan:

- ✅ **Otomatis XP Management** - Menambah, track, dan calculate level
- ✅ **Leveling System** - Automatic level calculation berdasarkan XP
- ✅ **Progress Tracking** - Track progress ke level berikutnya
- ✅ **Ranking System** - User ranking berdasarkan XP dan level
- ✅ **XP Logging** - History lengkap dari setiap XP transaction
- ✅ **Reward Service** - Pre-defined rewards untuk berbagai actions
- ✅ **Analytics** - Detailed analytics tentang XP earning

---

## 📊 Implementasi

### Files Created

1. **Trait: `app/Traits/HasXpAndLeveling.php`**
    - Core leveling logic
    - ~350 lines dengan dokumentasi lengkap

2. **Model: `app/Models/UserXpLog.php`**
    - Log setiap XP transaction
    - Relationship ke User

3. **Service: `app/Services/XpRewardService.php`**
    - Pre-defined rewards
    - Award methods untuk berbagai actions
    - Leaderboard dan analytics
    - ~400 lines

4. **Migrations:**
    - `2026_03_13_063208_add_xp_and_level_to_users.php` - Add columns ke users
    - `2026_03_13_063246_create_user_xp_logs_table.php` - Create XP log table

5. **Updated:**
    - `app/Models/User.php` - Add trait, add relationship

---

## 🗄️ Database Schema

### Users Table (Columns Added)

```
users
├── xp (bigInteger, default: 0)
│   └── Total experience points
├── level (integer, default: 1)
│   └── Current level (1-100)
├── next_level_xp (integer, default: 100)
│   └── XP required untuk level berikutnya
└── last_xp_earned_at (timestamp, nullable)
    └── Terakhir kali user dapat XP
```

### UserXpLogs Table (New)

```
user_xp_logs
├── id (PK)
├── user_id (FK → users)
├── amount (integer) - XP yang diterima
├── source (string) - Sumber XP: module_completed, quiz_passed, etc
├── previous_xp (bigInteger) - XP sebelum
├── current_xp (bigInteger) - XP sesudah
├── previous_level (integer)
├── current_level (integer)
├── leveled_up (boolean) - Apakah user naik level
├── metadata (json) - Custom data
└── timestamps
```

---

## 🚀 Quick Start

### 1. Run Migrations

```bash
php artisan migrate
```

Ini akan:

- Menambah kolom ke `users` table
- Membuat `user_xp_logs` table

### 2. Menggunakan Trait

Trait sudah otomatis integrated ke User model. Sekarang Anda bisa langsung gunakan:

```php
$user = User::find(1);

// Tambah XP
$result = $user->addXp(100, 'module_completed', ['module_id' => 5]);

// Check status
echo $user->xp;                  // Current XP
echo $user->level;               // Current level
echo $user->next_level_xp;       // XP untuk next level
echo $user->getXpProgress();     // Progress % (0-100)
```

### 3. Menggunakan XpRewardService

```php
use App\Services\XpRewardService;

$rewardService = app(XpRewardService::class);
$user = User::find(1);

// Award XP untuk modul selesai
$rewardService->awardModuleCompletion($user, $moduleId);

// Award XP untuk quiz passed
$rewardService->awardQuizPassed($user, $quizId, $score);

// Get leaderboard
$leaderboard = $rewardService->getLeaderboard(100);

// Get user analytics
$analytics = $rewardService->getUserXpAnalytics($user);
```

---

## 📖 API Reference

### Trait Methods

#### Adding XP

**`addXp(int $amount, string $source, array $metadata): array`**

Menambah XP ke user.

```php
$result = $user->addXp(100, 'module_completed', [
    'module_id' => 5,
    'completion_time' => 120 // seconds
]);

// Result array:
// [
//     'previous_xp' => 0,
//     'current_xp' => 100,
//     'previous_level' => 1,
//     'current_level' => 2,
//     'leveled_up' => true,
//     'next_level_xp' => 210,
//     'xp_progress' => 0.0
// ]
```

**`addMultipleXp(array $xpArray): array`**

Menambah XP dari multiple sources sekaligus.

```php
$results = $user->addMultipleXp([
    'module_completed' => 100,
    'quiz_passed' => 50,
    'discussion_post' => 10
]);
```

#### Getting XP Information

**`getXpSummary(): array`**

Dapatkan summary lengkap tentang XP user.

```php
$summary = $user->getXpSummary();
// [
//     'current_xp' => 250,
//     'current_level' => 3,
//     'next_level_xp' => 331,
//     'xp_until_next_level' => 81,
//     'xp_progress_percentage' => 33.06,
//     'total_xp_in_current_level' => 40,
//     'is_max_level' => false,
//     'rank' => 5,
//     'rank_percentage' => 0.5,
//     'last_xp_earned_at' => '2026-03-13T10:30:00Z'
// ]
```

**`getXpProgress(): float`**

Dapatkan progress % ke level berikutnya (0-100).

```php
$progress = $user->getXpProgress(); // 45.5
```

**`getXpUntilNextLevel(): int`**

Dapatkan berapa XP lagi untuk naik level.

```php
$remaining = $user->getXpUntilNextLevel(); // 50
```

**`getXpInCurrentLevel(): int`**

Dapatkan XP yang sudah dikumpulkan di level saat ini.

```php
$xpInLevel = $user->getXpInCurrentLevel(); // 150
```

**`getRank(): array`**

Dapatkan ranking user berdasarkan XP.

```php
$rank = $user->getRank();
// [
//     'rank' => 5,               // Rank ke-5
//     'total_users' => 1000,     // Total users
//     'percentage' => 0.5        // Top 0.5%
// ]
```

#### Level Information

**`getXpRequiredForLevel(int $level): int`**

Dapatkan XP yang dibutuhkan untuk mencapai level tertentu.

```php
$xp = $user->getXpRequiredForLevel(5); // 464 XP
```

**`getTotalXpForCurrentLevel(): int`**

Dapatkan total XP yang dibutuhkan untuk level saat ini.

```php
$total = $user->getTotalXpForCurrentLevel(); // 210
```

**`isMaxLevel(): bool`**

Check apakah user sudah max level.

```php
if ($user->isMaxLevel()) {
    echo "User has reached max level!";
}
```

#### Admin Methods

**`setXp(int $amount): array`**

Set XP langsung (untuk admin).

```php
$user->setXp(5000);
```

**`resetXpAndLevel(): void`**

Reset XP dan level ke awal (untuk admin).

```php
$user->resetXpAndLevel();
```

---

### Service Methods

#### XpRewardService

**`awardModuleCompletion(User $user, int $moduleId, array $metadata): array`**

```php
$rewardService->awardModuleCompletion($user, $moduleId, [
    'score' => 95,
    'time_taken' => 3600
]);
```

**`awardQuizPassed(User $user, int $quizId, float $score, array $metadata): array`**

```php
// Score 100 = 150 XP (perfect score bonus)
// Score < 100 = 50 XP
$rewardService->awardQuizPassed($user, $quizId, 95);
```

**`awardCourseCompletion(User $user, int $courseId): array`**

```php
$rewardService->awardCourseCompletion($user, $courseId);
```

**`awardStreak(User $user, int $days): array`**

```php
// Award untuk 5-day atau 30-day streaks
$rewardService->awardStreak($user, 5);  // 200 XP
$rewardService->awardStreak($user, 30); // 1000 XP
```

**`awardDiscussionActivity(User $user, int $discussionId, string $type): array`**

```php
$rewardService->awardDiscussionActivity($user, $discussionId, 'post');
$rewardService->awardDiscussionActivity($user, $discussionId, 'helpful_answer');
```

**`awardCustom(User $user, int $amount, string $source, array $metadata): array`**

```php
$rewardService->awardCustom($user, 250, 'custom_event', [
    'reason' => 'Helped another student'
]);
```

#### Leaderboard & Rankings

**`getTopUsersByXp(int $limit = 10)`**

```php
$topUsers = $rewardService->getTopUsersByXp(10);
```

**`getTopUsersByLevel(int $limit = 10)`**

```php
$topUsers = $rewardService->getTopUsersByLevel(10);
```

**`getLeaderboard(int $limit = 100)`**

```php
$leaderboard = $rewardService->getLeaderboard(100);
// Includes 'rank' property dengan rank urutan
```

**`getUserRank(User $user): int`**

```php
$rank = $rewardService->getUserRank($user); // User rank di leaderboard
```

#### Analytics

**`getUserXpAnalytics(User $user): array`**

```php
$analytics = $rewardService->getUserXpAnalytics($user);
// [
//     'total_xp_earned' => 2500,
//     'current_xp' => 2500,
//     'current_level' => 5,
//     'average_xp_per_day' => 83.33,
//     'most_common_source' => 'module_completed',
//     'level_up_count' => 4,
//     'recent_level_up' => ['level' => 5, 'date' => '...'],
//     'rank' => 15
// ]
```

---

## 💡 Usage Examples

### Example 1: Award XP Ketika Module Selesai

```php
// Di ModuleController.php

public function complete(Request $request, Course $course, Module $module)
{
    $user = auth()->user();

    // ... existing logic ...

    // Award XP
    $rewardService = app(XpRewardService::class);
    $xpResult = $rewardService->awardModuleCompletion($user, $module->id);

    return response()->json([
        'message' => 'Module completed!',
        'xp_awarded' => $xpResult,
        'user_summary' => $user->getXpSummary()
    ]);
}
```

### Example 2: Display User Level & Progress

```php
// Di Blade template

<div class="user-level-card">
    <h3>Level {{ $user->level }}</h3>

    <div class="level-progress">
        <progress
            value="{{ $user->getXpProgress() }}"
            max="100">
        </progress>
        <span>{{ $user->getXpProgress() }}%</span>
    </div>

    <p>
        {{ $user->getXpInCurrentLevel() }} /
        {{ $user->next_level_xp - $user->getTotalXpForCurrentLevel() }} XP
    </p>

    <p>{{ $user->getXpUntilNextLevel() }} XP until next level</p>
</div>
```

### Example 3: Display Leaderboard

```php
// Di Controller

public function leaderboard()
{
    $rewardService = app(XpRewardService::class);
    $leaderboard = $rewardService->getLeaderboard(100);

    return view('leaderboard', [
        'leaderboard' => $leaderboard,
        'userRank' => $rewardService->getUserRank(auth()->user())
    ]);
}

// Di Blade
@foreach($leaderboard as $entry)
    <tr>
        <td>#{{ $entry->rank }}</td>
        <td>{{ $entry->name }}</td>
        <td>Level {{ $entry->level }}</td>
        <td>{{ $entry->xp }} XP</td>
    </tr>
@endforeach
```

### Example 4: Level Up Notification

```php
// Event Listener atau di Observer

public function created($module)
{
    // When module is created & completed by user
    $user->addXp(100, 'module_completed', ['module_id' => $module->id]);

    // Get updated summary
    $summary = $user->getXpSummary();

    if ($summary['leveled_up'] ?? false) {
        // Send notification
        \Notification::send($user, new LevelUpNotification($summary['current_level']));
    }
}
```

---

## ⚙️ Configuration

### Customize XP Requirements

Edit constants di `HasXpAndLeveling` trait:

```php
const BASE_XP_FOR_LEVEL = 100;      // Base XP untuk level 1
const XP_MULTIPLIER = 1.1;          // Multiplier untuk setiap level
const MAX_LEVEL = 100;              // Maximum level
```

**Formula untuk XP required:**

```
XP_required = BASE_XP * (MULTIPLIER ^ (level - 1))
```

Level 1: 0 XP
Level 2: 100 XP
Level 3: 110 XP
Level 4: 121 XP
Level 5: 133 XP
...

### Customize Rewards

Edit `REWARDS` di `XpRewardService`:

```php
const REWARDS = [
    'module_completed' => 100,
    'quiz_passed' => 50,
    'course_completed' => 500,
    // ... add more
];
```

---

## 🧪 Testing

```bash
# Test XP system
php artisan test tests/Feature/XpAndLevelingTest.php
```

Example test:

```php
public function test_user_gains_xp()
{
    $user = User::factory()->create();
    $result = $user->addXp(100, 'test');

    $this->assertEqual($user->xp, 100);
    $this->assertTrue($result['leveled_up'] === false);
}

public function test_user_levels_up()
{
    $user = User::factory()->create();
    $user->addXp(100, 'test'); // Level 1 -> 2

    $this->assertEqual($user->level, 2);
}
```

---

## 📊 Analytics & Reports

### Get User XP Stats

```php
$user = User::find(1);
$stats = $user->getXpSummary();

echo $stats['current_level'];      // 5
echo $stats['xp_progress_percentage']; // 45.5%
echo $stats['rank'];               // 10 (top 10 users)
```

### Get XP History

```php
$logs = $user->xpLogs()
    ->orderByDesc('created_at')
    ->take(20)
    ->get();

// Each log includes:
// - amount, source, previous_xp, current_xp
// - previous_level, current_level, leveled_up
// - metadata (custom data)
```

### Batch Analytics

```php
$rewardService = app(XpRewardService::class);

// Top performers
$topUsers = $rewardService->getTopUsersByXp(10);

// Leaderboard dengan ranking
$leaderboard = $rewardService->getLeaderboard(100);

// User analytics
$userStats = $rewardService->getUserXpAnalytics($user);
```

---

## 🔧 Integration Points

### Integrate dengan ModuleController

```php
use App\Services\XpRewardService;

class ModuleController extends Controller
{
    public function __construct(private XpRewardService $rewardService)
    {}

    public function complete(Course $course, Module $module)
    {
        // ... complete logic ...

        // Award XP
        $this->rewardService->awardModuleCompletion(
            auth()->user(),
            $module->id
        );
    }
}
```

### Integrate dengan Quiz System

```php
public function submitQuiz(Quiz $quiz, Request $request)
{
    $score = calculateScore($request->answers);

    // Award XP based on score
    $rewardService = app(XpRewardService::class);
    $rewardService->awardQuizPassed(auth()->user(), $quiz->id, $score);
}
```

---

## 📈 Summary

| Feature           | Capability                               |
| ----------------- | ---------------------------------------- |
| **XP Management** | Add, track, log XP automatically         |
| **Leveling**      | Auto-calculate level berdasarkan XP      |
| **Ranking**       | Global & per-user ranking                |
| **History**       | Complete log dari setiap XP transaction  |
| **Analytics**     | Detailed stats dan trends                |
| **Customization** | Easy to configure rewards & requirements |
| **Scalability**   | Support hingga 100 levels                |
| **Admin Tools**   | Reset, set XP for users                  |

---

## 🎯 Next Steps

1. Run migrations: `php artisan migrate`
2. Integrate into ModuleController
3. Create leaderboard views
4. Setup notifications untuk level up
5. Create analytics dashboard

---

## 📞 Support

Lihat file-file implementasi untuk lebih detail:

- `app/Traits/HasXpAndLeveling.php` - Core logic
- `app/Services/XpRewardService.php` - Rewards & analytics
- `app/Models/UserXpLog.php` - Log model
