# ✅ Gating Logic Implementation Checklist

Gunakan checklist ini untuk memastikan semua komponen gating logic telah dikonfigurasi dengan benar.

---

## 📦 Generated Files & Components

- [x] **Migrations**
    - `2026_03_13_062351_create_courses_table.php` ✓
    - `2026_03_13_062402_create_modules_table.php` ✓
    - `2026_03_13_062409_create_enrollments_table.php` ✓
    - `2026_03_13_062416_create_module_progress_table.php` ✓

- [x] **Models**
    - `app/Models/User.php` - Updated with relationships ✓
    - `app/Models/Course.php` ✓
    - `app/Models/Module.php` - With prerequisite support ✓
    - `app/Models/Enrollment.php` ✓
    - `app/Models/ModuleProgress.php` ✓

- [x] **Middleware**
    - `app/Http/Middleware/CheckModuleAccess.php` - Main gating middleware ✓

- [x] **Service**
    - `app/Services/ModuleGatingService.php` - Business logic ✓

- [x] **Policy**
    - `app/Policies/ModulePolicy.php` - Authorization ✓

- [x] **Controller**
    - `app/Http/Controllers/ModuleController.php` - Example implementation ✓

- [x] **Tests**
    - `tests/Feature/ModuleGatingTest.php` - Comprehensive test suite ✓

- [x] **Seeders**
    - `database/seeders/MOOCTestDataSeeder.php` - Sample data ✓

- [x] **Documentation**
    - `GATING_LOGIC_DOCS.md` - Complete documentation ✓
    - `QUICK_START.md` - Quick start guide ✓
    - `SETUP_INSTRUCTIONS.php` - Setup code snippets ✓
    - `routes/module-routes-example.php` - Route examples ✓

---

## 🚀 Configuration Steps

### Step 1: Run Migrations

```bash
php artisan migrate
```

- [ ] All migrations executed successfully
- [ ] `courses` table created
- [ ] `modules` table created with prerequisite_module_id column
- [ ] `enrollments` table created
- [ ] `module_progress` table created

### Step 2: Register Middleware

**File:** `app/Http/Kernel.php`

```php
protected $routeMiddleware = [
    // ... existing middleware ...
    'check.module.access' => \App\Http\Middleware\CheckModuleAccess::class,
];
```

- [ ] Middleware added to `$routeMiddleware` array
- [ ] Correct namespace: `\App\Http\Middleware\CheckModuleAccess::class`

### Step 3: Register Policy

**File:** `app/Providers/AuthServiceProvider.php`

Option A - Using the `$policies` property:

```php
protected $policies = [
    \App\Models\Module::class => \App\Policies\ModulePolicy::class,
];
```

Option B - In the `boot()` method:

```php
Gate::policy(\App\Models\Module::class, \App\Policies\ModulePolicy::class);
```

- [ ] Policy registered in AuthServiceProvider
- [ ] Module policy mapped to Module model

### Step 4: Create Routes

**File:** `routes/web.php` or `routes/api.php`

Use the example from `routes/module-routes-example.php`:

```php
Route::middleware(['auth'])->group(function () {
    Route::get('/courses/{course}/modules', [ModuleController::class, 'index'])
        ->name('modules.index');

    Route::get('/courses/{course}/modules/{module}', [ModuleController::class, 'show'])
        ->middleware('check.module.access')
        ->name('modules.show');

    Route::post('/courses/{course}/modules/{module}/complete', [ModuleController::class, 'complete'])
        ->middleware('check.module.access')
        ->name('modules.complete');
});
```

- [ ] Routes created and named appropriately
- [ ] Middleware applied to gating routes
- [ ] Route parameters match: `{course}` and `{module}`

---

## 🧪 Testing & Verification

### Step 1: Seed Test Data

```bash
php artisan db:seed --class=MOOCTestDataSeeder
```

- [ ] Test data seeded successfully
- [ ] Command output shows credentials
- [ ] Database contains test course, modules, and enrollments

### Step 2: Verify Models

```bash
php artisan tinker

# Check Course model
$course = Course::first();
$course->modules();  // Should work
$course->enrollments();  // Should work

# Check Module model
$module = Module::first();
$module->course;  // Should work
$module->prerequisite;  // Should work

# Check Enrollment model
$enrollment = Enrollment::first();
$enrollment->user;  // Should work
$enrollment->course;  // Should work

# Check ModuleProgress model
$progress = ModuleProgress::first();
$progress->user;  // Should work
$progress->module;  // Should work
```

- [ ] All model relationships work correctly
- [ ] No errors when accessing relationships

### Step 3: Test Gating Service

```php
# In tinker

$user = User::where('email', 'student1@mooc.test')->first();
$course = Course::first();
$module = Module::where('order', 2)->first();  // Locked module

// Test service
$gatingService = app(\App\Services\ModuleGatingService::class);
$access = $gatingService->checkModuleAccess($user, $module);
echo $access['can_access'];  // Should be false initially

// Get accessible modules
$modules = $gatingService->getAccessibleModules($user, $course->id);
foreach ($modules as $m) {
    echo $m->title . ': ' . ($m->can_access ? 'accessible' : 'locked');
}

// Check progress
$progress = $gatingService->getCourseProgress($user, $course->id);
echo $progress['percentage'];
```

- [ ] Service returns correct access status
- [ ] Accessible modules list is accurate
- [ ] Progress calculation is correct

### Step 4: Run Test Suite

```bash
php artisan test tests/Feature/ModuleGatingTest.php
```

