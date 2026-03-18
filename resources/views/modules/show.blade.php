@extends('app')

@section('title', $module->title . ' - MoocsPangarti')

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
                    @php
                        $contentDecoded = null;
                        if ($module->content) {
                            $contentDecoded = json_decode($module->content, true);
                        }
                    @endphp

                    {{-- TYPE: TEXT --}}
                    @if($module->type === \App\Models\Module::TYPE_TEXT)
                        @include('partials.editorjs-renderer', ['content' => $module->content])

                    {{-- TYPE: YOUTUBE --}}
                    @elseif($module->type === \App\Models\Module::TYPE_YOUTUBE)
                        @php
                            $ytUrl = $contentDecoded['url'] ?? '';
                            preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $ytUrl, $ytMatch);
                            $videoId = $ytMatch[1] ?? null;
                        @endphp
                        @if($videoId)
                            <div class="ratio ratio-16x9">
                                <iframe src="https://www.youtube.com/embed/{{ $videoId }}"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen></iframe>
                            </div>
                        @else
                            <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>Link YouTube tidak valid.</div>
                        @endif

                    {{-- TYPE: IFRAME --}}
                    @elseif($module->type === \App\Models\Module::TYPE_IFRAME)
                        @php $iframeCode = $contentDecoded['code'] ?? ''; @endphp
                        @if($iframeCode)
                            <div class="ratio ratio-16x9">{!! $iframeCode !!}</div>
                        @else
                            <div class="alert alert-warning">Konten iframe tidak tersedia.</div>
                        @endif

                    {{-- TYPE: FILE --}}
                    @elseif($module->type === \App\Models\Module::TYPE_FILE)
                        @php
                            $filePath = $contentDecoded['path'] ?? null;
                            $fileName = $contentDecoded['original_name'] ?? 'File';
                            $fileSize = $contentDecoded['size'] ?? 0;
                            $fileMime = $contentDecoded['mime'] ?? '';
                            $fileUrl  = $filePath ? \Illuminate\Support\Facades\Storage::url($filePath) : null;
                            $isPdf    = str_contains(strtolower($fileMime), 'pdf') || str_ends_with(strtolower($fileName), '.pdf');
                        @endphp
                        @if($fileUrl)
                            <div class="text-center py-3">
                                <i class="bi bi-file-earmark-{{ $isPdf ? 'pdf text-danger' : 'arrow-down text-primary' }}" style="font-size:3.5rem;"></i>
                                <h5 class="mt-3 mb-1">{{ $fileName }}</h5>
                                <p class="text-muted small mb-4">{{ $fileSize > 0 ? number_format($fileSize / 1024 / 1024, 2) . ' MB' : '' }}</p>
                                @if($isPdf)
                                    <div class="mb-4" style="height:640px;">
                                        <iframe src="{{ $fileUrl }}" class="w-100 h-100 border rounded" type="application/pdf"></iframe>
                                    </div>
                                @endif
                                <a href="{{ $fileUrl }}" download="{{ $fileName }}" class="btn btn-primary">
                                    <i class="bi bi-download me-2"></i>Download {{ $fileName }}
                                </a>
                            </div>
                        @else
                            <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>File tidak tersedia.</div>
                        @endif

                    {{-- TYPE: AUDIO --}}
                    @elseif($module->type === \App\Models\Module::TYPE_AUDIO)
                        @php
                            $audioPath = $contentDecoded['path'] ?? null;
                            $audioName = $contentDecoded['original_name'] ?? 'Audio';
                            $audioUrl  = $audioPath ? \Illuminate\Support\Facades\Storage::url($audioPath) : null;
                        @endphp
                        @if($audioUrl)
                            <div class="text-center py-4">
                                <i class="bi bi-music-note-beamed text-primary" style="font-size:3rem;"></i>
                                <h5 class="mt-3 mb-3">{{ $audioName }}</h5>
                                <audio controls class="w-100" style="max-width:520px;">
                                    <source src="{{ $audioUrl }}">
                                    Browser Anda tidak mendukung pemutar audio.
                                </audio>
                                <div class="mt-3">
                                    <a href="{{ $audioUrl }}" download="{{ $audioName }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-download me-1"></i>Download
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">Audio tidak tersedia.</div>
                        @endif

                    {{-- TYPE: COACHING --}}
                    @elseif($module->type === \App\Models\Module::TYPE_COACHING)
                        @php
                            $meetingLink = $contentDecoded['meeting_link'] ?? '';
                            $coachNotes  = $contentDecoded['notes'] ?? '';
                        @endphp
                        <div class="py-3">
                            @if($meetingLink)
                                <div class="mb-4">
                                    <h6 class="fw-semibold"><i class="bi bi-camera-video me-2"></i>Link Meeting</h6>
                                    <a href="{{ $meetingLink }}" target="_blank" rel="noopener" class="btn btn-success">
                                        <i class="bi bi-box-arrow-up-right me-2"></i>Bergabung ke Sesi Coaching
                                    </a>
                                </div>
                            @endif
                            @if($coachNotes)
                                <h6 class="fw-semibold"><i class="bi bi-journal-text me-2"></i>Catatan / Jadwal</h6>
                                <div class="p-3 bg-light rounded">{!! nl2br(e($coachNotes)) !!}</div>
                            @endif
                        </div>

                    {{-- TYPE: QUIZ --}}
                    @elseif($module->type === \App\Models\Module::TYPE_QUIZ)
                        <div class="text-center py-5">
                            <i class="bi bi-patch-question-fill text-primary" style="font-size:3rem;"></i>
                            <h5 class="mt-3 mb-1">{{ $module->title }}</h5>
                            @if($module->quiz_duration)
                                <p class="text-muted small"><i class="bi bi-stopwatch me-1"></i>Waktu: {{ $module->quiz_duration }} menit</p>
                            @endif
                            @if($module->quiz_one_attempt)
                                <p class="text-muted small"><i class="bi bi-1-circle me-1"></i>Hanya bisa dikerjakan 1 kali</p>
                            @endif
                            <div class="mt-3">
                                <a href="{{ route('questions.index', [$course, $module]) }}" class="btn btn-primary">
                                    <i class="bi bi-pencil-square me-2"></i>Mulai Kuis
                                </a>
                            </div>
                        </div>

                    {{-- TYPE: VIDEO DRM --}}
                    @elseif($module->type === \App\Models\Module::TYPE_VIDEO_DRM)
                        @php
                            $drmUrl      = $contentDecoded['url'] ?? '';
                            $drmProvider = $contentDecoded['provider'] ?? '';
                        @endphp
                        <div class="alert alert-info">
                            <i class="bi bi-shield-lock me-2"></i>Video DRM ({{ $drmProvider ?: 'Protected' }})
                            @if($drmUrl)
                                — <a href="{{ $drmUrl }}" target="_blank">Buka Video</a>
                            @endif
                        </div>

                    {{-- TYPE: TAG --}}
                    @elseif($module->type === \App\Models\Module::TYPE_TAG)
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-tag fs-1"></i>
                            <p class="mt-2">Modul penanda.</p>
                        </div>

                    {{-- FALLBACK --}}
                    @else
                        @include('partials.editorjs-renderer', ['content' => $module->content])
                    @endif
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

                    // Check if next module is accessible for student
                    $nextModuleLocked = false;
                    $nextModuleObj = null;
                    if ($nextModule && !$canManageCourse) {
                        $nextModuleObj = $course->modules()->find($nextModule);
                        if ($nextModuleObj && $nextModuleObj->is_locked && $nextModuleObj->prerequisite_module_id) {
                            $nextModuleLocked = !Auth::user()->moduleProgress()
                                ->where('module_id', $nextModuleObj->prerequisite_module_id)
                                ->where('is_completed', true)
                                ->exists();
                        }
                    }
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
                            @if($nextModuleLocked)
                                <button class="btn btn-outline-secondary" disabled
                                    title="Selesaikan modul ini terlebih dahulu untuk membuka modul berikutnya">
                                    Next Module <i class="bi bi-lock-fill ms-1"></i>
                                </button>
                            @else
                                <a href="/courses/{{ $course->id }}/modules/{{ $nextModule }}" class="btn btn-outline-primary">
                                    Next Module <i class="bi bi-arrow-right"></i>
                                </a>
                            @endif
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
/* ── Base content wrapper ─────────────────────────────── */
.module-content,
.editorjs-content {
    line-height: 1.8;
    color: #333;
    font-size: 1.05rem;
}

