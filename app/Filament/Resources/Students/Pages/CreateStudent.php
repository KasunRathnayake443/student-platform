<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use App\Models\Grade;
use App\Models\LearningClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected function handleRecordCreation(array $data): Student
    {

        return DB::transaction(function () use ($data) {

            /*
            |--------------------------------------------------------------------------
            | Create User
            |--------------------------------------------------------------------------
            */

            $user = User::create([

                'name' => $data['name'],

                'email' => $data['email'],

                'password' => Hash::make(
                    $data['password']
                ),

                'must_change_password' => true,

            ]);

            if (
                Role::where(
                    'name',
                    'student'
                )->exists()
            ) {

                $user->assignRole('student');

            }

            /*
            |--------------------------------------------------------------------------
            | Create Student
            |--------------------------------------------------------------------------
            */

            $student = Student::create([

                'user_id' => $user->id,

                'parent_id' => $this->resolveParentAccountId($data),

                'profile_photo' => $data['profile_photo'] ?? null,

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
            | Create Enrollments
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

                                'student_id' => $student->id,

                                'school_id' => $schoolId,

                                'grade_id' => $grade->id,

                                'academic_year' => $data['academic_year']
                                    ??
                                    date('Y'),

                                'status' => $data['status']
                                    ??
                                    'active',

                            ]);

                        /*
                        |--------------------------------------------------------------------------
                        | Attach Classes
                        |--------------------------------------------------------------------------
                        */

                        foreach (
                            $data['classes'] ?? [] as $classId
                        ) {

                            $classExists =
                                LearningClass::where(
                                    'id',
                                    $classId
                                )
                                    ->where(
                                        'grade_id',
                                        $grade->id
                                    )
                                    ->exists();

                            if ($classExists) {

                                $student
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

            return $student;

        });

    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        if (
            request()->has('school_id')
        ) {

            $data['assign_school'] = true;

            $data['schools'] = [

                request()->get('school_id'),

            ];

        }

        return $data;

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
}
