@php
    $height = $height ?? '180px';
    $status = data_get($course, 'status');
    $title = data_get($course, 'title', 'Course');
    $thumbnailUrl = data_get($course, 'thumbnail_url');
    $statusLabel = data_get($course, 'status_label', $status ? ($status === 'pending_approval' ? 'Pending Approval' : ucfirst($status)) : null);
    $statusBadgeClass = data_get($course, 'status_badge_class', $status === 'published' ? 'bg-success' : ($status === 'draft' ? 'bg-warning text-dark' : ($status === 'pending_approval' ? 'bg-info text-dark' : 'bg-secondary')));
@endphp

<div class="course-thumbnail-frame" style="--thumbnail-height: {{ $height }};">
    @if($thumbnailUrl)
        <img
            src="{{ $thumbnailUrl }}"
            alt="{{ $title }}"
            class="course-thumbnail-image"
        >
    @else
        <div class="course-thumbnail-fallback">
            <div>
                <span class="course-thumbnail-chip mb-3">
                    <i class="bi bi-image"></i> Course cover
                </span>
                <div class="feature-icon mx-auto mb-3">
                    <i class="bi bi-camera"></i>
                </div>
                <h6 class="mb-1">{{ \Illuminate\Support\Str::limit($title, 38) }}</h6>
                <div class="small text-muted">
                    {{ $status === 'published' ? 'Course ini siap tampil dengan cover yang lebih menarik.' : 'Tambahkan thumbnail agar course terlihat lebih premium.' }}
                </div>
            </div>
        </div>
    @endif

    @if($status)
        <div class="course-thumbnail-status">
            <span class="badge {{ $statusBadgeClass }}">
                {{ $statusLabel }}
            </span>
        </div>
    @endif
</div>
