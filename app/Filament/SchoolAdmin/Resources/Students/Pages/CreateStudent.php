<?php

namespace App\Filament\SchoolAdmin\Resources\Students\Pages;

use App\Filament\SchoolAdmin\Resources\Students\StudentResource;
use App\Filament\SchoolAdmin\Scopes\SchoolAdminScopes;
use App\Models\Student;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    /**
     * Create (or link) a student account for the given email.
     *
     * - If a user with that email exists, the account is reused: the student
     *   role is assigned and a student profile + enrollments are created only
     *   if they do not already exist.
     * - If no account exists yet, a new user is created with a random
     *   password (or the one provided) and must_change_password set to true.
     */
    protected function handleRecordCreation(array $data): Student
    {
        return DB::transaction(function () use ($data) {
            $user = User::where('email', $data['email'])->first();

            if (! $user) {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password'] ?? Str::random(16)),
                    'must_change_password' => true,
                ]);
            } else {
                $user->update(['name' => $data['name']]);
            }

            if (! $user->hasRole('student')) {
                $user->assignRole('student');
            }

            $student = $user->student;

            if (! $student) {
                $student = Student::create([
                    'user_id' => $user->id,
                    'profile_photo' => $data['profile_photo'] ?? null,
                    'admission_no' => $data['admission_no'],
                    'date_of_birth' => $data['date_of_birth'] ?? null,
                    'gender' => $data['gender'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                    'parent_name' => $data['parent_name'] ?? null,
                    'parent_phone' => $data['parent_phone'] ?? null,
                ]);
            }

            $this->syncEnrollments($student, $data);

            return $student;
        });
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (request()->has('school_id')) {
            $schoolId = (int) request()->get('school_id');

            if (in_array($schoolId, SchoolAdminScopes::schoolIds(), true)) {
                $data['assign_school'] = true;
                $data['schools'] = [$schoolId];
            }
        }

        return $data;
    }

    private function syncEnrollments(Student $student, array $data): void
    {
        StudentResource::syncEnrollments($student, $data);
    }

    public function getLayout(): string
    {
        return 'filament.school-admin.layouts.app';
    }
}
