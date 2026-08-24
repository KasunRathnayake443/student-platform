<?php

use App\Filament\Teacher\Resources\LearningClasses\Pages\ViewLearningClass;
use App\Filament\Teacher\Resources\LearningClasses\RelationManagers\QuizzesRelationManager;
use App\Models\LearningClass;
use App\Models\Quiz;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function qzUser(string $email): User
{
    return User::where('email', $email)->firstOrFail();
}

function qzTeacherByNo(string $employeeNo): Teacher
{
    return Teacher::where('employee_no', $employeeNo)->firstOrFail();
}

function sciencePhysicsClassForQuizzes(): LearningClass
{
    // teacher1's class: 10-B Science & Physics
    return LearningClass::where('name', '10-B Science & Physics')->firstOrFail();
}

function seededClass2Quiz(): Quiz
{
    return Quiz::where('learning_class_id', LearningClass::where('name', '10-B Science & Physics')->firstOrFail()->getKey())
        ->firstOrFail();
}

function quizQuestionData(): array
{
    return [
        [
            'question_text' => 'What does a speedometer measure?',
            'points' => 1,
            'options' => [
                ['option_text' => 'Velocity', 'is_correct' => false],
                ['option_text' => 'Speed', 'is_correct' => true],
            ],
        ],
    ];
}

function createQuizViaAction($component, array $data): void
{
    $component
        ->mountTableAction('createQuiz')
        ->set('mountedActions.0.data', array_merge([
            'passing_percentage' => 50,
            'max_attempts' => 1,
            'available_immediately' => true,
            'availability_type' => 'immediate',
            'end_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'questions' => quizQuestionData(),
            'teacher_ids' => [auth()->user()->teacher->getKey()],
        ], $data))
        ->callMountedTableAction();
}

test('quizzes tab lists existing quizzes and links to the teacher panel', function () {
    $this->seed();
    $this->actingAs(qzUser('teacher1@example.com'));

    $quiz = seededClass2Quiz();

    Livewire::test(QuizzesRelationManager::class, [
        'ownerRecord' => sciencePhysicsClassForQuizzes(),
        'pageClass' => ViewLearningClass::class,
    ])
        ->assertSuccessful()
        ->assertSee($quiz->title)
        ->assertSee('Create Quiz')
        ->assertSee('/teacher/quizzes/');
});

test('created quizzes are automatically assigned to the authenticated teacher', function () {
    $this->seed();
    $me = qzTeacherByNo('EMP-T-1001');
    $this->actingAs(qzUser('teacher1@example.com'));

    createQuizViaAction(
        Livewire::test(QuizzesRelationManager::class, [
            'ownerRecord' => sciencePhysicsClassForQuizzes(),
            'pageClass' => ViewLearningClass::class,
        ]),
        ['title' => 'Kinematics Quick Check'],
    );

    $quiz = Quiz::where('title', 'Kinematics Quick Check')->firstOrFail();

    expect($quiz->teacher_id)->toBe($me->getKey())
        ->and($quiz->total_points)->toBe(1)
        ->and($quiz->questions()->count())->toBe(1)
        ->and($quiz->teachers()->pluck('teachers.id')->toArray())->toBe([$me->getKey()]);
});

test('a quiz can be co-assigned to other teachers of the same class', function () {
    $this->seed();
    $me = qzTeacherByNo('EMP-T-1001');
    $coTeacher = qzTeacherByNo('EMP-T-1002');
    $this->actingAs(qzUser('teacher1@example.com'));

    $class = sciencePhysicsClassForQuizzes();
    $class->teachers()->syncWithoutDetaching([$coTeacher->getKey()]);

    createQuizViaAction(
        Livewire::test(QuizzesRelationManager::class, [
            'ownerRecord' => $class,
            'pageClass' => ViewLearningClass::class,
        ]),
        [
            'title' => 'Shared Review Quiz',
            'questions' => [[
                'question_text' => 'Unit of force?',
                'points' => 2,
                'options' => [
                    ['option_text' => 'Newton', 'is_correct' => true],
                    ['option_text' => 'Joule', 'is_correct' => false],
                ],
            ]],
            'teacher_ids' => [$me->getKey(), $coTeacher->getKey()],
        ],
    );

    $quiz = Quiz::where('title', 'Shared Review Quiz')->firstOrFail();
    $assignees = $quiz->teachers()->orderBy('teachers.id')->pluck('teachers.id');

    expect(in_array($me->getKey(), $assignees->all()))->toBeTrue()
        ->and(in_array($coTeacher->getKey(), $assignees->all()))->toBeTrue();
});

test('teachers outside the class cannot be assigned to a quiz', function () {
    $this->seed();
    $me = qzTeacherByNo('EMP-T-1001');
    $outsider = qzTeacherByNo('EMP-T-1003');
    $this->actingAs(qzUser('teacher1@example.com'));

    createQuizViaAction(
        Livewire::test(QuizzesRelationManager::class, [
            'ownerRecord' => sciencePhysicsClassForQuizzes(),
            'pageClass' => ViewLearningClass::class,
        ]),
        [
            'title' => 'Hijacked Quiz',
            'questions' => [[
                'question_text' => 'Q?',
                'points' => 1,
                'options' => [
                    ['option_text' => 'A', 'is_correct' => true],
                    ['option_text' => 'B', 'is_correct' => false],
                ],
            ]],
            'teacher_ids' => [$me->getKey(), $outsider->getKey()],
        ],
    );

    expect(Quiz::where('title', 'Hijacked Quiz')->exists())->toBeFalse();
});

test('own-class quizzes can be viewed and edited without delete access', function () {
    $this->seed();
    $this->actingAs(qzUser('teacher1@example.com'));

    $quiz = seededClass2Quiz();

    $this->get("/teacher/quizzes/{$quiz->getKey()}")
        ->assertOk()
        ->assertDontSee('Delete Quiz');

    $this->get("/teacher/quizzes/{$quiz->getKey()}/edit")
        ->assertOk()
        ->assertDontSee('Delete Quiz');
});

test('lessons quizzes of other teachers classes are inaccessible', function () {
    $this->seed();
    $this->actingAs(qzUser('teacher1@example.com'));

    // A quiz in a class taught only by Sarah Connor
    $foreignClass = LearningClass::create([
        'grade_id' => sciencePhysicsClassForQuizzes()->grade_id,
        'name' => '10-D Foreign Quiz Class',
        'medium' => 'English',
        'is_active' => true,
    ]);
    $foreignClass->teachers()->syncWithoutDetaching([
        qzTeacherByNo('EMP-T-1002')->getKey(),
    ]);

    $quiz = Quiz::create([
        'learning_class_id' => $foreignClass->getKey(),
        'teacher_id' => qzTeacherByNo('EMP-T-1002')->getKey(),
        'title' => 'Secret Algebra Quiz',
        'passing_percentage' => 50,
        'max_attempts' => 1,
        'total_points' => 0,
        'availability_type' => 'immediate',
        'is_published' => true,
    ]);

    $this->get("/teacher/quizzes/{$quiz->getKey()}")
        ->assertNotFound();

    $this->get("/teacher/quizzes/{$quiz->getKey()}/edit")
        ->assertNotFound();
});
