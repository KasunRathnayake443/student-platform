<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;

use App\Models\StudentEnrollment;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class EditStudent extends EditRecord
{

    protected static string $resource = StudentResource::class;



    protected function mutateFormDataBeforeFill(array $data): array
    {

        $student = $this->record;


        /*
        |--------------------------------------------------------------------------
        | Load User Data
        |--------------------------------------------------------------------------
        */


        $data['name'] =
            $student->user?->name;


        $data['email'] =
            $student->user?->email;



        /*
        |--------------------------------------------------------------------------
        | Load Current Enrollment
        |--------------------------------------------------------------------------
        */


        $enrollment =
            $student->enrollments()
                ->latest()
                ->first();



        if ($enrollment) {


            $data['assign_school'] = true;


            $data['school_id'] =
                $enrollment->school_id;


            $data['grade_id'] =
                $enrollment->grade_id;


            $data['academic_year'] =
                $enrollment->academic_year;


            $data['status'] =
                $enrollment->status;



            $class =
                $student->classes()
                    ->where(
                        'learning_classes.grade_id',
                        $enrollment->grade_id
                    )
                    ->first();



            if ($class) {

                $data['learning_class_id'] =
                    $class->id;

            }

        }


        return $data;

    }





    protected function handleRecordUpdate(
        \Illuminate\Database\Eloquent\Model $record,
        array $data
    ): \Illuminate\Database\Eloquent\Model
    {


        return DB::transaction(function () use (
            $record,
            $data
        ) {


            /*
            |--------------------------------------------------------------------------
            | Update User
            |--------------------------------------------------------------------------
            */


            $record->user()->update([


                'name' =>
                    $data['name'],


                'email' =>
                    $data['email'],


                'password' =>
                    !empty($data['password'])

                        ? Hash::make($data['password'])

                        : $record->user->password,


            ]);





            /*
            |--------------------------------------------------------------------------
            | Update Student
            |--------------------------------------------------------------------------
            */


            $record->update([


                'profile_photo' =>
                    $data['profile_photo'] ?? $record->profile_photo,
            
            
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
            | Enrollment Update
            |--------------------------------------------------------------------------
            */


            $enrollment =
                $record->enrollments()
                    ->latest()
                    ->first();




            if (
                !empty($data['assign_school'])
                &&
                $data['assign_school']
            ) {


                if ($enrollment) {


                    $enrollment->update([


                        'school_id' =>
                            $data['school_id'],


                        'grade_id' =>
                            $data['grade_id'],


                        'academic_year' =>
                            $data['academic_year'] ?? date('Y'),


                        'status' =>
                            $data['status'] ?? 'active',

                    ]);



                } else {


                    StudentEnrollment::create([


                        'student_id' =>
                            $record->id,


                        'school_id' =>
                            $data['school_id'],


                        'grade_id' =>
                            $data['grade_id'],


                        'academic_year' =>
                            $data['academic_year'] ?? date('Y'),


                        'status' =>
                            $data['status'] ?? 'active',


                    ]);

                }





                /*
                |--------------------------------------------------------------------------
                | Update Class
                |--------------------------------------------------------------------------
                */


                if (
                    !empty($data['learning_class_id'])
                ) {


                    $record
                        ->classes()
                        ->sync([
                            $data['learning_class_id']
                        ]);


                }



            } else {


                /*
                Remove enrollment if school assignment removed
                */


                $record
                    ->enrollments()
                    ->delete();



                $record
                    ->classes()
                    ->detach();


            }



            return $record;


        });


    }




    protected function getHeaderActions(): array
    {
        return [

            Actions\ViewAction::make(),

            Actions\DeleteAction::make(),

        ];
    }

}