# 🎓 MOOCS Prototipe

Platform MOOC (Massive Open Online Course) — Sistem Manajemen Pembelajaran (LMS) berbasis web yang dibangun dengan Laravel. Platform ini memungkinkan pengguna untuk mendaftar kursus, menyelesaikan modul, dan mendapatkan poin pengalaman (XP) melalui sistem gamifikasi.

---

## 📋 Daftar Isi

- [Fitur Utama](#-fitur-utama)
- [Teknologi yang Digunakan](#-teknologi-yang-digunakan)
- [Persyaratan Sistem](#-persyaratan-sistem)
- [Instalasi dan Konfigurasi](#-instalasi-dan-konfigurasi)
- [Menjalankan Aplikasi](#-menjalankan-aplikasi)
- [Struktur Proyek](#-struktur-proyek)
- [Skema Database](#-skema-database)
- [API Endpoints](#-api-endpoints)
- [Peran Pengguna](#-peran-pengguna)
- [Dokumentasi Tambahan](#-dokumentasi-tambahan)
- [Pemecahan Masalah](#-pemecahan-masalah)

---

## ✨ Fitur Utama

### 📚 Manajemen Pembelajaran
- **Manajemen Kursus** — Buat, edit, dan publikasikan kursus dengan status *draft*, *pending approval*, atau *published*
- **Sistem Modul** — Susun kursus ke dalam modul berurutan dengan dukungan prasyarat (*prerequisite*)
- **Gating Modul** — Kunci modul hingga modul prasyarat diselesaikan
- **Pelacakan Kemajuan** — Pantau status penyelesaian setiap modul per pengguna
- **Sistem Pendaftaran** — Pengguna mendaftar ke kursus dan mengikuti progres mereka

### 🎮 Gamifikasi
- **Sistem XP** — Poin pengalaman diberikan saat menyelesaikan modul dan kuis
- **Leveling Otomatis** — Level dihitung otomatis berdasarkan total XP
- **Leaderboard Global** — Peringkat pengguna berdasarkan XP dan level
- **Leaderboard per Kursus** — Peringkat khusus dalam setiap kursus
- **Riwayat XP** — Jejak audit lengkap setiap transaksi XP

### 👤 Manajemen Pengguna
- **Autentikasi** — Login/registrasi aman dengan hash bcrypt
- **Role-based Access** — Peran Admin, Instruktur, dan Siswa
- **Profil Pengguna** — Kelola profil dan ubah kata sandi
- **Aplikasi Instruktur** — Siswa dapat mengajukan diri menjadi instruktur (perlu persetujuan admin)
- **Dashboard Admin** — Kelola kursus, pengguna, dan persetujuan instruktur

### 🔌 API REST
- 15+ endpoint JSON
- Autentikasi berbasis token (Laravel Sanctum)
- Dukungan CORS

---

## 🛠 Teknologi yang Digunakan

| Lapisan | Teknologi |
|---------|-----------|
| **Bahasa** | PHP 8.0+ |
| **Framework Backend** | Laravel 9.x |
| **ORM** | Eloquent (Laravel) |
| **Autentikasi API** | Laravel Sanctum |
| **Database** | MySQL / MariaDB |
| **Frontend Templating** | Blade (Laravel) |
| **CSS Framework** | Bootstrap 5 |
| **Build Tool Frontend** | Vite 4 |
| **HTTP Client** | Axios, Guzzle 7 |
| **Testing** | PHPUnit 9 |
| **Linting** | Laravel Pint |

---

## 💻 Persyaratan Sistem

Sebelum memulai, pastikan perangkat memenuhi persyaratan berikut:

- PHP **8.0.2** atau lebih tinggi (dengan ekstensi: `mbstring`, `pdo`, `pdo_mysql`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`)
- **Composer** (manajer paket PHP)
- **MySQL** / MariaDB (bisa via XAMPP atau instalasi mandiri)
- **Node.js** 16+ dan **npm** (untuk aset frontend)
- Ruang disk bebas minimal **500 MB**
- Port **8000** (server Laravel) dan **3306** (MySQL) tersedia

---

## 🚀 Instalasi dan Konfigurasi

### 1. Masuk ke Direktori Proyek

```bash
cd /path/ke/MOOCS-PROTOTIPE
```

### 2. Install Dependensi PHP

```bash
composer install
```

### 3. Konfigurasi Environment

```bash
cp .env.example .env
```

Buka file `.env` dan sesuaikan nilai berikut:

```dotenv
APP_NAME="MOOC Platform"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mooks
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Buat Database

Di MySQL CLI atau phpMyAdmin:

```sql
CREATE DATABASE mooks;
```

### 6. Jalankan Migrasi Database

```bash
php artisan migrate:fresh
```

### 7. Isi Data Sample (Opsional tapi Disarankan)

```bash
php artisan db:seed
```

Data yang dibuat:
- 10 pengguna sample
- 5 kursus dengan 21 modul
- Pendaftaran kursus dan data progres sample

### 8. Install Dependensi Frontend

```bash
npm install
```

### 9. Build Aset Frontend

```bash
# Untuk produksi
npm run build

# Atau untuk pengembangan (dengan hot-reload)
npm run dev
```

---

## ▶️ Menjalankan Aplikasi

### Server Pengembangan

```bash
# Terminal 1: Jalankan server Laravel
php artisan serve
# Server berjalan di http://localhost:8000

# Terminal 2: Kompilasi aset frontend (mode development)
npm run dev
```

### Akses Halaman

| Halaman | URL | Keterangan |
|---------|-----|-----------|
| Beranda | `http://localhost:8000/` | Halaman publik |
| Login | `http://localhost:8000/login` | Form masuk |
| Register | `http://localhost:8000/register` | Daftar akun baru |
| Dashboard | `http://localhost:8000/dashboard` | Memerlukan login |
| Kursus | `http://localhost:8000/courses` | Daftar kursus |
| Leaderboard | `http://localhost:8000/leaderboard` | Peringkat pengguna |
| Profil | `http://localhost:8000/profile` | Profil pengguna |
| Admin | `http://localhost:8000/admin/dashboard` | Khusus admin |

### Kredensial Demo (dari Seeder)

| Email | Password | Peran |
|-------|----------|-------|
| `user1@example.com` | `password` | Siswa |
| `admin@example.com` | `password` | Admin |

### Menjalankan Tests

```bash
# Jalankan semua tests
./vendor/bin/phpunit

# Jalankan test suite tertentu
./vendor/bin/phpunit tests/Feature

# Jalankan dengan laporan coverage
./vendor/bin/phpunit --coverage-html coverage/
```

---

## 📁 Struktur Proyek

```
MOOCS-PROTOTIPE/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # 13 controller (Home, Auth, Dashboard, Courses, dll.)
│   │   ├── Middleware/         # Autentikasi, kontrol akses modul, CSRF
│   │   └── Kernel.php
│   ├── Models/                 # 7 Eloquent model
│   │   ├── User.php
│   │   ├── Course.php
│   │   ├── Module.php
│   │   ├── Enrollment.php
│   │   ├── ModuleProgress.php
│   │   ├── UserXpLog.php
│   │   └── InstructorApplication.php
│   ├── Policies/               # Kebijakan otorisasi
│   ├── Services/               # Logika bisnis
│   │   ├── ModuleGatingService.php
│   │   └── XpRewardService.php
│   └── Traits/
│       └── HasXpAndLeveling.php
├── database/
│   ├── migrations/             # 14 file migrasi
│   ├── seeders/                # Data sample
│   └── factories/              # Factory untuk testing
├── resources/
│   ├── views/                  # 30+ template Blade
│   ├── css/
│   └── js/
├── routes/
│   ├── web.php                 # 25+ rute web
│   └── api.php                 # 15+ endpoint API
├── tests/                      # Test PHPUnit
├── .env.example                # Template konfigurasi environment
├── composer.json
├── package.json
└── vite.config.js
```

---

## 🗄 Skema Database

### Tabel Utama (11 tabel)

| Tabel | Deskripsi | Kolom Penting |
|-------|-----------|---------------|
| `users` | Akun pengguna | `id`, `name`, `email`, `role`, `xp`, `level` |
| `courses` | Data kursus | `id`, `title`, `description`, `instructor_id`, `status` |
| `modules` | Modul kursus | `id`, `course_id`, `title`, `order`, `prerequisite_module_id` |
| `enrollments` | Pendaftaran pengguna ke kursus | `id`, `user_id`, `course_id`, `status` |
| `module_progress` | Progres modul per pengguna | `id`, `user_id`, `module_id`, `is_completed` |
| `user_xp_logs` | Riwayat transaksi XP | `id`, `user_id`, `amount`, `source`, `leveled_up` |
| `instructor_applications` | Pengajuan instruktur | `id`, `user_id`, `status` |

### Relasi Antar Model

```
User ──< Enrollment >── Course ──< Module
User ──< ModuleProgress >── Module
User ──< UserXpLog
User ──< Course (sebagai instruktur)
Module ──o Module (prasyarat, relasi self-referencing)
```

> Dokumentasi lengkap skema: [`DATABASE_SCHEMA_GAMIFICATION.md`](DATABASE_SCHEMA_GAMIFICATION.md)

---

## 📡 API Endpoints

**Base URL:** `http://localhost:8000/api`

### Endpoint Publik (Tanpa Autentikasi)

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/leaderboard/xp` | Top pengguna berdasarkan XP |
| `GET` | `/leaderboard/level` | Top pengguna berdasarkan level |
| `GET` | `/leaderboard/stats` | Statistik platform |
| `GET` | `/leaderboard/weekly` | Pengguna aktif dalam 7 hari terakhir |
| `GET` | `/leaderboard/course/{courseId}` | Leaderboard per kursus |
| `GET` | `/users/{userId}/xp` | Info XP pengguna tertentu |

### Endpoint Terproteksi (Memerlukan Token)

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/user/xp-summary` | Ringkasan XP dan level pengguna |
| `GET` | `/user/xp-logs` | Riwayat transaksi XP (dengan paginasi) |
| `GET` | `/user/xp-analytics` | Analitik XP pengguna |
| `GET` | `/user/rank` | Peringkat pengguna saat ini |
| `GET` | `/courses/{courseId}/modules` | Semua modul dalam kursus |
| `POST` | `/courses/{courseId}/modules/{moduleId}/complete` | Tandai modul selesai |
| `POST` | `/users/{userId}/award-xp` | Berikan XP ke pengguna (admin/instruktur) |

### Format Respons

```json
{
  "success": true,
  "data": { "..." },
  "message": "...",
  "pagination": { "..." }
}
```

### Autentikasi API

```bash
# Login untuk mendapatkan token
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'

# Gunakan token di header
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/api/user/xp-summary
```

> Dokumentasi lengkap API: [`API_DOCUMENTATION.md`](API_DOCUMENTATION.md)

---

## 👥 Peran Pengguna

| Peran | Kemampuan |
|-------|-----------|
| **Siswa** (`user`) | Mendaftar kursus, menyelesaikan modul, melihat leaderboard |
| **Instruktur** (`instructor`) | Semua kemampuan siswa + membuat dan mengelola kursus sendiri |
| **Admin** (`admin`) | Semua kemampuan + menyetujui kursus/instruktur, mengelola semua pengguna |

---

## 📚 Dokumentasi Tambahan

| File | Isi |
|------|-----|
| [`API_DOCUMENTATION.md`](API_DOCUMENTATION.md) | Dokumentasi lengkap REST API |
| [`DATABASE_SCHEMA_GAMIFICATION.md`](DATABASE_SCHEMA_GAMIFICATION.md) | Skema database detail |
| [`GATING_LOGIC_DOCS.md`](GATING_LOGIC_DOCS.md) | Dokumentasi logika akses modul (gating) |
| [`XP_AND_LEVELING_DOCS.md`](XP_AND_LEVELING_DOCS.md) | Dokumentasi sistem XP dan level |
| [`FRONTEND_SETUP.md`](FRONTEND_SETUP.md) | Panduan setup rute web, controller, dan views |
| [`INSTALLATION_CHECKLIST.md`](INSTALLATION_CHECKLIST.md) | Checklist instalasi langkah demi langkah |
| [`COMPLETE_SUMMARY.md`](COMPLETE_SUMMARY.md) | Ringkasan lengkap implementasi |
| [`QUICK_START.md`](QUICK_START.md) | Panduan cepat untuk logika gating |
| [`XP_LEVELING_QUICKSTART.md`](XP_LEVELING_QUICKSTART.md) | Panduan cepat sistem XP/leveling |

---

## 🔧 Pemecahan Masalah

### `SQLSTATE[HY000] [2002] Connection refused`

MySQL tidak berjalan atau kredensial `.env` salah.

```bash
# Verifikasi MySQL berjalan, lalu periksa .env:
# DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
```

### `No application encryption key has been generated`

```bash
php artisan key:generate
```

### Port 8000 sudah digunakan

```bash
php artisan serve --port=8001
# Akses di: http://localhost:8001
```

### Error saat migrasi

```bash
php artisan config:clear
php artisan migrate:fresh
```

### Aset CSS/JS tidak tampil

```bash
npm install && npm run build
```

### Perintah Artisan yang Berguna

```bash
# Bersihkan cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Database
php artisan migrate:status
php artisan migrate:rollback
php artisan migrate:fresh --seed

# Development
php artisan tinker
php artisan serve --port=8000
```

---

## 📄 Lisensi

Proyek ini menggunakan lisensi [MIT](https://opensource.org/licenses/MIT).

---

**Versi:** 1.0.0 | **Status:** ✅ Siap Produksi | **Diperbarui:** Maret 2026