- [ ] All tests pass
- [ ] Middleware tests pass
- [ ] Prerequisite validation tests pass
- [ ] Service method tests pass
- [ ] Policy tests pass

### Step 5: Manual Testing (with browser)

**Test Scenario 1: Unauthenticated User**

- [ ] Accessing module route redirects to login

**Test Scenario 2: Enrolled but Without Prerequisites**

- [ ] Can access module 1 (first module, no prerequisites)
- [ ] Cannot access module 2 (locked, requires module 1)
- [ ] Receives 403 error with clear message

**Test Scenario 3: After Completing Prerequisite**

- [ ] Mark module 1 as completed
- [ ] Now can access module 2
- [ ] Can access module 3 after completing module 2

**Test Scenario 4: Authorization Policy**

- [ ] Policy denies access to locked modules
- [ ] Authorization checks work correctly

---

## 📊 Database Verification

### Check Table Structure

```sql
-- Verify courses table
DESCRIBE courses;
-- Should have: id, title, description, instructor_id, status, timestamps

-- Verify modules table
DESCRIBE modules;
-- Should have: id, course_id, title, content, order, is_locked, prerequisite_module_id, timestamps

-- Verify enrollments table
DESCRIBE enrollments;
-- Should have: id, user_id, course_id, status, enrolled_at, completed_at, timestamps

-- Verify module_progress table
DESCRIBE module_progress;
-- Should have: id, user_id, module_id, is_viewed, is_completed, started_at, completed_at, timestamps
```

- [ ] All tables have correct structure
- [ ] Foreign keys are properly configured
- [ ] Indexes are created for performance

### Check Data Relationships

```sql
-- Verify enrollment integrity
SELECT COUNT(*) FROM enrollments
WHERE user_id NOT IN (SELECT id FROM users);
-- Should return: 0

-- Verify module integrity
SELECT COUNT(*) FROM modules
WHERE course_id NOT IN (SELECT id FROM courses);
-- Should return: 0

-- Verify prerequisite chain integrity
SELECT COUNT(*) FROM modules m1
JOIN modules m2 ON m1.prerequisite_module_id = m2.id
WHERE m1.course_id != m2.course_id;
-- Should return: 0 (prerequisites must be in same course)
```

- [ ] No orphaned foreign key references
- [ ] Prerequisite chain is valid

---

## 🔧 Configuration Verification

### Check Middleware is Registered

```php
# In tinker
$kernel = app(\App\Http\Kernel::class);
$middleware = $kernel->$routeMiddleware;
echo $middleware['check.module.access'];  // Should show class path
```

- [ ] Middleware appears in route middleware list

### Check Policy is Registered

```php
$policy = \Gate::getPolicyFor(\App\Models\Module::class);
echo get_class($policy);  // Should be ModulePolicy
```

- [ ] Policy is properly registered

### Check Model Relationships

```php
$reflectionClass = new \ReflectionClass(\App\Models\Module::class);
foreach ($reflectionClass->getMethods() as $method) {
    if (in_array($method->name, ['course', 'prerequisite', 'dependents', 'progress'])) {
        echo $method->name . ' ✓';
    }
}
```

- [ ] All expected methods exist
- [ ] Methods are accessible

---

## 📋 Route Verification

```bash
php artisan route:list | grep module
```

Expected output should include:

- [ ] `GET /courses/{course}/modules` (index)
- [ ] `GET /courses/{course}/modules/{module}` (show with middleware)
- [ ] `POST /courses/{course}/modules/{module}/complete` (complete with middleware)

---

## 🎯 Common Issues & Solutions

### Issue: Middleware Not Triggering

- **Check:** Is `'check.module.access'` in the route definition?
- **Check:** Is middleware registered in `Kernel.php`?
- **Solution:** See SETUP_INSTRUCTIONS.php

### Issue: Policy Authorization Failing

- **Check:** Did you run `php artisan cache:clear`?
- **Check:** Is ModulePolicy registered in AuthServiceProvider?
- **Solution:** Clear cache: `php artisan cache:clear`

### Issue: Module Not Being Marked as Viewed

- **Check:** Did the middleware execute successfully?
- **Check:** Are there database errors in the logs?
- **Solution:** Check `storage/logs/laravel.log`

### Issue: Tests Failing

- **Check:** Did you run migrations: `php artisan migrate`?
- **Check:** Are you using correct database for tests?
- **Solution:** Run: `php artisan test --sqlite`

---

## 📚 Documentation Reference

| Document                             | Purpose                         |
| ------------------------------------ | ------------------------------- |
| `GATING_LOGIC_DOCS.md`               | Complete API documentation      |
| `QUICK_START.md`                     | Quick setup & usage guide       |
| `SETUP_INSTRUCTIONS.php`             | Code snippets for configuration |
| `tests/Feature/ModuleGatingTest.php` | Test examples                   |
| `routes/module-routes-example.php`   | Route examples                  |

---

## ✅ Final Verification Checklist

- [ ] All migrations executed
- [ ] Middleware registered in Kernel.php
- [ ] Policy registered in AuthServiceProvider
- [ ] Routes created with middleware
- [ ] Test data seeded
- [ ] All tests passing
- [ ] Manual testing scenarios verified
- [ ] Database integrity checks passed
- [ ] Documentation reviewed

---

## 🚀 Ready for Production!

Once all checkboxes are marked, your gating logic middleware is fully implemented and ready to use!

For next steps, refer to:

- **QUICK_START.md** - Quick implementation guide
- **GATING_LOGIC_DOCS.md** - Detailed API reference
- **tests/Feature/ModuleGatingTest.php** - Testing examples
