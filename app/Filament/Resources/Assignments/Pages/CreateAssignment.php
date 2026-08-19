<?php

namespace App\Filament\Resources\Assignments\Pages;

use App\Filament\Resources\Assignments\AssignmentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateAssignment extends CreateRecord
{
    protected static string $resource = AssignmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset(
            $data['existing_attachments'],
            $data['new_attachments']
        );

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->saveAttachments();
    }

    protected function saveAttachments(): void
    {
        $state = $this->form->getState();

        $files = $state['new_attachments'] ?? [];

        if (! is_array($files)) {
            $files = [];
        }

        foreach ($files as $index => $path) {

            if (! is_string($path)) {
                continue;
            }

            if (! Storage::disk('public')->exists($path)) {
                continue;
            }

            $this->record->attachments()->create([
                'original_name' => basename($path),

                'file_path' => $path,

                'mime_type' => Storage::disk('public')
                    ->mimeType($path),

                'file_size' => Storage::disk('public')
                    ->size($path),

                'sort_order' => $index,
            ]);
        }

        $this->record->unsetRelation('attachments');

        $this->record->load('attachments');
    }
}
