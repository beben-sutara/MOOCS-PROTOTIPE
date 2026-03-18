@php
    use App\Models\Module;
    $labels = Module::typeLabels();
    $icons  = Module::typeIcons();
    $size   = $btnSize ?? '';
    // When called from inside a section card, include section_id + quick_add
    $extraParams = isset($section) ? ['section_id' => $section->id, 'quick_add' => 1] : [];
@endphp

<div class="btn-group dropup">
    <button type="button"
            class="btn {{ $btnClass }} {{ $size }} dropdown-toggle"
            data-bs-toggle="dropdown"
            aria-expanded="false">
        <i class="bi bi-plus-circle"></i> Tambah Modul
    </button>

    <ul class="dropdown-menu dropdown-menu-end" style="min-width: 13rem;">
        <li><h6 class="dropdown-header">Pilih Tipe Modul</h6></li>
        <li><hr class="dropdown-divider m-0"></li>
        @foreach($labels as $type => $label)
            <li>
                <a class="dropdown-item"
                   href="{{ route('modules.create', array_merge([$course], ['type' => $type], $extraParams)) }}">
                    <i class="bi {{ $icons[$type] }} me-2 text-primary"></i>
                    {{ $label }}
                </a>
            </li>
        @endforeach
    </ul>
</div>
