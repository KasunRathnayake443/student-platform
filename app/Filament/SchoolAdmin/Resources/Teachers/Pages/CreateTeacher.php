<?php

namespace App\Filament\SchoolAdmin\Resources\Teachers\Pages;

use App\Filament\SchoolAdmin\Resources\Teachers\TeacherResource;
use App\Models\Teacher;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateTeacher extends CreateRecord
{
    protected static string $resource = TeacherResource::class;

    /**
     * Create (or link) a teacher account for the given email.
     *
     * - If a user with that email exists, the account is reused: the teacher
     *   role is assigned and the teacher profile is linked to the schools
     *   selected by the school admin (new schools are added, none removed).
     * - If no account exists yet, a new user is created with a random
     *   password (or the one provided) and must_change_password set to true.
     */
    protected function handleRecordCreation(array $data): Teacher
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

            if (! $user->hasRole('teacher')) {
                $user->assignRole('teacher');
            }

            $teacher = $user->teacher;

            if (! $teacher) {
                $teacher = Teacher::create([
                    'user_id' => $user->id,
                    'profile_photo' => $data['profile_photo'] ?? null,
                    'employee_no' => $data['employee_no'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                ]);
            }

            if (! empty($data['schools'])) {
                $teacher->schools()->syncWithoutDetaching($data['schools']);
            }

            if (! empty($data['classes'])) {
                $teacher->classes()->syncWithoutDetaching($data['classes']);
            }

            return $teacher;
        });
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (request()->has('school_id') && empty($data['schools'])) {
            $data['schools'] = [request()->get('school_id')];
        }

        return $data;
    }

    public function getLayout(): string
    {
        return 'filament.school-admin.layouts.app';
    }
}
