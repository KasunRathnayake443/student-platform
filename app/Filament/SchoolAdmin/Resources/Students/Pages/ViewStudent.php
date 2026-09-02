<?php

namespace App\Filament\SchoolAdmin\Resources\Students\Pages;

use App\Filament\Resources\Students\Pages\ViewStudent as BaseViewStudent;
use App\Filament\SchoolAdmin\Resources\Students\StudentResource;

class ViewStudent extends BaseViewStudent
{
    protected static string $resource = StudentResource::class;

    public function getLayout(): string
    {
        return 'filament.school-admin.layouts.app';
    }
}
