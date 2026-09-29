<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Grade;
use App\Models\LearningClass;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class SchoolEmailNotificationService
{
    public function __construct(private SchoolMailTransport $mailTransport) {}

    /**
     * Send a teacher-authored message to the students of one class, and
     * optionally to each student's parent or guardian email.
     *
     * When an assignment or quiz is supplied, the matching submission score
     * or attempt score is added per student.
     *
     * @param  Collection<int, Student>  $students
     */
    public function sendCustomStudentMessage(
        LearningClass $class,
        Collection $students,
        string $message,
        ?Assignment $assignment = null,
        ?Quiz $quiz = null,
        bool $includeParents = true,
        ?string $teacherName = null,
    ): int {
        $school = $this->schoolForClass($class);

        if (! $school instanceof School || ! $school->is_active || ! $this->mailTransport->isConfigured($school)) {
            return 0;
        }

        $sender = $teacherName !== null && trim($teacherName) !== ''
            ? trim($teacherName)
            : $this->teacherName(null);

        $subject = "Message from {$sender}: ".$class->name;
        $dispatched = 0;

        foreach ($students as $student) {
            $studentUser = $student->user;
            $parentEmail = $student->parentEmailAddress();

            $lines = $this->customMessageLines($class, $sender, $message, $student, $assignment, $quiz);

            if ($studentUser instanceof User && filled($studentUser->email)) {
                Notification::send(
                    $studentUser,
                    new SchoolActivityNotification(
                        $school->getKey(),
                        $subject,
                        $this->messageIntro($sender, $class),
                        $lines,
                    ),
                );

                $dispatched++;
            }

            if ($includeParents && $parentEmail !== null) {
                Notification::route('mail', $parentEmail)->notify(
                    new SchoolActivityNotification(
                        $school->getKey(),
                        $subject,
                        $this->messageIntro($sender, $class),
                        $this->parentMessageLines($student, $lines),
                    ),
                );

                $dispatched++;
            }
        }

        return $dispatched;
    }

    protected function messageIntro(string $teacherName, LearningClass $class): string
    {
        return "{$teacherName} has sent a message to the students of {$class->name}.";
    }

    /**
     * Parents need to know which student the message is about, so the name
     * is prepended to the lines the student themselves receives.
     *
     * @param  array<int, string>  $studentLines
     * @return array<int, string>
     */
    protected function parentMessageLines(Student $student, array $studentLines): array
    {
        $name = trim((string) $student->user->name);

        return [
            'Student: '.($name !== '' ? $name : $student->admission_no),
            ...$studentLines,
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function customMessageLines(
        LearningClass $class,
        string $teacherName,
        string $message,
        Student $student,
        ?Assignment $assignment,
        ?Quiz $quiz,
    ): array {
        $lines = [
            'Class: '.$class->name,
            'Teacher: '.$teacherName,
            '',
            trim($message),
        ];

        if ($assignment instanceof Assignment) {
            $lines[] = '';
            $lines[] = 'Assignment: '.$assignment->title;
            $lines[] = $this->assignmentScoreLine($assignment, $student);
        }

        if ($quiz instanceof Quiz) {
            $lines[] = '';
            $lines[] = 'Quiz: '.$quiz->title;
            $lines[] = $this->quizScoreLine($quiz, $student);
        }

        return $lines;
    }

    protected function assignmentScoreLine(Assignment $assignment, Student $student): string
    {
        $submission = AssignmentSubmission::query()
            ->where('assignment_id', $assignment->getKey())
            ->where('student_id', $student->getKey())
            ->latest('id')
            ->first();

        if (! $submission instanceof AssignmentSubmission) {
            return 'Your submission: Not submitted';
        }

        if ($submission->score === null) {
            return 'Your submission: Submitted, not graded yet';
        }

        return 'Your score: '.$this->formatScore((float) $submission->score).' / '.$assignment->max_score;
    }

    protected function quizScoreLine(Quiz $quiz, Student $student): string
    {
        $attempt = QuizAttempt::query()
            ->where('quiz_id', $quiz->getKey())
            ->where('student_id', $student->getKey())
            ->whereNotNull('score')
            ->latest('id')
            ->first();

        if (! $attempt instanceof QuizAttempt) {
            return 'Your attempt: No completed attempt recorded';
        }

        $line = 'Your score: '.$this->formatScore((float) $attempt->score).' / '.$quiz->total_points;

        $percentage = $attempt->percentage;

        if (filled($percentage)) {
            $line .= ' ('.$this->formatScore((float) $percentage).'%)';
        }

        if ($attempt->is_passed) {
            $line .= ' - Passed';
        }

        return $line;
    }

    protected function formatScore(float $score): string
    {
        return rtrim(rtrim(number_format($score, 2), '0'), '.');
    }

    public function quizCreated(Quiz $quiz): void
    {
        if (! $quiz->is_published) {
            $this->markQuizEmailNotSent($quiz);

            return;
        }

        $this->notifyPublishedQuiz($quiz);
    }

    public function notifyPublishedQuiz(Quiz $quiz): void
    {
        if (! $quiz->is_published) {
            return;
        }

        $this->sendQuizNotification($quiz);
        $this->markQuizEmailSent($quiz);
    }

    public function sendQuizNotification(Quiz $quiz): void
    {
        $quiz->loadMissing([
            'learningClass.grade.school',
            'teacher.user',
        ]);

        $class = $quiz->learningClass;

        if (! $class instanceof LearningClass) {
            return;
        }

        $school = $this->schoolForClass($class);

        if (! $school instanceof School) {
            return;
        }

        $teacherName = $this->teacherName($quiz->teacher);
        $lines = $this->quizContentLines($class, $teacherName, $quiz);

        $this->sendToClass(
            $class,
            $school,
            "New quiz: {$quiz->title}",
            "{$teacherName} has ".($quiz->is_published ? 'published' : 'created').' a new quiz.',
            $lines,
            $quiz->is_published
                ? $this->studentUrl('filament.student.pages.quiz-attempt', $quiz->getKey(), 'quiz')
                : null,
        );
    }

    public function markQuizEmailSent(Quiz $quiz): void
    {
        $quiz->forceFill(['email_sent' => true])->saveQuietly();
    }

    protected function markQuizEmailNotSent(Quiz $quiz): void
    {
        $quiz->forceFill(['email_sent' => false])->saveQuietly();
    }

    public function assignmentCreated(Assignment $assignment): void
    {
        if (! $assignment->is_published) {
            $this->markAssignmentEmailNotSent($assignment);

            return;
        }

        $this->notifyPublishedAssignment($assignment);
    }

    public function notifyPublishedAssignment(Assignment $assignment): void
    {
        if (! $assignment->is_published) {
            return;
        }

        $this->sendAssignmentNotification($assignment);
        $this->markAssignmentEmailSent($assignment);
    }

    public function sendAssignmentNotification(Assignment $assignment): void
    {
        $assignment->loadMissing([
            'learningClass.grade.school',
            'teacher.user',
        ]);

        $class = $assignment->learningClass;

        if (! $class instanceof LearningClass) {
            return;
        }

        $school = $this->schoolForClass($class);

        if (! $school instanceof School) {
            return;
        }

        $teacherName = $this->teacherName($assignment->teacher);
        $lines = $this->assignmentContentLines($class, $teacherName, $assignment);

        $this->sendToClass(
            $class,
            $school,
            "New assignment: {$assignment->title}",
            "{$teacherName} has ".($assignment->is_published ? 'published' : 'created').' a new assignment.',
            $lines,
            $assignment->is_published
                ? $this->studentUrl('filament.student.pages.assignment', $assignment->getKey(), 'assignment')
                : null,
        );
    }

    public function markAssignmentEmailSent(Assignment $assignment): void
    {
        $assignment->forceFill(['email_sent' => true])->saveQuietly();
    }

    protected function markAssignmentEmailNotSent(Assignment $assignment): void
    {
        $assignment->forceFill(['email_sent' => false])->saveQuietly();
    }

    public function lessonCreated(Lesson $lesson): void
    {
        if (! $lesson->is_published) {
            $this->markLessonEmailNotSent($lesson);

            return;
        }

        $this->notifyPublishedLesson($lesson);
    }

    public function notifyPublishedLesson(Lesson $lesson): void
    {
        if (! $lesson->is_published) {
            return;
        }

        $this->sendLessonNotification($lesson);
        $this->markLessonEmailSent($lesson);
    }

    public function sendLessonNotification(Lesson $lesson): void
    {
        $lesson->loadMissing([
            'learningClass.grade.school',
            'teacher.user',
        ]);

        $class = $lesson->learningClass;

        if (! $class instanceof LearningClass) {
            return;
        }

        $school = $this->schoolForClass($class);

        if (! $school instanceof School) {
            return;
        }

        $teacherName = $this->teacherName($lesson->teacher);
        $lines = $this->contentLines($class, $teacherName);

        $this->sendToClass(
            $class,
            $school,
            "New lesson: {$lesson->title}",
            "{$teacherName} has ".($lesson->is_published ? 'published' : 'created').' a new lesson.',
            $lines,
            $lesson->is_published
                ? $this->studentUrl('filament.student.pages.lesson', $lesson->getKey(), 'lesson')
                : null,
        );
    }

    public function markLessonEmailSent(Lesson $lesson): void
    {
        $lesson->forceFill(['email_sent' => true])->saveQuietly();
    }

    protected function markLessonEmailNotSent(Lesson $lesson): void
    {
        $lesson->forceFill(['email_sent' => false])->saveQuietly();
    }

    public function assignmentGraded(AssignmentSubmission $submission): void
    {
        $submission->loadMissing([
            'assignment.learningClass.grade.school',
            'assignment.teacher.user',
            'student.user',
        ]);

        $assignment = $submission->assignment;
        $student = $submission->student;

        if (! $assignment instanceof Assignment || ! $student instanceof Student) {
            return;
        }

        $class = $assignment->learningClass;

        if (! $class instanceof LearningClass) {
            return;
        }

        $school = $this->schoolForClass($class);

        if (! $school instanceof School) {
            return;
        }

        $user = $this->activeStudentUser($class, $school, $student);

        if (! $user instanceof User) {
            return;
        }

        $teacherName = $this->teacherName($assignment->teacher);
        $score = $submission->score === null
            ? 'Not graded'
            : rtrim(rtrim(number_format((float) $submission->score, 2), '0'), '.');
        $lines = [
            'Assignment: '.$assignment->title,
            'Score: '.$score.' / '.$assignment->max_score,
        ];
        $feedback = trim(strip_tags((string) $submission->feedback));

        if ($feedback !== '') {
            $lines[] = 'Feedback: '.Str::limit($feedback, 500);
        }

        $this->sendToUser(
            $school,
            $user,
            "Assignment graded: {$assignment->title}",
            "{$teacherName} has graded your assignment submission.",
            $lines,
            $assignment->is_published
                ? $this->studentUrl('filament.student.pages.assignment', $assignment->getKey(), 'assignment')
                : null,
        );
    }

    /** @param array<int, string> $lines */
    protected function sendToClass(
        LearningClass $class,
        ?School $school,
        string $subject,
        string $message,
        array $lines,
        ?string $actionUrl,
    ): void {
        if (! $school instanceof School || ! $school->is_active || ! $this->mailTransport->isConfigured($school)) {
            return;
        }

        $users = $class->enrollments()
            ->where('student_enrollments.school_id', $school->getKey())
            ->where('student_enrollments.status', 'active')
            ->whereHas('student', function (Builder $query): void {
                $query->whereHas('user', function (Builder $userQuery): void {
                    $userQuery->whereNotNull('email');
                });
            })
            ->with('student.user')
            ->get()
            ->map(function (StudentEnrollment $enrollment): ?User {
                $student = $enrollment->student;

                if (! $student instanceof Student) {
                    return null;
                }

                $user = $student->user;

                return $user instanceof User ? $user : null;
            })
            ->filter(fn (?User $user): bool => $user instanceof User && filled($user->email))
            ->unique(fn (User $user): int => (int) $user->getKey())
            ->values();

        $this->send($users, $school, $subject, $message, $lines, $actionUrl);
    }

    /** @param array<int, string> $lines */
    protected function sendToUser(
        ?School $school,
        ?User $user,
        string $subject,
        string $message,
        array $lines,
        ?string $actionUrl,
    ): void {
        if (! $school instanceof School || ! $user instanceof User || ! filled($user->email)) {
            return;
        }

        $this->send(collect([$user]), $school, $subject, $message, $lines, $actionUrl);
    }

    /**
     * @param  Collection<int, User>  $users
     * @param  array<int, string>  $lines
     */
    protected function send(
        Collection $users,
        School $school,
        string $subject,
        string $message,
        array $lines,
        ?string $actionUrl,
    ): void {
        if (! $school->is_active || ! $this->mailTransport->isConfigured($school)) {
            return;
        }

        $recipients = $users->filter(fn (User $user): bool => filled($user->email));

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $recipients,
            new SchoolActivityNotification(
                $school->getKey(),
                $subject,
                $message,
                $lines,
                $actionUrl,
            ),
        );
    }

    /** @return array<int, string> */
    protected function contentLines(LearningClass $class, string $teacherName, ?DateTimeInterface $deadline = null): array
    {
        $lines = [
            'Class: '.$class->name,
            'Teacher: '.$teacherName,
        ];

        if ($deadline instanceof DateTimeInterface) {
            $lines[] = 'Deadline: '.$deadline->format('j M Y, g:i A');
        }

        return $lines;
    }

    /** @return array<int, string> */
    protected function quizContentLines(LearningClass $class, string $teacherName, Quiz $quiz): array
    {
        $lines = [
            'Class: '.$class->name,
            'Teacher: '.$teacherName,
        ];

        $lines[] = 'Start date: '.($quiz->start_at instanceof DateTimeInterface
            ? $quiz->start_at->format('j M Y, g:i A')
            : 'Available immediately');

        $lines[] = 'End date: '.($quiz->end_at instanceof DateTimeInterface
            ? $quiz->end_at->format('j M Y, g:i A')
            : 'No deadline');

        if ($quiz->total_points) {
            $lines[] = 'Total points: '.$quiz->total_points;
        }

        if ($quiz->time_limit_minutes) {
            $lines[] = 'Time limit: '.$quiz->time_limit_minutes.' minutes';
        }

        if ($quiz->max_attempts) {
            $lines[] = 'Attempts allowed: '.$quiz->max_attempts;
        }

        if ($quiz->passing_percentage) {
            $lines[] = 'Passing score: '.$quiz->passing_percentage.'%';
        }

        return $lines;
    }

    /** @return array<int, string> */
    protected function assignmentContentLines(LearningClass $class, string $teacherName, Assignment $assignment): array
    {
        $lines = [
            'Class: '.$class->name,
            'Teacher: '.$teacherName,
        ];

        $lines[] = 'Start date: '.($assignment->start_at instanceof DateTimeInterface
            ? $assignment->start_at->format('j M Y, g:i A')
            : 'Available immediately');

        $lines[] = 'End date: '.($assignment->end_at instanceof DateTimeInterface
            ? $assignment->end_at->format('j M Y, g:i A')
            : 'No deadline');

        if ($assignment->max_score) {
            $lines[] = 'Maximum score: '.$assignment->max_score;
        }

        if ($assignment->allow_late_submissions && $assignment->end_at instanceof DateTimeInterface) {
            $deadline = $assignment->lateSubmissionDeadline();
            $lines[] = 'Late submissions accepted until: '.($deadline instanceof DateTimeInterface
                ? $deadline->format('j M Y, g:i A')
                : $assignment->end_at->format('j M Y, g:i A'));
        }

        return $lines;
    }

    protected function schoolForClass(LearningClass $class): ?School
    {
        $grade = $class->grade;

        if (! $grade instanceof Grade) {
            return null;
        }

        $school = $grade->school;

        return $school instanceof School ? $school : null;
    }

    protected function teacherName(?Teacher $teacher): string
    {
        $user = $teacher?->user;

        return $user instanceof User ? $user->name : 'Your teacher';
    }

    protected function activeStudentUser(LearningClass $class, School $school, Student $student): ?User
    {
        $enrollment = $class->enrollments()
            ->where('student_enrollments.student_id', $student->getKey())
            ->where('student_enrollments.school_id', $school->getKey())
            ->where('student_enrollments.status', 'active')
            ->with('student.user')
            ->first();

        if (! $enrollment instanceof StudentEnrollment) {
            return null;
        }

        $enrolledStudent = $enrollment->student;

        if (! $enrolledStudent instanceof Student) {
            return null;
        }

        $user = $enrolledStudent->user;

        return $user instanceof User && filled($user->email) ? $user : null;
    }

    protected function studentUrl(string $route, int $recordId, string $query): string
    {
        return route($route).'?'.$query.'='.$recordId;
    }
}
