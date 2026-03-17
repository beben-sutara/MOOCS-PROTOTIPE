@csrf

@php
    $statusOptions = $statusOptions ?? ['draft', 'pending_approval'];
@endphp

<div class="mb-3">
    <label for="title" class="form-label">Judul Course</label>
    <input
        type="text"
        class="form-control"
        id="title"
        name="title"
        value="{{ old('title', $course->title) }}"
        maxlength="255"
        required
    >
</div>

<div class="mb-3">
    @if($course->thumbnail_url)
        <div class="mb-3">
            <label class="form-label">Thumbnail Saat Ini</label>
            <div>
                <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="img-fluid rounded border" style="max-height: 220px;">
            </div>
        </div>
    @endif

    <label for="thumbnail" class="form-label">Thumbnail Course</label>
    <input
        type="file"
        class="form-control"
        id="thumbnail"
        name="thumbnail"
        accept="image/*"
    >
    <small class="text-muted">Format gambar umum didukung, maksimal 2 MB.</small>
</div>

<div class="mb-3">
    <label for="description" class="form-label">Deskripsi</label>
    <textarea
        class="form-control"
        id="description"
        name="description"
        rows="6"
        placeholder="Tulis ringkasan materi, target peserta, atau hasil belajar course ini."
    >{{ old('description', $course->description) }}</textarea>
</div>

<div class="mb-4">
    <label for="status" class="form-label">Status</label>
    <select class="form-select" id="status" name="status" required>
        @foreach($statusOptions as $statusOption)
            <option value="{{ $statusOption }}" @selected(old('status', $course->status) === $statusOption)>
                {{ $statusOption === 'pending_approval' ? 'Pending Approval' : ucfirst($statusOption) }}
            </option>
        @endforeach
    </select>
    <small class="text-muted">
        Draft untuk persiapan, Pending Approval untuk dikirim ke admin, Published tampil ke peserta, Archived menyembunyikan course dari daftar aktif.
    </small>
</div>

<div class="d-flex justify-content-end gap-2">
    <a href="{{ $cancelRoute }}" class="btn btn-outline-secondary">Batal</a>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-save"></i> {{ $submitLabel }}
    </button>
</div>
