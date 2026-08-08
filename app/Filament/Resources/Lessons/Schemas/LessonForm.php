<?php

namespace App\Filament\Resources\Lessons\Schemas;

use App\Models\Teacher;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class LessonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | Lesson Information
                |--------------------------------------------------------------------------
                */

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
                            ->mapWithKeys(
                                function (Teacher $teacher) {

                                    return [
                                        $teacher->id =>
                                            $teacher->user->name
                                            . ' - '
                                            . $teacher->employee_no,
                                    ];
                                }
                            );
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

                /*
                |--------------------------------------------------------------------------
                | Existing Attachments
                |--------------------------------------------------------------------------
                */

                Repeater::make('existing_attachments')
                    ->label('Existing Attachments')
                    ->default([])
                    ->schema([

                        Hidden::make('id'),

                        Hidden::make('file_path'),

                        Placeholder::make('file_name')
                            ->label('File')
                            ->content(function ($get) {

                                $name = $get('original_name');

                                return $name ?: 'Attachment';
                            }),

                        Placeholder::make('file_size')
                            ->label('Size')
                            ->content(function ($get) {

                                $bytes = $get('attachment_size');

                                if (! $bytes) {
                                    return 'Unknown size';
                                }

                                $bytes = (int) $bytes;

                                if ($bytes >= 1073741824) {

                                    return number_format(
                                        $bytes / 1073741824,
                                        2
                                    ) . ' GB';
                                }

                                if ($bytes >= 1048576) {

                                    return number_format(
                                        $bytes / 1048576,
                                        2
                                    ) . ' MB';
                                }

                                if ($bytes >= 1024) {

                                    return number_format(
                                        $bytes / 1024,
                                        1
                                    ) . ' KB';
                                }

                                return $bytes . ' bytes';
                            }),

                        Placeholder::make('download')
                            ->label('Download')
                            ->content(function ($get) {

                                $path = $get('file_path');

                                if (! $path) {
                                    return 'Unavailable';
                                }

                                if (
                                    ! Storage::disk('public')
                                        ->exists($path)
                                ) {
                                    return 'File missing';
                                }

                                $url = Storage::disk('public')
                                    ->url($path);

                                $name = $get('original_name')
                                    ?: basename($path);

                                return new \Illuminate\Support\HtmlString(
                                    '<a href="' . e($url) . '" '
                                    . 'download="' . e($name) . '" '
                                    . 'target="_blank" '
                                    . 'class="inline-flex items-center gap-1 text-primary-600 hover:underline">'
                                    . 'Download'
                                    . '</a>'
                                );
                            }),

                        Placeholder::make('view')
                            ->label('View')
                            ->content(function ($get) {

                                $path = $get('file_path');

                                if (! $path) {
                                    return 'Unavailable';
                                }

                                if (
                                    ! Storage::disk('public')
                                        ->exists($path)
                                ) {
                                    return 'File missing';
                                }

                                $url = Storage::disk('public')
                                    ->url($path);

                                return new \Illuminate\Support\HtmlString(
                                    '<a href="' . e($url) . '" '
                                    . 'target="_blank" '
                                    . 'class="inline-flex items-center gap-1 text-primary-600 hover:underline">'
                                    . 'Open'
                                    . '</a>'
                                );
                            }),

                    ])
                    ->columns(4)
                    ->addable(false)
                    ->reorderable(false)
                    ->deletable(true)
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | New Attachments
                |--------------------------------------------------------------------------
                */

                FileUpload::make('new_attachments')
                    ->label('Add New Attachments')
                    ->helperText(
                        'Upload additional files. Existing attachments can be removed above.'
                    )
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