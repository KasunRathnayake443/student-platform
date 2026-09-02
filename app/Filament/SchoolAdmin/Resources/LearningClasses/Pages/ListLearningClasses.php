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
            CreateAction::make(),
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

    public function getLayout(): string
    {
        return 'filament.school-admin.layouts.app';
    }
}
