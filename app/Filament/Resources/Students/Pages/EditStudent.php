<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use App\Models\Grade;
use App\Models\LearningClass;
use App\Models\StudentEnrollment;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {

        $student = $this->record;

        /*
        |--------------------------------------------------------------------------
        | User Data
        |--------------------------------------------------------------------------
        */

        $data['name'] =
            $student->user?->name;

        $data['email'] =
            $student->user?->email;

        /*
        |--------------------------------------------------------------------------
        | Parent Account
        |--------------------------------------------------------------------------
        */

        $data['parent_account_mode'] =
            $student->parent_id ? 'existing' : 'none';

        $data['parent_id'] =
            $student->parent_id;

        /*
        |--------------------------------------------------------------------------
        | Enrollment Data
        |--------------------------------------------------------------------------
        */

        $enrollments =
            $student
                ->enrollments()
                ->get();

        if ($enrollments->count()) {

            $data['assign_school'] = true;

            $data['schools'] =

                $enrollments

                    ->pluck('school_id')

                    ->unique()

                    ->values()

                    ->toArray();

            $data['grades'] =

                $enrollments

                    ->pluck('grade_id')

                    ->unique()

                    ->values()

                    ->toArray();

            $data['classes'] =

                $student

                    ->classes()

                    ->pluck('learning_classes.id')

                    ->unique()

                    ->values()

                    ->toArray();

            $latest =

                $enrollments->sortByDesc('id')->first();

            $data['academic_year'] =

                $latest?->academic_year;

            $data['status'] =

                $latest?->status;

        }

        return $data;

    }

    protected function handleRecordUpdate(
        Model $record,
        array $data
    ): Model {

        return DB::transaction(function () use ($record, $data) {

            $parentId = array_key_exists('parent_account_mode', $data)
                ? $this->resolveParentAccountId($data)
                : $record->parent_id;

            /*
            |--------------------------------------------------------------------------
            | Update User
            |--------------------------------------------------------------------------
            */

            $record->user()->update([

                'name' => $data['name'],

                'email' => $data['email'],

                'password' => ! empty($data['password'])

                    ? Hash::make($data['password'])

                    : $record->user->password,

            ]);

            /*
            |--------------------------------------------------------------------------
            | Update Student
            |--------------------------------------------------------------------------
            */

            $record->update([

                'profile_photo' => $data['profile_photo']
                    ??
                    $record->profile_photo,

                'parent_id' => $parentId,

                'admission_no' => $data['admission_no'],

                'date_of_birth' => $data['date_of_birth'] ?? null,

                'gender' => $data['gender'] ?? null,

                'phone' => $data['phone'] ?? null,

                'address' => $data['address'] ?? null,

                'parent_name' => $data['parent_name'] ?? null,

                'parent_phone' => $data['parent_phone'] ?? null,

                'parent_email' => $data['parent_email'] ?? null,

            ]);

            /*
            |--------------------------------------------------------------------------
            | Remove Old Enrollment + Class Links
            |--------------------------------------------------------------------------
            */

            $record
                ->classes()
                ->detach();

            $record
                ->enrollments()
                ->delete();

            /*
            |--------------------------------------------------------------------------
            | Recreate Enrollment Structure
            |--------------------------------------------------------------------------
            */

            if (
                ! empty($data['assign_school'])
                &&
                ! empty($data['schools'])
            ) {

                foreach (
                    $data['schools'] as $schoolId
                ) {

                    $grades =

                        Grade::whereIn(
                            'id',
                            $data['grades'] ?? []
                        )
                            ->where(
                                'school_id',
                                $schoolId
                            )
                            ->get();

                    foreach (
                        $grades as $grade
                    ) {

                        $enrollment =

                            StudentEnrollment::create([

                                'student_id' => $record->id,

                                'school_id' => $schoolId,

                                'grade_id' => $grade->id,

                                'academic_year' => $data['academic_year']
                                    ??
                                    date('Y'),

                                'status' => $data['status']
                                    ??
                                    'active',

                            ]);

                        foreach (
                            $data['classes'] ?? [] as $classId
                        ) {

                            $validClass =

                                LearningClass::where(
                                    'id',
                                    $classId
                                )
                                    ->where(
                                        'grade_id',
                                        $grade->id
                                    )
                                    ->exists();

                            if ($validClass) {

                                $record
                                    ->classes()
                                    ->attach(

                                        $classId,

                                        [

                                            'student_enrollment_id' => $enrollment->id,

                                        ]

                                    );

                            }

                        }

                    }

                }

            }

            return $record;

        });

    }

    /**
     * Resolve the parent account selected or created in the student form.
     */
    protected function resolveParentAccountId(array $data): ?int
    {

        return match ($data['parent_account_mode'] ?? 'none') {

            'existing' => User::role('parent')

                ->whereKey($data['parent_id'] ?? 0)

                ->value('id'),

            'new' => $this->createParentAccount($data),

            default => null,

        };

    }

    protected function createParentAccount(array $data): int
    {

        $parent = User::create([

            'name' => $data['parent_account_name'],

            'email' => $data['parent_account_email'],

            'password' => Hash::make(
                $data['parent_account_password']
            ),

            'must_change_password' => true,

        ]);

        $parentRole = Role::firstOrCreate([

            'name' => 'parent',

            'guard_name' => 'web',

        ]);

        $parent->assignRole($parentRole);

        return $parent->id;

    }

    protected function getHeaderActions(): array
    {

        return [

            Actions\ViewAction::make(),

            Actions\DeleteAction::make(),

        ];

    }
}
