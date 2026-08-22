<?php

use App\Filament\Teacher\Resources\LearningClasses\Pages\ViewLearningClass;
use App\Filament\Teacher\Resources\LearningClasses\RelationManagers\StudentsRelationManager;
use App\Filament\Teacher\Resources\Students\Pages\ViewStudent;
use App\Filament\Teacher\Resources\Students\RelationManagers\StudentAssignmentsRelationManager;
use App\Filament\Teacher\Resources\Students\RelationManagers\StudentQuizzesRelationManager;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\LearningClass;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Services\ClassContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function teacherUser(string $email = 'teacher1@example.com'): User
{
    return User::where('email', $email)->firstOrFail();
}

function studentByEmail(string $email): Student
{
    return Student::whereHas('user', fn ($q) => $q->where('email', $email))->firstOrFail();
}

function scienceClass(): LearningClass
{
    // teacher1's class: 10-B Science & Physics (school 1, grade 10)
    return LearningClass::where('name', '10-B Science & Physics')->firstOrFail();
}

test('assign action only accepts students enrolled in same school and grade as the class', function () {
    $this->seed();
    $this->actingAs(teacherUser());

    $class = scienceClass();

    // Eligible: enrolled in school 1, grade 10, not yet in this class
    $eligible = studentByEmail('student9@example.com');
    // Same school but different grade -> must be rejected
    $wrongGrade = studentByEmail('student3@example.com');
    // Different school entirely -> must be rejected
    $otherSchool = studentByEmail('student7@example.com');

    $enrollmentCountBefore = StudentEnrollment::count();

    Livewire::test(StudentsRelationManager::class, [
        'ownerRecord' => $class,
        'pageClass' => ViewLearningClass::class,
    ])
        ->callTableAction('assignStudent', data: ['student_id' => $wrongGrade->getKey()]);

    expect($class->students()->whereKey($wrongGrade->getKey())->exists())->toBeFalse();

    Livewire::test(StudentsRelationManager::class, [
        'ownerRecord' => $class,
        'pageClass' => ViewLearningClass::class,
    ])
        ->callTableAction('assignStudent', data: ['student_id' => $otherSchool->getKey()]);

    expect($class->students()->whereKey($otherSchool->getKey())->exists())->toBeFalse();
    expect(StudentEnrollment::count())->toBe($enrollmentCountBefore);

    Livewire::test(StudentsRelationManager::class, [
        'ownerRecord' => $class,
        'pageClass' => ViewLearningClass::class,
    ])
        ->callTableAction('assignStudent', data: ['student_id' => $eligible->getKey()]);

    expect($class->students()->whereKey($eligible->getKey())->exists())->toBeTrue();
});

test('assign form only lists students from the same school and grade', function () {
    $this->seed();
    $this->actingAs(teacherUser());

    $class = scienceClass();

    $options = app(ClassContextService::class)
        ->eligibleStudentsQuery($class)
        ->get()
        ->mapWithKeys(fn ($enrollment) => [$enrollment->student_id => $enrollment->student?->user?->name.' - '.$enrollment->student->admission_no]);

    // Eligible (same school + grade, not yet in the class)
    expect($options->has(studentByEmail('student9@example.com')->getKey()))->toBeTrue();

    // Same school, different grade
    expect($options->has(studentByEmail('student3@example.com')->getKey()))->toBeFalse();

    // Different school
    expect($options->has(studentByEmail('student7@example.com')->getKey()))->toBeFalse();

    // Already assigned to the class
    expect($options->has(studentByEmail('student1@example.com')->getKey()))->toBeFalse();
});

