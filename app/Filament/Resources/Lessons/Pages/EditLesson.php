<?php

namespace App\Filament\Resources\Lessons\Pages;

use App\Filament\Resources\Lessons\LessonResource;
use App\Models\LessonAttachment;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditLesson extends EditRecord
{
    protected static string $resource = LessonResource::class;

    /**
     * Attachment paths that existed when the edit page was opened.
     */
    protected array $originalAttachmentPaths = [];

    /**
     * Fill the edit form.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->load('attachments');

        $attachments = $this->record
            ->attachments
            ->sortBy('sort_order')
            ->values();

        $paths = $attachments
            ->pluck('file_path')
            ->filter()
            ->values()
            ->toArray();

        $data['attachments'] = $paths;

        $this->originalAttachmentPaths = $paths;

        return $data;
    }

    /**
     * Prevent attachments from being saved
     * into the lessons table.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['attachments']);

        return $data;
    }

    /**
     * Synchronize attachments after the Lesson
     * itself has been saved.
     */
    protected function afterSave(): void
    {
        $this->syncAttachments();
    }

    /**
     * Synchronize lesson attachment records.
     */
    protected function syncAttachments(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Get current form state
        |--------------------------------------------------------------------------
        */

        $formState = $this->form->getState();

        $currentPaths = $formState['attachments'] ?? [];

        if (! is_array($currentPaths)) {
            $currentPaths = [];
        }

        /*
        |--------------------------------------------------------------------------
        | Normalize paths
        |--------------------------------------------------------------------------
        |
        | Filament may return temporary upload objects or strings.
        | We only need the final stored paths here.
        |
        */

        $normalizedPaths = [];

        foreach ($currentPaths as $file) {

            if (is_string($file)) {

                $normalizedPaths[] = $file;

                continue;
            }

            /*
             * Handle UploadedFile-like objects.
             */

            if (
                is_object($file) &&
                method_exists($file, 'getRealPath')
            ) {

                /*
                 * The FileUpload component normally stores the file
                 * before the form is submitted, so this is primarily
                 * a safety fallback.
                 */

                continue;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Remove duplicate paths
        |--------------------------------------------------------------------------
        */

        $normalizedPaths = array_values(
            array_unique(
                array_filter($normalizedPaths)
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Load existing attachments
        |--------------------------------------------------------------------------
        */

        $this->record->load('attachments');

        $existingAttachments = $this->record
            ->attachments
            ->keyBy('file_path');

        /*
        |--------------------------------------------------------------------------
        | Delete removed attachments
        |--------------------------------------------------------------------------
        */

        foreach ($existingAttachments as $path => $attachment) {

            if (
                ! in_array(
                    $path,
                    $normalizedPaths,
                    true
                )
            ) {

                /*
                |--------------------------------------------------------------------------
                | Delete physical file
                |--------------------------------------------------------------------------
                */

                if (
                    $attachment->file_path &&
                    Storage::disk('public')->exists(
                        $attachment->file_path
                    )
                ) {
                    Storage::disk('public')->delete(
                        $attachment->file_path
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Delete database record
                |--------------------------------------------------------------------------
                */

                $attachment->delete();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Reload remaining attachments
        |--------------------------------------------------------------------------
        */

        $this->record->unsetRelation('attachments');

        $this->record->load('attachments');

        /*
        |--------------------------------------------------------------------------
        | Existing attachment paths
        |--------------------------------------------------------------------------
        */

        $existingPaths = $this->record
            ->attachments
            ->pluck('file_path')
            ->filter()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Create database records for new files
        |--------------------------------------------------------------------------
        */

        foreach (
            $normalizedPaths as $index => $path
        ) {

            /*
            |--------------------------------------------------------------------------
            | Already registered
            |--------------------------------------------------------------------------
            */

            if (
                in_array(
                    $path,
                    $existingPaths,
                    true
                )
            ) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Make sure the uploaded file exists
            |--------------------------------------------------------------------------
            */

            if (
                ! Storage::disk('public')->exists($path)
            ) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Get file metadata
            |--------------------------------------------------------------------------
            */

            $mimeType = null;
            $fileSize = null;

            try {

                $mimeType = Storage::disk('public')
                    ->mimeType($path);

            } catch (\Throwable $e) {

                $mimeType = null;
            }

            try {

                $fileSize = Storage::disk('public')
                    ->size($path);

            } catch (\Throwable $e) {

                $fileSize = null;
            }

            /*
            |--------------------------------------------------------------------------
            | Create attachment
            |--------------------------------------------------------------------------
            */

            $this->record->attachments()->create([

                'original_name' =>
                    basename($path),

                'file_path' =>
                    $path,

                'mime_type' =>
                    $mimeType,

                'file_size' =>
                    $fileSize,

                'sort_order' =>
                    $index,

            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Update attachment ordering
        |--------------------------------------------------------------------------
        */

        $this->record->load('attachments');

        foreach (
            $normalizedPaths as $index => $path
        ) {

            $attachment = $this->record
                ->attachments
                ->firstWhere(
                    'file_path',
                    $path
                );

            if ($attachment) {

                $attachment->update([
                    'sort_order' => $index,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Reload final relationship
        |--------------------------------------------------------------------------
        */

        $this->record->unsetRelation('attachments');

        $this->record->load('attachments');
    }
}
