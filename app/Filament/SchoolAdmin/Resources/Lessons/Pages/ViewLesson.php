<?php

namespace App\Filament\SchoolAdmin\Resources\Lessons\Pages;

use App\Filament\Resources\Lessons\Pages\ViewLesson as BaseViewLesson;
use App\Filament\SchoolAdmin\Resources\Lessons\LessonResource;

class ViewLesson extends BaseViewLesson
{
    protected static string $resource = LessonResource::class;

    public function getLayout(): string
    {
        return 'filament.school-admin.layouts.app';
    }
}
