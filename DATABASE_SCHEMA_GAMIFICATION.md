# 🗄️ Database Schema & Implementasi Gamifikasi MOOC Platform

Dokumentasi lengkap database schema dan implementasi sistem gamifikasi untuk MOOC platform.

---

## 📊 ER Diagram (Entity Relationship)

```
┌─────────────────────────────────────────────────────────────────────┐
│                         MOOC PLATFORM ER DIAGRAM                     │
└─────────────────────────────────────────────────────────────────────┘

                              ┌──────────────┐
                              │    users     │
                              ├──────────────┤
                              │ id (PK)      │
                              │ name         │
                              │ email        │
                              │ phone        │
                              │ password     │
                              │ role         │
                              │ xp           │◄────────────┐ XP & Leveling
                              │ level        │             │
                              │ next_level_xp│             │
                              │ last_xp_...  │             │
                              │ timestamps   │             │
                              └──────┬───────┘             │
                                     │                     │
                        ┌────────────┼────────────┐       │
                        │            │            │       │
                        ▼            ▼            ▼       ▼
                    ┌────────┐  ┌──────────┐  ┌────────────────┐
                    │courses │  │enroll... │  │ user_xp_logs   │
                    ├────────┤  ├──────────┤  ├────────────────┤
                    │ id (PK)│  │ id (PK)  │  │ id (PK)        │
                    │ title  │  │ user_id  │  │ user_id (FK)   │
                    │ desc.. │  │ course_id│  │ amount         │
                    │ instr..│  │ status   │  │ source         │
                    │ status │  │ timestamps   │ prev_xp        │
                    │ times..│  └──────────┘  │ curr_xp        │
                    └────┬───┘                │ prev_level     │
                         │                    │ curr_level     │
                         │                    │ leveled_up     │
                         ▼                    │ metadata       │
                    ┌──────────┐              │ timestamps     │
                    │ modules  │              └────────────────┘
                    ├──────────┤
                    │ id (PK)  │
                    │ course_id│
                    │ title    │
                    │ content  │
                    │ order    │
                    │ is_locked│
                    │ prereq.. │ (self-referencing)
                    │ times..  │
                    └────┬─────┘
                         │
                         ▼
                    ┌─────────────┐
                    │mod_progress │
                    ├─────────────┤
                    │ id (PK)     │
                    │ user_id (FK)│
                    │ module_id..(FK)
                    │ is_viewed   │
                    │ is_completed│
                    │ timestamps  │
                    └─────────────┘
```

---

## 🗂️ Database Tables Detail

### 1. users

**Purpose:** Core user data dengan XP & leveling columns

| Column                | Type                              | Nullable | Index     | Description                  |
| --------------------- | --------------------------------- | -------- | --------- | ---------------------------- |
| id                    | BIGINT UNSIGNED                   | NO       | PK        | User ID                      |
| name                  | VARCHAR(255)                      | NO       |           | User name                    |
| email                 | VARCHAR(255)                      | NO       | UNIQUE    | Email address                |
| phone                 | VARCHAR(20)                       | YES      |           | Phone number                 |
| email_verified_at     | TIMESTAMP                         | YES      |           | Email verification           |
| password              | VARCHAR(255)                      | NO       |           | Hashed password              |
| role                  | ENUM('user','instructor','admin') | NO       | INDEX     | User role                    |
| **xp**                | **BIGINT UNSIGNED**               | NO       | **INDEX** | **Total XP earned**          |
| **level**             | **INTEGER UNSIGNED**              | NO       | **INDEX** | **Current level (1-100)**    |
| **next_level_xp**     | **INTEGER UNSIGNED**              | NO       |           | **XP needed for next level** |
| **last_xp_earned_at** | **TIMESTAMP**                     | YES      |           | **Last XP award time**       |
| remember_token        | VARCHAR(100)                      | YES      |           | Remember-me token            |
| created_at            | TIMESTAMP                         | YES      | INDEX     | Created date                 |
| updated_at            | TIMESTAMP                         | YES      |           | Updated date                 |

**Indexes:**

```sql
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_xp ON users(xp DESC);
CREATE INDEX idx_users_level ON users(level DESC);
CREATE INDEX idx_users_created_at ON users(created_at);
```

**Key Notes:**

