<?php

namespace App\Filament\SchoolAdmin\Resources\Lessons\Pages;

use App\Filament\Resources\Lessons\Pages\EditLesson as BaseEditLesson;
use App\Filament\SchoolAdmin\Resources\Lessons\LessonResource;

class EditLesson extends BaseEditLesson
{
    protected static string $resource = LessonResource::class;
}
