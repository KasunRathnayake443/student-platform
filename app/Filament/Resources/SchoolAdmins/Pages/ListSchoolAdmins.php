<?php

namespace App\Filament\Resources\SchoolAdmins\Pages;

use App\Filament\Resources\SchoolAdmins\SchoolAdminResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSchoolAdmins extends ListRecords
{
    protected static string $resource =
        SchoolAdminResource::class;

    protected function getHeaderActions(): array
    {

        return [

            CreateAction::make(),

        ];

    }
}
