<?php

namespace App\Filament\Resources\Lessons\Pages;

use App\Filament\Resources\Lessons\LessonResource;
use App\Models\Lesson;
use App\Services\SchoolEmailNotificationService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CreateLesson extends CreateRecord
{
    protected static string $resource = LessonResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $attachments = $data['attachments'] ?? [];

        unset($data['attachments']);

        $lesson = static::getModel()::create($data);

        if (! $lesson instanceof Lesson) {
            return $lesson;
        }

        foreach ($attachments as $index => $file) {

            $path = is_string($file)
                ? $file
                : $file->store('lessons', 'public');

            $lesson->attachments()->create([
                'original_name' => basename($path),

                'file_path' => $path,

                'mime_type' => Storage::disk('public')
                    ->mimeType($path),

                'file_size' => Storage::disk('public')
                    ->size($path),

                'sort_order' => $index,
            ]);
        }

        app(SchoolEmailNotificationService::class)->lessonCreated($lesson);

        return $lesson;
    }
}
