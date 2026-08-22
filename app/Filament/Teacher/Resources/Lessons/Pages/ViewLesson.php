<?php

namespace App\Filament\Teacher\Resources\Lessons\Pages;

use App\Filament\Resources\Lessons\Pages\ViewLesson as BaseViewLesson;
use App\Filament\Teacher\Resources\Lessons\LessonResource;
use Filament\Actions\EditAction;

class ViewLesson extends BaseViewLesson
{
    protected static string $resource = LessonResource::class;

    public function getLayout(): string
    {
        return 'filament.teacher.layouts.app';
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->url(
                    fn () => LessonResource::getUrl('edit', ['record' => $this->getRecord()], panel: 'teacher')
                ),
        ];
    }
}
