@php
    $userProgress = $user?->moduleProgress()
        ->where('module_id', $module->id)
        ->first();
    $isCompleted = $userProgress?->is_completed ?? false;
    $isViewed    = $userProgress?->is_viewed ?? false;

    $isAccessible = ! $module->prerequisite_module_id;
    if ($module->prerequisite_module_id) {
        $isAccessible = $user?->moduleProgress()
            ->where('module_id', $module->prerequisite_module_id)
            ->where('is_completed', true)
            ->exists() ?? false;
    }
@endphp

<div class="list-group-item py-3">
    <div class="row align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center">
                <div style="min-width: 40px; text-align: center;">
                    @if($isCompleted)
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 1.5rem;"></i>
                    @elseif($isViewed)
                        <i class="bi bi-play-circle-fill text-info" style="font-size: 1.5rem;"></i>
                    @elseif(! $isAccessible)
                        <i class="bi bi-lock-fill text-danger" style="font-size: 1.5rem;"></i>
                    @else
                        <i class="bi bi-play-circle text-muted" style="font-size: 1.5rem;"></i>
                    @endif
                </div>
                <div class="ms-3">
                    <h5 class="mb-1">
                        {{ $module->order }}. {{ $module->title }}
                        <span class="badge bg-light text-secondary border ms-1" style="font-size: 0.65rem; font-weight: 500;">
                            <i class="bi {{ $module->typeIcon() }}"></i> {{ $module->typeLabel() }}
                        </span>
                    </h5>
                    <p class="text-muted mb-0 small">{{ $module->excerpt() }}</p>
                    @if(! $isAccessible)
                        <small class="text-danger">
                            <i class="bi bi-lock"></i> Complete "{{ $module->prerequisite?->title }}" to unlock
                        </small>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4 text-end">
            @if($canManageCourse)
                <div class="d-flex justify-content-end gap-2">
                    @if($module->type === \App\Models\Module::TYPE_QUIZ)
                        <a href="{{ route('questions.index', ['course' => $course, 'module' => $module]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-patch-question"></i> Pertanyaan
                        </a>
                    @else
                        <a href="{{ route('courses.modules.show', ['course' => $course, 'module' => $module]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> Preview
                        </a>
                    @endif
                    <a href="{{ route('modules.edit', ['course' => $course, 'module' => $module]) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <form method="POST" action="{{ route('modules.destroy', ['course' => $course, 'module' => $module]) }}" onsubmit="return confirm('Hapus modul ini?')" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            @else
                @if($isAccessible)
                    @if($isCompleted)
                        <span class="badge bg-success mb-2">Completed</span><br>
                        <a href="/courses/{{ $course->id }}/modules/{{ $module->id }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> Review
                        </a>
                    @else
                        <a href="/courses/{{ $course->id }}/modules/{{ $module->id }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-play-fill"></i> Start Module
                        </a>
                    @endif
                @else
                    <button class="btn btn-sm btn-outline-secondary" disabled>
                        <i class="bi bi-lock"></i> Locked
                    </button>
                @endif
            @endif
        </div>
    </div>
</div>
