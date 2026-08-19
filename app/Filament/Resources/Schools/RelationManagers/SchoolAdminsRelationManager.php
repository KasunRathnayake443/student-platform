<?php

namespace App\Filament\Resources\Schools\RelationManagers;

use App\Filament\Resources\SchoolAdmins\SchoolAdminResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DetachAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class SchoolAdminsRelationManager extends RelationManager
{
    protected static string $relationship = 'schoolAdmins';

    protected static ?string $title = 'School Admins';

    public function table(Table $table): Table
    {

        return $table

            ->columns([

                Tables\Columns\ImageColumn::make('schoolAdmin.profile_photo')
                    ->label('Admin Photo')
                    ->circular(),

                Tables\Columns\TextColumn::make('name')

                    ->label('Name')

                    ->searchable()

                    ->sortable(),

                Tables\Columns\TextColumn::make('email')

                    ->label('Email')

                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')

                    ->label('Joined')

                    ->date(),

            ])

            ->headerActions([

                Action::make('addExistingAdmin')

                    ->label('Add Existing Admin')

                    ->icon('heroicon-o-user-plus')

                    ->form([

                        Select::make('user_id')

                            ->label('School Admin')

                            ->options(function () {

                                return User::role('school_admin')

                                    ->whereHas(
                                        'schoolAdmin'
                                    )

                                    ->whereNotIn(

                                        'id',

                                        $this->getOwnerRecord()

                                            ->schoolAdmins()

                                            ->pluck('users.id')

                                    )

                                    ->pluck(
                                        'name',
                                        'id'
                                    );

                            })

                            ->searchable()

                            ->required(),

                    ])

                    ->action(function (array $data) {

                        $this->getOwnerRecord()

                            ->schoolAdmins()

                            ->syncWithoutDetaching([

                                $data['user_id'],

                            ]);

                    }),

                Action::make('createAdmin')

                    ->label('Create New Admin')

                    ->icon('heroicon-o-plus')

                    ->form([

                        TextInput::make('name')

                            ->required(),

                        TextInput::make('email')

                            ->email()

                            ->required()

                            ->unique(
                                'users',
                                'email'
                            ),

                        TextInput::make('password')

                            ->password()

                            ->required(),

                        TextInput::make('phone'),

                        TextInput::make('address'),

                    ])

                    ->action(function (array $data) {

                        $user = User::create([

                            'name' => $data['name'],

                            'email' => $data['email'],

                            'password' => Hash::make(
                                $data['password']
                            ),

                            'must_change_password' => true,

                        ]);

                        $user->assignRole(
                            'school_admin'
                        );

                        $user->schoolAdmin()->create([

                            'phone' => $data['phone'] ?? null,

                            'address' => $data['address'] ?? null,

                        ]);

                        $this->getOwnerRecord()

                            ->schoolAdmins()

                            ->attach(
                                $user->id
                            );

                    }),

            ])

            ->recordActions([

                ViewAction::make()

                    ->url(
                        fn ($record) => SchoolAdminResource::getUrl(
                            'view',
                            [
                                'record' => $record->schoolAdmin->id,
                            ]
                        )
                    ),

                DetachAction::make()

                    ->label(
                        'Remove From School'
                    ),

            ]);

    }
}