- `xp` adalah cumulative total XP
- `level` auto-calculated dari `xp` menggunakan formula
- `next_level_xp` diupdate saat level up
- Indexed untuk leaderboard queries

---

### 2. user_xp_logs

**Purpose:** Audit trail dan history dari setiap XP transaction

| Column         | Type             | Nullable | Index    | Description                                |
| -------------- | ---------------- | -------- | -------- | ------------------------------------------ |
| id             | BIGINT UNSIGNED  | NO       | PK       | Log ID                                     |
| user_id        | BIGINT UNSIGNED  | NO       | FK+INDEX | Reference ke users                         |
| amount         | SMALLINT         | NO       |          | XP amount (+/-)                            |
| source         | VARCHAR(100)     | NO       | INDEX    | Source: module_completed, quiz_passed, etc |
| previous_xp    | BIGINT UNSIGNED  | NO       |          | XP sebelum transaction                     |
| current_xp     | BIGINT UNSIGNED  | NO       |          | XP sesudah transaction                     |
| previous_level | INTEGER UNSIGNED | NO       |          | Level sebelum                              |
| current_level  | INTEGER UNSIGNED | NO       |          | Level sesudah                              |
| leveled_up     | BOOLEAN          | NO       |          | Ada level up? (true/false)                 |
| metadata       | JSON             | YES      |          | Extra data (module_id, quiz_id, etc)       |
| created_at     | TIMESTAMP        | NO       | INDEX    | Transaction timestamp                      |
| updated_at     | TIMESTAMP        | YES      |          |                                            |

**Indexes:**

```sql
CREATE INDEX idx_user_xp_logs_user_id ON user_xp_logs(user_id);
CREATE INDEX idx_user_xp_logs_source ON user_xp_logs(source);
CREATE INDEX idx_user_xp_logs_created_at ON user_xp_logs(created_at);
CREATE INDEX idx_user_xp_logs_user_created ON user_xp_logs(user_id, created_at DESC);
```

**Example Data:**

```json
{
    "id": 1,
    "user_id": 5,
    "amount": 100,
    "source": "module_completed",
    "previous_xp": 150,
    "current_xp": 250,
    "previous_level": 2,
    "current_level": 3,
    "leveled_up": true,
    "metadata": {
        "module_id": 12,
        "module_title": "Introduction to PHP",
        "completion_time": "5:30"
    },
    "created_at": "2026-03-13 10:15:00"
}
```

---

### 3. courses

**Purpose:** Course/program definitions

| Column        | Type                                 | Nullable | Index | Description        |
| ------------- | ------------------------------------ | -------- | ----- | ------------------ |
| id            | BIGINT UNSIGNED                      | NO       | PK    | Course ID          |
| title         | VARCHAR(255)                         | NO       | INDEX | Course title       |
| description   | TEXT                                 | YES      |       | Course description |
| instructor_id | BIGINT UNSIGNED                      | NO       | FK    | Instructor (users) |
| status        | ENUM('draft','published','archived') | NO       |       | Course status      |
| created_at    | TIMESTAMP                            | YES      |       |                    |
| updated_at    | TIMESTAMP                            | YES      |       |                    |

**Relationships:**

- `instructor_id` → `users.id` (BelongsTo)
- Has-Many: `modules`, `enrollments`

---

### 4. modules

**Purpose:** Individual modules/lessons within a course

| Column                 | Type             | Nullable | Index | Description                   |
| ---------------------- | ---------------- | -------- | ----- | ----------------------------- |
| id                     | BIGINT UNSIGNED  | NO       | PK    | Module ID                     |
| course_id              | BIGINT UNSIGNED  | NO       | FK    | Parent course                 |
| title                  | VARCHAR(255)     | NO       |       | Module title                  |
| content                | LONGTEXT         | YES      |       | Module content                |
| order                  | INTEGER UNSIGNED | NO       |       | Display order                 |
| is_locked              | BOOLEAN          | NO       |       | Is module locked?             |
| prerequisite_module_id | BIGINT UNSIGNED  | YES      | FK    | Self-reference (prerequisite) |
| created_at             | TIMESTAMP        | YES      |       |                               |
| updated_at             | TIMESTAMP        | YES      |       |                               |

**Relationships:**

