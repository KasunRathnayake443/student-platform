<?php

namespace App\Filament\Resources\LearningClasses;


use App\Filament\Resources\LearningClasses\Pages\CreateLearningClass;
use App\Filament\Resources\LearningClasses\Pages\EditLearningClass;
use App\Filament\Resources\LearningClasses\Pages\ListLearningClasses;
use App\Filament\Resources\LearningClasses\Pages\ViewLearningClass;


use App\Filament\Resources\LearningClasses\Schemas\LearningClassForm;
use App\Filament\Resources\LearningClasses\Schemas\LearningClassInfolist;
use App\Filament\Resources\LearningClasses\Tables\LearningClassesTable;


use App\Filament\Resources\LearningClasses\RelationManagers;

use Illuminate\Database\Eloquent\Builder;

use App\Models\LearningClass;


use BackedEnum;


use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;



class LearningClassResource extends Resource
{


    protected static ?string $model = LearningClass::class;



    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedRectangleStack;



    protected static ?string $recordTitleAttribute = 'name';






    public static function form(Schema $schema): Schema
    {

        return LearningClassForm::configure($schema);

    }







    public static function infolist(Schema $schema): Schema
    {

        return LearningClassInfolist::configure($schema);

    }







    public static function table(Table $table): Table
    {

        return LearningClassesTable::configure($table);

    }








    public static function getRelations(): array
    {

        return [


            RelationManagers\TeachersRelationManager::class,


            RelationManagers\StudentsRelationManager::class,


        ];

    }







    public static function getPages(): array
    {

        return [

            'index' =>
                ListLearningClasses::route('/'),


            'create' =>
                CreateLearningClass::route('/create'),


            'view' =>
                ViewLearningClass::route('/{record}'),


            'edit' =>
                EditLearningClass::route('/{record}/edit'),

        ];

    }






    public static function getEloquentQuery(): Builder
    {
    
        return parent::getEloquentQuery()
    
            ->withCount([
    
                'students',
    
                'teachers',
    
            ])
    
            ->with([
    
                'grade.school'
    
            ]);
    
    }



}