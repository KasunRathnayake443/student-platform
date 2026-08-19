<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class StudentContextService
{
    private const SESSION_KEY = 'student_active_context';

    /**
     * Get all contexts (school+grade combos) for a student, grouped by school.
     * Returns: Collection of ['school' => School, 'grade' => Grade, 'enrollment' => StudentEnrollment, 'classes' => Collection]
     */
    public function getContextsFor(Student $student): Collection
    {
        return $student
            ->enrollments()
            ->with(['school', 'grade', 'classes'])
            ->where('status', 'active')
            ->get()
            ->map(fn (StudentEnrollment $enrollment) => [
                'key'        => $enrollment->school_id . '_' . $enrollment->grade_id,
                'enrollment' => $enrollment,
                'school'     => $enrollment->school,
                'grade'      => $enrollment->grade,
                'classes'    => $enrollment->classes,
            ])
            ->values();
    }

    /**
     * Get the currently active context for a student.
     * Defaults to the first/most-recent enrollment if none stored.
     */
    public function getActiveContext(Student $student): ?array
    {
        $contexts = $this->getContextsFor($student);

        if ($contexts->isEmpty()) {
            return null;
        }

        $storedKey = Session::get(self::SESSION_KEY . '_' . $student->id);

        if ($storedKey) {
            $found = $contexts->firstWhere('key', $storedKey);
            if ($found) {
                return $found;
            }
        }

        // Default to first context
        $default = $contexts->first();
        $this->setActiveContext($student, $default['key']);

        return $default;
    }

    /**
     * Set the active context key for a student (stored in session).
     */
    public function setActiveContext(Student $student, string $contextKey): void
    {
        Session::put(self::SESSION_KEY . '_' . $student->id, $contextKey);
    }

    /**
     * Get all contexts grouped by school for rendering the switcher UI.
     */
    public function getContextsGroupedBySchool(Student $student): Collection
    {
        return $this->getContextsFor($student)
            ->groupBy(fn ($ctx) => $ctx['school']->id)
            ->map(fn ($schoolContexts) => [
                'school'   => $schoolContexts->first()['school'],
                'contexts' => $schoolContexts->values(),
            ])
            ->values();
    }
}
