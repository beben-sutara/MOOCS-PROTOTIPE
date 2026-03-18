<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Ensure the authenticated user can manage the course. */
    protected function canManageCourse(Course $course): bool
    {
        $user = auth()->user();
        return $user->role === 'admin'
            || ($user->role === 'instructor' && $course->instructor_id === $user->id);
    }

    /** Ensure module belongs to course and is type=quiz. */
    protected function resolveQuizModule(Course $course, Module $module): void
    {
        abort_unless($module->course_id === $course->id, 404);
        abort_unless($module->type === Module::TYPE_QUIZ, 404);
    }

    public function index(Course $course, Module $module)
    {
        abort_unless($this->canManageCourse($course), 403);
        $this->resolveQuizModule($course, $module);

        $questions = $module->questions()->with('options')->get();

        return view('questions.index', compact('course', 'module', 'questions'));
    }

    public function create(Course $course, Module $module)
    {
        abort_unless($this->canManageCourse($course), 403);
        $this->resolveQuizModule($course, $module);

        return view('questions.create', compact('course', 'module'));
    }

    public function store(Request $request, Course $course, Module $module)
    {
        abort_unless($this->canManageCourse($course), 403);
        $this->resolveQuizModule($course, $module);

        $data = $request->validate([
            'type'          => ['required', 'in:multiple_choice,true_false,essay'],
            'question'      => ['required', 'string', 'max:2000'],
            'explanation'   => ['nullable', 'string', 'max:2000'],
            'points'        => ['required', 'integer', 'min:1', 'max:100'],
            // multiple_choice
            'options'       => ['array'],
            'options.*'     => ['nullable', 'string', 'max:500'],
            'correct_option' => ['nullable', 'integer'],
            // true_false
            'tf_correct'    => ['nullable', 'in:true,false'],
        ]);

        DB::transaction(function () use ($data, $request, $module) {
            $order = (int) $module->questions()->max('order') + 1;

            $question = $module->questions()->create([
                'type'        => $data['type'],
                'question'    => $data['question'],
                'explanation' => $data['explanation'] ?? null,
                'points'      => $data['points'],
                'order'       => $order,
            ]);

            $this->syncOptions($question, $request);
        });

        return redirect()->route('questions.index', [$course, $module])
            ->with('success', 'Pertanyaan berhasil ditambahkan.');
    }

    public function edit(Course $course, Module $module, Question $question)
    {
        abort_unless($this->canManageCourse($course), 403);
        $this->resolveQuizModule($course, $module);
        abort_unless($question->module_id === $module->id, 404);

        $question->load('options');

        return view('questions.edit', compact('course', 'module', 'question'));
    }

    public function update(Request $request, Course $course, Module $module, Question $question)
    {
        abort_unless($this->canManageCourse($course), 403);
        $this->resolveQuizModule($course, $module);
        abort_unless($question->module_id === $module->id, 404);

        $data = $request->validate([
            'type'           => ['required', 'in:multiple_choice,true_false,essay'],
            'question'       => ['required', 'string', 'max:2000'],
            'explanation'    => ['nullable', 'string', 'max:2000'],
            'points'         => ['required', 'integer', 'min:1', 'max:100'],
            'options'        => ['array'],
            'options.*'      => ['nullable', 'string', 'max:500'],
            'correct_option' => ['nullable', 'integer'],
            'tf_correct'     => ['nullable', 'in:true,false'],
        ]);

        DB::transaction(function () use ($data, $request, $question) {
            $question->update([
                'type'        => $data['type'],
                'question'    => $data['question'],
                'explanation' => $data['explanation'] ?? null,
                'points'      => $data['points'],
            ]);

            $question->options()->delete();
            $this->syncOptions($question, $request);
        });

        return redirect()->route('questions.index', [$course, $module])
            ->with('success', 'Pertanyaan berhasil diperbarui.');
    }

    public function destroy(Course $course, Module $module, Question $question)
    {
        abort_unless($this->canManageCourse($course), 403);
        $this->resolveQuizModule($course, $module);
        abort_unless($question->module_id === $module->id, 404);

        $question->delete();

        return back()->with('success', 'Pertanyaan dihapus.');
    }

    public function downloadTemplate(Course $course, Module $module)
    {
        abort_unless($this->canManageCourse($course), 403);
        $this->resolveQuizModule($course, $module);

        $csv  = "question,type,option_a,option_b,option_c,option_d,correct_answer,explanation,points\n";
        $csv .= "\"Ibukota Indonesia?\",multiple_choice,Jakarta,Bandung,Surabaya,Medan,a,\"Jakarta adalah ibukota RI\",1\n";
        $csv .= "\"Bumi berbentuk bulat?\",true_false,,,,,true,,1\n";
        $csv .= "\"Jelaskan proses fotosintesis\",essay,,,,,,,2\n";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template-pertanyaan.csv"',
        ]);
    }

    public function importForm(Course $course, Module $module)
    {
        abort_unless($this->canManageCourse($course), 403);
        $this->resolveQuizModule($course, $module);

        return view('questions.import', compact('course', 'module'));
    }

    public function importProcess(Request $request, Course $course, Module $module)
    {
        abort_unless($this->canManageCourse($course), 403);
        $this->resolveQuizModule($course, $module);

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $path   = $request->file('csv_file')->getRealPath();
        $handle = fopen($path, 'r');

        if (!$handle) {
            return back()->with('error', 'Gagal membuka file CSV.');
        }

        // Skip header row
        $header = fgetcsv($handle);

        $imported = 0;
        $errors   = [];
        $order    = (int) $module->questions()->max('order');

        DB::transaction(function () use ($handle, $module, &$imported, &$errors, &$order) {
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < 3) {
                    continue;
                }

                [$questionText, $type, $optA, $optB, $optC, $optD, $correctAnswer, $explanation, $points]
                    = array_pad($row, 9, '');

                $questionText = trim($questionText);
                $type         = strtolower(trim($type));
                $points       = max(1, (int) ($points ?: 1));

                if ($questionText === '' || !in_array($type, Question::allTypes())) {
                    $errors[] = "Baris dilewati: tipe tidak valid atau pertanyaan kosong.";
                    continue;
                }

                $order++;
                $question = $module->questions()->create([
                    'type'        => $type,
                    'question'    => $questionText,
                    'explanation' => trim($explanation),
                    'points'      => $points,
                    'order'       => $order,
                ]);

                if ($type === Question::TYPE_MULTIPLE_CHOICE) {
                    $options    = [trim($optA), trim($optB), trim($optC), trim($optD)];
                    $correctIdx = array_search(strtolower(trim($correctAnswer)), ['a', 'b', 'c', 'd']);

                    foreach ($options as $i => $opt) {
                        if ($opt === '') {
                            continue;
                        }
                        $question->options()->create([
                            'option_text' => $opt,
                            'is_correct'  => $i === $correctIdx,
                            'order'       => $i,
                        ]);
                    }
                } elseif ($type === Question::TYPE_TRUE_FALSE) {
                    $correct = strtolower(trim($correctAnswer));
                    $question->options()->createMany([
                        ['option_text' => 'Benar', 'is_correct' => $correct === 'true',  'order' => 0],
                        ['option_text' => 'Salah', 'is_correct' => $correct === 'false', 'order' => 1],
                    ]);
                }

                $imported++;
            }
        });

        fclose($handle);

        $message = "Berhasil mengimpor {$imported} pertanyaan.";
        if ($errors) {
            $message .= ' ' . count($errors) . ' baris dilewati.';
        }

        return redirect()->route('questions.index', [$course, $module])
            ->with('success', $message);
    }

    /** Sync options for a question based on request data. */
    private function syncOptions(Question $question, Request $request): void
    {
        $type = $question->type;

        if ($type === Question::TYPE_MULTIPLE_CHOICE) {
            $options       = $request->input('options', []);
            $correctOption = (int) $request->input('correct_option', 0);

            foreach ($options as $i => $optText) {
                $optText = trim((string) $optText);
                if ($optText === '') {
                    continue;
                }
                $question->options()->create([
                    'option_text' => $optText,
                    'is_correct'  => $i === $correctOption,
                    'order'       => $i,
                ]);
            }
        } elseif ($type === Question::TYPE_TRUE_FALSE) {
            $correct = $request->input('tf_correct', 'true');
            $question->options()->createMany([
                ['option_text' => 'Benar', 'is_correct' => $correct === 'true',  'order' => 0],
                ['option_text' => 'Salah', 'is_correct' => $correct === 'false', 'order' => 1],
            ]);
        }
        // essay: no options needed
    }
}
