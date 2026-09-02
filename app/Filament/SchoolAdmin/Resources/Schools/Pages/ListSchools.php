<?php

namespace App\Filament\SchoolAdmin\Resources\Schools\Pages;

use App\Filament\SchoolAdmin\Resources\Schools\SchoolResource;
use Filament\Resources\Pages\ListRecords;

class ListSchools extends ListRecords
{
    protected static string $resource = SchoolResource::class;

    public function getLayout(): string
    {
        return 'filament.school-admin.layouts.app';
    }
}
