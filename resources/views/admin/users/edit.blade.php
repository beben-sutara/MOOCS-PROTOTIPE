@extends('app')

@section('title', 'Edit Pengguna Admin - MoocsPangarti')

@section('content')
<div class="soft-panel p-4 p-lg-5 mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <span class="market-badge mb-3">
                <i class="bi bi-person-gear"></i> Edit pengguna admin
            </span>
            <h1 class="section-title mb-2">Kelola akun {{ $managedUser->name }} dengan aman.</h1>
            <p class="section-subtitle mb-0">Admin bisa memperbarui data profil dan peran pengguna. Proteksi bawaan mencegah hilangnya admin terakhir maupun self-demotion akun admin yang sedang login.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke daftar pengguna
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <span class="market-badge mb-3">
                        <i class="bi bi-person-badge"></i> Profil user
                    </span>
                    <div class="feature-icon mx-auto mb-3">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div class="level-badge mb-3" style="font-size: 2rem;">
                        Lv {{ $managedUser->level }}
                    </div>
                    <h4 class="mb-1">{{ $managedUser->name }}</h4>
                    <p class="text-muted mb-2">{{ $managedUser->email }}</p>
                    @if($managedUser->phone)
                        <p class="small text-muted mb-3">
                            <i class="bi bi-telephone"></i> {{ $managedUser->phone }}
                        </p>
                    @endif

                    <div class="d-flex justify-content-center flex-wrap gap-2">
                        <span class="badge {{ $managedUser->role === 'admin' ? 'bg-danger' : ($managedUser->role === 'instructor' ? 'bg-primary' : 'bg-secondary') }}">
                            {{ $managedUser->role === 'admin' ? 'Admin' : ($managedUser->role === 'instructor' ? 'Instruktur' : 'Pengguna') }}
                        </span>
                        <span class="badge text-bg-light border">Gabung {{ $managedUser->created_at?->format('d M Y') ?? '-' }}</span>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <div class="surface-muted rounded-4 p-3 h-100">
                            <div class="small text-muted mb-1">XP</div>
                            <div class="h4 mb-0">{{ number_format($managedUser->xp) }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="surface-muted rounded-4 p-3 h-100">
                            <div class="small text-muted mb-1">Peringkat global</div>
                            <div class="h4 mb-0">#{{ $userRank['rank'] ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="surface-muted rounded-4 p-3 h-100">
                            <div class="small text-muted mb-1">Kursus dibuat</div>
                            <div class="h4 mb-0">{{ $courseCount }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="surface-muted rounded-4 p-3 h-100">
                            <div class="small text-muted mb-1">Pendaftaran</div>
                            <div class="h4 mb-0">{{ $enrollmentCount }}</div>
                        </div>
                    </div>
                </div>

                <div class="surface-muted rounded-4 p-3 text-start">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="fw-semibold">Ringkasan aktivitas</div>
                            <div class="small text-muted">Ringkasan singkat akun pengguna ini.</div>
                        </div>
                        <i class="bi bi-activity text-primary"></i>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Pendaftaran selesai</span>
                        <strong>{{ $completedEnrollmentCount }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Peran aktif</span>
                        <strong>{{ $managedUser->role === 'admin' ? 'Admin' : ($managedUser->role === 'instructor' ? 'Instruktur' : 'Pengguna') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">ID pengguna</span>
                        <strong>#{{ $managedUser->id }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8 mb-4">
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-pencil-square text-primary"></i> Perbarui pengguna</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.users.update', $managedUser) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nama lengkap</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $managedUser->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Alamat email</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $managedUser->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Nomor telepon</label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone', $managedUser->phone) }}" class="form-control @error('phone') is-invalid @enderror" placeholder="+62...">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label">Peran</label>
                            <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                                <option value="user" @selected(old('role', $managedUser->role) === 'user')>Pengguna</option>
                                <option value="instructor" @selected(old('role', $managedUser->role) === 'instructor')>Instruktur</option>
                                <option value="admin" @selected(old('role', $managedUser->role) === 'admin')>Admin</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle"></i> Perubahan peran berlaku langsung. Akun admin yang sedang login tidak bisa menurunkan peran dirinya sendiri, dan sistem selalu menjaga minimal satu admin aktif.
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Simpan perubahan pengguna
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-lightning text-primary"></i> Aktivitas XP terbaru</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Sumber</th>
                            <th>XP</th>
                            <th>Level</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentXpLogs as $log)
                            <tr>
                                <td><span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $log->source)) }}</span></td>
                                <td><span class="badge bg-success">+{{ $log->amount }} XP</span></td>
                                <td>{{ $log->previous_level }} -> {{ $log->current_level }}</td>
                                <td>{{ $log->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Belum ada aktivitas XP untuk user ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($managedUser->role === 'instructor' || $instructedCourses->isNotEmpty())
            <div class="card mt-4">
                <div class="card-header bg-white d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <div>
                        <h5 class="mb-1"><i class="bi bi-journal-richtext text-primary"></i> Daftar kursus instruktur</h5>
                        <p class="text-muted small mb-0">Daftar kursus yang dimiliki {{ $managedUser->name }}, lengkap dengan status dan aktivitas belajarnya.</p>
                    </div>
                    <span class="badge bg-dark">{{ $instructedCourses->count() }} kursus</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Kursus</th>
                                <th>Status</th>
                                <th>Modul</th>
                                <th>Pendaftaran</th>
                                <th>Update terakhir</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($instructedCourses as $course)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div style="width: 96px; min-width: 96px;" class="overflow-hidden rounded border">
                                                @include('courses.thumbnail', ['course' => $course, 'height' => '68px'])
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $course->title }}</div>
                                                @if($course->description)
                                                    <div class="text-muted small mb-2">{{ \Illuminate\Support\Str::limit($course->description, 90) }}</div>
                                                @endif
                                                <div class="d-flex flex-wrap gap-2">
                                                    <span class="badge rounded-pill text-bg-light border">ID #{{ $course->id }}</span>
                                                    <span class="badge rounded-pill text-bg-light border">Dibuat {{ $course->created_at->format('d M Y') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $course->status_badge_class }}">{{ $course->status_label }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $course->modules_count }}</div>
                                        <div class="small text-muted">modul tersedia</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $course->enrollments_count }}</div>
                                        <div class="small text-muted">pendaftar aktif</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $course->updated_at->diffForHumans() }}</div>
                                        <div class="small text-muted">{{ $course->updated_at->format('d M Y H:i') }}</div>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                            <a href="{{ route('courses.show', $course) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-eye"></i> Lihat
                                            </a>
                                            <a href="{{ route('courses.edit', $course) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i> Ubah
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <div class="py-2">
                                            <div class="fw-semibold text-dark mb-1">Instruktur ini belum memiliki kursus.</div>
                                            <div class="small">Saat instruktur mulai membuat kursus, daftarnya akan muncul di sini.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
