<?php

namespace App\Filament\Resources\Schools\Pages;

use App\Filament\Resources\Schools\SchoolResource;
use App\Models\School;
use Filament\Actions\CreateAction;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListSchools extends ListRecords
{
    protected static string $resource = SchoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('All Schools'),
        ];

        $schools = School::orderBy('name')->get();

        foreach ($schools as $school) {
            $tabs['school_' . $school->id] = Tab::make($school->name)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('id', $school->id));
        }

        return $tabs;
    }
}

