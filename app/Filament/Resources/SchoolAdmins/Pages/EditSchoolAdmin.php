<?php

namespace App\Filament\Resources\SchoolAdmins\Pages;


use App\Filament\Resources\SchoolAdmins\SchoolAdminResource;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;



class EditSchoolAdmin extends EditRecord
{


    protected static string $resource =
        SchoolAdminResource::class;




    protected function getHeaderActions(): array
    {

        return [

            Actions\ViewAction::make(),

            Actions\DeleteAction::make(),

        ];

    }


}