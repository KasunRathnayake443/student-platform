<?php

namespace App\Filament\Resources\SchoolAdmins\Schemas;

use App\Models\School;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SchoolAdminForm
{
    public static function configure(Schema $schema): Schema
    {

        return $schema

            ->components([

                FileUpload::make('profile_photo')

                    ->label('Profile Picture')

                    ->image()

                    ->directory('school-admins/profile')

                    ->imageEditor()

                    ->avatar()

                    ->nullable(),

                TextInput::make('name')

                    ->required(),

                TextInput::make('email')

                    ->email()

                    ->required(),

                TextInput::make('password')

                    ->password()

                    ->required(fn ($record) => ! $record),

                TextInput::make('phone'),

                Textarea::make('address'),

                Select::make('schools')

                    ->label('Assigned Schools')

                    ->multiple()

                    ->options(
                        School::pluck(
                            'name',
                            'id'
                        )
                    )

                    ->searchable()

                    ->preload(),

            ]);

    }
}
