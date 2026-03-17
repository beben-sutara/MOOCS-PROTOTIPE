# Panduan Cepat Setup Gating Logic Middleware

## 📋 Sekilas Fitur

Sistem gating logic ini menyediakan:

- ✅ Pembatasan akses modul berdasarkan enrollment
- ✅ Validasi prerequisite otomatis
- ✅ Tracking progress pengguna per modul
- ✅ Authorization policy untuk kontrol granular
- ✅ Service layer untuk abstraksi logika bisnis

---

## 🚀 Langkah-Langkah Setup

### Step 1: Run Migrations

Buat semua tabel yang diperlukan:

```bash
php artisan migrate
```

Ini akan membuat:

- `courses` - Tabel kursus
- `modules` - Tabel modul dengan prerequisite support
- `enrollments` - Tabel pendaftaran user ke kursus
- `module_progress` - Tabel tracking progress

### Step 2: Register Middleware

Edit `app/Http/Kernel.php` dan tambahkan di `$routeMiddleware`:

```php
protected $routeMiddleware = [
    // ... existing middleware
    'check.module.access' => \App\Http\Middleware\CheckModuleAccess::class,
];
```

### Step 3: Register Policy

Edit `app/Providers/AuthServiceProvider.php`:

```php
use App\Models\Module;
use App\Policies\ModulePolicy;

protected $policies = [
    Module::class => ModulePolicy::class,
];
```

### Step 4: Create Routes

Gunakan contoh dari `routes/module-routes-example.php`:

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

### Step 5: (Optional) Seed Sample Data

Create a seeder untuk test data:

```bash
php artisan make:seeder CourseSeed
php artisan make:seeder ModuleSeed
```

Edit seeder untuk membuat:

- 1 Course dengan instructor
- 5 Modules dengan prerequisite chain
- Test enrollment

---

## 📝 Struktur Data

### Course Table

```
id | title | description | instructor_id | status | created_at | updated_at
1  | Laravel Advanced | ... | 1 | published | ... | ...
```

### Module Table

```
id | course_id | title | order | is_locked | prerequisite_module_id | created_at
1  | 1 | Intro | 1 | 0 | NULL | ...
2  | 1 | Middleware | 2 | 1 | 1 | ...
3  | 1 | Services | 3 | 1 | 2 | ...
```

### Enrollment Table

```
id | user_id | course_id | status | enrolled_at | completed_at
1  | 2 | 1 | active | ... | NULL
```

### ModuleProgress Table

```
id | user_id | module_id | is_viewed | is_completed | started_at | completed_at
1  | 2 | 1 | 1 | 1 | ... | ...
2  | 2 | 2 | 1 | 0 | ... | NULL
```

---

## 🔐 Gating Logic Flow

```
User Request untuk akses Module
        ↓
CheckModuleAccess Middleware
        ↓
┌─────────────────────────────────────┐
│ 1. Cek Autentikasi ✓                │
│ 2. Validasi Module & Course ada ✓   │
│ 3. Cek Enrollment di Course ✓       │
│ 4. Cek Prerequisite selesai? ✓      │
│ 5. Mark Module Viewed ✓             │
│ 6. Pass ke Next Handler ✓           │
└─────────────────────────────────────┘
        ↓
Controller Handler dengan
Authorization Policy Check
        ↓
Return Response ✓
```

---

## 💡 Contoh Penggunaan

### 1. Menampilkan List Module dengan Status

```php
// Controller
public function index(Course $course)
{
    $gatingService = app(ModuleGatingService::class);
    $modules = $gatingService->getAccessibleModules(auth()->user(), $course->id);

    return view('modules.index', [
        'modules' => $modules,
        'progress' => $gatingService->getCourseProgress(auth()->user(), $course->id)
    ]);
}

// View (Blade)
@foreach($modules as $module)
    <div class="module-card">
        <h3>{{ $module->title }}</h3>

        @if($module->can_access)
            <a href="{{ route('modules.show', $module) }}" class="btn btn-primary">
                Buka Modul
            </a>
        @else
            <div class="alert alert-warning">
                ⚠️ Modul Terkunci
                <p>{{ $module->access_reason === 'prerequisite_not_met' ?
                    'Selesaikan modul sebelumnya terlebih dahulu' :
                    'Anda belum terdaftar dalam kursus ini' }}</p>
            </div>
        @endif

        @if($module->progress && $module->progress->is_completed)
            <span class="badge badge-success">✓ Selesai</span>
        @elseif($module->progress && $module->progress->is_viewed)
            <span class="badge badge-info">⏳ Sedang Dikerjakan</span>
        @endif
    </div>
@endforeach
```

### 2. Menyelesaikan Module

```php
// Controller - POST request
public function complete(Request $request, Course $course, Module $module)
{
    $this->authorize('complete', $module);

    $gatingService = app(ModuleGatingService::class);
    $gatingService->completeModule(auth()->user(), $module);

    return response()->json([
        'message' => 'Modul berhasil diselesaikan!',
        'progress' => $gatingService->getCourseProgress(auth()->user(), $course->id)
    ]);
}

// Frontend JavaScript
async function completeModule(moduleId) {
    const response = await fetch(`/courses/${courseId}/modules/${moduleId}/complete`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': token }
    });

    const data = await response.json();
    console.log(data.message);
    console.log(`Progress: ${data.progress.percentage}%`);
}
```

