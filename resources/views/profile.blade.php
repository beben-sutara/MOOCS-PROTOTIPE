@extends('app')

@section('title', 'Profile - MoocsPangarti')

@section('content')
<div class="soft-panel p-4 p-lg-5 mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <span class="market-badge mb-3">
                <i class="bi bi-person-circle"></i> Profile center
            </span>
            <h1 class="section-title mb-2">Kelola profil dan keamanan akun Anda.</h1>
            <p class="section-subtitle mb-0">Halaman profil kini mengikuti visual baru agar konsisten dengan dashboard, course marketplace, dan workflow instructor.</p>
        </div>
        <a href="/dashboard" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<div class="row">
    {{-- Profile Card --}}
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body text-center p-4">
                <div class="feature-icon mx-auto mb-3">
                    <i class="bi bi-person-badge"></i>
                </div>
                <div class="level-badge mb-3" style="font-size: 2.2rem;">
                    {{ Auth::user()->level }}
                </div>
                
                <h4>{{ Auth::user()->name }}</h4>
                <p class="text-muted">Level {{ Auth::user()->level }} User</p>

                <hr>

                <div class="mb-3">
                    <p class="text-muted mb-2">Total XP</p>
                    <h3 class="text-primary">{{ Auth::user()->xp }}</h3>
                </div>

                <div class="mb-3">
                    <p class="text-muted mb-2">Global Rank</p>
                    <h3 class="text-warning">{{ '#' . ($userRank['rank'] ?? 'N/A') }}</h3>
                </div>

                <hr>

                <div class="text-start">
                    <small class="text-muted d-block mb-2">
                        <i class="bi bi-envelope"></i> {{ Auth::user()->email }}
                    </small>
                    @if(Auth::user()->phone)
                        <small class="text-muted d-block mb-2">
                            <i class="bi bi-telephone"></i> {{ Auth::user()->phone }}
                        </small>
                    @endif
                    <small class="text-muted d-block mb-2">
                        <i class="bi bi-calendar-event"></i> Joined {{ Auth::user()->created_at?->format('d M Y') ?? '-' }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Profile Form --}}
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="bi bi-pencil-square text-primary"></i> Edit Profile
                </h5>
            </div>

            <div class="card-body">
                <form method="POST" action="/profile/update">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                            id="name" name="name" value="{{ Auth::user()->name }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                            id="email" name="email" value="{{ Auth::user()->email }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                            id="phone" name="phone" value="{{ Auth::user()->phone ?? '' }}" placeholder="+62...">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password (to confirm changes)</label>
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                            id="current_password" name="current_password" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Save Changes
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Change Password Section --}}
        <div class="card mt-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="bi bi-shield-lock text-warning"></i> Change Password
                </h5>
            </div>

            <div class="card-body">
                <form method="POST" action="/profile/change-password">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="old_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control @error('old_password') is-invalid @enderror" 
                            id="old_password" name="old_password" required>
                        @error('old_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control @error('new_password') is-invalid @enderror" 
                            id="new_password" name="new_password" required>
                        @error('new_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">At least 6 characters</small>
                    </div>

                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" 
                            id="new_password_confirmation" name="new_password_confirmation" required>
                    </div>

                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-key"></i> Update Password
                    </button>
                </form>
            </div>
        </div>

        @if(Auth::user()->role === 'user')
            <div class="card mt-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-person-workspace text-primary"></i> Instructor Application
                    </h5>
                    @if($latestInstructorApplication)
                        <span class="badge {{ $latestInstructorApplication->status_badge_class }}">{{ $latestInstructorApplication->status_label }}</span>
                    @endif
                </div>
                <div class="card-body">
                    @if($latestInstructorApplication)
                        <p class="text-muted">
                            Pengajuan terakhir Anda dikirim {{ $latestInstructorApplication->created_at->diffForHumans() }} dengan bidang keahlian <strong>{{ $latestInstructorApplication->expertise }}</strong>.
                        </p>
                        @if($latestInstructorApplication->admin_notes)
                            <div class="alert {{ $latestInstructorApplication->status === 'rejected' ? 'alert-danger' : 'alert-info' }}">
                                <strong>Catatan admin:</strong> {{ $latestInstructorApplication->admin_notes }}
                            </div>
                        @endif
                    @else
                        <p class="text-muted">Belum ada pengajuan instructor. Anda bisa mengajukan diri untuk mulai membuat dan mengelola course.</p>
                    @endif

                    <a href="{{ route('instructor.apply') }}" class="btn btn-primary">
                        <i class="bi bi-send"></i>
                        {{ $latestInstructorApplication?->status === 'pending' ? 'Lihat Status Pengajuan' : 'Ajukan Menjadi Instructor' }}
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- XP Analytics --}}
<div class="mt-5 mb-4">
    <h2 class="section-title mb-2">
        <i class="bi bi-graph-up text-primary"></i> XP Analytics
    </h2>
    <p class="section-subtitle mb-0">Ringkasan progres pembelajaran dan aktivitas XP Anda.</p>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body p-4">
                <p class="text-muted mb-2">Progress to Next Level</p>
                <h3 class="text-primary mb-3">{{ Auth::user()->getXpProgress() }}%</h3>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar" role="progressbar" 
                        data-progress-width="{{ Auth::user()->getXpProgress() }}"
                        aria-valuenow="{{ Auth::user()->getXpProgress() }}" 
                        aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <small class="text-muted mt-2 d-block">
                    {{ Auth::user()->getXpInCurrentLevel() }} / 
                    {{ Auth::user()->next_level_xp - Auth::user()->getTotalXpForCurrentLevel() }} XP
                </small>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body p-4">
                <p class="text-muted mb-2">XP Until Next Level</p>
                <h3 class="text-success mb-3">{{ Auth::user()->getXpUntilNextLevel() }}</h3>
                <small class="text-muted">Keep learning to advance!</small>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body p-4">
                <p class="text-muted mb-2">Courses Enrolled</p>
                <h3 class="text-info mb-3">{{ Auth::user()->enrollments()->count() }}</h3>
                <small class="text-muted">{{ Auth::user()->enrollments()->where('status', 'completed')->count() }} completed</small>
            </div>
        </div>
    </div>
</div>

{{-- Recent Activity --}}
<div class="mt-4 mb-4">
    <h2 class="section-title mb-2">
        <i class="bi bi-clock-history text-primary"></i> Recent Activity
    </h2>
    <p class="section-subtitle mb-0">20 aktivitas XP terakhir dari perjalanan belajar Anda.</p>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Source</th>
                            <th>XP Earned</th>
                            <th>Level Change</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(Auth::user()->xpLogs()->latest()->take(20)->get() as $log)
                            <tr>
                                <td>
                                    <span class="badge bg-info">
                                        {{ ucfirst(str_replace('_', ' ', $log->source)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-success">
                                        +{{ $log->amount }} XP
                                    </span>
                                </td>
                                <td>
                                    @if($log->leveled_up)
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-arrow-up"></i> Level {{ $log->current_level }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $log->created_at->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <p class="text-muted mb-0">Belum ada aktivitas</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
