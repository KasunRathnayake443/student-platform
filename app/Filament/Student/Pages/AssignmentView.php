<?php

namespace App\Filament\Student\Pages;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AssignmentSubmissionAttachment;
use Carbon\CarbonInterface;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class AssignmentView extends Page
{
    protected static string $layout = 'filament-panels::components.layout.base';

    protected string $view = 'filament.student.pages.assignment-view';

    protected static ?string $slug = 'assignment';

    public ?Assignment $assignment = null;

    public ?AssignmentSubmission $submission = null;

    public string $tier = 'junior';

    public ?string $schoolName = null;

    public ?string $gradeName = null;

    public ?string $className = null;

    public ?string $teacherName = null;

    public string $submissionContent = '';

    /** @var array<int, TemporaryUploadedFile>|null */
    public $submissionFiles = null;

    public string $submissionMessage = '';

    public string $submissionMessageType = 'success';

    public bool $editing = false;

    /** @var array<int, int> */
    public array $pendingRemovalAttachmentIds = [];

    public function mount(): void
    {
        $user = Auth::user();
        $student = $user?->student;

        if (! $student) {
            $this->redirect('/student');

            return;
        }

        $assignmentId = (int) request()->query('assignment');

        /** @var Assignment|null $assignment */
        $assignment = Assignment::with([
            'learningClass.grade.school',
            'learningClass.teachers.user',
            'teacher.user',
            'attachments',
        ])
            ->where('is_published', true)
            ->find($assignmentId);

        if (! $assignment) {
            abort(404);
        }

        // The student must be enrolled in the assignment's class before viewing it.
        $enrollments = $student->enrollments()
            ->with(['school', 'grade', 'classes'])
            ->where('status', 'active')
            ->get();

        $enrolledInClass = $enrollments->contains(
            fn ($enrollment) => $enrollment->classes->contains('id', $assignment->learning_class_id)
        );

        if (! $enrolledInClass) {
            abort(403);
        }

        $class = $assignment->learningClass;

        $this->assignment = $assignment;
        $this->tier = $student->getAgeTier();
        $this->schoolName = $class?->grade?->school?->name ?? 'My School';
        $this->gradeName = $class?->grade?->name ?? null;
        $this->className = $class?->name ?? 'General Class';
        $this->teacherName = $assignment->teacher?->user?->name
            ?? $class?->teachers->first()?->user?->name
            ?? 'Your Teacher';

        $this->submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->with(['attachments', 'grader.user', 'assignment'])
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Availability helpers
    |--------------------------------------------------------------------------
    */

    public function availabilityState(): string
    {
        $assignment = $this->assignment;

        if ($this->isGradedSubmission()) {
            return 'graded';
        }

        if (! $assignment->isAvailable()) {
            return 'not_started';
        }

        if ($assignment->isExpired()) {
            return $assignment->acceptsLateSubmissions() ? 'late' : 'closed';
        }

        return 'open';
    }

    public function isGradedSubmission(): bool
    {
        return $this->submission && $this->submission->isGraded() && $this->submission->score !== null;
    }

    public function canSubmitNow(): bool
    {
        return in_array($this->availabilityState(), ['open', 'late'], true);
    }

    public function allowsText(): bool
    {
        return in_array('text', $this->assignment->allowed_submission_types ?? ['text'], true);
    }

    public function allowsFiles(): bool
    {
        $types = $this->assignment->allowed_submission_types ?? [];

        return collect($types)->reject(fn ($type) => $type === 'text')->isNotEmpty();
    }

    /**
     * MIME types accepted for file uploads based on the teacher's allowed types.
     *
     * @return array<int, string>
     */
    public function acceptedMimeTypes(): array
    {
        $map = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image' => 'image/*',
            'video' => 'video/*',
            'audio' => 'audio/*',
            'zip' => 'application/zip,application/x-rar-compressed',
            'txt' => 'text/plain',
        ];

        return collect($this->assignment->allowed_submission_types ?? [])
            ->reject(fn ($type) => $type === 'text')
            ->map(fn ($type) => $map[$type] ?? null)
            ->filter()
            ->values()
            ->flatMap(fn ($value) => explode(',', (string) $value))
            ->values()
            ->all();
    }

    public function submissionTypeLabels(): array
    {
        $labels = [
            'text' => 'Text Answer',
            'pdf' => 'PDF',
            'doc' => 'Word (.doc)',
            'docx' => 'Word (.docx)',
            'ppt' => 'PowerPoint (.ppt)',
            'pptx' => 'PowerPoint (.pptx)',
            'xls' => 'Excel (.xls)',
            'xlsx' => 'Excel (.xlsx)',
            'image' => 'Images',
            'video' => 'Video',
            'audio' => 'Audio / MP3',
            'zip' => 'ZIP Archive',
            'txt' => 'Text File (.txt)',
        ];

        return collect($this->assignment->allowed_submission_types ?? [])
            ->map(fn ($type) => $labels[$type] ?? ucfirst((string) $type))
            ->values()
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | Submission
    |--------------------------------------------------------------------------
    */

    /**
     * Human-readable file size for pending upload previews.
     */
    public function pendingFileSize(TemporaryUploadedFile $file): string
    {
        $bytes = $file->getSize();

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1).' MB';
        }

        return round($bytes / 1024, 1).' KB';
    }

    /**
     * Signed, short-lived URL that serves a Livewire temporary upload so the
     * student can open a newly chosen file before it is submitted.
     */
    public function pendingFileUrl(TemporaryUploadedFile $file): string
    {
        return URL::temporarySignedRoute(
            'livewire.preview-file',
            now()->addMinutes(30)->endOfHour(),
            ['filename' => $file->getFilename()]
        );
    }

    public function isImageUpload(TemporaryUploadedFile $file): bool
    {
        return in_array(strtolower($file->guessExtension()), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'], true);
    }

    /**
     * Remove a pending (not yet saved) livewire upload from the list.
     */
    public function removePendingFile(int $index): void
    {
        $files = collect($this->submissionFiles ?? []);

        if ($remove = $files->get($index)) {
            $files->forget($index);
        }

        $this->submissionFiles = $files->values()->all();
    }

    /**
     * Start editing an existing submission.
     */
    public function startEditing(): void
    {
        $this->submissionMessage = '';
        $this->editing = true;
        $this->submissionContent = $this->submission?->content ?? '';
        $this->pendingRemovalAttachmentIds = [];
    }

    public function cancelEditing(): void
    {
        $this->editing = false;
        $this->submissionContent = '';
        $this->submissionFiles = null;
        $this->pendingRemovalAttachmentIds = [];
    }

    /**
     * Toggle whether an already-saved attachment should be removed on save.
     */
    public function toggleAttachmentRemoval(int $attachmentId): void
    {
        if (in_array($attachmentId, $this->pendingRemovalAttachmentIds, true)) {
            $this->pendingRemovalAttachmentIds = array_values(
                array_diff($this->pendingRemovalAttachmentIds, [$attachmentId])
            );
        } else {
            $this->pendingRemovalAttachmentIds[] = $attachmentId;
        }
    }

    protected function validateSubmissionInput(): bool
    {
        $rules = [];
        if ($this->allowsText()) {
            $rules['submissionContent'] = ['nullable', 'string', 'max:20000'];
        }
        if ($this->allowsFiles()) {
            $rules['submissionFiles'] = ['nullable', 'array', 'max:10'];
            $rules['submissionFiles.*'] = ['file', 'max:51200'];
        }
        $this->validate($rules);

        $acceptedMimes = $this->acceptedMimeTypes();
        $files = collect($this->submissionFiles ?? []);

        if ($this->allowsFiles() && $acceptedMimes) {
            foreach ($files as $file) {
                $mime = (string) $file->getMimeType();
                $matched = false;

                foreach ($acceptedMimes as $accepted) {
                    if (str_ends_with($accepted, '/*')) {
                        if (str_starts_with($mime, substr($accepted, 0, -1))) {
                            $matched = true;
                            break;
                        }
                    } elseif ($accepted === $mime) {
                        $matched = true;
                        break;
                    }
                }

                if (! $matched) {
                    $this->submissionMessage = 'One of your files is not an allowed submission type for this assignment.';
                    $this->submissionMessageType = 'error';

                    return false;
                }
            }
        }

        return true;
    }

    protected function storePendingFiles(AssignmentSubmission $submission): void
    {
        $files = collect($this->submissionFiles ?? []);

        if ($files->isEmpty()) {
            return;
        }

        $sortOrder = $submission->attachments()->max('sort_order') ?? -1;

        foreach ($files as $file) {
            $path = $file->store('submissions', 'public');

            AssignmentSubmissionAttachment::create([
                'assignment_submission_id' => $submission->id,
                'original_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'sort_order' => ++$sortOrder,
            ]);
        }
    }

    public function submitAssignment(): void
    {
        $assignment = $this->assignment;

        if (! $this->canSubmitNow()) {
            $this->submissionMessage = 'Submissions are closed for this assignment right now.';
            $this->submissionMessageType = 'error';

            return;
        }

        if ($this->submission) {
            $this->submissionMessage = 'You have already submitted this assignment. Use the Edit button to make changes.';
            $this->submissionMessageType = 'error';

            return;
        }

        if (! $this->validateSubmissionInput()) {
            return;
        }

        $hasContent = $this->allowsText() && trim((string) $this->submissionContent) !== '';
        $files = collect($this->submissionFiles ?? []);

        if (! $hasContent && $files->isEmpty()) {
            $this->submissionMessage = 'Please write an answer or upload at least one file before submitting.';
            $this->submissionMessageType = 'error';

            return;
        }

        $now = now();
        $studentId = Auth::user()?->student?->id;

        /** @var AssignmentSubmission $submission */
        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $studentId,
            'content' => $hasContent ? $this->submissionContent : null,
            'submitted_at' => $now,
            'is_late' => $assignment->end_at ? $now->gt($assignment->end_at) : false,
            'status' => 'submitted',
        ]);

        $this->storePendingFiles($submission);

        $this->submission = $submission->load(['attachments', 'grader.user', 'assignment']);
        $this->submissionContent = '';
        $this->submissionFiles = null;
        $this->editing = false;
        $this->submissionMessage = 'Submitted successfully! Your teacher has received your assignment.';
        $this->submissionMessageType = 'success';
    }

    public function saveSubmissionChanges(): void
    {
        $assignment = $this->assignment;
        $submission = $this->submission;

        if (! $this->canSubmitNow()) {
            $this->submissionMessage = 'Submissions are closed for this assignment right now.';
            $this->submissionMessageType = 'error';

            return;
        }

        if (! $submission || $this->isGradedSubmission()) {
            $this->submissionMessage = 'This submission can no longer be changed.';
            $this->submissionMessageType = 'error';

            return;
        }

        if (! $this->validateSubmissionInput()) {
            return;
        }

        $hasContent = $this->allowsText() && trim((string) $this->submissionContent) !== '';
        $newFiles = collect($this->submissionFiles ?? []);

        if (! $hasContent && $newFiles->isEmpty() && $submission->attachments()->count() === count($this->pendingRemovalAttachmentIds)) {
            $this->submissionMessage = 'Your submission must keep a written answer or at least one attachment.';
            $this->submissionMessageType = 'error';

            return;
        }

        foreach ($this->pendingRemovalAttachmentIds as $attachmentId) {
            /** @var AssignmentSubmissionAttachment|null $attachment */
            $attachment = AssignmentSubmissionAttachment::find($attachmentId);

            if ($attachment && (int) $attachment->assignment_submission_id === (int) $submission->id) {
                if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
                    Storage::disk('public')->delete($attachment->file_path);
                }
                $attachment->delete();
            }
        }

        $submission->update([
            'content' => $hasContent ? $this->submissionContent : null,
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);

        $this->storePendingFiles($submission);

        $this->submission = $submission->refresh()->load(['attachments', 'grader.user', 'assignment']);
        $this->submissionContent = '';
        $this->submissionFiles = null;
        $this->pendingRemovalAttachmentIds = [];
        $this->editing = false;
        $this->submissionMessage = 'Changes saved! Your updated answer has been sent to your teacher.';
        $this->submissionMessageType = 'success';
    }

    public function getTitle(): string
    {
        return $this->assignment?->title ?? 'Assignment';
    }

    /**
     * Human "time until" label for the schedule countdown, e.g. "1 day 3h" / "42m".
     */
    public function timeUntil(?CarbonInterface $date): string
    {
        if (! $date || ! $date->isFuture()) {
            return '';
        }

        $seconds = max(0, $date->diffInSeconds(now()));

        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        if ($days > 0) {
            return "{$days} day".($days > 1 ? 's' : '')." $hours h";
        }

        if ($hours > 0) {
            return "{$hours} h $minutes m";
        }

        return $minutes.' m';
    }
}
