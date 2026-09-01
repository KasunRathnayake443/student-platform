<?php

use App\Filament\Resources\Quizzes\Pages\CreateQuiz as AdminCreateQuiz;
use App\Filament\Teacher\Resources\Quizzes\Pages\CreateQuiz as TeacherCreateQuiz;
use App\Models\LearningClass;
use App\Models\Quiz;
use App\Models\User;
use App\Services\QuizImportService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('the CSV template can be downloaded by an authenticated teacher', function () {
    $this->seed();

    $this->actingAs(User::where('email', 'teacher1@example.com')->firstOrFail());

    $response = $this->get(route('quiz-questions.import.template'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
        ->assertDownload('quiz-questions-template.csv');

    expect($response->streamedContent())
        ->toContain('question,option_a,option_b,option_c,option_d,option_e,option_f,correct_option,points,explanation');
});

test('the CSV template matches the expected header structure', function () {
    $service = app(QuizImportService::class);

    $csv = $service->buildTemplate();
    $header = str_getcsv(trim(explode("\n", $csv)[0]));

    expect($header)->toBe(QuizImportService::HEADERS);
});

test('a valid CSV file is parsed into questions with the correct answer marked', function () {
    $service = app(QuizImportService::class);

    $csv = "question,option_a,option_b,option_c,option_d,correct_option,points,explanation\n"
        ."What is 2+2?,3,4,5,6,B,2,Basic addition.\n"
        ."What color is the sky?,Green,Blue,Red,,C,1,Sky can look blue.\n";

    $tempPath = tempnam(sys_get_temp_dir(), 'quiz_import_test_');
    file_put_contents($tempPath, $csv);

    try {
        $questions = $service->parse(new UploadedFile($tempPath, 'quiz.csv', 'text/csv', null, true));
    } finally {
        @unlink($tempPath);
    }

    expect($questions)->toHaveCount(2);

    $first = $questions[0];
    expect($first['question_text'])->toBe('What is 2+2?')
        ->and($first['points'])->toBe(2)
        ->and($first['explanation'])->toBe('Basic addition.')
        ->and($first['options'])->toHaveCount(4)
        ->and(collect($first['options'])->firstWhere('is_correct', true)['option_text'])->toBe('4');

    $second = $questions[1];
    expect(collect($second['options'])->firstWhere('is_correct', true)['option_text'])->toBe('Red');
});

test('a CSV with a UTF-8 BOM is parsed correctly', function () {
    $service = app(QuizImportService::class);

    $csv = "\xEF\xBB\xBFquestion,option_a,option_b,correct_option,points\n"
        ."Capital of Sri Lanka?,Kandy,Colombo,B,1\n";

    $tempPath = tempnam(sys_get_temp_dir(), 'quiz_import_test_');
    file_put_contents($tempPath, $csv);

    try {
        $questions = $service->parse(new UploadedFile($tempPath, 'quiz.csv', 'text/csv', null, true));
    } finally {
        @unlink($tempPath);
    }

    expect($questions)->toHaveCount(1)
        ->and($questions[0]['question_text'])->toBe('Capital of Sri Lanka?')
        ->and(collect($questions[0]['options'])->firstWhere('is_correct', true)['option_text'])->toBe('Colombo');
});

test('a CSV missing required columns throws a RuntimeException', function () {
    $service = app(QuizImportService::class);

    $csv = "question,option_a\nSample?,A\n";

    $tempPath = tempnam(sys_get_temp_dir(), 'quiz_import_test_');
    file_put_contents($tempPath, $csv);

    try {
        $service->parse(new UploadedFile($tempPath, 'quiz.csv', 'text/csv', null, true));
    } finally {
        @unlink($tempPath);
    }
})->throws(RuntimeException::class);

test('points below 1 default to 1', function () {
    $service = app(QuizImportService::class);

    $csv = "question,option_a,option_b,correct_option,points\nQ?,A,B,A,0\n";

    $tempPath = tempnam(sys_get_temp_dir(), 'quiz_import_test_');
    file_put_contents($tempPath, $csv);

    try {
        $questions = $service->parse(new UploadedFile($tempPath, 'quiz.csv', 'text/csv', null, true));
    } finally {
        @unlink($tempPath);
    }

    expect($questions[0]['points'])->toBe(1);
});

test('uploading a CSV file automatically populates the questions repeater', function () {
    $this->seed();

    $admin = User::where('email', 'admin1@example.com')->firstOrFail();
    Filament::setCurrentPanel('admin');
    $this->actingAs($admin);

    $csv = "question,option_a,option_b,correct_option,points\n"
        ."What is 2+2?,3,4,B,2\n"
        ."Capital of Sri Lanka?,Kandy,Colombo,B,1\n";

    $file = UploadedFile::fake()->createWithContent('quiz.csv', $csv);

    Livewire::test(AdminCreateQuiz::class)
        ->fillForm([
            'title' => 'CSV Quiz',
            'learning_class_id' => LearningClass::where('name', '10-B Science & Physics')->firstOrFail()->getKey(),
            'passing_percentage' => 50,
            'availability_type' => 'immediate',
            'end_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
        ])
        ->set('data.import_file', $file)
        ->assertSet('data.questions.0.question_text', 'What is 2+2?')
        ->assertSet('data.questions.1.question_text', 'Capital of Sri Lanka?')
        ->assertSet('data.questions.0.points', 2);
});

test('quiz create form has default values configured', function () {
    $this->seed();

    $admin = User::where('email', 'admin1@example.com')->firstOrFail();
    Filament::setCurrentPanel('admin');
    $this->actingAs($admin);

    Livewire::test(AdminCreateQuiz::class)
        ->assertFormSet([
            'max_attempts' => 1,
            'passing_percentage' => 50,
            'show_correct_answers_after_submission' => true,
            'available_immediately' => true,
            'is_published' => true,
        ]);
});

test('a question with an uploaded image persists its path and stored file on create', function () {
    Storage::fake('local');
    $this->seed();

    $admin = User::where('email', 'admin1@example.com')->firstOrFail();
    Filament::setCurrentPanel('admin');
    $this->actingAs($admin);

    $class = LearningClass::where('name', '10-B Science & Physics')->firstOrFail();
    $teacher = $class->teachers()->firstOrFail();

    Livewire::test(AdminCreateQuiz::class)
        ->fillForm([
            'title' => 'Media Quiz',
            'learning_class_id' => $class->getKey(),
            'teacher_ids' => [$teacher->getKey()],
            'passing_percentage' => 50,
            'availability_type' => 'immediate',
            'end_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'questions' => [
                [
                    'question_text' => 'What does a speedometer measure?',
                    'points' => 1,
                    'options' => [
                        ['option_text' => 'Velocity', 'is_correct' => false],
                        ['option_text' => 'Speed', 'is_correct' => true],
                    ],
                ],
            ],
        ])
        ->upload('data.questions.0.question_image', [UploadedFile::fake()->image('q1.png')], true)
        ->call('create')
        ->assertHasNoFormErrors();

    $quiz = Quiz::where('title', 'Media Quiz')->firstOrFail();
    $question = $quiz->questions()->firstOrFail();

    expect($question->question_image)->not->toBeNull();
    Storage::disk('local')->assertExists((string) $question->question_image);
});

test('a CSV-imported question keeps its uploaded image and video on teacher create', function () {
    Storage::fake('local');
    $this->seed();

    Filament::setCurrentPanel('teacher');
    $this->actingAs(User::where('email', 'teacher1@example.com')->firstOrFail());

    $class = LearningClass::where('name', '10-B Science & Physics')->firstOrFail();

    $csv = "question,option_a,option_b,correct_option,points\n"
        ."What is 2+2?,3,4,B,2\n";

    $file = UploadedFile::fake()->createWithContent('quiz.csv', $csv);

    Livewire::test(TeacherCreateQuiz::class)
        ->fillForm([
            'title' => 'Teacher CSV Media Quiz',
            'learning_class_id' => $class->getKey(),
            'teacher_ids' => [$class->teachers()->firstOrFail()->getKey()],
            'passing_percentage' => 50,
            'availability_type' => 'immediate',
            'end_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
        ])
        ->set('data.import_file', $file)
        ->assertSet('data.questions.0.question_text', 'What is 2+2?')
        ->upload('data.questions.0.question_image', [UploadedFile::fake()->image('q1.png')], true)
        ->upload('data.questions.0.question_video', [UploadedFile::fake()->create('clip.mp4', 100, 'video/mp4')], true)
        ->call('create')
        ->assertHasNoFormErrors();

    $quiz = Quiz::where('title', 'Teacher CSV Media Quiz')->firstOrFail();
    $question = $quiz->questions()->firstOrFail();

    expect($question->question_image)->not->toBeNull()
        ->and($question->question_video)->not->toBeNull();

    Storage::disk('local')->assertExists((string) $question->question_image);
    Storage::disk('local')->assertExists((string) $question->question_video);
});

test('a CSV-imported question keeps its uploaded image on admin create', function () {
    Storage::fake('local');
    $this->seed();

    Filament::setCurrentPanel('admin');
    $this->actingAs(User::where('email', 'admin1@example.com')->firstOrFail());

    $class = LearningClass::where('name', '10-B Science & Physics')->firstOrFail();
    $teacher = $class->teachers()->firstOrFail();

    $csv = "question,option_a,option_b,correct_option,points\n"
        ."What is 2+2?,3,4,B,2\n";

    $file = UploadedFile::fake()->createWithContent('quiz.csv', $csv);

    Livewire::test(AdminCreateQuiz::class)
        ->fillForm([
            'title' => 'Admin CSV Media Quiz',
            'learning_class_id' => $class->getKey(),
            'teacher_ids' => [$teacher->getKey()],
            'passing_percentage' => 50,
            'availability_type' => 'immediate',
            'end_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
        ])
        ->set('data.import_file', $file)
        ->assertSet('data.questions.0.question_text', 'What is 2+2?')
        ->upload('data.questions.0.question_image', [UploadedFile::fake()->image('a1.png')], true)
        ->call('create')
        ->assertHasNoFormErrors();

    $quiz = Quiz::where('title', 'Admin CSV Media Quiz')->firstOrFail();
    $question = $quiz->questions()->firstOrFail();

    expect($question->question_image)->not->toBeNull();

    Storage::disk('local')->assertExists((string) $question->question_image);
});
