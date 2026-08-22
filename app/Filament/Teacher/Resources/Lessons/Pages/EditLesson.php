<?php

namespace App\Filament\Teacher\Resources\Lessons\Pages;

use App\Filament\Resources\Lessons\Pages\EditLesson as BaseEditLesson;
use App\Filament\Teacher\Resources\Lessons\LessonResource;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class EditLesson extends BaseEditLesson
{
    protected static string $resource = LessonResource::class;

    public function getLayout(): string
    {
        return 'filament.teacher.layouts.app';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')
                ->label('View Lesson')
                ->icon(Heroicon::OutlinedEye)
                ->url(fn () => static::getResource()::getUrl('view', ['record' => $this->getRecord()], panel: 'teacher')),
        ];
    }
}
