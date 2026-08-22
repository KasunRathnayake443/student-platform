<?php

namespace App\Filament\Teacher\Resources\Lessons\Schemas;

use App\Models\LearningClass;
use App\Models\Lesson;
use App\Models\Teacher;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

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

                /*
                 * The lesson always stays in its class; teachers may only
                 * hand ownership to another teacher of the SAME class.
                 */
                Placeholder::make('learning_class_display')
                    ->label('Learning Class')
                    ->content(function (Get $get): string {
                        $class = LearningClass::find($get('learning_class_id'));

                        return $class instanceof LearningClass
                            ? $class->name
                            : '-';
                    }),

                Hidden::make('learning_class_id'),

                Select::make('teacher_id')
                    ->label('Teacher')
                    ->options(function () use ($schema): array {
                        $record = $schema->getRecord();

                        if (! $record instanceof Lesson) {
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
                            )
                            ->toArray();
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

                Repeater::make('existing_attachments')
                    ->label('Existing Attachments')
                    ->default([])
                    ->schema([
                        Hidden::make('id'),
                        Hidden::make('file_path'),
                        Placeholder::make('file_name')
                            ->label('File')
                            ->content(fn (Get $get) => $get('original_name') ?: 'Attachment'),
                        Placeholder::make('download')
                            ->label('Download')
                            ->content(function (Get $get): HtmlString|string {
                                $path = $get('file_path');

                                if (! $path || ! Storage::disk('public')->exists($path)) {
                                    return 'Unavailable';
                                }

                                $url = Storage::disk('public')->url($path);
                                $name = $get('original_name') ?: basename($path);

                                return new HtmlString(
                                    '<a href="'.e($url).'" '
                                    .'download="'.e($name).'" '
                                    .'target="_blank" '
                                    .'class="inline-flex items-center gap-1 text-primary-600 hover:underline">'
                                    .'Download'
                                    .'</a>'
                                );
                            }),
                        Placeholder::make('view')
                            ->label('View')
                            ->content(function (Get $get): HtmlString|string {
                                $path = $get('file_path');

                                if (! $path || ! Storage::disk('public')->exists($path)) {
                                    return 'Unavailable';
                                }

                                $url = Storage::disk('public')->url($path);

                                return new HtmlString(
                                    '<a href="'.e($url).'" '
                                    .'target="_blank" '
                                    .'class="inline-flex items-center gap-1 text-primary-600 hover:underline">'
                                    .'Open'
                                    .'</a>'
                                );
                            }),
                    ])
                    ->columns(3)
                    ->addable(false)
                    ->reorderable(false)
                    ->deletable(true)
                    ->columnSpanFull(),

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