- `course_id` → `courses.id` (BelongsTo)
- `prerequisite_module_id` → `modules.id` (Self-referencing BelongsTo)
- Has-Many: `progress`, `dependents`

**Constraints:**

```sql
FOREIGN KEY(course_id) REFERENCES courses(id) ON DELETE CASCADE;
FOREIGN KEY(prerequisite_module_id) REFERENCES modules(id) ON DELETE SET NULL;
UNIQUE KEY(course_id, order);
```

---

### 5. enrollments

**Purpose:** Track user enrollment di courses

| Column     | Type                                   | Nullable | Index | Description       |
| ---------- | -------------------------------------- | -------- | ----- | ----------------- |
| id         | BIGINT UNSIGNED                        | NO       | PK    | Enrollment ID     |
| user_id    | BIGINT UNSIGNED                        | NO       | FK    | Student           |
| course_id  | BIGINT UNSIGNED                        | NO       | FK    | Course            |
| status     | ENUM('enrolled','completed','dropped') | NO       |       | Enrollment status |
| created_at | TIMESTAMP                              | YES      |       |                   |
| updated_at | TIMESTAMP                              | YES      |       |                   |

**Indexes & Constraints:**

```sql
UNIQUE KEY(user_id, course_id);
FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE;
FOREIGN KEY(course_id) REFERENCES courses(id) ON DELETE CASCADE;
CREATE INDEX idx_enrollments_status ON enrollments(status);
```

---

### 6. module_progress

**Purpose:** Track progress per user per module

| Column       | Type            | Nullable | Index     | Description     |
| ------------ | --------------- | -------- | --------- | --------------- |
| id           | BIGINT UNSIGNED | NO       | PK        | Progress ID     |
| user_id      | BIGINT UNSIGNED | NO       | FK        | Student         |
| module_id    | BIGINT UNSIGNED | NO       | FK        | Module          |
| is_viewed    | BOOLEAN         | NO       | DEFAULT 0 | User viewed?    |
| is_completed | BOOLEAN         | NO       | DEFAULT 0 | User completed? |
| created_at   | TIMESTAMP       | YES      |           |                 |
| updated_at   | TIMESTAMP       | YES      |           |                 |

**Indexes & Constraints:**

```sql
UNIQUE KEY(user_id, module_id);
FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE;
FOREIGN KEY(module_id) REFERENCES modules(id) ON DELETE CASCADE;
CREATE INDEX idx_module_progress_completed ON module_progress(is_completed);
```

---

## 🎮 Implementasi Gamifikasi

### 1. XP Calculation & Storage

**Flow Chart:**

```
User Action
    ↓
Determine XP Amount
    ↓
Award: $user->addXp($amount, $source, $metadata)
    ↓
┌─────────────────────────────────────┐
│ Create UserXpLog entry              │
│ - Record previous XP, level         │
│ - Record current XP, level          │
│ - Record source & metadata          │
│ - Set leveled_up flag if applicable │
└─────────────────────────────────────┘
    ↓
Update users table
    ├─ users.xp = new_xp
    ├─ users.level = calculated_level (auto)
    ├─ users.next_level_xp = updated
    └─ users.last_xp_earned_at = now
    ↓
Return Result Array
    ├─ xp_awarded
    ├─ previous_xp, current_xp
    ├─ previous_level, current_level
    ├─ leveled_up (boolean)
    └─ metadata
```

### 2. Level Calculation Formula

**Constants** (dalam `HasXpAndLeveling` trait):

```php
const BASE_XP_FOR_LEVEL = 100;    // XP needed untuk level 2
const XP_MULTIPLIER = 1.1;        // Multiply by 1.1 each level
const MAX_LEVEL = 100;            // Maximum level
```

**Formula untuk XP required di level N:**

```
XP_Required_Level(N) = BASE_XP * (MULTIPLIER ^ (N - 1))

Contoh:
Level 1: 100 * (1.1 ^ 0) = 100 * 1         = 100
Level 2: 100 * (1.1 ^ 1) = 100 * 1.1       = 110
Level 3: 100 * (1.1 ^ 2) = 100 * 1.21      = 121
Level 4: 100 * (1.1 ^ 3) = 100 * 1.331     = 133.1
...
Level 10: 100 * (1.1 ^ 9) = 100 * 2.357... = 235.79
Level 50: 100 * (1.1 ^ 49) ≈ 11,740 XP
Level 100: 100 * (1.1 ^ 99) ≈ Max level reached
```

