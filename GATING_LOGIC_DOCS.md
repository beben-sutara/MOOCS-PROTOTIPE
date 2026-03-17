# Dokumentasi Middleware Gating Logic untuk MOOC Platform

## Pengenalan

Middleware `CheckModuleAccess` dirancang untuk membatasi akses modul berdasarkan:

1. **Status Enrollment** - Pengguna harus terdaftar dalam kursus
2. **Prerequisite Modules** - Pengguna harus menyelesaikan modul yang disyaratkan terlebih dahulu
3. **Module Lock Status** - Modul dapat dikunci sampai prerequisite selesai

---

## Struktur Komponen

### 1. Models

#### Course

```php
- id: integer (PK)
- title: string
- description: text
- instructor_id: integer (FK) - referensi User
- status: enum (draft, published, archived)
- timestamps
```

**Relasi:**

- `modules()` - HasMany Module
- `enrollments()` - HasMany Enrollment
- `instructor()` - BelongsTo User

---

#### Module

```php
- id: integer (PK)
- course_id: integer (FK)
- title: string
- content: text
- order: integer - urutan modul dalam kursus
- is_locked: boolean - status kunci modul
- prerequisite_module_id: integer (FK) - modul persyaratan (nullable)
- timestamps
```

**Relasi:**

- `course()` - BelongsTo Course
- `prerequisite()` - BelongsTo Module (parent)
- `dependents()` - HasMany Module (children)
- `progress()` - HasMany ModuleProgress

---

#### Enrollment

```php
- id: integer (PK)
- user_id: integer (FK)
- course_id: integer (FK)
- status: enum (active, completed, dropped)
- enrolled_at: timestamp
- completed_at: timestamp (nullable)
- unique constraint: (user_id, course_id)
- timestamps
```

**Relasi:**

- `user()` - BelongsTo User
- `course()` - BelongsTo Course

---

#### ModuleProgress

```php
- id: integer (PK)
- user_id: integer (FK)
- module_id: integer (FK)
- is_viewed: boolean
- is_completed: boolean
- started_at: timestamp (nullable)
- completed_at: timestamp (nullable)
- unique constraint: (user_id, module_id)
- timestamps
```

**Relasi:**

- `user()` - BelongsTo User
- `module()` - BelongsTo Module

---

### 2. Middleware: CheckModuleAccess

**Lokasi:** `app/Http/Middleware/CheckModuleAccess.php`

**Fungsi Utama:**

- Validasi autentikasi pengguna
- Verifikasi keikutsertaan dalam kursus
- Pemeriksaan modul prerequisite
- Pencatatan akses modul (is_viewed)
- Penanganan akses ditolak dengan pesan yang jelas

**Logika Alur:**

```
Request dengan route parameter 'module'
    ↓
Cek Autentikasi (authenticated?)
    ├─ NO  → Redirect ke login
    └─ YES ↓
Validasi Module & Course ada
    ├─ NO  → Abort 404
    └─ YES ↓
Cek Enrollment di Course
    ├─ NO  → Abort 403 "Tidak terdaftar"
    └─ YES ↓
Cek Module Locked & Prerequisite selesai
    ├─ NO  → Abort 403 "Modul terkunci"
    └─ YES ↓
Mark Module sebagai Viewed
    ↓
Store Module di Request
    ↓
Next Handler ✓
```

**Implementasi:**

```php
// Gating checks performed
private function canAccessModule($user, $module): bool
{
    // If no prerequisite, return true
    if (!$module->prerequisite_module_id) {
        return true;
    }

    // Check if prerequisite is completed
    return $user->moduleProgress()
        ->where('module_id', $module->prerequisite_module_id)
        ->where('is_completed', true)
        ->exists();
}
```

---

### 3. Service: ModuleGatingService

**Lokasi:** `app/Services/ModuleGatingService.php`

**Metode Utama:**

#### `checkModuleAccess(User $user, Module $module): array`

Memeriksa akses dengan detail alasan penolakan.

```php
Response Format:
{
    'can_access' => boolean,
    'reason' => 'not_enrolled|prerequisite_not_met|access_granted',
    'message' => string,
    'prerequisite' => Module|null
}
```

