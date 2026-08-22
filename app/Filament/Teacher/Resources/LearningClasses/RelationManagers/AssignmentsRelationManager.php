<?php

namespace App\Filament\Teacher\Resources\LearningClasses\RelationManagers;

use App\Filament\Teacher\Resources\Assignments\AssignmentResource;
use App\Models\Assignment;
use App\Models\LearningClass;
use App\Models\Teacher;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class AssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'assignments';

    protected static ?string $title = 'Assignments';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->with(['teacher.user', 'teachers.user'])
                ->withCount(['attachments', 'submissions']))
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Assignment')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('assignee_names')
                    ->label('Teachers')
                    ->badge()
                    ->state(function (Assignment $record): array {
                        $names = $record->teachers
                            ->map(fn (Teacher $teacher) => $teacher->user->name);

                        if ($names->isEmpty() && $record->teacher) {
                            return [$record->teacher->user->name];
                        }

                        return $names->values()->toArray();
                    }),

                Tables\Columns\TextColumn::make('max_score')
                    ->label('Marks')
                    ->suffix(' marks')
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_at')
                    ->label('Starts')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_at')
                    ->label('Deadline')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('submissions_count')
                    ->label('Submissions')
                    ->sortable(),

                Tables\Columns\TextColumn::make('attachments_count')
                    ->label('Files')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Action::make('createAssignment')
                    ->label('Create Assignment')
                    ->icon('heroicon-o-plus')
                    ->form([

                        Section::make('Assignment Information')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Assignment Title')
                                    ->required()
                                    ->maxLength(255),

                                Textarea::make('description')
                                    ->label('Short Description')
                                    ->rows(3)
                                    ->maxLength(1000)
                                    ->columnSpanFull(),

                                RichEditor::make('instructions')
                                    ->label('Assignment Instructions')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),

                        Section::make('Assigned Teachers')
                            ->description(
                                'You are assigned by default. Co-teachers of this class can be added to grade submissions together.'
                            )
                            ->schema([
                                Select::make('teacher_ids')
                                    ->label('Teachers')
                                    ->options(function () {
                                        $class = $this->getOwnerRecord();

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
                                    ->default(auth()->user()->teacher?->id)
                                    ->multiple()
                                    ->minItems(1)
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ])
                            ->columnSpanFull(),

                        Section::make('Scoring & Availability')
                            ->schema([
                                TextInput::make('max_score')
                                    ->label('Maximum Score')
                                    ->numeric()
                                    ->integer()
                                    ->minValue(1)
                                    ->default(100)
                                    ->required()
                                    ->suffix('marks'),

                                Toggle::make('available_immediately')
                                    ->label('Available Immediately')
                                    ->default(true)
                                    ->live(),

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
                                    ->after('start_at'),

                                Toggle::make('is_published')
                                    ->label('Published')
                                    ->default(true),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),

                        Section::make('Late Submission')
                            ->schema([
                                Toggle::make('allow_late_submissions')
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
                                        fn (Get $get): bool => (bool) $get('allow_late_submissions')
                                    )
                                    ->visible(
                                        fn (Get $get): bool => (bool) $get('allow_late_submissions')
                                    ),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),

                        Section::make('Allowed Student Submissions')
                            ->schema([
                                Select::make('allowed_submission_types')
                                    ->label('Allowed Submission Types')
                                    ->multiple()
                                    ->options([
                                        'text' => 'Text Answer',
                                        'pdf' => 'PDF',
                                        'doc' => 'Word (.doc)',
                                        'docx' => 'Word (.docx)',
                                        'ppt' => 'PowerPoint (.ppt)',
                                        'pptx' => 'PowerPoint (.pptx)',
                                        'xls' => 'Excel (.xls)',
                                        'xlsx' => 'Excel (.xlsx)',
                                        'image' => 'Images',
                                        'video' => 'Video',
                                        'audio' => 'Audio / MP3',
                                        'zip' => 'ZIP Archive',
                                        'txt' => 'Text File',
                                    ])
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),

                        Section::make('Assignment Attachments')
                            ->schema([
                                FileUpload::make('attachments')
                                    ->label('Files for Students')
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
                    ])
                    ->action(function (array $data) {
                        $class = $this->getOwnerRecord();

                        if (! $class instanceof LearningClass) {
                            return;
                        }

                        $teacherId = auth()->user()->teacher?->id;

                        $assignment = Assignment::create([
                            'learning_class_id' => $class->getKey(),
                            'teacher_id' => $teacherId,

                            'title' => $data['title'],
                            'description' => $data['description'] ?? null,
                            'instructions' => $data['instructions'] ?? null,
                            'max_score' => $data['max_score'] ?? 100,

                            'availability_type' => ($data['available_immediately'] ?? true)
                                    ? 'immediate'
                                    : 'scheduled',
                            'start_at' => $data['start_at'] ?? null,
                            'end_at' => $data['end_at'] ?? null,

                            'allow_late_submissions' => $data['allow_late_submissions'] ?? false,
                            'late_submission_value' => ! empty($data['late_submission_minutes'])
                                    ? $data['late_submission_minutes']
                                    : null,
                            'late_submission_unit' => ! empty($data['late_submission_minutes'])
                                    ? 'minutes'
                                    : null,

                            'allowed_submission_types' => $data['allowed_submission_types'] ?? [],
                            'is_published' => $data['is_published'] ?? false,
                        ]);

                        /*
                         * Every selected teacher becomes an assignee so all
                         * of them can view submissions and grade.
                         */
                        $assignment->teachers()->sync(
                            array_map(intval(...), $data['teacher_ids'])
                        );

                        if (! empty($data['attachments'])) {
                            foreach ($data['attachments'] as $index => $file) {
                                $path = is_string($file)
                                    ? $file
                                    : $file->store('assignments', 'public');

                                if (! Storage::disk('public')->exists($path)) {
                                    continue;
                                }

                                $assignment->attachments()->create([
                                    'original_name' => basename($path),
                                    'file_path' => $path,
                                    'mime_type' => Storage::disk('public')->mimeType($path),
                                    'file_size' => Storage::disk('public')->size($path),
                                    'sort_order' => $index,
                                ]);
                            }
                        }
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(
                        fn (Assignment $record) => AssignmentResource::getUrl('view', ['record' => $record], panel: 'teacher')
                    ),

                EditAction::make()
                    ->url(
                        fn (Assignment $record) => AssignmentResource::getUrl('edit', ['record' => $record], panel: 'teacher')
                    ),
            ]);
    }
}
