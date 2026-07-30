<?php

namespace App\Filament\Resources\SchoolAdmins\Pages;


use App\Filament\Resources\SchoolAdmins\SchoolAdminResource;

use Filament\Resources\Pages\CreateRecord;

use Illuminate\Support\Facades\Hash;



class CreateSchoolAdmin extends CreateRecord
{


    protected static string $resource =
        SchoolAdminResource::class;




    protected function mutateFormDataBeforeCreate(array $data): array
    {


        $data['password'] =
            Hash::make(
                $data['password']
            );


        return $data;

    }





    protected function afterCreate(): void
    {

        $this->record
            ->assignRole(
                'school_admin'
            );

    }


}