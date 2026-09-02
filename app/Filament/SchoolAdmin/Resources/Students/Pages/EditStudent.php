<?php

namespace App\Filament\SchoolAdmin\Resources\Students\Pages;

use App\Filament\Resources\Students\Pages\EditStudent as BaseEditStudent;
use App\Filament\SchoolAdmin\Resources\Students\StudentResource;
use App\Filament\SchoolAdmin\Scopes\SchoolAdminScopes;
use App\Models\Grade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EditStudent extends BaseEditStudent
{
    protected static string $resource = StudentResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $student = $this->record;

        $data['name'] = $student->user?->name;
        $data['email'] = $student->user?->email;

        $enrollments = $student->enrollments()
            ->whereIn('school_id', SchoolAdminScopes::schoolIds())
            ->get();

        if ($enrollments->count()) {
            $data['assign_school'] = true;

            $data['schools'] = $enrollments->pluck('school_id')->unique()->values()->toArray();
            $data['grades'] = $enrollments->pluck('grade_id')->unique()->values()->toArray();
            $data['classes'] = $student->classes()
                ->wherePivotIn('student_enrollment_id', $enrollments->pluck('id'))
                ->pluck('learning_classes.id')
                ->unique()
                ->values()
                ->toArray();

            $latest = $enrollments->sortByDesc('id')->first();
            $data['academic_year'] = $latest?->academic_year;
            $data['status'] = $latest?->status;
        }

        return $data;
    }

    /**
     * Only enrollments belonging to the admin's schools are managed here;
     * enrollments in other schools are left untouched.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            $user = $record->user;

            if ($user) {
                $user->update([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => ! empty($data['password'])
                        ? Hash::make($data['password'])
                        : $user->password,
                ]);

                if (! $user->hasRole('student')) {
                    $user->assignRole('student');
                }
            }

            $record->update([
                'profile_photo' => $data['profile_photo'] ?? $record->profile_photo,
                'admission_no' => $data['admission_no'],
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => $data['gender'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'parent_name' => $data['parent_name'] ?? null,
                'parent_phone' => $data['parent_phone'] ?? null,
            ]);

            $adminSchoolIds = SchoolAdminScopes::schoolIds();

            $keptEnrollmentIds = [];

            if (! empty($data['assign_school']) && ! empty($data['schools'])) {
                foreach (array_intersect($data['schools'], $adminSchoolIds) as $schoolId) {
                    foreach (Grade::whereIn('id', $data['grades'] ?? [])->where('school_id', $schoolId)->get() as $grade) {
                        $enrollmentId = $record->enrollments()
                            ->where('school_id', $schoolId)
                            ->where('grade_id', $grade->id)
                            ->where('academic_year', $data['academic_year'] ?? date('Y'))
                            ->value('id');

                        if ($enrollmentId) {
                            $keptEnrollmentIds[] = $enrollmentId;
                        }
                    }
                }
            }

            $scopedEnrollmentIds = $record->enrollments()
                ->whereIn('school_id', $adminSchoolIds)
                ->pluck('id')
                ->toArray();

            if ($scopedEnrollmentIds) {
                DB::table('class_student')
                    ->where('student_id', $record->id)
                    ->whereIn('student_enrollment_id', $scopedEnrollmentIds)
                    ->when($keptEnrollmentIds, fn ($query) => $query->whereNotIn('student_enrollment_id', $keptEnrollmentIds))
                    ->delete();

                $record->enrollments()
                    ->whereIn('id', $scopedEnrollmentIds)
                    ->when($keptEnrollmentIds, fn ($query) => $query->whereNotIn('id', $keptEnrollmentIds))
                    ->delete();
            }

            StudentResource::syncEnrollments($record, $data);

            return $record;
        });
    }

    public function getLayout(): string
    {
        return 'filament.school-admin.layouts.app';
    }
}
