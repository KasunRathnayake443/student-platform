<?php

namespace App\Filament\Resources\SchoolAdmins;

use App\Models\User;

use App\Filament\Resources\SchoolAdmins\Pages\CreateSchoolAdmin;
use App\Filament\Resources\SchoolAdmins\Pages\EditSchoolAdmin;
use App\Filament\Resources\SchoolAdmins\Pages\ListSchoolAdmins;
use App\Filament\Resources\SchoolAdmins\Pages\ViewSchoolAdmin;

use App\Filament\Resources\SchoolAdmins\Schemas\SchoolAdminForm;
use App\Filament\Resources\SchoolAdmins\Tables\SchoolAdminsTable;

use BackedEnum;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;


class SchoolAdminResource extends Resource
{

    protected static ?string $model = User::class;


    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedUserGroup;



    protected static ?string $navigationLabel =
        'School Admins';



    protected static ?string $recordTitleAttribute =
        'name';




    public static function form(Schema $schema): Schema
    {
        return SchoolAdminForm::configure($schema);
    }



    public static function table(Table $table): Table
    {
        return SchoolAdminsTable::configure($table);
    }



    public static function getPages(): array
    {
        return [

            'index' =>
                ListSchoolAdmins::route('/'),


            'create' =>
                CreateSchoolAdmin::route('/create'),


            'view' =>
                ViewSchoolAdmin::route('/{record}'),


            'edit' =>
                EditSchoolAdmin::route('/{record}/edit'),

        ];
    }


}