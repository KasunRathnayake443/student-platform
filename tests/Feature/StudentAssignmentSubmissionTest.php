<?php

use App\Filament\Student\Pages\AssignmentView;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AssignmentSubmissionAttachment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function svStudentUser(string $email = 'student1@example.com'): User
{
    return User::where('email', $email)->firstOrFail();
}

function svStudent(User $user): Student
{
    return Student::where('user_id', $user->getKey())->firstOrFail();
}

function svAssignment(): Assignment
{
    return Assignment::where('title', 'Quadratic Problem Set & Vertex Form Applications')->firstOrFail();
}

function svFreshTeacherAssignment(): Assignment
{
    return Assignment::firstOrCreate(
        ['title' => 'Repro: Files Check Assignment'],
        [
            'learning_class_id' => \App\Models\LearningClass::where('name', '10-A Mathematics')->firstOrFail()->getKey(),
            'teacher_id' => \App\Models\Teacher::where('employee_no', 'EMP-T-1002')->firstOrFail()->getKey(),
            'description' => 'Test',
            'max_score' => 10,
            'availability_type' => 'immediate',
            'allowed_submission_types' => ['file', 'text'],
            'is_published' => true,
        ]
    );
}

function svOpenTextAssignment(): Assignment
{
    return Assignment::firstOrCreate(
        ['title' => 'Repro: Open Text Assignment'],
        [
            'learning_class_id' => \App\Models\LearningClass::where('name', '10-A Mathematics')->firstOrFail()->getKey(),
            'teacher_id' => \App\Models\Teacher::where('employee_no', 'EMP-T-1002')->firstOrFail()->getKey(),
            'description' => 'Test',
            'max_score' => 10,
            'availability_type' => 'immediate',
            'allowed_submission_types' => ['text'],
            'is_published' => true,
        ]
    );
}

test('student can render an open assignment page', function () {
    $this->seed();
    $this->actingAs(svStudentUser());

    Livewire::withQueryParams(['assignment' => svAssignment()->getKey()])
        ->test(AssignmentView::class)
        ->assertSuccessful()
        ->assertSee('Quadratic Problem Set & Vertex Form Applications');
});

test('student can submit a text answer to an open assignment', function () {
    $this->seed();
    $this->actingAs(svStudentUser());

    $assignment = svOpenTextAssignment();

    Livewire::withQueryParams(['assignment' => $assignment->getKey()])
        ->test(AssignmentView::class)
        ->assertSuccessful()
        ->set('submissionContent', 'My working out: x = 2, x = 3')
        ->call('submitAssignment')
        ->assertSet('submissionMessageType', 'success');

    $student = svStudent(svStudentUser());
    $submission = AssignmentSubmission::where('assignment_id', $assignment->getKey())
        ->where('student_id', $student->getKey())
        ->first();

    expect($submission)->not->toBeNull()
        ->and($submission->content)->toContain('My working out')
        ->and($submission->status)->toBe('submitted')
        ->and($submission->is_late)->toBeFalse();
});

test('student can submit a file and it is stored as an attachment the teacher sees', function () {
    Storage::fake('public');
    $this->seed();
    $this->actingAs(svStudentUser());

$assignment = svFreshTeacherAssignment();
    $pdf = UploadedFile::fake()->create('working.pdf', 100, 'application/pdf');

    Livewire::withQueryParams(['assignment' => $assignment->getKey()])
        ->test(AssignmentView::class)
        ->assertSuccessful()
        ->upload('submissionFiles', [$pdf], true)
        ->call('submitAssignment')
        ->assertSet('submissionMessageType', 'success');

    $student = svStudent(svStudentUser());
    $submission = AssignmentSubmission::where('assignment_id', $assignment->getKey())
        ->where('student_id', $student->getKey())
        ->first();

    expect($submission)->not->toBeNull();
    $attachment = AssignmentSubmissionAttachment::where('assignment_submission_id', $submission->getKey())->first();

    expect($attachment)->not->toBeNull()
        ->and($attachment->original_name)->toBe('working.pdf');

    Storage::disk('public')->assertExists($attachment->file_path);
});

