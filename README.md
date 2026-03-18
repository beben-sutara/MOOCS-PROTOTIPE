<div align="center">

# 🎓 MoocsPangarti

**Platform Pembelajaran Online (LMS) berbasis Laravel**

[![PHP](https://img.shields.io/badge/PHP-8.0.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-9.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

MoocsPangarti adalah platform **Massive Open Online Course (MOOC)** yang dibangun dengan Laravel.  
Pengguna dapat mendaftar kursus, menyelesaikan modul, mendapatkan XP, bersaing di leaderboard, dan meraih sertifikat kelulusan.

[📦 Instalasi](#-instalasi) • [✨ Fitur](#-fitur) • [📡 API](#-api-reference) • [🗺️ Roadmap](#-roadmap)

</div>

---

## ✨ Fitur

### 📚 Pembelajaran
- ✅ **Kursus** — Buat, kelola, dan ikuti kursus dengan konten lengkap
- ✅ **Modul & Seksi** — Atur modul ke dalam bab/seksi yang terstruktur
- ✅ **Prerequisite Modul** — Akses modul berikutnya hanya setelah menyelesaikan modul sebelumnya
- ✅ **Pelacakan Progress** — Progress per modul dan per kursus dicatat secara otomatis
- ✅ **Multi-tipe Konten** — Teks, YouTube, iFrame, Video DRM, File, Audio, Quiz, Coaching, Tag

### 🏆 Gamifikasi
- ✅ **XP (Experience Points)** — Dapatkan XP setiap kali menyelesaikan modul atau kursus
- ✅ **Sistem Level** — Naik level otomatis berdasarkan total XP yang dikumpulkan
- ✅ **Leaderboard** — Papan peringkat global berdasarkan XP, Level, mingguan, dan per kursus
- ✅ **Riwayat XP** — Log lengkap semua transaksi XP dengan alasannya

### 📜 Sertifikat
- ✅ **Sertifikat Otomatis** — Diterbitkan otomatis saat kursus diselesaikan
- ✅ **Nomor Unik** — Setiap sertifikat memiliki nomor verifikasi unik
- ✅ **Verifikasi Publik** — URL publik untuk memverifikasi keaslian sertifikat
- ✅ **Template PDF** — Sertifikat dapat diunduh dalam format yang rapi

### 📝 Kuis & Asesmen
- ✅ **Buat Kuis** — Tambahkan pertanyaan pilihan ganda ke modul
- ✅ **Manajemen Soal** — Buat, edit, hapus soal dan pilihan jawaban
- ✅ **Import CSV** — Import soal kuis secara massal dari file CSV

### 👥 Manajemen Pengguna
- ✅ **Registrasi & Login** — Autentikasi berbasis sesi dengan "ingat saya"
- ✅ **3 Peran Pengguna** — Student, Instructor, Admin
- ✅ **Pengajuan Instruktur** — Pengguna bisa mengajukan diri menjadi instruktur
- ✅ **Manajemen Profil** — Edit profil dan ubah kata sandi

### ⚙️ Admin & Instruktur
- ✅ **Dashboard Admin** — Statistik platform, kelola pengguna & kursus
- ✅ **Persetujuan Kursus** — Admin approve/reject kursus sebelum dipublikasikan
- ✅ **Persetujuan Instruktur** — Admin approve/reject pengajuan instruktur
- ✅ **Peserta Kursus** — Lihat daftar semua pengguna yang terdaftar di kursus

### 📡 REST API
- ✅ **15 Endpoint API** — Leaderboard, XP, modul, progress (publik & protected)
- ✅ **Laravel Sanctum** — Autentikasi API berbasis token

---

## 🛠️ Tech Stack

| Kategori | Teknologi |
|----------|-----------|
| **Backend** | PHP 8.0.2+, Laravel 9.19 |
| **Frontend** | Blade, Bootstrap 5, Vite 4 |
| **Database** | MySQL / MariaDB |
| **Auth** | Laravel Sanctum, Session-based |
| **PDF** | barryvdh/laravel-dompdf 2.2 |
| **HTTP Client** | Axios 1.1.2 |
| **Icons** | Bootstrap Icons |
| **Testing** | PHPUnit 9.5 |
| **Dev Tools** | Laravel Pint, Laravel Sail |

---

## 👤 Peran Pengguna

| Peran | Kemampuan |
|-------|-----------|
| **Student** | Daftar kursus, selesaikan modul, kumpulkan XP, lihat leaderboard, unduh sertifikat |
| **Instructor** | Semua fitur Student + buat/kelola kursus, modul, seksi, soal kuis, lihat peserta |
| **Admin** | Semua fitur Instructor + kelola semua pengguna, approve kursus & instruktur |

---

## 📦 Instalasi

### Prasyarat
- PHP >= 8.0.2
- Composer
- MySQL / MariaDB
- Node.js & NPM

### Langkah Instalasi

```bash
# 1. Clone repository
git clone https://github.com/beben-sutara/MOOCS-PROTOTIPE.git
cd MOOCS-PROTOTIPE

# 2. Install dependensi PHP
composer install

# 3. Install dependensi frontend
npm install

# 4. Salin file konfigurasi
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Buat database MySQL
# mysql -u root -p
# > CREATE DATABASE moocs;

# 7. Konfigurasi database di .env
# DB_DATABASE=moocs
# DB_USERNAME=root
# DB_PASSWORD=your_password

# 8. Jalankan migrasi & seeder
php artisan migrate --seed

# 9. Build aset frontend
npm run build

# 10. Jalankan server
php artisan serve
```

Buka browser: **http://localhost:8000**

---

## ⚙️ Konfigurasi `.env`

```ini
APP_NAME=MoocsPangarti
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=moocs
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

---

## 🚀 Penggunaan

### URL Utama

| Halaman | URL |
|---------|-----|
| Beranda | `http://localhost:8000/` |
| Login | `http://localhost:8000/login` |
| Registrasi | `http://localhost:8000/register` |
| Dashboard | `http://localhost:8000/dashboard` |
| Daftar Kursus | `http://localhost:8000/courses` |
| Leaderboard | `http://localhost:8000/leaderboard` |
| Profil | `http://localhost:8000/profile` |
| Sertifikat | `http://localhost:8000/certificates` |
| Admin Dashboard | `http://localhost:8000/admin/dashboard` |

### Akun Default (Seeder)

| Role | Email | Password |
|------|-------|----------|
| Student | `user1@example.com` | `password` |
| Instructor | `instructor@example.com` | `password` |
| Admin | `admin@example.com` | `password` |

---

## 📡 API Reference

### Endpoint Publik (Tanpa Autentikasi)

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/leaderboard/xp` | Top 100 pengguna berdasarkan XP |
| GET | `/api/leaderboard/level` | Top 100 pengguna berdasarkan Level |
| GET | `/api/leaderboard/stats` | Statistik platform |
| GET | `/api/leaderboard/weekly` | Peringkat mingguan |
| GET | `/api/leaderboard/course/{courseId}` | Leaderboard per kursus |
| GET | `/api/leaderboard/level/{level}` | Pengguna berdasarkan level |
| GET | `/api/users/{user}/xp` | Info XP pengguna tertentu |
| GET | `/verify/{number}` | Verifikasi sertifikat |

### Endpoint Protected (Membutuhkan Token Sanctum)

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/user` | Info pengguna yang sedang login |
| GET | `/api/user/xp-summary` | Ringkasan XP pengguna |
| GET | `/api/user/xp-logs` | Riwayat transaksi XP |
| GET | `/api/user/rank` | Peringkat global pengguna |
| POST | `/api/users/{user}/award-xp` | Berikan XP (admin/instruktur) |
| GET | `/api/courses/{course}/modules` | Daftar modul kursus |
| POST | `/api/courses/{course}/modules/{module}/complete` | Tandai modul selesai |

---

## 📁 Struktur Folder

```
mooc-platform/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # CoursesController, ModuleController, dst.
│   │   ├── Middleware/       # CheckModuleAccess, EnsureRegularUser
│   │   └── Kernel.php
│   ├── Models/               # User, Course, Module, Section, Certificate, dst.
│   ├── Policies/             # ModulePolicy
│   └── Services/             # ModuleGatingService, CertificateService
├── database/
│   ├── migrations/           # Semua migrasi database
│   └── seeders/              # Data awal
├── resources/
│   └── views/
│       ├── app.blade.php     # Layout utama
│       ├── admin/            # Halaman admin
│       ├── auth/             # Login & register
│       ├── certificates/     # Sertifikat
│       ├── courses/          # Kursus
│       ├── modules/          # Modul
│       ├── questions/        # Soal kuis
│       └── sections/         # Seksi
├── routes/
│   ├── web.php               # Route web
│   └── api.php               # Route API
└── tests/
    └── Feature/              # 50+ test cases
```

---

## 🗄️ Database

| Tabel | Deskripsi |
|-------|-----------|
| `users` | Akun pengguna (role, xp, level) |
| `courses` | Data kursus |
| `modules` | Modul pembelajaran |
| `sections` | Bab/seksi dalam kursus |
| `enrollments` | Pendaftaran pengguna ke kursus |
| `module_progress` | Progress modul per pengguna |
| `user_xp_logs` | Riwayat transaksi XP |
| `questions` | Soal kuis |
| `question_options` | Pilihan jawaban kuis |
| `certificates` | Sertifikat yang diterbitkan |
| `instructor_applications` | Pengajuan instruktur |

---

## 🧪 Testing

```bash
# Jalankan semua test
php artisan test

# Jalankan test spesifik
php artisan test --filter=LeaderboardAccessTest
```

---

## 🗺️ Roadmap

- [ ] Notifikasi real-time (WebSockets / Pusher)
- [ ] Diskusi & komentar per modul
- [ ] Sistem pembayaran kursus premium
- [ ] Mobile app (React Native / Flutter)
- [ ] Rekomendasi kursus berbasis AI
- [ ] Jalur pembelajaran adaptif
- [ ] Integrasi hosting video
- [ ] Mode pembelajaran offline

---

## 🤝 Kontribusi

1. Fork repository ini
2. Buat branch fitur baru: `git checkout -b fitur/nama-fitur`
3. Commit perubahan: `git commit -m 'Tambah fitur ...'`
4. Push ke branch: `git push origin fitur/nama-fitur`
5. Buat Pull Request

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah [MIT License](https://opensource.org/licenses/MIT).

---

<div align="center">

Dibuat dengan ❤️ menggunakan [Laravel](https://laravel.com)

</div>
