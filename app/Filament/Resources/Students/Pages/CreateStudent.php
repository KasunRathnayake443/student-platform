<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;

use App\Models\User;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\School;

use Filament\Resources\Pages\CreateRecord;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class CreateStudent extends CreateRecord
{

    protected static string $resource = StudentResource::class;



    protected function handleRecordCreation(array $data): Student
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



            /*
            |--------------------------------------------------------------------------
            | Assign Student Role
            |--------------------------------------------------------------------------
            */


            if (
                \Spatie\Permission\Models\Role::where(
                    'name',
                    'student'
                )->exists()
            ) {

                $user->assignRole('student');

            }



            /*
            |--------------------------------------------------------------------------
            | Create Student Profile
            |--------------------------------------------------------------------------
            */


            $student = Student::create([


                'user_id' => $user->id,
            
            
                'profile_photo' =>
                    $data['profile_photo'] ?? null,
            
            
                'admission_no' =>
                    $data['admission_no'],


                'date_of_birth' =>
                    $data['date_of_birth'] ?? null,


                'gender' =>
                    $data['gender'] ?? null,


                'phone' =>
                    $data['phone'] ?? null,


                'address' =>
                    $data['address'] ?? null,


                'parent_name' =>
                    $data['parent_name'] ?? null,


                'parent_phone' =>
                    $data['parent_phone'] ?? null,


            ]);





            /*
            |--------------------------------------------------------------------------
            | Optional Enrollment
            |--------------------------------------------------------------------------
            */


            if (
                isset($data['assign_school'])
                &&
                $data['assign_school']
            ) {


                StudentEnrollment::create([


                    'student_id' =>
                        $student->id,


                    'school_id' =>
                        $data['school_id'],


                    'grade_id' =>
                        $data['grade_id'],


                    'academic_year' =>
                        $data['academic_year'] ?? date('Y'),


                    'status' =>
                        $data['status'] ?? 'active',


                ]);



                /*
                |--------------------------------------------------------------------------
                | Attach Class
                |--------------------------------------------------------------------------
                */


                if (
                    !empty($data['learning_class_id'])
                ) {


                    $student
                        ->classes()
                        ->attach(
                            $data['learning_class_id']
                        );


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

            $data['school_id'] =
                request()->get('school_id');

        }


        return $data;

    }

}