**Cumulative XP Table (First 20 Levels):**

| Level | XP This Level | Cumulative XP | Required for Next |
| ----- | ------------- | ------------- | ----------------- |
| 1     | 0             | 0             | 100               |
| 2     | 100           | 100           | 110               |
| 3     | 110           | 210           | 121               |
| 4     | 121           | 331           | 133               |
| 5     | 133           | 464           | 146               |
| 6     | 146           | 610           | 161               |
| 7     | 161           | 771           | 177               |
| 8     | 177           | 948           | 195               |
| 9     | 195           | 1,143         | 215               |
| 10    | 215           | 1,358         | 236               |
| 15    | 329           | 2,836         | 411               |
| 20    | 614           | 7,570         | 835               |
| 25    | 1,143         | 13,955        | 1,554             |
| 30    | 2,129         | 25,296        | 2,897             |

---

### 3. XP Reward Sources

**Predefined Rewards** (dalam `XpRewardService`):

```php
const REWARDS = [
    'module_completed' => 100,           // User menyelesaikan module
    'quiz_passed' => 50,                 // User lulus quiz
    'quiz_perfect_score' => 150,         // 100% score = 150 XP bonus
    'course_completed' => 500,           // Entire course completed
    'streak_5_days' => 200,              // 5-day streak
    'streak_30_days' => 1000,            // 30-day streak
    'discussion_post' => 10,             // Forum post
    'discussion_helpful_answer' => 25,   // Helpful forum answer
    'achievement_unlocked' => 100,       // Achievement badge
];
```

**Custom Rewards:**

```php
// Flexible - any amount
$user->addXp(75, 'custom_event', ['description' => 'Event name']);
```

---

### 4. Leveling Up Mechanics

**Automatic Level Calculation:**

```
When user XP updated:
    ↓
Get total XP accumulated
    ↓
Loop through levels 1 to MAX_LEVEL:
    For each level:
        Calculate cumulative XP needed
        If user.xp >= cumulative_xp
            Then user is at least this level
        Else
            Break loop, user is at previous level
    ↓
Update users.level
    ↓
If level changed:
    Set leveled_up = true
    Calculate new next_level_xp
    Record in user_xp_logs with leveled_up flag
```

**Progress Tracking:**

```
User Progress to Next Level:

Progress % = (XP in Current Level / XP Required for Next Level) * 100

Example:
- User is Level 5 (has 464 total XP)
- XP required for Level 5: 133
- User has: 464 - 331 = 133 XP in this level
- XP needed for Level 6: 146
- Progress: (133 / 146) * 100 = 91.1%
```

---

### 5. Database Queries untuk Gamifikasi

**Query 1: Get User Leaderboard**

```sql
SELECT
    RANK() OVER (ORDER BY xp DESC) AS `rank`,
    id,
    name,
    email,
    level,
    xp,
    last_xp_earned_at
FROM users
WHERE role != 'admin'
ORDER BY xp DESC
LIMIT 100;
```

**Query 2: Get User XP History**

```sql
SELECT * FROM user_xp_logs
WHERE user_id = ?
ORDER BY created_at DESC
LIMIT 50;
```

**Query 3: Top Users by Level (Secondary by XP)**

```sql
SELECT
    id,
    name,
    level,
    xp
FROM users
WHERE role != 'admin'
ORDER BY level DESC, xp DESC
LIMIT 10;
```

**Query 4: User Analytics**

```sql
SELECT
    COUNT(*) as total_transactions,
    SUM(amount) as total_xp_earned,
    AVG(amount) as avg_xp_per_transaction,
    source,
    COUNT(DISTINCT source) as unique_sources
FROM user_xp_logs
WHERE user_id = ?
GROUP BY source
ORDER BY COUNT(*) DESC;
```

**Query 5: Level Up History**

```sql
SELECT
    current_level,
    created_at,
    amount as xp_earned
FROM user_xp_logs
WHERE user_id = ? AND leveled_up = true
ORDER BY created_at DESC;
```

---

### 6. Gamification Workflow Integration

