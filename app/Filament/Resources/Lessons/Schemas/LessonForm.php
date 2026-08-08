<?php

namespace App\Filament\Resources\Lessons\Schemas;

use App\Models\Teacher;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LessonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('title')
                    ->label('Lesson Title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Select::make('learning_class_id')
                    ->label('Learning Class')
                    ->relationship(
                        'learningClass',
                        'name'
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('teacher_id')
                    ->label('Teacher')
                    ->options(function () {
                        return Teacher::query()
                            ->with('user')
                            ->get()
                            ->mapWithKeys(function (Teacher $teacher) {
                                return [
                                    $teacher->id =>
                                        $teacher->user->name
                                        . ' - '
                                        . $teacher->employee_no,
                                ];
                            });
                    })
                    ->searchable()
                    ->preload()
                    ->required(),

                Textarea::make('description')
                    ->label('Short Description')
                    ->rows(3)
                    ->maxLength(1000)
                    ->columnSpanFull(),

                RichEditor::make('content')
                    ->label('Lesson Content')
                    ->columnSpanFull(),

                TextInput::make('video_url')
                    ->label('Video URL')
                    ->url()
                    ->maxLength(2048)
                    ->columnSpanFull(),

                TextInput::make('sort_order')
                    ->label('Display Order')
                    ->numeric()
                    ->integer()
                    ->default(0)
                    ->minValue(0),

                Toggle::make('is_published')
                    ->label('Published')
                    ->default(false),

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
            ]);
    }
}
