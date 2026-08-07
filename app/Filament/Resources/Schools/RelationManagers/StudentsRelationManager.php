<?php

namespace App\Filament\Resources\Schools\RelationManagers;


use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\LearningClass;


use App\Filament\Resources\Students\StudentResource;


use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;


use Filament\Resources\RelationManagers\RelationManager;


use Filament\Tables;
use Filament\Tables\Table;


use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;


use Illuminate\Support\Facades\DB;



class StudentsRelationManager extends RelationManager
{


    protected static string $relationship = 'students';



    protected static ?string $title = 'Students';








    public function table(Table $table): Table
    {


        return $table



            ->recordTitleAttribute('user.name')



            ->columns([



                Tables\Columns\ImageColumn::make('profile_photo')

                    ->label('Profile Photo')

                    ->circular(),



                Tables\Columns\TextColumn::make('user.name')

                    ->label('Student Name')

                    ->searchable()

                    ->sortable(),



                Tables\Columns\TextColumn::make('admission_no')

                    ->label('Admission No'),



                Tables\Columns\TextColumn::make('enrollments.grade.name')

                    ->label('Grade'),



                Tables\Columns\TextColumn::make('phone')

                    ->label('Phone'),


            ])







            ->headerActions([





                Action::make('attachStudent')


                    ->label('Add Existing Student')

                    ->icon('heroicon-o-link')



                    ->form([




                        Select::make('student_id')

                            ->label('Student')

                            ->options(

                                Student::with('user')

                                    ->get()

                                    ->pluck(
                                        'user.name',
                                        'id'
                                    )

                            )

                            ->searchable()

                            ->required(),





                        Select::make('grade_id')

                            ->label('Grade')

                            ->options(

                                $this->getOwnerRecord()

                                    ->grades()

                                    ->where(
                                        'is_active',
                                        true
                                    )

                                    ->pluck(
                                        'name',
                                        'id'
                                    )

                            )

                            ->required()

                            ->live(),






                        Select::make('learning_class_ids')

                            ->label('Classes')

                            ->multiple()

                            ->options(function($get){


                                if(!$get('grade_id')){

                                    return [];

                                }



                                return LearningClass::where(

                                    'grade_id',

                                    $get('grade_id')

                                )

                                ->where(
                                    'is_active',
                                    true
                                )

                                ->pluck(
                                    'name',
                                    'id'
                                );


                            })

                            ->searchable(),






                        TextInput::make('academic_year')

                            ->label('Academic Year')

                            ->default(
                                date('Y')
                            )

                            ->required(),



                    ])





                    ->action(function(array $data){


                        DB::transaction(function() use ($data){



                            $student =

                                Student::findOrFail(
                                    $data['student_id']
                                );



                            $school =

                                $this->getOwnerRecord();






                            $enrollment =

                                StudentEnrollment::create([



                                    'student_id' =>
                                        $student->id,



                                    'school_id' =>
                                        $school->id,



                                    'grade_id' =>
                                        $data['grade_id'],



                                    'academic_year' =>
                                        $data['academic_year'],



                                    'status' =>
                                        'active',


                                ]);








                            foreach(
                                $data['learning_class_ids'] ?? []
                                as $classId
                            ){



                                $student
                                    ->classes()
                                    ->attach(


                                        $classId,


                                        [


                                            'student_enrollment_id' =>

                                                $enrollment->id


                                        ]

                                    );


                            }



                        });



                    }),










                Action::make('createStudent')


                    ->label('Add New Student')


                    ->icon('heroicon-o-user-plus')



                    ->url(fn () =>


                        StudentResource::getUrl(

                            'create',

                            [

                                'school_id' =>

                                    $this->getOwnerRecord()->id,

                            ]

                        )


                    ),





            ])








            ->recordActions([




                ViewAction::make()


                    ->url(

                        fn($record) =>


                            StudentResource::getUrl(

                                'view',

                                [

                                    'record' => $record,

                                ]

                            )

                    ),






                EditAction::make(),






                DeleteAction::make(),




            ]);



    }



}