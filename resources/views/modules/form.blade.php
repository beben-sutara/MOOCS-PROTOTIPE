@php
    use App\Models\Module;
    $moduleType = old('type', $module->type ?? Module::TYPE_TEXT);
    $isQuickAdd = $quickAdd ?? false;
    // In quick-add mode, section is pre-assigned; pass it as hidden
    $quickSectionId = $presetSectionId ?? $module->section_id ?? null;
@endphp
@csrf
<input type="hidden" name="type" value="{{ $moduleType }}">
@if($isQuickAdd)
    <input type="hidden" name="quick_add" value="1">
    <input type="hidden" name="section_id" value="{{ $quickSectionId }}">
    <input type="hidden" name="order" value="">
@endif

<div class="mb-3">
    <label for="title" class="form-label">Judul Modul <span class="text-danger">*</span></label>
    <input
        type="text"
        class="form-control"
        id="title"
        name="title"
        value="{{ old('title', $module->title) }}"
        maxlength="255"
        required
    >
</div>

@if(!$isQuickAdd)
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="order" class="form-label">Urutan</label>
        <input
            type="number"
            class="form-control"
            id="order"
            name="order"
            min="0"
            value="{{ old('order', $module->order ?? 0) }}"
            required
        >
    </div>
    <div class="col-md-6 mb-3">
        <label for="prerequisite_module_id" class="form-label">Prasyarat</label>
        <select class="form-select" id="prerequisite_module_id" name="prerequisite_module_id">
            <option value="">Tanpa prasyarat</option>
            @foreach($prerequisiteOptions as $prerequisite)
                <option value="{{ $prerequisite->id }}" @selected((string) old('prerequisite_module_id', $module->prerequisite_module_id) === (string) $prerequisite->id)>
                    {{ $prerequisite->order }}. {{ $prerequisite->title }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="mb-3">
    <label for="section_id" class="form-label">Bab (Section)</label>
    <select class="form-select" id="section_id" name="section_id">
        <option value="">— Tanpa Bab —</option>
        @foreach($course->sections()->orderBy('order')->get() as $sec)
            <option value="{{ $sec->id }}" @selected((string) old('section_id', $module->section_id ?? '') === (string) $sec->id)>
                {{ $sec->title }}
            </option>
        @endforeach
    </select>
    <div class="form-text">Opsional — pilih bab untuk mengelompokkan modul ini.</div>
</div>
@endif

<div class="mb-3 form-check">
    <input
        class="form-check-input"
        type="checkbox"
        value="1"
        id="is_locked"
        name="is_locked"
        @checked(old('is_locked', $module->is_locked))
    >
    <label class="form-check-label" for="is_locked">
        Modul terkunci sampai prasyarat selesai
    </label>
</div>

<div class="mb-4">
    @php
        $contentDecoded = null;
        if ($module->content) {
            $contentDecoded = json_decode($module->content, true);
        }
    @endphp

    {{-- === TEKS: Editor.js === --}}
    @if($moduleType === \App\Models\Module::TYPE_TEXT)
        <label class="form-label">Konten Modul (Teks)</label>
        <textarea id="content" name="content" class="d-none">{{ old('content', $module->content) }}</textarea>
        <div id="editorjs-holder" class="editorjs-holder border rounded"></div>
        @error('content')
            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
        @enderror

    {{-- === YOUTUBE === --}}
    @elseif($moduleType === \App\Models\Module::TYPE_YOUTUBE)
        <label for="youtube_url" class="form-label">URL YouTube <span class="text-danger">*</span></label>
        <input
            type="url"
            class="form-control @error('youtube_url') is-invalid @enderror"
            id="youtube_url"
            name="youtube_url"
            placeholder="https://www.youtube.com/watch?v=..."
            value="{{ old('youtube_url', $contentDecoded['url'] ?? '') }}"
        >
        @error('youtube_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">Tempel URL video YouTube. Contoh: <code>https://youtu.be/dQw4w9WgXcQ</code></div>

    {{-- === IFRAME === --}}
    @elseif($moduleType === \App\Models\Module::TYPE_IFRAME)
        <label for="iframe_code" class="form-label">Kode Embed (iframe)</label>
        <textarea
            class="form-control font-monospace @error('iframe_code') is-invalid @enderror"
            id="iframe_code"
            name="iframe_code"
            rows="6"
            placeholder='<iframe src="https://..." width="100%" height="450" ...></iframe>'
        >{{ old('iframe_code', $contentDecoded['code'] ?? '') }}</textarea>
        @error('iframe_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">Tempel kode <code>&lt;iframe&gt;</code> dari layanan embed manapun.</div>

    {{-- === VIDEO DRM === --}}
    @elseif($moduleType === \App\Models\Module::TYPE_VIDEO_DRM)
        <label for="drm_url" class="form-label">URL Video DRM</label>
        <input
            type="url"
            class="form-control mb-2 @error('drm_url') is-invalid @enderror"
            id="drm_url"
            name="drm_url"
            placeholder="https://cdn.example.com/video.m3u8"
            value="{{ old('drm_url', $contentDecoded['url'] ?? '') }}"
        >
        @error('drm_url')<div class="invalid-feedback">{{ $message }}</div>@enderror

        <label for="drm_provider" class="form-label">Provider DRM</label>
        <input
            type="text"
            class="form-control mb-2"
            id="drm_provider"
            name="drm_provider"
            placeholder="Widevine / FairPlay / PlayReady"
            value="{{ old('drm_provider', $contentDecoded['provider'] ?? '') }}"
        >

        <label for="drm_token" class="form-label">Token / License URL</label>
        <input
            type="text"
            class="form-control"
            id="drm_token"
            name="drm_token"
            placeholder="Token atau URL license server"
            value="{{ old('drm_token', $contentDecoded['token'] ?? '') }}"
        >

    {{-- === KUIS === --}}
    @elseif($moduleType === \App\Models\Module::TYPE_QUIZ)
        {{-- Timer --}}
        <div class="mb-3">
            <label for="quiz_duration" class="form-label">
                <i class="bi bi-stopwatch me-1"></i>Timer Pengerjaan Kuis
            </label>
            <div class="input-group" style="max-width:220px">
                <input
                    type="number"
                    class="form-control @error('quiz_duration') is-invalid @enderror"
                    id="quiz_duration"
                    name="quiz_duration"
                    min="1"
                    max="600"
                    placeholder="—"
                    value="{{ old('quiz_duration', $module->quiz_duration ?? '') }}"
                >
                <span class="input-group-text">menit</span>
            </div>
            <div class="form-text">Kosongkan jika tidak ada batas waktu.</div>
            @error('quiz_duration')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        {{-- Pengaturan Quiz --}}
        <div class="mb-1">
            <div class="form-check form-switch mb-2">
                <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    id="quiz_one_attempt"
                    name="quiz_one_attempt"
                    value="1"
                    {{ old('quiz_one_attempt', $module->quiz_one_attempt ?? false) ? 'checked' : '' }}
                >
                <label class="form-check-label" for="quiz_one_attempt">
                    Pelanggan hanya bisa mengerjakan quiz ini sekali.
                </label>
            </div>
            <div class="form-check form-switch">
                <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    id="quiz_required_for_next"
                    name="quiz_required_for_next"
                    value="1"
                    {{ old('quiz_required_for_next', $module->quiz_required_for_next ?? false) ? 'checked' : '' }}
                >
                <label class="form-check-label" for="quiz_required_for_next">
                    Pelanggan harus menyelesaikan quiz untuk mengakses konten-konten selanjutnya.
                </label>
            </div>
        </div>

        @if($module->exists)
            <div class="alert alert-info d-flex align-items-center gap-2 mt-3 mb-0 py-2">
                <i class="bi bi-patch-question-fill"></i>
                <span class="small">
                    Kuis ini memiliki <strong>{{ $module->questions()->count() }} pertanyaan</strong>.
                    <a href="{{ route('questions.index', [$course ?? $module->course, $module]) }}" class="alert-link">Kelola Pertanyaan →</a>
                </span>
            </div>
        @endif

    {{-- === COACHING === --}}
    @elseif($moduleType === \App\Models\Module::TYPE_COACHING)
        <label for="coaching_link" class="form-label">Link Meeting</label>
        <input
            type="url"
            class="form-control mb-2"
            id="coaching_link"
            name="coaching_link"
            placeholder="https://zoom.us/j/... atau https://meet.google.com/..."
            value="{{ old('coaching_link', $contentDecoded['meeting_link'] ?? '') }}"
        >

        <label for="coaching_notes" class="form-label">Catatan / Jadwal</label>
        <textarea
            class="form-control"
            id="coaching_notes"
            name="coaching_notes"
            rows="4"
            placeholder="Informasi jadwal, topik, atau instruksi sesi coaching..."
        >{{ old('coaching_notes', $contentDecoded['notes'] ?? '') }}</textarea>

    {{-- === FILE === --}}
    @elseif($moduleType === \App\Models\Module::TYPE_FILE)
        @if(!empty($contentDecoded['original_name']))
            <div class="alert alert-secondary d-flex align-items-center gap-2 mb-2 py-2">
                <i class="bi bi-file-earmark-check text-primary"></i>
                <span class="small">File saat ini: <strong>{{ $contentDecoded['original_name'] }}</strong>
                    ({{ number_format(($contentDecoded['size'] ?? 0) / 1024, 1) }} KB)</span>
            </div>
        @endif
        <label for="upload_file" class="form-label">
            {{ !empty($contentDecoded['original_name']) ? 'Ganti File (opsional)' : 'Upload File' }}
        </label>
        <input
            type="file"
            class="form-control @error('upload_file') is-invalid @enderror"
            id="upload_file"
            name="upload_file"
        >
        @error('upload_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">Maks. 100 MB. Semua format didukung (PDF, DOCX, ZIP, dsb.).</div>

    {{-- === AUDIO === --}}
    @elseif($moduleType === \App\Models\Module::TYPE_AUDIO)
        @if(!empty($contentDecoded['original_name']))
            <div class="alert alert-secondary d-flex align-items-center gap-2 mb-2 py-2">
                <i class="bi bi-music-note-beamed text-primary"></i>
                <span class="small">Audio saat ini: <strong>{{ $contentDecoded['original_name'] }}</strong></span>
            </div>
        @endif
        <label for="upload_audio" class="form-label">
            {{ !empty($contentDecoded['original_name']) ? 'Ganti Audio (opsional)' : 'Upload Audio' }}
        </label>
        <input
            type="file"
            class="form-control @error('upload_audio') is-invalid @enderror"
            id="upload_audio"
            name="upload_audio"
            accept=".mp3,.ogg,.wav,.m4a,.aac"
        >
        @error('upload_audio')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">Format didukung: MP3, OGG, WAV, M4A, AAC. Maks. 100 MB.</div>

    {{-- === TAG === --}}
    @elseif($moduleType === \App\Models\Module::TYPE_TAG)
        <div class="alert alert-light border d-flex align-items-start gap-3 mb-0">
            <i class="bi bi-tag-fill fs-4 mt-1 text-secondary"></i>
            <div>
                <div class="fw-semibold">Modul Tag</div>
                <div class="small text-muted">Modul ini hanya sebagai penanda/label dalam kurikulum. Tidak ada konten yang perlu diisi — judul modul sudah cukup.</div>
            </div>
        </div>
    @endif
</div>

{{-- ============================================================ --}}
{{-- PENGATURAN AKSES                                             --}}
{{-- ============================================================ --}}
<div class="card border mb-4" style="border-color: rgba(164,53,240,0.2) !important;">
    <div class="card-header py-2 px-3" style="background: rgba(164,53,240,0.05); border-bottom: 1px solid rgba(164,53,240,0.15);">
        <span class="fw-semibold small"><i class="bi bi-shield-lock text-primary me-1"></i> Pengaturan Akses</span>
    </div>
    <div class="card-body p-3">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="available_from" class="form-label fw-semibold small mb-1">Tanggal Buka Akses</label>
                <input
                    type="datetime-local"
                    class="form-control form-control-sm @error('available_from') is-invalid @enderror"
                    id="available_from"
                    name="available_from"
                    value="{{ old('available_from', $module->available_from?->format('Y-m-d\TH:i')) }}"
                >
                @error('available_from')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Jika diisi, peserta hanya bisa mengakses modul setelah tanggal ini.</div>
            </div>
            <div class="col-md-6">
                <label for="available_until" class="form-label fw-semibold small mb-1">Tanggal Tutup Akses</label>
                <input
                    type="datetime-local"
                    class="form-control form-control-sm @error('available_until') is-invalid @enderror"
                    id="available_until"
                    name="available_until"
                    value="{{ old('available_until', $module->available_until?->format('Y-m-d\TH:i')) }}"
                >
                @error('available_until')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Jika diisi, peserta tidak bisa mengakses modul setelah tanggal ini.</div>
            </div>
        </div>

        <hr class="my-3">

        <div class="form-check mb-1">
            <input
                class="form-check-input"
                type="checkbox"
                id="is_preview"
                name="is_preview"
                value="1"
                @checked(old('is_preview', $module->is_preview ?? false))
            >
            <label class="form-check-label" for="is_preview">
                Pengunjung situs dapat preview materi ini.
            </label>
        </div>
        <div class="form-check mt-2">
            <input
                class="form-check-input"
                type="checkbox"
                id="is_member_access"
                name="is_member_access"
                value="1"
                @checked(old('is_member_access', $module->is_member_access ?? true))
            >
            <label class="form-check-label" for="is_member_access">
                Pelanggan yang telah membeli kelas bisa melihat materi ini.
            </label>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2">
    <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-secondary">Batal</a>
    <button type="submit" class="btn btn-primary" id="module-submit-btn">
        <i class="bi bi-save"></i> {{ $submitLabel }}
    </button>
</div>

@section('extra_css')
<style>
.editorjs-holder {
    min-height: 420px;
    background: #fff;
    padding: 4px;
}
.codex-editor__redactor {
    padding-bottom: 120px !important;
}
.ce-toolbar__plus,
.ce-toolbar__settings-btn {
    color: var(--primary) !important;
}
.ce-block--selected .ce-block__content {
    background: #f3e8ff;
}
.cdx-marker {
    background: #fef08a;
    padding: 0 2px;
}
.inline-code {
    background: #f1f5f9;
    padding: 2px 5px;
    border-radius: 4px;
    font-family: monospace;
    font-size: 0.9em;
}
</style>
@endsection

@section('extra_js')
@if($moduleType === \App\Models\Module::TYPE_TEXT)
<!-- Editor.js Core -->
<script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest"></script>
<!-- Editor.js Tools -->
<script src="https://cdn.jsdelivr.net/npm/@editorjs/header@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/list@1.10.0"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/code@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/quote@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/table@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/delimiter@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/embed@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/checklist@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/warning@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/marker@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/inline-code@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/image@latest"></script>
<script>
(function () {
    var rawContent  = document.getElementById('content').value.trim();
    var initialData = {};

    if (rawContent) {
        try {
            var parsed = JSON.parse(rawContent);
            if (parsed && Array.isArray(parsed.blocks)) {
                initialData = parsed;
            } else {
                // Legacy HTML from TinyMCE — wrap in a single paragraph block
                initialData = { blocks: [{ type: 'paragraph', data: { text: rawContent } }] };
            }
        } catch (e) {
            // Not JSON — treat as legacy HTML
            initialData = { blocks: [{ type: 'paragraph', data: { text: rawContent } }] };
        }
    }

    var editor = new EditorJS({
        holder: 'editorjs-holder',
        data: initialData,
        placeholder: 'Mulai tulis konten modul di sini...',
        tools: {
            header: {
                class: Header,
                config: { levels: [2, 3, 4], defaultLevel: 2 }
            },
            list: {
                class: List,
                inlineToolbar: true,
                config: { defaultStyle: 'unordered' }
            },
            code: CodeTool,
            quote: {
                class: Quote,
                inlineToolbar: true
            },
            table: {
                class: Table,
                inlineToolbar: true,
                config: { rows: 2, cols: 3 }
            },
            delimiter: Delimiter,
            embed: {
                class: Embed,
                config: {
                    services: { youtube: true, codesandbox: true, codepen: true }
                }
            },
            checklist: {
                class: Checklist,
                inlineToolbar: true
            },
            warning: {
                class: Warning,
                inlineToolbar: true,
                config: { titlePlaceholder: 'Judul', messagePlaceholder: 'Pesan...' }
            },
            Marker: {
                class: Marker,
                shortcut: 'CMD+SHIFT+M'
            },
            inlineCode: {
                class: InlineCode,
                shortcut: 'CMD+SHIFT+C'
            },
            image: {
                class: ImageTool,
                config: {
                    uploader: {
                        uploadByFile: function (file) {
                            var formData = new FormData();
                            formData.append('image', file);
                            return fetch('/upload/image', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                },
                                body: formData,
                            }).then(function (r) { return r.json(); });
                        },
                        uploadByUrl: function (url) {
                            return Promise.resolve({ success: 1, file: { url: url } });
                        }
                    }
                }
            }
        }
    });

    var form = document.getElementById('module-submit-btn').closest('form');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            editor.save().then(function (output) {
                document.getElementById('content').value = JSON.stringify(output);
                form.submit();
            }).catch(function (err) {
                console.error('Editor.js save error:', err);
                form.submit();
            });
        });
    }
})();
</script>
@endif
@endsection
