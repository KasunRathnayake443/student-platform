<?php

namespace App\Filament\Resources\Lessons\Pages;

use App\Filament\Resources\Lessons\LessonResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditLesson extends EditRecord
{
    protected static string $resource = LessonResource::class;

    /*
    |--------------------------------------------------------------------------
    | Fill Existing Attachments
    |--------------------------------------------------------------------------
    */

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->load('attachments');

        $data['existing_attachments'] = $this->record
            ->attachments
            ->map(function ($attachment) {

                return [
                    'id' => $attachment->id,

                    'original_name' =>
                        $attachment->original_name,

                    'file_path' =>
                        $attachment->file_path,

                    'attachment_size' =>
                        $attachment->file_size,

                    'mime_type' =>
                        $attachment->mime_type,
                ];
            })
            ->values()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | New attachments start empty
        |--------------------------------------------------------------------------
        */

        $data['new_attachments'] = [];

        return $data;
    }


    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make()
                ->label('Delete Lesson')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->modalHeading('Delete Lesson')
                ->modalDescription(
                    'This will permanently delete the lesson and all of its attached files.'
                )
                ->modalSubmitActionLabel('Yes, Delete Lesson'),
        ];
    }

    
    /*
    |--------------------------------------------------------------------------
    | Don't Save Attachment Fields Into Lessons Table
    |--------------------------------------------------------------------------
    */

    protected function mutateFormDataBeforeSave(
        array $data
    ): array {

        unset(
            $data['existing_attachments'],
            $data['new_attachments']
        );

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | After Lesson Saved
    |--------------------------------------------------------------------------
    */

    protected function afterSave(): void
    {
        $this->processExistingAttachments();

        $this->processNewAttachments();

        /*
        |--------------------------------------------------------------------------
        | Reload relationship
        |--------------------------------------------------------------------------
        */

        $this->record->unsetRelation('attachments');

        $this->record->load('attachments');
    }

    /*
    |--------------------------------------------------------------------------
    | Existing Attachments
    |--------------------------------------------------------------------------
    |
    | Anything removed from the repeater gets deleted from:
    |
    | 1. lesson_attachments table
    | 2. public storage
    |
    |--------------------------------------------------------------------------
    */

    protected function processExistingAttachments(): void
    {
        $formState = $this->form->getState();

        $existingAttachments =
            $formState['existing_attachments']
            ?? [];

        if (! is_array($existingAttachments)) {
            $existingAttachments = [];
        }

        /*
        |--------------------------------------------------------------------------
        | IDs still present in the form
        |--------------------------------------------------------------------------
        */

        $remainingIds = collect($existingAttachments)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Get database attachments
        |--------------------------------------------------------------------------
        */

        $attachments = $this->record
            ->attachments()
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Delete removed attachments
        |--------------------------------------------------------------------------
        */

        foreach ($attachments as $attachment) {

            if (
                ! in_array(
                    (int) $attachment->id,
                    $remainingIds,
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
    }

    /*
    |--------------------------------------------------------------------------
    | New Attachments
    |--------------------------------------------------------------------------
    */

    protected function processNewAttachments(): void
    {
        $formState = $this->form->getState();

        $newAttachments =
            $formState['new_attachments']
            ?? [];

        if (! is_array($newAttachments)) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Existing database paths
        |--------------------------------------------------------------------------
        */

        $existingPaths = $this->record
            ->attachments()
            ->pluck('file_path')
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Process uploaded files
        |--------------------------------------------------------------------------
        */

        foreach (
            $newAttachments
            as $index => $path
        ) {

            /*
            |--------------------------------------------------------------------------
            | Filament FileUpload should give us a string path
            |--------------------------------------------------------------------------
            */

            if (! is_string($path)) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Prevent duplicates
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
            | Make sure file exists
            |--------------------------------------------------------------------------
            */

            if (
                ! Storage::disk('public')->exists($path)
            ) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Get metadata
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
            | Create Attachment
            |--------------------------------------------------------------------------
            */

            $this->record
                ->attachments()
                ->create([

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

            /*
            |--------------------------------------------------------------------------
            | Remember path so duplicates aren't created
            |--------------------------------------------------------------------------
            */

            $existingPaths[] = $path;
        }
    }
}