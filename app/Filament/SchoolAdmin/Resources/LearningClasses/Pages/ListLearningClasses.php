<?php

namespace App\Filament\SchoolAdmin\Resources\LearningClasses\Pages;

use App\Filament\SchoolAdmin\Resources\LearningClasses\LearningClassResource;
use App\Filament\SchoolAdmin\Scopes\SchoolAdminScopes;
use App\Models\School;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListLearningClasses extends ListRecords
{
    protected static string $resource = LearningClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->url(fn (): string => static::getResource()::getUrl('create', array_filter([
                    'school_id' => $this->getActiveSchoolId(),
                ]))),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('All'),
        ];

        $schools = School::query()
            ->whereIn('id', SchoolAdminScopes::schoolIds())
            ->orderBy('name')
            ->get();

        foreach ($schools as $school) {
            $tabs['school_'.$school->id] = Tab::make($school->name)
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('grade', fn ($q) => $q->where('school_id', $school->id)));
        }

        return $tabs;
    }

    protected function getActiveSchoolId(): ?int
    {
        if (! preg_match('/^school_(\d+)$/', (string) $this->activeTab, $matches)) {
            return null;
        }

        $schoolId = (int) $matches[1];

        if (! in_array($schoolId, SchoolAdminScopes::schoolIds(), true)) {
            return null;
        }

        return $schoolId;
    }

    public function getLayout(): string
    {
        return 'filament.school-admin.layouts.app';
    }
}
