<?php

namespace App\Filament\Teacher\Resources\LearningClasses\Pages;

use App\Filament\Teacher\Resources\LearningClasses\LearningClassResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewLearningClass extends ViewRecord
{
    protected static string $resource = LearningClassResource::class;

    protected string $view = 'filament.teacher.resources.learning-classes.pages.view-learning-class';

    public function getLayout(): string
    {
        return 'filament.teacher.layouts.app';
    }

    public function getTitle(): string
    {
        return $this->getRecord()->name;
    }

    /**
     * The class overview is rendered as a custom hero in the page view, so the
     * page content only needs to host the relation manager tabs.
     */
    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getRelationManagersContentComponent(),
            ]);
    }
}
