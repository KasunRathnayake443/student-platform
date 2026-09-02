<?php

namespace App\Filament\SchoolAdmin\Resources\Grades\Pages;

use App\Filament\SchoolAdmin\Resources\Grades\GradeResource;
use App\Filament\SchoolAdmin\Scopes\SchoolAdminScopes;
use App\Models\School;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListGrades extends ListRecords
{
    protected static string $resource = GradeResource::class;

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
                ->modifyQueryUsing(fn (Builder $query) => $query->where('school_id', $school->id));
        }

        return $tabs;
    }
}
