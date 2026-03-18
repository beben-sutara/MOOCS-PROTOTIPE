<form method="POST" action="{{ $action }}">
    @csrf
    @if(isset($method) && $method !== 'POST')
        @method($method)
    @endif

    {{-- Judul --}}
    <div class="mb-4">
        <label for="title" class="form-label fw-semibold">
            Judul <span class="text-danger">*</span>
        </label>
        <input
            type="text"
            id="title"
            name="title"
            class="form-control @error('title') is-invalid @enderror"
            value="{{ old('title', $section->title ?? '') }}"
            placeholder="Contoh: Pengenalan Dasar, Konsep Lanjutan..."
            required
            maxlength="255"
            autofocus
        >
        @error('title')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Deskripsi --}}
    <div class="mb-4">
        <label for="description" class="form-label fw-semibold">
            Deskripsi
        </label>
        <textarea
            id="description"
            name="description"
            class="form-control @error('description') is-invalid @enderror"
            rows="3"
            placeholder="Opsional — Jelaskan secara singkat isi dari bab ini..."
            maxlength="1000"
        >{{ old('description', $section->description ?? '') }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Maksimal 1000 karakter.</div>
    </div>

    {{-- Order (hidden, auto) --}}
    <input type="hidden" name="order" value="{{ old('order', $section->order ?? 0) }}">

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-circle"></i> {{ $submitLabel ?? 'Simpan Bab' }}
        </button>
        <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-secondary">
            Batal
        </a>
    </div>
</form>
