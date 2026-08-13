<?php

namespace App\Filament\Resources\Assignments\Schemas;

use App\Models\LearningClass;
use App\Models\Teacher;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class AssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | Assignment Information
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
                | Class & Teacher
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
                            ->label('Responsible Teacher')
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
                            ->integer()
                            ->minValue(1)
                            ->default(100)
                            ->required()
                            ->suffix('marks'),

                    ])
                    ->columnSpanFull(),


                /*
                |--------------------------------------------------------------------------
                | Assignment Availability
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
                            ->afterStateUpdated(
                                function (
                                    Set $set,
                                    bool $state
                                ): void {

                                    $set(
                                        'availability_type',
                                        $state
                                            ? 'immediate'
                                            : 'scheduled'
                                    );

                                    /*
                                     * If assignment is made immediately
                                     * available, there is no need for a
                                     * scheduled start date.
                                     */
                                    if ($state) {
                                        $set('start_at', null);
                                    }
                                }
                            )
                            ->helperText(
                                'Turn this off if students should wait until a specific date and time.'
                            ),

                        /*
                        |--------------------------------------------------------------------------
                        | Hidden database field
                        |--------------------------------------------------------------------------
                        */

                        TextInput::make('availability_type')
                            ->hidden()
                            ->dehydrated(true)
                            ->default('immediate'),

                        DateTimePicker::make('start_at')
                            ->label('Start Date & Time')
                            ->seconds(false)
                            ->native(false)
                            ->required(
                                fn (Get $get): bool =>
                                    ! (bool) $get('available_immediately')
                            )
                            ->hidden(
                                fn (Get $get): bool =>
                                    (bool) $get('available_immediately')
                            ),

                        DateTimePicker::make('end_at')
                            ->label('End Date & Time')
                            ->seconds(false)
                            ->native(false)
                            ->required()
                            ->after('start_at')
                            ->helperText(
                                'Normal submissions are closed after this time.'
                            ),

                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(true)
                            ->helperText(
                                'Students will only see published assignments.'
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

                        Toggle::make('allow_late_submissions')
                            ->label('Allow Late Submissions')
                            ->default(false)
                            ->live()
                            ->afterStateUpdated(
                                function (
                                    Set $set,
                                    bool $state
                                ): void {

                                    if (! $state) {
                                        $set(
                                            'late_submission_value',
                                            null
                                        );

                                        $set(
                                            'late_submission_unit',
                                            null
                                        );
                                    }
                                }
                            ),

                        TextInput::make('late_submission_value')
                            ->label('Late Submission Period')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->required(
                                fn (Get $get): bool =>
                                    (bool) $get(
                                        'allow_late_submissions'
                                    )
                            )
                            ->visible(
                                fn (Get $get): bool =>
                                    (bool) $get(
                                        'allow_late_submissions'
                                    )
                            ),

                        Select::make('late_submission_unit')
                            ->label('Period Unit')
                            ->options([
                                'minutes' => 'Minutes',
                                'hours' => 'Hours',
                                'days' => 'Days',
                            ])
                            ->default('minutes')
                            ->required(
                                fn (Get $get): bool =>
                                    (bool) $get(
                                        'allow_late_submissions'
                                    )
                            )
                            ->visible(
                                fn (Get $get): bool =>
                                    (bool) $get(
                                        'allow_late_submissions'
                                    )
                            ),

                    ])
                    ->columns(3)
                    ->columnSpanFull(),


                /*
                |--------------------------------------------------------------------------
                | Student Submission Types
                |--------------------------------------------------------------------------
                */

                Section::make('Student Submission Settings')
                    ->description(
                        'Choose exactly what students are allowed to submit.'
                    )
                    ->schema([

                        Select::make('allowed_submission_types')
                            ->label('Allowed Submission Types')
                            ->multiple()
                            ->options([

                                'text' => 'Text Answer',

                                'pdf' => 'PDF',

                                'doc' => 'Word Document (.doc)',

                                'docx' => 'Word Document (.docx)',

                                'ppt' => 'PowerPoint (.ppt)',

                                'pptx' => 'PowerPoint (.pptx)',

                                'xls' => 'Excel (.xls)',

                                'xlsx' => 'Excel (.xlsx)',

                                'image' => 'Images',

                                'video' => 'Video',

                                'audio' => 'Audio / MP3',

                                'zip' => 'ZIP Archive',

                                'txt' => 'Text File (.txt)',

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
                | Existing Attachments
                |--------------------------------------------------------------------------
                |
                | IMPORTANT:
                |
                | This is deliberately separate from new_attachments.
                |
                | Existing files are represented by database records.
                | Removing a repeater item tells EditAssignment to delete
                | that attachment.
                |
                */

                Section::make('Existing Assignment Attachments')
                    ->description(
                        'These files are already attached to this assignment. Remove files you no longer need.'
                    )
                    ->schema([

                        Repeater::make('existing_attachments')
                            ->label('')
                            ->schema([

                                TextInput::make('original_name')
                                    ->label('File')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('file_size')
                                    ->label('Size')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('attachment_id')
                                    ->hidden()
                                    ->dehydrated(true),

                            ])
                            ->columns(2)
                            ->addable(false)
                            ->reorderable(false)
                            ->deletable(true)
                            ->itemLabel(
                                fn (array $state): string =>
                                    $state['original_name']
                                    ?? 'Attachment'
                            )
                            ->columnSpanFull(),

                    ])
                    ->columnSpanFull(),


                /*
                |--------------------------------------------------------------------------
                | NEW Attachments
                |--------------------------------------------------------------------------
                |
                | IMPORTANT:
                |
                | This field ONLY handles newly uploaded files.
                |
                | It does not contain existing database attachments.
                |
                */

                Section::make('Add New Assignment Attachments')
                    ->description(
                        'Upload additional files that students will receive with the assignment.'
                    )
                    ->schema([

                        FileUpload::make('new_attachments')
                            ->label('New Assignment Files')
                            ->multiple()
                            ->reorderable()
                            ->downloadable()
                            ->openable()

                            /*
                            |--------------------------------------------------------------------------
                            | Storage
                            |--------------------------------------------------------------------------
                            */

                            ->disk('public')
                            ->directory('assignments')
                            ->preserveFilenames()

                            /*
                            |--------------------------------------------------------------------------
                            | Accepted Files
                            |--------------------------------------------------------------------------
                            */

                            ->acceptedFileTypes([

                                // PDF
                                'application/pdf',

                                // Word
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',

                                // PowerPoint
                                'application/vnd.ms-powerpoint',
                                'application/vnd.openxmlformats-officedocument.presentationml.presentation',

                                // Excel
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

                                // Images
                                'image/jpeg',
                                'image/png',
                                'image/webp',

                                // Video
                                'video/mp4',
                                'video/webm',

                                // Audio
                                'audio/mpeg',
                                'audio/wav',

                                // Archives
                                'application/zip',
                                'application/x-rar-compressed',

                                // Text
                                'text/plain',

                            ])

                            /*
                            |--------------------------------------------------------------------------
                            | Maximum File Size
                            |--------------------------------------------------------------------------
                            |
                            | 50 MB per file.
                            |
                            */

                            ->maxSize(51200)

                            ->columnSpanFull(),

                    ])
                    ->columnSpanFull(),

            ]);
    }
}