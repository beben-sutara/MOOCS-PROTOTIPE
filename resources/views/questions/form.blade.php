{{--
    Shared question form partial.
    Required vars: $course, $module, $question (model or new Question()), $formAction, $formMethod
--}}
<div class="mb-3">
    <label class="form-label fw-semibold">Tipe Pertanyaan <span class="text-danger">*</span></label>
    <div class="d-flex flex-wrap gap-2" id="type-switcher">
        @foreach(\App\Models\Question::allTypes() as $t)
            @php $label = \App\Models\Question::typeLabels()[$t]; @endphp
            <div class="form-check form-check-inline border rounded px-3 py-2 mb-0"
                 style="cursor:pointer" onclick="selectQuestionType('{{ $t }}')">
                <input class="form-check-input" type="radio" name="type" id="type_{{ $t }}"
                       value="{{ $t }}"
                       {{ old('type', $question->type ?? 'multiple_choice') === $t ? 'checked' : '' }}
                       onchange="selectQuestionType('{{ $t }}')">
                <label class="form-check-label" for="type_{{ $t }}" style="cursor:pointer">{{ $label }}</label>
            </div>
        @endforeach
    </div>
    @error('type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="q_question" class="form-label fw-semibold">Pertanyaan <span class="text-danger">*</span></label>
    <textarea
        class="form-control @error('question') is-invalid @enderror"
        id="q_question"
        name="question"
        rows="3"
        placeholder="Tulis pertanyaan di sini..."
        required
    >{{ old('question', $question->question ?? '') }}</textarea>
    @error('question')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

{{-- Multiple Choice Options --}}
<div id="mc-options" class="{{ old('type', $question->type ?? 'multiple_choice') === 'multiple_choice' ? '' : 'd-none' }}">
    <label class="form-label fw-semibold">Pilihan Jawaban</label>
    <div class="form-text mb-2">Tandai radio di sebelah kiri sebagai jawaban yang benar.</div>

    @php
        $opts    = $question->options ?? collect();
        $correct = old('correct_option');
        if ($correct === null) {
            $correctIdx = $opts->search(fn($o) => $o->is_correct);
            $correct    = $correctIdx !== false ? $correctIdx : 0;
        }
    @endphp

    @for($i = 0; $i < 4; $i++)
        <div class="input-group mb-2">
            <div class="input-group-text">
                <input
                    class="form-check-input mt-0"
                    type="radio"
                    name="correct_option"
                    value="{{ $i }}"
                    {{ (int)$correct === $i ? 'checked' : '' }}
                    required
                    title="Jawaban benar"
                >
            </div>
            <span class="input-group-text fw-bold">{{ chr(65 + $i) }}</span>
            <input
                type="text"
                class="form-control"
                name="options[]"
                placeholder="Pilihan {{ chr(65 + $i) }}"
                value="{{ old("options.$i", $opts[$i]->option_text ?? '') }}"
            >
        </div>
    @endfor
    @error('options.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>

{{-- True / False --}}
<div id="tf-options" class="{{ old('type', $question->type ?? 'multiple_choice') === 'true_false' ? '' : 'd-none' }}">
    <label class="form-label fw-semibold">Jawaban Benar</label>
    @php
        $tfCorrect = old('tf_correct');
        if ($tfCorrect === null && $question->exists && $question->type === 'true_false') {
            $tfCorrect = optional($question->options->firstWhere('is_correct', true))->option_text === 'Benar' ? 'true' : 'false';
        }
        $tfCorrect = $tfCorrect ?? 'true';
    @endphp
    <div class="d-flex gap-3">
        <div class="form-check">
            <input class="form-check-input" type="radio" name="tf_correct" id="tf_true" value="true"
                   {{ $tfCorrect === 'true' ? 'checked' : '' }}>
            <label class="form-check-label" for="tf_true">Benar</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="tf_correct" id="tf_false" value="false"
                   {{ $tfCorrect === 'false' ? 'checked' : '' }}>
            <label class="form-check-label" for="tf_false">Salah</label>
        </div>
    </div>
</div>

{{-- Essay info --}}
<div id="essay-info" class="{{ old('type', $question->type ?? 'multiple_choice') === 'essay' ? '' : 'd-none' }}">
    <div class="alert alert-secondary py-2 small mb-0">
        <i class="bi bi-pencil-square me-1"></i>Pertanyaan esai tidak dinilai secara otomatis. Instruktur perlu menilai secara manual.
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-6 mb-3">
        <label for="q_points" class="form-label fw-semibold">Poin</label>
        <input
            type="number"
            class="form-control @error('points') is-invalid @enderror"
            id="q_points"
            name="points"
            min="1"
            max="100"
            value="{{ old('points', $question->points ?? 1) }}"
            required
        >
        @error('points')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="mb-3">
    <label for="q_explanation" class="form-label fw-semibold">Penjelasan <span class="text-muted fw-normal">(opsional)</span></label>
    <textarea
        class="form-control"
        id="q_explanation"
        name="explanation"
        rows="2"
        placeholder="Penjelasan jawaban yang ditampilkan setelah peserta menjawab..."
    >{{ old('explanation', $question->explanation ?? '') }}</textarea>
</div>

<script>
function selectQuestionType(type) {
    document.querySelectorAll('[name="type"]').forEach(r => r.checked = r.value === type);
    document.getElementById('mc-options').classList.toggle('d-none', type !== 'multiple_choice');
    document.getElementById('tf-options').classList.toggle('d-none', type !== 'true_false');
    document.getElementById('essay-info').classList.toggle('d-none', type !== 'essay');
}
document.addEventListener('DOMContentLoaded', function () {
    const checked = document.querySelector('[name="type"]:checked');
    if (checked) selectQuestionType(checked.value);
});
</script>
