<?php

namespace App\Filament\Teacher\Resources\Assignments\Schemas;

use App\Models\Assignment;
use App\Models\LearningClass;
use App\Models\Teacher;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
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

                Section::make('Assignment Information')
                    ->description('Basic information about the assignment.')
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
                            ->helperText('Explain what students need to do.')
                            ->columnSpanFull(),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                /*
                 * The assignment always stays in its class. All teachers
                 * assigned to it (creator included) may grade submissions.
                 */
                Section::make('Class & Teachers')
                    ->description(
                        'Select the teachers responsible for this assignment.'
                    )
                    ->schema([

                        Placeholder::make('learning_class_display')
                            ->label('Learning Class')
                            ->content(function (Get $get): string {
                                $class = LearningClass::find($get('learning_class_id'));

                                return $class instanceof LearningClass
                                    ? $class->name
                                    : '-';
                            }),

                        Hidden::make('learning_class_id'),

                        Select::make('teacher_ids')
                            ->label('Assigned Teachers')
                            ->options(function () use ($schema): array {
                                $record = $schema->getRecord();

                                if (! $record instanceof Assignment) {
                                    return [];
                                }

                                $class = $record->learningClass;

                                if (! $class instanceof LearningClass) {
                                    return [];
                                }

                                return $class
                                    ->teachers()
                                    ->with('user')
                                    ->get()
                                    ->mapWithKeys(
                                        fn (Teacher $teacher) => [
                                            $teacher->id => $teacher->user->name
                                                .' - '
                                                .$teacher->employee_no,
                                        ]
                                    );
                            })
                            ->searchable()
                            ->preload()
                            ->multiple()
                            ->minItems(1)
                            ->required()
                            ->helperText(
                                'All selected teachers can view submissions and grade them.'
                            )
                            ->columnSpanFull(),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),

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

                Section::make('Assignment Availability')
                    ->description('Control when students can access and submit this assignment.')
                    ->schema([

                        Toggle::make('available_immediately')
                            ->label('Available Immediately')
                            ->default(true)
                            ->live()
                            ->afterStateUpdated(
                                function (Set $set, bool $state): void {
                                    $set(
                                        'availability_type',
                                        $state ? 'immediate' : 'scheduled'
                                    );

                                    if ($state) {
                                        $set('start_at', null);
                                    }
                                }
                            )
                            ->helperText(
                                'Turn this off if students should wait until a specific date and time.'
                            ),

                        TextInput::make('availability_type')
                            ->hidden()
                            ->dehydrated(true)
                            ->default('immediate'),

                        DateTimePicker::make('start_at')
                            ->label('Start Date & Time')
                            ->seconds(false)
                            ->native(false)
                            ->required(
                                fn (Get $get): bool => ! (bool) $get('available_immediately')
                            )
                            ->hidden(
                                fn (Get $get): bool => (bool) $get('available_immediately')
                            ),

                        DateTimePicker::make('end_at')
                            ->label('End Date & Time')
                            ->seconds(false)
                            ->native(false)
                            ->required()
                            ->after('start_at')
                            ->helperText('Normal submissions are closed after this time.'),

                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(true)
                            ->helperText('Students will only see published assignments.'),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Late Submission')
                    ->description('Allow students to submit after the normal deadline.')
                    ->schema([

                        Toggle::make('allow_late_submissions')
                            ->label('Allow Late Submissions')
                            ->default(false)
                            ->live()
                            ->afterStateUpdated(
                                function (Set $set, bool $state): void {
                                    if (! $state) {
                                        $set('late_submission_value', null);
                                        $set('late_submission_unit', null);
                                    }
                                }
                            ),

                        TextInput::make('late_submission_value')
                            ->label('Late Submission Period')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->required(
                                fn (Get $get): bool => (bool) $get('allow_late_submissions')
                            )
                            ->visible(
                                fn (Get $get): bool => (bool) $get('allow_late_submissions')
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
                                fn (Get $get): bool => (bool) $get('allow_late_submissions')
                            )
                            ->visible(
                                fn (Get $get): bool => (bool) $get('allow_late_submissions')
                            ),

                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make('Student Submission Settings')
                    ->description('Choose exactly what students are allowed to submit.')
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
                            ->helperText('Students will only be able to submit the selected types.')
                            ->columnSpanFull(),

                    ])
                    ->columnSpanFull(),

                Section::make('Existing Assignment Attachments')
                    ->description('These files are already attached to this assignment. Remove files you no longer need.')
                    ->schema([

                        Repeater::make('existing_attachments')
                            ->label('')
                            ->schema([
                                Hidden::make('attachment_id')->dehydrated(),
                                Placeholder::make('file_name')
                                    ->label('File')
                                    ->content(fn (Get $get) => $get('original_name') ?: 'Attachment'),
                                Placeholder::make('file_size_display')
                                    ->label('Size')
                                    ->content(fn (Get $get) => $get('file_size') ?: 'Unknown size'),
                            ])
                            ->columns(2)
                            ->addable(false)
                            ->reorderable(false)
                            ->deletable(true)
                            ->itemLabel(
                                fn (array $state): string => $state['original_name'] ?? 'Attachment'
                            )
                            ->columnSpanFull(),

                    ])
                    ->columnSpanFull(),

                Section::make('Add New Assignment Attachments')
                    ->description('Upload additional files that students will receive with the assignment.')
                    ->schema([

                        FileUpload::make('new_attachments')
                            ->label('New Assignment Files')
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
                            ->maxSize(51200)
                            ->columnSpanFull(),

                    ])
                    ->columnSpanFull(),
            ]);
    }
}