### 3. Membuat Module dengan Prerequisite

```php
// Dalam seeder atau controller
$module1 = Module::create([
    'course_id' => $course->id,
    'title' => 'Pengenalan',
    'order' => 1,
    'is_locked' => false,
    'prerequisite_module_id' => null,
]);

$module2 = Module::create([
    'course_id' => $course->id,
    'title' => 'Intermediate',
    'order' => 2,
    'is_locked' => true,  // Terkunci
    'prerequisite_module_id' => $module1->id,  // Wajib selesaikan module 1
]);

$module3 = Module::create([
    'course_id' => $course->id,
    'title' => 'Advanced',
    'order' => 3,
    'is_locked' => true,
    'prerequisite_module_id' => $module2->id,  // Wajib selesaikan module 2
]);
```

---

## 🧪 Testing

Buat test untuk memverifikasi gating logic:

```bash
php artisan make:test ModuleGatingTest
```

```php
public function test_user_cannot_access_locked_module_without_prerequisite()
{
    $user = User::factory()->create();
    $course = Course::factory()->create();
    $module1 = Module::factory()->create(['course_id' => $course->id]);
    $module2 = Module::factory()->create([
        'course_id' => $course->id,
        'is_locked' => true,
        'prerequisite_module_id' => $module1->id
    ]);

    $user->enrollments()->create([
        'course_id' => $course->id,
        'status' => 'active'
    ]);

    $response = $this->actingAs($user)->get(route('modules.show', $module2));
    $response->assertForbidden();
}

public function test_user_can_access_module_after_completing_prerequisite()
{
    // ... setup ...

    $user->moduleProgress()->create([
        'module_id' => $module1->id,
        'is_completed' => true
    ]);

    $response = $this->actingAs($user)->get(route('modules.show', $module2));
    $response->assertSuccessful();
}
```

---

## 📊 API Response Examples

### GET /courses/{id}/modules

```json
{
    "course": {
        "id": 1,
        "title": "Advanced Laravel",
        "instructor_id": 1
    },
    "modules": [
        {
            "id": 1,
            "title": "Middleware Basics",
            "can_access": true,
            "access_reason": "access_granted",
            "progress": {
                "id": 1,
                "is_viewed": true,
                "is_completed": true
            }
        },
        {
            "id": 2,
            "title": "Custom Middleware",
            "can_access": true,
            "access_reason": "access_granted",
            "progress": {
                "id": 2,
                "is_viewed": true,
                "is_completed": false
            }
        }
    ],
    "progress": {
        "completed": 1,
        "total": 3,
        "percentage": 33.33
    }
}
```

### GET /courses/{courseId}/modules/{moduleId}

```json
{
    "module": {
        "id": 2,
        "title": "Custom Middleware",
        "content": "..."
    },
    "prerequisites": null,
    "next_module": {
        "id": 3,
        "title": "Advanced Patterns"
    },
    "previous_module": {
        "id": 1,
        "title": "Middleware Basics"
    },
    "user_progress": {
        "is_viewed": true,
        "is_completed": false
    }
}
```

### POST /courses/{courseId}/modules/{moduleId}/complete

```json
{
    "message": "Modul telah diselesaikan",
    "progress": {
        "id": 2,
        "is_completed": true,
        "completed_at": "2026-03-13T10:30:00Z"
    },
    "course_progress": {
        "completed": 2,
        "total": 3,
        "percentage": 66.67
    }
}
```

---

## 🐛 Troubleshooting

| Masalah                      | Solusi                                                 |
| ---------------------------- | ------------------------------------------------------ |
| Module selalu terkunci       | Pastikan prerequisite module sudah di-mark completed   |
| Middleware tidak berjalan    | Periksa registrasi di Kernel.php                       |
| 403 Forbidden - Not Enrolled | User harus di-enroll ke course terlebih dahulu         |
| Policy tidak bekerja         | Pastikan AuthServiceProvider mendaftarkan ModulePolicy |
| 404 Not Found                | Module atau Course tidak ada di database               |

---

## 📚 File-File Penting

| File                                        | Fungsi                            |
| ------------------------------------------- | --------------------------------- |
| `app/Http/Middleware/CheckModuleAccess.php` | Middleware utama untuk gating     |
| `app/Services/ModuleGatingService.php`      | Service untuk logika gating       |
| `app/Policies/ModulePolicy.php`             | Authorization policies            |
| `app/Http/Controllers/ModuleController.php` | Controller example                |
| `app/Models/Module.php`                     | Module model dengan relationships |
| `GATING_LOGIC_DOCS.md`                      | Dokumentasi lengkap               |

---

## 🎯 Next Steps

1. Setup routes menggunakan contoh di `routes/module-routes-example.php`
2. Create views untuk tampilkan module list dan detail
3. Implement quiz/assessment untuk tambahan gating criteria
4. Add notifications ketika prerequisite selesai
5. Create analytics dashboard untuk tracking progress

---

## 📞 Dukungan

Untuk pertanyaan lebih lanjut, lihat:

- `GATING_LOGIC_DOCS.md` - Dokumentasi lengkap
- `app/Services/ModuleGatingService.php` - Method reference
- `app/Http/Middleware/CheckModuleAccess.php` - Middleware logic