```
MODULE COMPLETION FLOW:
─────────────────────────

1. User views module
   └─ Middleware marks module as viewed

2. User completes module
   └─ PUT /modules/{id}/complete
   └─ ModuleController@complete

3. In Controller:
   ├─ Validate enrollment & prerequisites (via policy)
   ├─ Mark module as completed (ModuleProgress)
   ├─ Award XP: $user->addXp(100, 'module_completed', [...])
   │
   └─ Check for additional rewards:
      ├─ Course completed? → Award 500 XP
      ├─ All prerequisites done? → Can unlock next module
      └─ Check streak status

4. Return Response:
   ├─ Success message
   ├─ XP awarded
   ├─ New level (if leveled up)
   ├─ Progress to next level
   └─ Updated user summary

───────────────────────────────

QUIZ COMPLETION FLOW:

1. User submits quiz answers
   └─ POST /quizzes/{id}/submit
   └─ QuizController@submit

2. Calculate score
   ├─ 0-49%: No XP
   ├─ 50-99%: 50 XP
   ├─ 100%: 150 XP (perfect bonus!)

3. Award XP & log
   ├─ $rewardService->awardQuizPassed($user, $quizId, $score)
   ├─ Automatically determines correct amount
   └─ Logs transaction with metadata

4. Check course completion
   ├─ If all modules + quizzes done
   ├─ Award 500 XP bonus
   └─ Mark course as completed

───────────────────────────────

LEADERBOARD DISPLAY FLOW:

1. User requests leaderboard
   └─ GET /leaderboard
   └─ LeaderboardController@index

2. Fetch top 100 users
   ├─ $rewardService->getLeaderboard(100)
   ├─ Sorted by XP DESC
   ├─ With rank calculated
   └─ Include level & current XP

3. Fetch user's details
   ├─ $rewardService->getUserRank($user)
   ├─ User's global position
   ├─ XP gap to next rank
   └─ User's analytics (avg/day, sources, etc)

4. Return views
   ├─ Top 100 users table
   ├─ User's stats card
   ├─ User's XP history graph
   └─ Achievement progress
```

---

### 7. Caching Strategy untuk Performance

**Cached Queries:**

```php
// Cache leaderboard untuk 1 hour
Cache::remember('leaderboard:top100', 3600, function () {
    return User::where('role', '!=', 'admin')
        ->orderByDesc('xp')
        ->take(100)
        ->get();
});

// Cache user ranking untuk 30 minutes
Cache::remember("user:rank:{$userId}", 1800, function () use ($userId) {
    return User::where('role', '!=', 'admin')
        ->where('xp', '>', User::find($userId)->xp)
        ->count() + 1;
});

// Cache user XP analytics untuk 24 hours
Cache::remember("user:analytics:{$userId}", 86400, function () use ($userId) {
    return UserXpLog::where('user_id', $userId)
        ->selectRaw('source, COUNT(*) as count, SUM(amount) as total')
        ->groupBy('source')
        ->get();
});
```

**Cache Invalidation:**

```php
// When XP is awarded, invalidate related caches
public function addXp($amount, $source, $metadata = [])
{
    // ... XP addition logic ...

    // Clear caches
    Cache::forget("leaderboard:top100");
    Cache::forget("user:rank:{$this->id}");
    Cache::forget("user:analytics:{$this->id}");

    return [...];
}
```

---

## 🔗 Data Relationships Diagram

```
users (1) ──────────── (∞) enrollments
  │ 1                           │ ∞
  │                             │
  ├──────────────── (1) courses
  │                      (1)
  │
  ├──────────── (∞) module_progress
  │                    │ ∞
  │                    │
  │                (1) modules
  │                    │ ∞
  │                    │
  │                (1) courses
  │
  └──────────── (∞) user_xp_logs
```

---

## 📈 Sample Data

### Users Table

```sql
INSERT INTO users (name, email, password, role, xp, level, next_level_xp, created_at) VALUES
('John Doe', 'john@example.com', bcrypt('password'), 'user', 350, 3, 331, now()),
('Jane Smith', 'jane@example.com', bcrypt('password'), 'user', 1250, 8, 948, now()),
('Admin User', 'admin@example.com', bcrypt('password'), 'admin', 5000, 20, 7570, now());
```

