<?php

namespace App\Filament\Resources\SchoolAdmins\Pages;


use App\Filament\Resources\SchoolAdmins\SchoolAdminResource;


use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;


use Filament\Resources\Pages\ViewRecord;



class ViewSchoolAdmin extends ViewRecord
{


    protected static string $resource =
        SchoolAdminResource::class;




    protected function getHeaderActions(): array
    {

        return [

            EditAction::make(),

            DeleteAction::make(),

        ];

    }



}