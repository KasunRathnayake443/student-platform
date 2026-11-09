<?php

namespace App\Services;

use App\Models\LearningClass;
use App\Models\Lesson;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TeacherService
{
    /**
     * Permanently remove a teacher from the system.
     *
     * Deleting the teacher profile cascades their class/school links, lessons,
     * assignments, quizzes and related submissions. The teacher role is then
     * removed from the account and, when nothing else belongs to the account,
     * the user account itself is deleted.
     */
    public function deleteFromSystem(Teacher $teacher): bool
    {
        return DB::transaction(function () use ($teacher): bool {
            $user = $teacher->user;

            if (filled($teacher->profile_photo)) {
                Storage::disk((string) config('filament.default_filesystem_disk', 'local'))
                    ->delete($teacher->profile_photo);
            }

            // Detach explicitly so the pivots are cleared even if the database
            // does not enforce the cascading foreign keys.
            $teacher->schools()->detach();
            $teacher->classes()->detach();

            // Delete lessons through Eloquent so their attachment files are
            // removed as well; a database cascade would bypass the model event.
            $teacher->lessons()->get()->each(
                fn (Lesson $lesson) => $lesson->delete(),
            );

            $teacher->delete();

            if (! $user) {
                return true;
            }

            if ($user->hasRole('teacher')) {
                $user->removeRole('teacher');
            }

            $hasOtherRoles = $user->roles()->exists();
            $hasOtherProfiles = $user->student()->exists() || $user->schoolAdmin()->exists();

            if (! $hasOtherRoles && ! $hasOtherProfiles) {
                $user->delete();
            }

            return true;
        });
    }

    /**
     * Remove a teacher from the given schools without deleting the account.
     *
     * Only the teacher's links to the supplied schools (and the classes that
     * belong to those schools) are detached. Assignments in every other school
     * are left untouched.
     *
     * @param  array<int, int|string>  $schoolIds
     */
    public function removeFromSchools(Teacher $teacher, array $schoolIds): void
    {
        $schoolIds = array_values(array_unique(array_map('intval', $schoolIds)));

        if ($schoolIds === []) {
            return;
        }

        DB::transaction(function () use ($teacher, $schoolIds): void {
            $classIds = LearningClass::query()
                ->whereHas('grade', fn (Builder $query) => $query->whereIn('school_id', $schoolIds))
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if ($classIds !== []) {
                $teacher->classes()->detach($classIds);
            }

            $teacher->schools()->detach($schoolIds);
        });
    }
}
