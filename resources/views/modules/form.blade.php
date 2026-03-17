@csrf

<div class="mb-3">
    <label for="title" class="form-label">Judul Modul</label>
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
    <label for="content" class="form-label">Konten Modul</label>
    <textarea
        class="form-control"
        id="content"
        name="content"
        rows="10"
        placeholder="Tulis materi modul di sini. Anda bisa memasukkan HTML sederhana bila diperlukan."
    >{{ old('content', $module->content) }}</textarea>
</div>

<div class="d-flex justify-content-end gap-2">
    <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-secondary">Batal</a>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-save"></i> {{ $submitLabel }}
    </button>
</div>
