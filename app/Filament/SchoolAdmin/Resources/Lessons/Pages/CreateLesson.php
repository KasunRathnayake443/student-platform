<?php

namespace App\Filament\SchoolAdmin\Resources\Lessons\Pages;

use App\Filament\SchoolAdmin\Resources\Lessons\LessonResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CreateLesson extends CreateRecord
{
    protected static string $resource = LessonResource::class;

    /**
     * The lesson form uses `new_attachments` (a FileUpload), while other
     * panels use `attachments`. Process `new_attachments` on creation.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $newAttachments = $data['new_attachments'] ?? [];

        unset($data['new_attachments']);

        $lesson = static::getModel()::create($data);

        $existingPaths = [];

        foreach ($newAttachments as $index => $path) {
            if (! is_string($path)) {
                continue;
            }

            if (in_array($path, $existingPaths, true)) {
                continue;
            }

            if (! Storage::disk('public')->exists($path)) {
                continue;
            }

            $mimeType = null;
            $fileSize = null;

            try {
                $mimeType = Storage::disk('public')->mimeType($path);
            } catch (\Throwable $e) {
                $mimeType = null;
            }

            try {
                $fileSize = Storage::disk('public')->size($path);
            } catch (\Throwable $e) {
                $fileSize = null;
            }

            $lesson->attachments()->create([
                'original_name' => basename($path),
                'file_path' => $path,
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
                'sort_order' => $index,
            ]);

            $existingPaths[] = $path;
        }

        return $lesson;
    }
}