test('assignments tab shows only submissions of the opened class when scoped', function () {
    $this->seed();
    $teacher = teacherUser();
    $this->actingAs($teacher);

    $class2 = scienceClass(); // taught by teacher1
    $student = studentByEmail('student1@example.com'); // has class1 + class2 submissions

    // Extra class taught by teacher1 with another submission by the same student
    $extraClass = LearningClass::create([
        'grade_id' => $class2->grade_id,
        'name' => '10-C Extra Class',
        'medium' => 'English',
        'is_active' => true,
    ]);
    $extraClass->teachers()->attach($teacher->teacher);

    $assignment = Assignment::create([
        'learning_class_id' => $extraClass->id,
        'teacher_id' => $teacher->teacher->id,
        'title' => 'Extra Class Assignment',
        'max_score' => 50,
        'availability_type' => 'immediate',
        'allowed_submission_types' => ['pdf'],
        'is_published' => true,
    ]);

    AssignmentSubmission::create([
        'assignment_id' => $assignment->id,
        'student_id' => $student->id,
        'status' => 'submitted',
        'submitted_at' => now(),
        'content' => '<p>Extra work</p>',
    ]);

    $mount = [
        'ownerRecord' => $student,
        'pageClass' => ViewStudent::class,
    ];

    // Scoped to the opened class -> only that class's submission
    Livewire::test(StudentAssignmentsRelationManager::class, [...$mount, 'classId' => $class2->getKey()])
        ->assertSuccessful()
        ->assertSee('Physics Lab Report: Measuring Acceleration (F = ma)')
        ->assertDontSee('Extra Class Assignment');

    // Unscoped fallback -> everything the teacher teaches
    Livewire::test(StudentAssignmentsRelationManager::class, $mount)
        ->assertSuccessful()
        ->assertSee('Physics Lab Report: Measuring Acceleration (F = ma)')
        ->assertSee('Extra Class Assignment')
        // Never shows items outside the teacher's classes
        ->assertDontSee('Quadratic Problem Set & Vertex Form Applications');
});

test('quizzes tab shows only attempts of the opened class when scoped', function () {
    $this->seed();
    $teacher = teacherUser();
    $this->actingAs($teacher);

    $class2 = scienceClass();
    $student = studentByEmail('student1@example.com');

    $extraClass = LearningClass::create([
        'grade_id' => $class2->grade_id,
        'name' => '10-C Quiz Class',
        'medium' => 'English',
        'is_active' => true,
    ]);
    $extraClass->teachers()->attach($teacher->teacher);

    // The profile can only be scoped to a class the student actually belongs to
    $enrollment = StudentEnrollment::where('student_id', $student->getKey())
        ->where('status', 'active')
        ->firstOrFail();
    $extraClass->students()->syncWithoutDetaching([
        $student->getKey() => ['student_enrollment_id' => $enrollment->id],
    ]);

    $quiz = Quiz::create([
        'learning_class_id' => $extraClass->id,
        'teacher_id' => $teacher->teacher->id,
        'title' => 'Extra Class Quiz',
        'total_points' => 4,
        'time_limit_minutes' => 30,
        'max_attempts' => 1,
        'passing_percentage' => 50,
        'availability_type' => 'immediate',
        'is_published' => true,
    ]);

    QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'student_id' => $student->id,
        'attempt_number' => 1,
        'started_at' => now()->subHour(),
        'completed_at' => now()->subMinutes(30),
        'score' => 4,
        'percentage' => 100,
        'is_passed' => true,
        'status' => 'submitted',
    ]);

    // Attempt on teacher1's existing class2 quiz ("Newton's Laws...")
    $class2Quiz = Quiz::where('learning_class_id', $class2->getKey())->firstOrFail();
    QuizAttempt::create([
        'quiz_id' => $class2Quiz->id,
        'student_id' => $student->id,
        'attempt_number' => 1,
        'started_at' => now()->subHours(2),
        'completed_at' => now()->subHour(),
        'score' => 3,
        'percentage' => 75,
        'is_passed' => true,
        'status' => 'submitted',
    ]);

    $mount = [
        'ownerRecord' => $student,
        'pageClass' => ViewStudent::class,
    ];

    // Scoped to the opened class -> only that class's attempts
    Livewire::test(StudentQuizzesRelationManager::class, [...$mount, 'classId' => $class2->getKey()])
        ->assertSuccessful()
        ->assertSee("Newton's Laws of Motion Assessment")
        ->assertDontSee('Extra Class Quiz');

    Livewire::test(StudentQuizzesRelationManager::class, [...$mount, 'classId' => $extraClass->getKey()])
        ->assertSuccessful()
        ->assertSee('Extra Class Quiz')
        ->assertDontSee("Newton's Laws of Motion Assessment");

    Livewire::test(StudentQuizzesRelationManager::class, $mount)
        ->assertSuccessful()
        ->assertSee("Newton's Laws of Motion Assessment")
        ->assertSee('Extra Class Quiz');
});
