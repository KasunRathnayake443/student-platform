<?php

namespace App\Filament\Resources\Assignments\Schemas;

use App\Models\LearningClass;
use App\Models\Teacher;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class AssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | Basic Information
                |--------------------------------------------------------------------------
                */

                Section::make('Assignment Information')
                    ->description(
                        'Basic information about the assignment.'
                    )
                    ->schema([

                        TextInput::make('title')
                            ->label('Assignment Title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Short Description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),

                        RichEditor::make('instructions')
                            ->label('Assignment Instructions')
                            ->helperText(
                                'Explain what students need to do.'
                            )
                            ->columnSpanFull(),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),


                /*
                |--------------------------------------------------------------------------
                | Learning Class
                |--------------------------------------------------------------------------
                */

                Section::make('Class & Teacher')
                    ->description(
                        'Select the class and teacher responsible for this assignment.'
                    )
                    ->schema([

                        Select::make('learning_class_id')
                            ->label('Learning Class')
                            ->options(
                                fn () => LearningClass::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('teacher_id')
                            ->label('Teacher')
                            ->options(
                                fn () => Teacher::query()
                                    ->with('user')
                                    ->get()
                                    ->mapWithKeys(
                                        fn (Teacher $teacher) => [
                                            $teacher->id =>
                                                $teacher->user->name
                                                . ' - '
                                                . $teacher->employee_no,
                                        ]
                                    )
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText(
                                'Only the assigned teacher for this class can grade submissions.'
                            ),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),


                /*
                |--------------------------------------------------------------------------
                | Scoring
                |--------------------------------------------------------------------------
                */

                Section::make('Scoring')
                    ->schema([

                        TextInput::make('max_score')
                            ->label('Maximum Score')
                            ->numeric()
                            ->minValue(1)
                            ->default(100)
                            ->required()
                            ->suffix('marks'),

                    ])
                    ->columnSpanFull(),


                /*
                |--------------------------------------------------------------------------
                | Availability
                |--------------------------------------------------------------------------
                */

                Section::make('Assignment Availability')
                    ->description(
                        'Control when students can access and submit this assignment.'
                    )
                    ->schema([

                        Toggle::make('available_immediately')
                            ->label('Available Immediately')
                            ->default(true)
                            ->live()
                            ->helperText(
                                'Turn this off if students should wait for a scheduled start date.'
                            ),

                        DateTimePicker::make('start_at')
                            ->label('Start Date & Time')
                            ->seconds(false)
                            ->native(false)
                            ->required(
                                fn (Get $get): bool =>
                                    ! $get('available_immediately')
                            )
                            ->hidden(
                                fn (Get $get): bool =>
                                    $get('available_immediately')
                            ),

                        DateTimePicker::make('end_at')
                            ->label('End Date & Time')
                            ->seconds(false)
                            ->native(false)
                            ->required()
                            ->after('start_at')
                            ->helperText(
                                'Students cannot make normal submissions after this time.'
                            ),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),


                /*
                |--------------------------------------------------------------------------
                | Late Submission
                |--------------------------------------------------------------------------
                */

                Section::make('Late Submission')
                    ->description(
                        'Allow students to submit after the normal deadline.'
                    )
                    ->schema([

                        Toggle::make('allow_late_submission')
                            ->label('Allow Late Submissions')
                            ->default(false)
                            ->live(),

                        TextInput::make('late_submission_minutes')
                            ->label('Late Submission Period')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->suffix('minutes')
                            ->required(
                                fn (Get $get): bool =>
                                    (bool) $get('allow_late_submission')
                            )
                            ->visible(
                                fn (Get $get): bool =>
                                    (bool) $get('allow_late_submission')
                            )
                            ->helperText(
                                'Students can submit during this period after the normal deadline.'
                            ),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),


                /*
                |--------------------------------------------------------------------------
                | Submission Types
                |--------------------------------------------------------------------------
                */

                Section::make('Student Submission Settings')
                    ->description(
                        'Choose which types of files students are allowed to submit.'
                    )
                    ->schema([

                        Select::make('allowed_submission_types')
                            ->label('Allowed Submission Types')
                            ->multiple()
                            ->options([

                                'text' =>
                                    'Text Answer',

                                'pdf' =>
                                    'PDF',

                                'doc' =>
                                    'Word Document (.doc)',

                                'docx' =>
                                    'Word Document (.docx)',

                                'ppt' =>
                                    'PowerPoint (.ppt)',

                                'pptx' =>
                                    'PowerPoint (.pptx)',

                                'xls' =>
                                    'Excel (.xls)',

                                'xlsx' =>
                                    'Excel (.xlsx)',

                                'image' =>
                                    'Images',

                                'video' =>
                                    'Video',

                                'audio' =>
                                    'Audio / MP3',

                                'zip' =>
                                    'ZIP Archive',

                                'txt' =>
                                    'Text File (.txt)',

                            ])
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText(
                                'Students will only be able to submit the selected types.'
                            )
                            ->columnSpanFull(),

                    ])
                    ->columnSpanFull(),


                /*
                |--------------------------------------------------------------------------
                | Assignment Attachments
                |--------------------------------------------------------------------------
                */

                Section::make('Assignment Attachments')
                    ->description(
                        'Files provided to students with this assignment.'
                    )
                    ->schema([

                        FileUpload::make('attachments')
                            ->label('Assignment Files')
                            ->multiple()
                            ->reorderable()
                            ->downloadable()
                            ->openable()
                            ->disk('public')
                            ->directory('assignments')
                            ->preserveFilenames()
                            ->acceptedFileTypes([

                                'application/pdf',

                                'application/msword',

                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',

                                'application/vnd.ms-powerpoint',

                                'application/vnd.openxmlformats-officedocument.presentationml.presentation',

                                'application/vnd.ms-excel',

                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

                                'image/jpeg',

                                'image/png',

                                'image/webp',

                                'video/mp4',

                                'video/webm',

                                'audio/mpeg',

                                'audio/wav',

                                'application/zip',

                                'application/x-rar-compressed',

                                'text/plain',

                            ])
                            ->maxSize(102400)
                            ->columnSpanFull(),

                    ])
                    ->columnSpanFull(),


                /*
                |--------------------------------------------------------------------------
                | Publication
                |--------------------------------------------------------------------------
                */

                Section::make('Publication')
                    ->schema([

                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(false)
                            ->helperText(
                                'Students will only see published assignments.'
                            ),

                    ])
                    ->columnSpanFull(),

            ]);
    }
}