test('a scheduled assignment that has not started shows the form locked', function () {
    $this->seed();
    $this->actingAs(svStudentUser());

    $assignment = Assignment::firstOrCreate(
        ['title' => 'Repro: Scheduled Assignment'],
        [
            'learning_class_id' => \App\Models\LearningClass::where('name', '10-A Mathematics')->firstOrFail()->getKey(),
            'teacher_id' => \App\Models\Teacher::where('employee_no', 'EMP-T-1002')->firstOrFail()->getKey(),
            'max_score' => 10,
            'availability_type' => 'scheduled',
            'start_at' => now()->addDays(1),
            'end_at' => now()->addDays(3),
            'allowed_submission_types' => ['text'],
            'is_published' => true,
        ]
    );

    Livewire::withQueryParams(['assignment' => $assignment->getKey()])
        ->test(AssignmentView::class)
        ->assertSuccessful()
        ->assertSee('NOT STARTED YET')
        ->assertSee('SUBMISSIONS ARE LOCKED')
        ->assertDontSee('Submit your assignment');
});

test('teacher submissions table shows an attachment count badge', function () {
    Storage::fake('public');
    $this->seed();

Storage::disk('public')->put('submissions/demo.pdf', 'fake');
    $student = svStudent(svStudentUser());
    $assignment = svFreshTeacherAssignment();

    $submission = AssignmentSubmission::create([
        'assignment_id' => $assignment->getKey(),
        'student_id' => $student->getKey(),
        'content' => '<p>My report</p>',
        'submitted_at' => now(),
        'is_late' => false,
        'status' => 'submitted',
    ]);

    AssignmentSubmissionAttachment::create([
        'assignment_submission_id' => $submission->getKey(),
        'original_name' => 'working.pdf',
        'file_path' => 'submissions/demo.pdf',
        'mime_type' => 'application/pdf',
        'file_size' => 4,
        'sort_order' => 0,
    ]);

    $this->actingAs(User::where('email', 'teacher2@example.com')->firstOrFail());

    Livewire::test(
        \App\Filament\Teacher\Resources\Assignments\RelationManagers\SubmissionsRelationManager::class,
        [
            'ownerRecord' => $assignment,
            'pageClass' => \App\Filament\Teacher\Resources\Assignments\Pages\ViewAssignment::class,
        ]
    )
        ->assertSuccessful()
        ->assertSee('1 file');
});

test('teacher submission attachments render as downloadable links', function () {
    Storage::fake('public');
    $this->seed();

Storage::disk('public')->put('submissions/demo.pdf', 'fake');
    $student = svStudent(svStudentUser());
    $assignment = svFreshTeacherAssignment();

    $submission = AssignmentSubmission::create([
        'assignment_id' => $assignment->getKey(),
        'student_id' => $student->getKey(),
        'content' => '<p>My report</p>',
        'submitted_at' => now(),
        'is_late' => false,
        'status' => 'submitted',
    ]);

    AssignmentSubmissionAttachment::create([
        'assignment_submission_id' => $submission->getKey(),
        'original_name' => 'working.pdf',
        'file_path' => 'submissions/demo.pdf',
        'mime_type' => 'application/pdf',
        'file_size' => 4,
        'sort_order' => 0,
    ]);

    $this->actingAs(User::where('email', 'teacher2@example.com')->firstOrFail());

    // The share helper renders a working, storage-backed URL for each attachment.
    $rm = Livewire::test(
        \App\Filament\Teacher\Resources\Assignments\RelationManagers\SubmissionsRelationManager::class,
        [
            'ownerRecord' => $assignment,
            'pageClass' => \App\Filament\Teacher\Resources\Assignments\Pages\ViewAssignment::class,
        ]
    )->instance();

    $html = (new ReflectionMethod($rm, 'submissionAttachmentsHtml'))->invoke($rm, $submission);

    expect((string) $html)->toContain('/storage/submissions/demo.pdf')
        ->and((string) $html)->toContain('working.pdf')
        ->and((string) $html)->toContain('download');
});
