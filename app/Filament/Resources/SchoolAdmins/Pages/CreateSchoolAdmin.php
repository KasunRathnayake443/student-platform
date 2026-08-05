<?php

namespace App\Filament\Resources\SchoolAdmins\Pages;


use App\Filament\Resources\SchoolAdmins\SchoolAdminResource;

use App\Models\User;
use App\Models\SchoolAdmin;


use Filament\Resources\Pages\CreateRecord;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;



class CreateSchoolAdmin extends CreateRecord
{


    protected static string $resource =
        SchoolAdminResource::class;




    protected function handleRecordCreation(array $data): SchoolAdmin
    {


        return DB::transaction(function () use ($data) {



            $user = User::create([


                'name' =>
                    $data['name'],


                'email' =>
                    $data['email'],


                'password' =>
                    Hash::make(
                        $data['password']
                    ),


                'must_change_password' =>
                    true,


            ]);




            $user->assignRole(
                'school_admin'
            );




            $schoolAdmin = SchoolAdmin::create([

                'user_id'=>$user->id,
            
                'profile_photo'=>$data['profile_photo'] ?? null,
            
                'phone'=>$data['phone'] ?? null,
            
                'address'=>$data['address'] ?? null,
            
            ]);





            if (!empty($data['schools'])) {


                $user
                    ->schools()
                    ->sync(
                        $data['schools']
                    );


            }





            return $schoolAdmin;


        });


    }


}