#### `hasCompletedPrerequisite(User $user, Module $module): bool`

Validasi prerequisite sudah diselesaikan.

#### `getAccessibleModules(User $user, int $courseId)`

Dapatkan semua modul dengan status akses masing-masing.

```php
$modules = $gatingService->getAccessibleModules($user, $courseId);

// Setiap modul akan memiliki:
// - can_access: boolean
// - access_reason: string
// - progress: ModuleProgress|null
```

#### `completeModule(User $user, Module $module): ModuleProgress`

Tandai modul sebagai selesai.

#### `getCourseProgress(User $user, int $courseId): array`

Dapatkan statistik progres kursus.

```php
{
    'completed' => 3,      // modul selesai
    'total' => 10,         // total modul
    'percentage' => 30.0   // persentase
}
```

---

### 4. Policy: ModulePolicy

**Lokasi:** `app/Policies/ModulePolicy.php`

**Otorisasi Methods:**

#### `view(User $user, Module $module): bool`

Cek apakah user dapat melihat modul.

#### `complete(User $user, Module $module): bool`

Cek apakah user dapat menyelesaikan modul.

#### `viewLocked(User $user, Module $module): bool`

Cek apakah modul adalah terkunci untuk user.

**Penggunaan dalam Controller:**

```php
$this->authorize('view', $module);
$this->authorize('complete', $module);
```

---

### 5. Controller: ModuleController

**Lokasi:** `app/Http/Controllers/ModuleController.php`

**Route Handlers:**

#### `index(Course $course)`

Tampilkan semua modul untuk kursus dengan gating logic.

**Response:**

```json
{
    "course": { ... },
    "modules": [
        {
            "id": 1,
            "title": "Modul 1: Pengenalan",
            "can_access": true,
            "access_reason": "access_granted",
            "progress": { ... }
        }
    ],
    "progress": {
        "completed": 1,
        "total": 5,
        "percentage": 20
    }
}
```

#### `show(Course $course, Module $module)`

Tampilkan detail modul (dengan akses terbatas).

**Response:**

```json
{
    "module": { ... },
    "content": "Isi modul...",
    "prerequisites": { ... },
    "next_module": { ... },
    "previous_module": { ... },
    "user_progress": { ... }
}
```

#### `complete(Request $request, Course $course, Module $module)`

Tandai modul sebagai selesai.

**Response:**

```json
{
    "message": "Modul telah diselesaikan",
    "progress": { ... },
    "course_progress": { ... }
}
```

---

## Setup & Registrasi

### 1. Register Middleware di Kernel

Edit `app/Http/Kernel.php`:

```php
protected $routeMiddleware = [
    // ... existing middleware
    'check.module.access' => \App\Http\Middleware\CheckModuleAccess::class,
];
```

### 2. Register Policy di AuthServiceProvider

Edit `app/Providers/AuthServiceProvider.php`:

```php
use App\Models\Module;
use App\Policies\ModulePolicy;

protected $policies = [
    Module::class => ModulePolicy::class,
];
```

### 3. Register Service di AppServiceProvider

Edit `app/Providers/AppServiceProvider.php`:

```php
use App\Services\ModuleGatingService;

public function register()
{
    $this->app->singleton(ModuleGatingService::class);
}
```

---

## Penggunaan dalam Routes

### Contoh Route Definition

```php
// routes/api.php atau routes/web.php

Route::middleware(['auth'])->group(function () {
    // Lihat semua modul dalam kursus
    Route::get('/courses/{course}/modules',
        [ModuleController::class, 'index']
    )->name('modules.index');

    // Lihat detail modul (middleware check.module.access aktif)
    Route::get('/courses/{course}/modules/{module}',
        [ModuleController::class, 'show']
    )->middleware('check.module.access')
    ->name('modules.show');

    // Mark modul sebagai selesai
    Route::post('/courses/{course}/modules/{module}/complete',
        [ModuleController::class, 'complete']
    )->middleware('check.module.access')
    ->name('modules.complete');
});
```

---

## Contoh Penggunaan Praktis

### 1. Mengecek Akses Module

