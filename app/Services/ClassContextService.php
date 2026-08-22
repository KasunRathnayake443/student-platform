<?php

namespace App\Services;

use App\Models\LearningClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Database\Eloquent\Builder;

class ClassContextService
{
    /**
     * Students that may be assigned to a class: active enrollment in the
     * same school AND grade as the class, not already in it.
     *
     * @return Builder<StudentEnrollment>
     */
    public function eligibleStudentsQuery(LearningClass $class): Builder
    {
        return StudentEnrollment::query()
            ->where('school_id', $class->grade->school_id)
            ->where('grade_id', $class->grade_id)
            ->where('status', 'active')
            ->whereNotExists(function ($query) use ($class) {
                $query
                    ->selectRaw('1')
                    ->from('class_student')
                    ->whereColumn('class_student.student_id', 'student_enrollments.student_id')
                    ->where('class_student.learning_class_id', $class->getKey());
            })
            ->with(['student.user'])
            ->orderBy('student_id');
    }

    /**
     * Resolve a class id as a valid viewing context for a student profile:
     * the authenticated teacher must teach the class and the student must
     * be assigned to it. Returns null when invalid.
     */
    public function resolveForStudent(?int $classId, Student $student): ?int
    {
        if (blank($classId)) {
            return null;
        }

        $teacher = auth()->user()?->teacher;

        $isValid = LearningClass::query()
            ->whereKey($classId)
            ->whereHas('teachers', fn ($query) => $query->where('teachers.id', $teacher?->id))
            ->whereHas('students', fn ($query) => $query->where('students.id', $student->getKey()))
            ->exists();

        return $isValid ? $classId : null;
    }
}
