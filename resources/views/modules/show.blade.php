@extends('app')

@section('title', $module->title . ' - MOOC Platform')

@section('content')
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>
            {{ $module->title }}
        </h1>
        <a href="/courses/{{ $course->id }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Back to {{ $course->title }}
        </a>
    </div>
</div>

<div class="row">
    {{-- Main Content --}}
    <div class="col-md-9 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="module-content">
                    {!! $module->content !!}
                </div>
            </div>

            <div class="card-footer bg-light">
                @php
                    $userProgress = $canManageCourse ? null : Auth::user()->moduleProgress()
                        ->where('module_id', $module->id)
                        ->first();
                    $isCompleted = $userProgress?->is_completed ?? false;

                    // Get next and previous modules
                    $allModules = $course->modules()->orderBy('order')->pluck('id')->toArray();
                    $currentIndex = array_search($module->id, $allModules);
                    $previousModule = $currentIndex > 0 ? $allModules[$currentIndex - 1] : null;
                    $nextModule = $currentIndex < count($allModules) - 1 ? $allModules[$currentIndex + 1] : null;
                @endphp

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        @if($previousModule)
                            <a href="/courses/{{ $course->id }}/modules/{{ $previousModule }}" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left"></i> Previous Module
                            </a>
                        @endif
                    </div>

                    @if($canManageCourse)
                        <div class="d-flex gap-2">
                            <a href="{{ route('modules.edit', ['course' => $course, 'module' => $module]) }}" class="btn btn-outline-primary">
                                <i class="bi bi-pencil"></i> Edit Modul
                            </a>
                            <a href="{{ route('courses.show', $course) }}" class="btn btn-primary">
                                <i class="bi bi-gear"></i> Kelola Course
                            </a>
                        </div>
                    @elseif(!$isCompleted)
                        <button type="button" class="btn btn-success" id="completeBtn">
                            <i class="bi bi-check-circle"></i> Mark as Complete
                        </button>
                    @else
                        <span class="badge bg-success" style="font-size: 1rem;">
                            <i class="bi bi-check-circle"></i> Completed
                        </span>
                    @endif

                    <div>
                        @if($nextModule)
                            <a href="/courses/{{ $course->id }}/modules/{{ $nextModule }}" class="btn btn-outline-primary">
                                Next Module <i class="bi bi-arrow-right"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="col-md-3">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-body">
                <h5 class="card-title mb-3">Module Progress</h5>

                @php
                    $totalModules = $course->modules->count();
                    $completedModules = $canManageCourse ? 0 : Auth::user()->moduleProgress()
                        ->whereHas('module', function($q) use($course) {
                            $q->where('course_id', $course->id);
                        })
                        ->where('is_completed', true)
                        ->count();
                    $progress = $totalModules > 0 ? round(($completedModules / $totalModules) * 100) : 0;
                @endphp

                <div class="progress mb-3" style="height: 10px;">
                    <div class="progress-bar bg-success" role="progressbar" 
                        style="width: {{ $progress }}%;"
                        aria-valuenow="{{ $progress }}" 
                        aria-valuemin="0" aria-valuemax="100"></div>
                </div>

                <p class="small text-muted">
                    {{ $completedModules }} of {{ $totalModules }} modules completed
                </p>

                <hr>

                <h5 class="card-subtitle mb-3">All Modules</h5>

                <div class="list-group list-group-flush">
                    @foreach($course->modules()->orderBy('order')->get() as $m)
                        @php
                            $mProgress = $canManageCourse ? null : Auth::user()->moduleProgress()
                                ->where('module_id', $m->id)
                                ->first();
                            $mCompleted = $mProgress?->is_completed ?? false;
                        @endphp

                        <a href="/courses/{{ $course->id }}/modules/{{ $m->id }}" 
                            class="list-group-item list-group-item-action @if($m->id === $module->id) active @endif">
                            <div class="d-flex align-items-center">
                                @if($mCompleted)
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                @else
                                    <i class="bi bi-circle me-2"></i>
                                @endif
                                <span class="small">{{ Str::limit($m->title, 25) }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title mb-3">Info</h5>
                <small class="text-muted">
                    <strong>Course:</strong> {{ $course->title }}<br>
                    <strong>Module:</strong> {{ $module->title }}<br>
                </small>
                @if($module->prerequisite)
                    <br>
                    <small class="text-warning">
                        <i class="bi bi-asterisk"></i> Requires: {{ $module->prerequisite->title }}
                    </small>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.module-content {
    line-height: 1.8;
    color: #333;
    font-size: 1.05rem;
}

.module-content h2,
.module-content h3,
.module-content h4 {
    margin-top: 20px;
    margin-bottom: 10px;
    color: var(--primary);
    font-weight: 600;
}

.module-content p {
    margin-bottom: 15px;
}

.module-content ul,
.module-content ol {
    margin-bottom: 15px;
    margin-left: 20px;
}

.module-content li {
    margin-bottom: 8px;
}

.module-content code {
    background: #f5f5f5;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: 'Courier New', Courier, monospace;
}

.module-content pre {
    background: #f5f5f5;
    padding: 15px;
    border-radius: 8px;
    overflow-x: auto;
}
</style>

<script>
document.getElementById('completeBtn')?.addEventListener('click', async function() {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const button = this;
    const originalButtonMarkup = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';
    
    try {
        const response = await fetch('/courses/{{ $course->id }}/modules/{{ $module->id }}/complete', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            }
        });

        const data = await response.json().catch(() => ({}));

        if (response.ok) {
            const queuedToasts = [];
            const moduleAward = data.xp_awards?.module_completion;
            const courseAward = data.xp_awards?.course_completion;
            const levelUpAwards = [moduleAward, courseAward].filter(function (award) {
                return award?.leveled_up;
            });
            const finalLevelUpAward = levelUpAwards.reduce(function (highestAward, currentAward) {
                if (!highestAward) {
                    return currentAward;
                }

                return currentAward.current_level > highestAward.current_level ? currentAward : highestAward;
            }, null);

            if (moduleAward) {
                queuedToasts.push({
                    title: 'XP bertambah',
                    message: `Anda mendapatkan +${moduleAward.current_xp - moduleAward.previous_xp} XP dari modul ini.`,
                    variant: 'success',
                    icon: 'bi-lightning-charge-fill',
                });
            }

            if (finalLevelUpAward) {
                queuedToasts.push({
                    title: 'Level up!',
                    message: `Selamat! Anda naik ke level ${finalLevelUpAward.current_level}.`,
                    variant: 'warning',
                    icon: 'bi-stars',
                    delay: 6500,
                });

                window.queueAppCelebration?.({
                    type: 'level-up',
                    level: finalLevelUpAward.current_level,
                });
            }

            if (courseAward) {
                queuedToasts.push({
                    title: 'Course selesai',
                    message: `Hebat! Anda menuntaskan course ini dan mendapat +${courseAward.current_xp - courseAward.previous_xp} XP bonus.`,
                    variant: 'primary',
                    icon: 'bi-trophy-fill',
                    delay: 6500,
                });
            }

            if (queuedToasts.length === 0 && data.message) {
                queuedToasts.push({
                    title: 'Progress tersimpan',
                    message: data.message,
                    variant: 'success',
                    icon: 'bi-check-circle-fill',
                });
            }

            window.queueAppToasts?.(queuedToasts);
            window.location.reload();
        } else {
            window.showAppToast?.({
                title: 'Gagal menyelesaikan modul',
                message: data.message || 'Silakan coba lagi.',
                variant: 'danger',
                icon: 'bi-exclamation-triangle-fill',
            });
        }
    } catch (error) {
        console.error('Error:', error);
        window.showAppToast?.({
            title: 'Terjadi kesalahan',
            message: 'Permintaan tidak dapat diproses sekarang.',
            variant: 'danger',
            icon: 'bi-wifi-off',
        });
    } finally {
        button.disabled = false;
        button.innerHTML = originalButtonMarkup;
    }
});
</script>
@endsection