### Courses Table

```sql
INSERT INTO courses (title, description, instructor_id, status, created_at) VALUES
('Laravel Fundamentals', 'Learn Laravel basics', 1, 'published', now()),
('Advanced PHP', 'Deep dive into PHP', 1, 'published', now());
```

### Modules Table

```sql
INSERT INTO modules (course_id, title, content, `order`, is_locked, created_at) VALUES
(1, 'Installation & Setup', 'How to install Laravel...', 1, false, now()),
(1, 'Routing Basics', 'Understanding Laravel routes...', 2, false, now()),
(1, 'Database & Eloquent', 'Working with databases...', 3, true, 2),  -- Requires module 2
(2, 'OOP Concepts', 'Object-oriented PHP...', 1, false, now());
```

### UserXpLog Sample

```sql
INSERT INTO user_xp_logs
(user_id, amount, source, previous_xp, current_xp, previous_level, current_level, leveled_up, metadata, created_at)
VALUES
(1, 100, 'module_completed', 250, 350, 2, 3, true,
 JSON_OBJECT('module_id', 1, 'module_title', 'Installation & Setup'), now());
```

---

## 🔒 Database Constraints & Integrity

### Foreign Key Relationships

```sql
-- Users → Courses (Instructor)
ALTER TABLE courses
ADD CONSTRAINT fk_courses_instructor
FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE CASCADE;

-- Courses → Modules
ALTER TABLE modules
ADD CONSTRAINT fk_modules_course
FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE;

-- Modules → Modules (Prerequisite)
ALTER TABLE modules
ADD CONSTRAINT fk_modules_prerequisite
FOREIGN KEY (prerequisite_module_id) REFERENCES modules(id) ON DELETE SET NULL;

-- Users → Enrollments
ALTER TABLE enrollments
ADD CONSTRAINT fk_enrollments_user
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Courses → Enrollments
ALTER TABLE enrollments
ADD CONSTRAINT fk_enrollments_course
FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE;

-- Users → ModuleProgress
ALTER TABLE module_progress
ADD CONSTRAINT fk_module_progress_user
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Modules → ModuleProgress
ALTER TABLE module_progress
ADD CONSTRAINT fk_module_progress_module
FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE;

-- Users → UserXpLogs
ALTER TABLE user_xp_logs
ADD CONSTRAINT fk_user_xp_logs_user
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
```

### Unique Constraints

```sql
-- One enrollment per user per course
ALTER TABLE enrollments
ADD UNIQUE KEY uk_user_course (user_id, course_id);

-- One progress record per user per module
ALTER TABLE module_progress
ADD UNIQUE KEY uk_user_module (user_id, module_id);

-- Unique email per user
ALTER TABLE users
ADD UNIQUE KEY uk_email (email);
```

### Check Constraints

```sql
-- Level must be between 1-100
ALTER TABLE users
ADD CONSTRAINT chk_level_range
CHECK (level >= 1 AND level <= 100);

-- XP cannot be negative
ALTER TABLE users
ADD CONSTRAINT chk_xp_positive
CHECK (xp >= 0);

-- Order must be positive
ALTER TABLE modules
ADD CONSTRAINT chk_order_positive
CHECK (`order` > 0);
```

---

## 🚀 Query Performance

### Recommended Indexes

```sql
-- Users table
CREATE INDEX idx_users_xp ON users(xp DESC);
CREATE INDEX idx_users_level ON users(level DESC);
CREATE INDEX idx_users_created_at ON users(created_at DESC);
CREATE INDEX idx_users_role ON users(role);

-- UserXpLogs table
CREATE INDEX idx_user_xp_logs_user_id ON user_xp_logs(user_id);
CREATE INDEX idx_user_xp_logs_source ON user_xp_logs(source);
CREATE INDEX idx_user_xp_logs_created_at ON user_xp_logs(created_at DESC);
CREATE INDEX idx_user_xp_logs_user_created ON user_xp_logs(user_id, created_at DESC);
CREATE INDEX idx_user_xp_logs_leveled_up ON user_xp_logs(leveled_up);

-- Enrollments table
CREATE INDEX idx_enrollments_user_id ON enrollments(user_id);
CREATE INDEX idx_enrollments_course_id ON enrollments(course_id);
CREATE INDEX idx_enrollments_status ON enrollments(status);
CREATE INDEX idx_enrollments_user_course ON enrollments(user_id, course_id);

-- ModuleProgress table
CREATE INDEX idx_module_progress_user_id ON module_progress(user_id);
CREATE INDEX idx_module_progress_module_id ON module_progress(module_id);
CREATE INDEX idx_module_progress_completed ON module_progress(is_completed);

-- Modules table
CREATE INDEX idx_modules_course_id ON modules(course_id);
CREATE INDEX idx_modules_prerequisite_id ON modules(prerequisite_module_id);
```

