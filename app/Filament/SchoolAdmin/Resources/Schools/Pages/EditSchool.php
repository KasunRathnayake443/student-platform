<?php

namespace App\Filament\SchoolAdmin\Resources\Schools\Pages;

use App\Filament\Resources\Schools\Concerns\SendsSchoolTestEmail;
use App\Filament\SchoolAdmin\Resources\Schools\SchoolResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSchool extends EditRecord
{
    use SendsSchoolTestEmail;

    protected static string $resource = SchoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            $this->getSendTestEmailAction(),
        ];
    }

    public function getLayout(): string
    {
        return 'filament.school-admin.layouts.app';
    }
}
