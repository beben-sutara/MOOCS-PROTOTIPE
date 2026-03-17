@extends('app')

@section('title', 'Leaderboard - MOOC Platform')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>
        <i class="bi bi-trophy"></i> Leaderboard
    </h1>
    <a href="/" class="btn btn-outline-primary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

{{-- Filter Tabs --}}
<ul class="nav nav-pills mb-4" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="xp-tab" data-bs-toggle="tab" data-bs-target="#xp" type="button" role="tab" aria-controls="xp" aria-selected="true">
            <i class="bi bi-lightning"></i> By XP
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="level-tab" data-bs-toggle="tab" data-bs-target="#level" type="button" role="tab" aria-controls="level" aria-selected="false">
            <i class="bi bi-award"></i> By Level
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="weekly-tab" data-bs-toggle="tab" data-bs-target="#weekly" type="button" role="tab" aria-controls="weekly" aria-selected="false">
            <i class="bi bi-calendar-week"></i> Weekly
        </button>
    </li>
</ul>

<div class="tab-content">
    {{-- XP Leaderboard --}}
    <div class="tab-pane fade show active" id="xp" role="tabpanel" aria-labelledby="xp-tab">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 100px;">Rank</th>
                            <th>User</th>
                            <th class="text-center" style="width: 120px;">Level</th>
                            <th class="text-end" style="width: 150px;">Total XP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($xpLeaderboard as $index => $user)
                            <tr class="@if(Auth::check() && Auth::user()->id === $user['id']) table-primary @endif">
                                <td>
                                    <div class="display-flex align-items-center">
                                        @if($index === 0)
                                            <span class="badge bg-warning" style="font-size: 1rem;">
                                                <i class="bi bi-trophy"></i> 1st
                                            </span>
                                        @elseif($index === 1)
                                            <span class="badge bg-secondary" style="font-size: 1rem;">
                                                <i class="bi bi-award"></i> 2nd
                                            </span>
                                        @elseif($index === 2)
                                            <span class="badge bg-danger" style="font-size: 1rem;">
                                                <i class="bi bi-award"></i> 3rd
                                            </span>
                                        @else
                                            <span class="badge bg-light text-dark">#{{ $index + 1 }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <strong>{{ $user['name'] }}</strong><br>
                                            <small class="text-muted">{{ $user['email'] }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="level-badge">{{ $user['level'] }}</div>
                                </td>
                                <td class="text-end">
                                    <strong class="text-primary">{{ number_format($user['xp']) }}</strong> XP
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <p class="text-muted mb-0">Belum ada data</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Level Leaderboard --}}
    <div class="tab-pane fade" id="level" role="tabpanel" aria-labelledby="level-tab">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 100px;">Rank</th>
                            <th>User</th>
                            <th class="text-center" style="width: 120px;">Level</th>
                            <th class="text-end" style="width: 150px;">Total XP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($levelLeaderboard as $index => $user)
                            <tr class="@if(Auth::check() && Auth::user()->id === $user['id']) table-primary @endif">
                                <td>
                                    <div class="display-flex align-items-center">
                                        @if($index === 0)
                                            <span class="badge bg-warning" style="font-size: 1rem;">
                                                <i class="bi bi-trophy"></i> 1st
                                            </span>
                                        @elseif($index === 1)
                                            <span class="badge bg-secondary" style="font-size: 1rem;">
                                                <i class="bi bi-award"></i> 2nd
                                            </span>
                                        @elseif($index === 2)
                                            <span class="badge bg-danger" style="font-size: 1rem;">
                                                <i class="bi bi-award"></i> 3rd
                                            </span>
                                        @else
                                            <span class="badge bg-light text-dark">#{{ $index + 1 }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <strong>{{ $user['name'] }}</strong><br>
                                            <small class="text-muted">{{ $user['email'] }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="level-badge">{{ $user['level'] }}</div>
                                </td>
                                <td class="text-end">
                                    <strong class="text-primary">{{ number_format($user['xp']) }}</strong> XP
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <p class="text-muted mb-0">Belum ada data</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Weekly Leaderboard --}}
    <div class="tab-pane fade" id="weekly" role="tabpanel" aria-labelledby="weekly-tab">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 100px;">Rank</th>
                            <th>User</th>
                            <th class="text-center" style="width: 120px;">Level</th>
                            <th class="text-end" style="width: 150px;">This Week XP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($weeklyLeaderboard as $index => $user)
                            <tr class="@if(Auth::check() && Auth::user()->id === $user['id']) table-primary @endif">
                                <td>
                                    <div class="display-flex align-items-center">
                                        @if($index === 0)
                                            <span class="badge bg-warning" style="font-size: 1rem;">
                                                <i class="bi bi-trophy"></i> 1st
                                            </span>
                                        @elseif($index === 1)
                                            <span class="badge bg-secondary" style="font-size: 1rem;">
                                                <i class="bi bi-award"></i> 2nd
                                            </span>
                                        @elseif($index === 2)
                                            <span class="badge bg-danger" style="font-size: 1rem;">
                                                <i class="bi bi-award"></i> 3rd
                                            </span>
                                        @else
                                            <span class="badge bg-light text-dark">#{{ $index + 1 }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <strong>{{ $user['name'] }}</strong><br>
                                            <small class="text-muted">{{ $user['email'] }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="level-badge">{{ $user['level'] }}</div>
                                </td>
                                <td class="text-end">
                                    <strong class="text-success">+{{ number_format($user['weekly_xp'] ?? 0) }}</strong> XP
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <p class="text-muted mb-0">Belum ada data</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Your Stats Section --}}
@if(Auth::check())
    <hr class="my-5">
    <div class="row">
        <div class="col-md-12">
            <h4 class="mb-4">
                <i class="bi bi-person-badge"></i> Your Stats
            </h4>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="level-badge" style="font-size: 2rem; margin-bottom: 15px;">
                        {{ Auth::user()->level }}
                    </div>
                    <p class="text-muted mb-0">Your Level</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="text-primary">
                        <i class="bi bi-lightning"></i> {{ Auth::user()->xp }}
                    </h3>
                    <p class="text-muted mb-0">Total XP</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="text-warning">
                        <i class="bi bi-hash"></i> #{{ $userRank['rank'] ?? 'N/A' }}
                    </h3>
                    <p class="text-muted mb-0">Your Rank</p>
                </div>
            </div>
        </div>
    </div>
@endif

<style>
.level-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    font-weight: bold;
    font-size: 1.25rem;
}
</style>
@endsection
