<?php

namespace App\Filament\Resources\SchoolAdmins\Pages;


use App\Filament\Resources\SchoolAdmins\SchoolAdminResource;


use Filament\Actions;
use Filament\Resources\Pages\EditRecord;


use Illuminate\Support\Facades\Hash;



class EditSchoolAdmin extends EditRecord
{


    protected static string $resource =
        SchoolAdminResource::class;



    protected function mutateFormDataBeforeFill(array $data): array
    {


        $user = $this->record->user;



        $data['name'] =
            $user->name;



        $data['email'] =
            $user->email;



        $data['schools'] =
            $user
                ->schools()
                ->pluck('schools.id')
                ->toArray();



        return $data;

    }




    protected function mutateFormDataBeforeSave(array $data): array
    {


        $user = $this->record->user;



        $user->update([


            'name'=>$data['name'],


            'email'=>$data['email'],


        ]);




        if(!empty($data['password'])){


            $user->update([


                'password'=>
                    Hash::make(
                        $data['password']
                    ),


            ]);

        }




        $this->record->update([


            'phone'=>
                $data['phone'] ?? null,


            'address'=>
                $data['address'] ?? null,


        ]);




        $user
            ->schools()
            ->sync(
                $data['schools'] ?? []
            );




        unset($data['name']);

        unset($data['email']);

        unset($data['password']);



        return $data;


    }





    protected function getHeaderActions(): array
    {

        return [


            Actions\ViewAction::make(),


            Actions\DeleteAction::make(),


        ];

    }


}