.editorjs-content p,
.module-content p { margin-bottom: 1rem; }

.editorjs-content h2,
.editorjs-content h3,
.editorjs-content h4,
.module-content h2,
.module-content h3,
.module-content h4 {
    margin-top: 1.8rem;
    margin-bottom: .6rem;
    color: var(--primary);
    font-weight: 600;
}

.editorjs-content ul,
.editorjs-content ol,
.module-content ul,
.module-content ol { margin-bottom: 1rem; padding-left: 1.5rem; }

.editorjs-content li,
.module-content li { margin-bottom: .4rem; }

/* ── Code & Pre ───────────────────────────────────────── */
.editorjs-content code,
.module-content code {
    background: #f1f5f9;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: 'Courier New', Courier, monospace;
    font-size: .92em;
}

.editorjs-content pre,
.module-content pre {
    background: #1e1e2e;
    color: #cdd6f4;
    padding: 1rem 1.2rem;
    border-radius: 8px;
    overflow-x: auto;
    margin-bottom: 1rem;
}

.editorjs-content pre code,
.module-content pre code {
    background: none;
    color: inherit;
    padding: 0;
    font-size: .9rem;
}

/* ── Quote ────────────────────────────────────────────── */
.editorjs-quote {
    border-left: 4px solid var(--primary);
    background: #faf5ff;
    margin: 1.2rem 0;
    padding: .8rem 1.2rem;
    border-radius: 0 6px 6px 0;
}
.editorjs-quote p { margin: 0 0 .3rem; font-style: italic; }
.editorjs-quote cite { font-size: .875rem; color: var(--muted, #6b7280); }

/* ── Delimiter ────────────────────────────────────────── */
.editorjs-delimiter {
    border: none;
    border-top: 2px dashed #e2e8f0;
    margin: 1.5rem 0;
}

/* ── Embed ────────────────────────────────────────────── */
.editorjs-embed {
    margin: 1.2rem 0;
    border-radius: 8px;
    overflow: hidden;
}
.editorjs-embed figcaption {
    text-align: center;
    font-size: .875rem;
    color: var(--muted, #6b7280);
    margin-top: .4rem;
}

/* ── Checklist ────────────────────────────────────────── */
.editorjs-checklist { margin-bottom: 1rem; }
.editorjs-checklist-item {
    display: flex;
    align-items: flex-start;
    gap: .5rem;
    margin-bottom: .4rem;
    font-size: 1rem;
}

/* ── Image ────────────────────────────────────────────── */
.editorjs-image {
    margin: 1.2rem 0;
    text-align: center;
}
.editorjs-image img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
}
.editorjs-image.with-border img { border: 1px solid #e2e8f0; }
.editorjs-image.with-background { background: #f8fafc; padding: 1rem; border-radius: 8px; }
.editorjs-image.stretched img { width: 100%; }
.editorjs-image figcaption {
    font-size: .875rem;
    color: var(--muted, #6b7280);
    margin-top: .4rem;
}

/* ── Inline markup from Editor.js tools ──────────────── */
.editorjs-content mark,
.editorjs-content .cdx-marker { background: #fef08a; padding: 0 2px; }
.editorjs-content .inline-code {
    background: #f1f5f9;
    padding: 1px 5px;
    border-radius: 4px;
    font-family: monospace;
    font-size: .9em;
}

/* ── Table ────────────────────────────────────────────── */
.editorjs-content .table { margin-bottom: 1rem; }
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
