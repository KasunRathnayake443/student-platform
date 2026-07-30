<?php

namespace App\Filament\Resources\Schools\RelationManagers;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables;
use Filament\Tables\Table;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;


class SchoolAdminsRelationManager extends RelationManager
{

    protected static string $relationship = 'users';


    protected static ?string $title = 'School Admins';



    public function table(Table $table): Table
    {

        return $table

            ->modifyQueryUsing(function (Builder $query) {

                return $query->role('school_admin');

            })


            ->columns([


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



                /*
                |--------------------------------------------------------------------------
                | Add Existing Admin
                |--------------------------------------------------------------------------
                */


                Action::make('addExistingAdmin')

                    ->label('Add Existing Admin')

                    ->icon('heroicon-o-user-plus')


                    ->form([


                        Select::make('user_id')

                            ->label('School Admin')

                            ->options(function(){


                                $existingAdmins = 
                                    $this->getOwnerRecord()
                                        ->users()
                                        ->pluck('users.id');


                                return User::role('school_admin')
                                    ->whereNotIn(
                                        'users.id',
                                        $existingAdmins
                                    )
                                    ->pluck(
                                        'name',
                                        'id'
                                    );


                            })

                            ->searchable()

                            ->required(),


                    ])



                    ->action(function(array $data){


                        $this->getOwnerRecord()
                            ->users()
                            ->syncWithoutDetaching([

                                $data['user_id']

                            ]);


                        $this->dispatch(
                            'refresh'
                        );


                    }),





                /*
                |--------------------------------------------------------------------------
                | Create New Admin
                |--------------------------------------------------------------------------
                */


                Action::make('createAdmin')

                    ->label('Create New Admin')

                    ->icon('heroicon-o-plus')



                    ->form([



                        TextInput::make('name')

                            ->label('Name')

                            ->required(),




                        TextInput::make('email')

                            ->label('Email')

                            ->email()

                            ->required()

                            ->unique(
                                'users',
                                'email'
                            ),





                        TextInput::make('password')

                            ->label('Password')

                            ->password()

                            ->required(),




                        TextInput::make('phone')

                            ->label('Phone'),





                        TextInput::make('address')

                            ->label('Address'),


                    ])




                    ->action(function(array $data){



                        $user = User::create([



                            'name' => 
                                $data['name'],



                            'email' => 
                                $data['email'],



                            'password' =>
                                Hash::make(
                                    $data['password']
                                ),



                            'must_change_password' =>
                                true,



                        ]);





                        $user->assignRole(
                            'school_admin'
                        );





                        $this->getOwnerRecord()
                            ->users()
                            ->attach(
                                $user->id
                            );





                        $this->dispatch(
                            'refresh'
                        );


                    }),


            ])




            ->recordActions([



                DetachAction::make()

                    ->label(
                        'Remove From School'
                    ),


            ]);


    }


}