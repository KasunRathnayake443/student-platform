<?php

namespace App\Filament\Resources\Teachers\Pages;

use App\Filament\Resources\Teachers\TeacherResource;
use App\Models\Teacher;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateTeacher extends CreateRecord
{
    protected static string $resource =
        TeacherResource::class;

    /*
    |--------------------------------------------------------------------------
    | Handle School Context
    |--------------------------------------------------------------------------
    |
    | When teacher is created from:
    |
    | School → Teachers → Create New Teacher
    |
    | force that school assignment.
    |
    */

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        if (
            request()->has('school_id')
            &&
            empty($data['schools'])
        ) {

            $data['schools'] = [

                request()->get('school_id'),

            ];

        }

        return $data;

    }

    protected function handleRecordCreation(array $data): Teacher
    {

        return DB::transaction(function () use ($data) {

            /*
            |--------------------------------------------------------------------------
            | Create User Account
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

            $user->assignRole(
                'teacher'
            );

            /*
            |--------------------------------------------------------------------------
            | Create Teacher Profile
            |--------------------------------------------------------------------------
            */

            $teacher = Teacher::create([

                'user_id' => $user->id,

                'profile_photo' => $data['profile_photo'] ?? null,

                'employee_no' => $data['employee_no'] ?? null,

                'phone' => $data['phone'] ?? null,

                'address' => $data['address'] ?? null,

            ]);

            /*
            |--------------------------------------------------------------------------
            | Assign Schools
            |--------------------------------------------------------------------------
            */

            if (! empty($data['schools'])) {

                $teacher->schools()->sync(

                    $data['schools']

                );

            }

            /*
            |--------------------------------------------------------------------------
            | Assign Classes
            |--------------------------------------------------------------------------
            */

            if (! empty($data['classes'])) {

                $teacher->classes()->sync(

                    $data['classes']

                );

            }

            return $teacher;

        });

    }
}