```php
use App\Services\ModuleGatingService;

$gatingService = app(ModuleGatingService::class);
$user = auth()->user();
$module = Module::find(1);

$access = $gatingService->checkModuleAccess($user, $module);

if ($access['can_access']) {
    // User dapat mengakses
    return view('module.show', ['module' => $module]);
} else {
    // Tampilkan pesan error
    return back()->withError($access['message']);
}
```

### 2. Menampilkan Module List dengan Status Akses

```php
public function showCourse(Course $course)
{
    $gatingService = app(ModuleGatingService::class);
    $user = auth()->user();

    $modules = $gatingService->getAccessibleModules($user, $course->id);

    return view('course.show', [
        'course' => $course,
        'modules' => $modules,
        'progress' => $gatingService->getCourseProgress($user, $course->id)
    ]);
}
```

### 3. Menyelesaikan Module

```php
public function completeModule(Module $module)
{
    $gatingService = app(ModuleGatingService::class);
    $user = auth()->user();

    // Verify access
    if (!$gatingService->checkModuleAccess($user, $module)['can_access']) {
        return response()->json(['error' => 'Access denied'], 403);
    }

    // Mark as complete
    $gatingService->completeModule($user, $module);

    return response()->json(['message' => 'Module completed']);
}
```

### 4. Menggunakan Authorization dengan Policy

```php
// Di Blade template
@can('view', $module)
    <div class="module-content">
        {{ $module->content }}
    </div>
@else
    <div class="module-locked">
        <p>{{ __('Modul ini terkunci. Selesaikan persyaratan terlebih dahulu.') }}</p>
    </div>
@endcan

// Di Controller
app()->request->authorize('complete', $module);

// Atau langsung
$this->authorize('view', $module);
```

---

## Migrasi Database

Jalankan migrasi untuk membuat semua tabel:

```bash
php artisan migrate
```

Ini akan membuat:

- `courses` table
- `modules` table
- `enrollments` table
- `module_progress` table

---

## Error Handling

Middleware menangani beberapa situasi error:

| Status | Pesan            | Penyebab                          |
| ------ | ---------------- | --------------------------------- |
| 401    | Login Required   | User belum terautentikasi         |
| 403    | Not Enrolled     | User tidak terdaftar dalam kursus |
| 403    | Module Locked    | Prerequisite belum selesai        |
| 404    | Module Not Found | Module tidak ada                  |
| 404    | Course Not Found | Course tidak ada                  |

---

## Best Practices

1. **Selalu Gunakan Middleware** - Jangan skip middleware `check.module.access` untuk rute yang memerlukan akses modul
2. **Validasi di Database** - Gunakan database constraints untuk menjaga integritas data
3. **Caching** - Cache progress untuk performa yang lebih baik
4. **Audit Trail** - Log akses modul untuk analisis
5. **Error Messages** - Berikan pesan yang jelas dalam Bahasa Indonesia

---

## Troubleshooting

### Module Selalu Terkunci

Pastikan:

- `prerequisite_module_id` di-set dengan benar
- Prerequisite module telah di-mark completed untuk user

### Middleware Tidak Berjalan

Periksa:

- Middleware terdaftar di `Kernel.php`
- Route menggunakan middleware dengan benar
- Route parameter bernama `module`

### Policy Authorization Gagal

Pastikan:

- `AuthServiceProvider` mendaftarkan policy
- Method authorization dipanggil dengan benar

---

## Pengembangan Lebih Lanjut

Fitur yang dapat dikembangkan:

1. **Quiz/Assessment** - Wajibkan passing score untuk unlock next module
2. **Time-based Gating** - Release modul berdasarkan tanggal
3. **Grade-based Gating** - Batasi akses berdasarkan nilai rata-rata
4. **Progress Analytics** - Dashboard progres detail
5. **Notifications** - Notif ketika prerequisite selesai
6. **Module Dependencies Graph** - Visualisasi dependensi modul

---

## Dokumentasi Lengkap

Untuk informasi lebih lanjut, lihat:

- Model relationships di `app/Models/`
- Middleware logic di `app/Http/Middleware/CheckModuleAccess.php`
- Service methods di `app/Services/ModuleGatingService.php`
- Policy authorization di `app/Policies/ModulePolicy.php`