### Query Optimization Tips

**❌ Bad - Full table scan:**

```sql
SELECT * FROM users ORDER BY xp DESC; -- Slow for large dataset
```

**✅ Good - Uses index + limit:**

```sql
SELECT id, name, xp, level FROM users
ORDER BY xp DESC
LIMIT 100; -- Fast!
```

**❌ Bad - Expensive calculation:**

```sql
SELECT *, RANK() OVER (ORDER BY xp DESC) FROM users; -- Heavy
```

**✅ Good - Gets top N + single rank:**

```sql
-- In app code, use Laravel's collected data
SELECT id, name, xp, level FROM users
WHERE role != 'admin'
ORDER BY xp DESC
LIMIT 100;

-- Count rank via: count where xp > user_xp
```

---

## 📊 Database Statistics

**Expected Data Volumes (after 1 year):**

| Table           | Rows | Size   | Growth Rate |
| --------------- | ---- | ------ | ----------- |
| users           | 10K  | ~5MB   | 1K/month    |
| courses         | 100  | ~100KB | Stable      |
| modules         | 1K   | ~500KB | Stable      |
| enrollments     | 50K  | ~2MB   | ~5K/month   |
| module_progress | 500K | ~20MB  | ~50K/month  |
| user_xp_logs    | 5M   | ~500MB | ~500K/month |

**Recommended Actions:**

- Archive user_xp_logs older than 2 years annually
- Create partitions on user_xp_logs by date
- Backup database daily
- Monitor table sizes monthly

---

## 🔄 Migration Commands

```bash
# Create all tables
php artisan migrate

# Run specific migration
php artisan migrate --path=/database/migrations/2026_03_13_*.php

# Rollback
php artisan migrate:rollback

# Seed sample data
php artisan db:seed

# Fresh database (dev only!)
php artisan migrate:fresh --seed
```

---

## ✅ Data Validation Rules

### Users Table

- `name`: Required, max 255 chars
- `email`: Required, unique, valid email format
- `password`: Required, min 8 chars, hashed
- `xp`: Unsigned integer, >= 0
- `level`: Integer, 1-100

### UserXpLogs Table

- `user_id`: Required, must exist in users
- `amount`: Required, integer (can be negative for rollback)
- `source`: Required, max 100 chars
- `leveled_up`: Boolean, default false

### Modules Table

- `title`: Required, max 255 chars
- `course_id`: Required, must exist in courses
- `order`: Required, positive integer, unique per course
- `is_locked`: Boolean, default false
- `prerequisite_module_id`: Optional, must reference valid module

---

## 🎯 Summary

```
┌──────────────────────────────────────────┐
│ MOOC PLATFORM DATABASE ARCHITECTURE      │
├──────────────────────────────────────────┤
│                                          │
│  Core Tables:                            │
│  ✓ users (with XP columns)              │
│  ✓ courses                               │
│  ✓ modules (with prerequisites)         │
│  ✓ enrollments                           │
│  ✓ module_progress                       │
│                                          │
│  Gamification Tables:                    │
│  ✓ user_xp_logs (audit trail)          │
│  ✓ Automatic level calculation          │
│  ✓ XP reward tracking                   │
│                                          │
│  Performance:                            │
│  ✓ Strategic indexing                   │
│  ✓ Optimized queries                    │
│  ✓ Caching strategy                     │
│  ✓ Data integrity constraints           │
│                                          │
└──────────────────────────────────────────┘
```

---

**Next Steps:**

1. Run `php artisan migrate` to create all tables
2. Seed sample data with seeders
3. Test queries using provided examples
4. Monitor performance with database tools
5. Implement caching for leaderboards
