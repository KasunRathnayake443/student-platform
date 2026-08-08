<?php

namespace App\Filament\Resources\LearningClasses\RelationManagers;

use App\Filament\Resources\Lessons\LessonResource;
use App\Models\Lesson;
use App\Models\Teacher;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;

use Filament\Resources\RelationManagers\RelationManager;

use Filament\Tables;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';

    protected static ?string $title = 'Lessons';

    public function table(Table $table): Table
    {
        return $table

            /*
            |--------------------------------------------------------------------------
            | Query
            |--------------------------------------------------------------------------
            */

            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query
                    ->with([
                        'teacher.user',
                    ])
                    ->withCount('attachments');
            })

            /*
            |--------------------------------------------------------------------------
            | Columns
            |--------------------------------------------------------------------------
            */

            ->columns([

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Lesson')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('teacher.user.name')
                    ->label('Teacher')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('attachments_count')
                    ->label('Attachments')
                    ->counts('attachments'),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),

            ])

            /*
            |--------------------------------------------------------------------------
            | Default Sorting
            |--------------------------------------------------------------------------
            */

            ->defaultSort('sort_order')

            /*
            |--------------------------------------------------------------------------
            | Header Actions
            |--------------------------------------------------------------------------
            */

            ->headerActions([

                Action::make('createLesson')

                    ->label('Create Lesson')

                    ->icon('heroicon-o-plus')

                    ->form([

                        /*
                        |--------------------------------------------------------------------------
                        | Lesson Title
                        |--------------------------------------------------------------------------
                        */

                        TextInput::make('title')
                            ->label('Lesson Title')
                            ->required()
                            ->maxLength(255),

                        /*
                        |--------------------------------------------------------------------------
                        | Teacher
                        |--------------------------------------------------------------------------
                        */

                        Select::make('teacher_id')
                            ->label('Teacher')

                            ->options(function () {

                                $class = $this->getOwnerRecord();

                                return $class
                                    ->teachers()
                                    ->with('user')
                                    ->get()
                                    ->mapWithKeys(
                                        function (Teacher $teacher) {

                                            return [
                                                $teacher->id =>
                                                    $teacher->user->name,
                                            ];
                                        }
                                    );
                            })

                            ->searchable()
                            ->preload()
                            ->required(),

                        /*
                        |--------------------------------------------------------------------------
                        | Description
                        |--------------------------------------------------------------------------
                        */

                        Textarea::make('description')
                            ->label('Short Description')
                            ->rows(3)
                            ->maxLength(1000),

                        /*
                        |--------------------------------------------------------------------------
                        | Lesson Content
                        |--------------------------------------------------------------------------
                        */

                        RichEditor::make('content')
                            ->label('Lesson Content')
                            ->columnSpanFull(),

                        /*
                        |--------------------------------------------------------------------------
                        | Video URL
                        |--------------------------------------------------------------------------
                        */

                        TextInput::make('video_url')
                            ->label('Video URL')
                            ->url()
                            ->maxLength(2048),

                        /*
                        |--------------------------------------------------------------------------
                        | Sort Order
                        |--------------------------------------------------------------------------
                        */

                        TextInput::make('sort_order')
                            ->label('Display Order')
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->minValue(0),

                        /*
                        |--------------------------------------------------------------------------
                        | Published
                        |--------------------------------------------------------------------------
                        */

                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(false),

                        /*
                        |--------------------------------------------------------------------------
                        | Attachments
                        |--------------------------------------------------------------------------
                        */

                        FileUpload::make('attachments')
                            ->label('Lesson Attachments')

                            ->multiple()

                            ->reorderable()

                            ->downloadable()

                            ->openable()

                            ->disk('public')

                            ->directory('lessons')

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

                                'application/zip',

                                'application/x-rar-compressed',

                                'text/plain',

                            ])

                            ->maxSize(51200)

                            ->columnSpanFull(),

                    ])

                    /*
                    |--------------------------------------------------------------------------
                    | Create Lesson
                    |--------------------------------------------------------------------------
                    */

                    ->action(function (array $data) {

                        $class = $this->getOwnerRecord();

                        $lesson = Lesson::create([

                            'learning_class_id' =>
                                $class->id,

                            'teacher_id' =>
                                $data['teacher_id'],

                            'title' =>
                                $data['title'],

                            'description' =>
                                $data['description'] ?? null,

                            'content' =>
                                $data['content'] ?? null,

                            'video_url' =>
                                $data['video_url'] ?? null,

                            'sort_order' =>
                                $data['sort_order'] ?? 0,

                            'is_published' =>
                                $data['is_published'] ?? false,

                        ]);

                        /*
                        |--------------------------------------------------------------------------
                        | Save Attachments
                        |--------------------------------------------------------------------------
                        */

                        if (!empty($data['attachments'])) {

                            foreach (
                                $data['attachments']
                                as $index => $file
                            ) {

                                /*
                                |--------------------------------------------------------------------------
                                | File Path
                                |--------------------------------------------------------------------------
                                */

                                $path = is_string($file)

                                    ? $file

                                    : $file->store(
                                        'lessons',
                                        'public'
                                    );

                                /*
                                |--------------------------------------------------------------------------
                                | Create Attachment Record
                                |--------------------------------------------------------------------------
                                */

                                $lesson->attachments()->create([

                                    'original_name' =>
                                        basename($path),

                                    'file_path' =>
                                        $path,

                                    'mime_type' =>
                                        Storage::disk('public')
                                            ->mimeType($path),

                                    'file_size' =>
                                        Storage::disk('public')
                                            ->size($path),

                                    'sort_order' =>
                                        $index,

                                ]);
                            }
                        }
                    }),
            ])

            /*
            |--------------------------------------------------------------------------
            | Record Actions
            |--------------------------------------------------------------------------
            */

            ->recordActions([

                /*
                |--------------------------------------------------------------------------
                | View
                |--------------------------------------------------------------------------
                */

                ViewAction::make()

                    ->url(
                        fn (Lesson $record) =>
                            LessonResource::getUrl(
                                'view',
                                [
                                    'record' => $record,
                                ]
                            )
                    ),

                /*
                |--------------------------------------------------------------------------
                | Edit
                |--------------------------------------------------------------------------
                */

                EditAction::make()

                    ->url(
                        fn (Lesson $record) =>
                            LessonResource::getUrl(
                                'edit',
                                [
                                    'record' => $record,
                                ]
                            )
                    ),

                /*
                |--------------------------------------------------------------------------
                | Delete
                |--------------------------------------------------------------------------
                */

                DeleteAction::make(),

            ]);
    }
}
