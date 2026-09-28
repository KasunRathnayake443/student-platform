<?php

namespace App\Filament\Resources\Assignments\Pages;

use App\Filament\Resources\Assignments\AssignmentResource;
use App\Models\Assignment;
use App\Services\SchoolEmailNotificationService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class EditAssignment extends EditRecord
{
    protected static string $resource = AssignmentResource::class;

    protected bool $suppressAssignmentEmail = false;

    protected bool $manualAssignmentSaveCompleted = false;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function submitAndEmailStudentsAction(): Action
    {
        return Action::make('submitAndEmailStudents')
            ->label('Submit & Email Students')
            ->icon('heroicon-o-paper-airplane')
            ->color('primary')
            ->visible(function (): bool {
                $assignment = $this->getRecord();

                return $assignment instanceof Assignment && (bool) $assignment->email_sent;
            })
            ->action(function (): void {
                $this->handleSubmitAndEmailStudents();
            });
    }

    /** @return array<Action | ActionGroup> */
    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->submitAndEmailStudentsAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label('Submit');
    }

    protected function handleSubmitAndEmailStudents(): void
    {
        $this->suppressAssignmentEmail = true;
        $this->manualAssignmentSaveCompleted = false;

        try {
            $this->save(shouldRedirect: false, shouldSendSavedNotification: false);

            $assignment = $this->getRecord();

            if (! $this->manualAssignmentSaveCompleted || ! $assignment instanceof Assignment) {
                return;
            }

            $service = app(SchoolEmailNotificationService::class);
            $service->sendAssignmentNotification($assignment);
            $service->markAssignmentEmailSent($assignment);

            Notification::make()
                ->title('Assignment saved and students notified.')
                ->success()
                ->send();
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Assignment saved, but students could not be notified.')
                ->danger()
                ->send();
        } finally {
            $this->suppressAssignmentEmail = false;
            $this->manualAssignmentSaveCompleted = false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Load existing assignment data into the edit form
    |--------------------------------------------------------------------------
    */

    protected function mutateFormDataBeforeFill(array $data): array
    {
        unset($data['email_sent']);

        $this->record->load('attachments');

        $data['available_immediately'] = ($this->record->availability_type ?? 'immediate') !== 'scheduled';

        $data['existing_attachments'] = $this->record
            ->attachments
            ->map(function ($attachment) {
                return [
                    'attachment_id' => $attachment->id,
                    'original_name' => $attachment->original_name,
                    'file_size' => $this->formatFileSize(
                        $attachment->file_size
                    ),
                    'file_path' => $attachment->file_path,
                ];
            })
            ->values()
            ->toArray();

        $data['new_attachments'] = [];

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | Don't save attachment fields into assignments table
    |--------------------------------------------------------------------------
    */

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset(
            $data['existing_attachments'],
            $data['new_attachments'],
            $data['email_sent'],
        );

        $isImmediate = (bool) ($data['available_immediately'] ?? false);

        $data['availability_type'] = $isImmediate ? 'immediate' : 'scheduled';

        if ($isImmediate) {
            $data['start_at'] = null;
        }

        unset($data['available_immediately']);

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | Process attachments after assignment is saved
    |--------------------------------------------------------------------------
    */

    protected function afterSave(): void
    {
        $this->processAttachments();

        $assignment = $this->getRecord();

        if ($this->suppressAssignmentEmail) {
            $this->manualAssignmentSaveCompleted = true;
        } elseif ($assignment instanceof Assignment && ! $assignment->email_sent && $assignment->is_published) {
            app(SchoolEmailNotificationService::class)->notifyPublishedAssignment($assignment);
        }
    }

    protected function processAttachments(): void
    {
        $this->record->load('attachments');

        $state = $this->form->getState();

        /*
        |--------------------------------------------------------------------------
        | Existing attachments kept by the user
        |--------------------------------------------------------------------------
        */

        $existingAttachments =
            $state['existing_attachments'] ?? [];

        if (! is_array($existingAttachments)) {
            $existingAttachments = [];
        }

        /*
        |--------------------------------------------------------------------------
        | Get IDs of attachments that are still present
        |--------------------------------------------------------------------------
        */

        $keptAttachmentIds = collect($existingAttachments)
            ->pluck('attachment_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Delete attachments removed from the form
        |--------------------------------------------------------------------------
        */

        foreach ($this->record->attachments as $attachment) {

            if (
                ! in_array(
                    (int) $attachment->id,
                    $keptAttachmentIds,
                    true
                )
            ) {

                /*
                | Delete physical file
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
                | Delete database record
                */

                $attachment->delete();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Add new uploaded files
        |--------------------------------------------------------------------------
        */

        $newFiles = $state['new_attachments'] ?? [];

        if (! is_array($newFiles)) {
            $newFiles = [];
        }

        $existingDatabasePaths = $this->record
            ->attachments()
            ->pluck('file_path')
            ->toArray();

        foreach ($newFiles as $index => $path) {

            if (! is_string($path)) {
                continue;
            }

            /*
            | Make sure the uploaded file exists.
            */

            if (! Storage::disk('public')->exists($path)) {
                continue;
            }

            /*
            | Prevent duplicates.
            */

            if (
                in_array(
                    $path,
                    $existingDatabasePaths,
                    true
                )
            ) {
                continue;
            }

            /*
            | Create attachment record.
            */

            $this->record->attachments()->create([
                'original_name' => basename($path),

                'file_path' => $path,

                'mime_type' => $this->getMimeType($path),

                'file_size' => $this->getFileSize($path),

                'sort_order' => $index,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Reload attachments
        |--------------------------------------------------------------------------
        */

        $this->record->unsetRelation('attachments');

        $this->record->load('attachments');
    }

    /*
    |--------------------------------------------------------------------------
    | File helpers
    |--------------------------------------------------------------------------
    |
    | These helpers avoid the IDE's false "undefined method" warnings
    | around Storage disk methods.
    |
    */

    protected function getMimeType(string $path): ?string
    {
        try {
            return Storage::disk('public')->mimeType($path);
        } catch (Throwable) {
            return null;
        }
    }

    protected function getFileSize(string $path): ?int
    {
        try {
            return Storage::disk('public')->size($path);
        } catch (Throwable) {
            return null;
        }
    }

    protected function formatFileSize(?int $bytes): string
    {
        if ($bytes === null) {
            return 'Unknown size';
        }

        if ($bytes >= 1048576) {
            return number_format(
                $bytes / 1048576,
                2
            ).' MB';
        }

        if ($bytes >= 1024) {
            return number_format(
                $bytes / 1024,
                1
            ).' KB';
        }

        return $bytes.' bytes';
    }
}
