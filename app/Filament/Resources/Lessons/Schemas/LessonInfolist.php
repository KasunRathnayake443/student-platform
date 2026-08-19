<?php

namespace App\Filament\Resources\Lessons\Schemas;

use App\Models\Lesson;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LessonInfolist
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

                Section::make('Lesson Information')
                    ->schema([

                        TextEntry::make('title')
                            ->label('Lesson Title')
                            ->weight('bold'),

                        TextEntry::make('teacher.user.name')
                            ->label('Teacher'),

                        TextEntry::make('learningClass.name')
                            ->label('Learning Class'),

                        TextEntry::make('sort_order')
                            ->label('Display Order'),

                        TextEntry::make('is_published')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(
                                fn ($state) => $state
                                        ? 'Published'
                                        : 'Draft'
                            ),

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime(),

                    ])
                    ->columns(2),

                /*
                |--------------------------------------------------------------------------
                | Description
                |--------------------------------------------------------------------------
                */

                Section::make('Description')
                    ->schema([

                        TextEntry::make('description')
                            ->hiddenLabel()
                            ->placeholder('No description provided.')
                            ->columnSpanFull(),

                    ])
                    ->collapsible(),

                /*
                |--------------------------------------------------------------------------
                | Lesson Content
                |--------------------------------------------------------------------------
                */

                Section::make('Lesson Content')
                    ->schema([

                        TextEntry::make('content')
                            ->hiddenLabel()
                            ->html()
                            ->placeholder('No lesson content provided.')
                            ->columnSpanFull(),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | Video
                |--------------------------------------------------------------------------
                */

                Section::make('Video')
                    ->schema([

                        TextEntry::make('video_url')
                            ->label('Video URL')
                            ->url(
                                fn ($state) => filled($state)
                                        ? $state
                                        : null
                            )
                            ->openUrlInNewTab()
                            ->placeholder('No video attached.')
                            ->columnSpanFull(),

                    ])
                    ->visible(
                        fn (Lesson $record) => filled($record->video_url)
                    ),

                /*
                |--------------------------------------------------------------------------
                | Attachments
                |--------------------------------------------------------------------------
                */

                Section::make('Lesson Attachments')
                    ->schema([

                        RepeatableEntry::make('attachments')

                            ->hiddenLabel()

                            ->schema([

                                TextEntry::make('original_name')
                                    ->label('File'),

                                TextEntry::make('mime_type')
                                    ->label('Type')
                                    ->placeholder('Unknown'),

                                TextEntry::make('file_size')
                                    ->label('Size')
                                    ->formatStateUsing(
                                        function ($state) {

                                            if (
                                                is_null($state)
                                            ) {
                                                return 'Unknown size';
                                            }

                                            $bytes = (int) $state;

                                            if (
                                                $bytes >= 1048576
                                            ) {
                                                return number_format(
                                                    $bytes / 1048576,
                                                    2
                                                ).' MB';
                                            }

                                            if (
                                                $bytes >= 1024
                                            ) {
                                                return number_format(
                                                    $bytes / 1024,
                                                    1
                                                ).' KB';
                                            }

                                            return $bytes.' bytes';
                                        }
                                    ),

                                TextEntry::make('file_path')
                                    ->label('Download')
                                    ->formatStateUsing(
                                        fn () => 'Open File'
                                    )
                                    ->url(
                                        function ($state) {

                                            if (
                                                blank($state)
                                            ) {
                                                return null;
                                            }

                                            return asset(
                                                'storage/'.ltrim(
                                                    $state,
                                                    '/'
                                                )
                                            );
                                        }
                                    )
                                    ->openUrlInNewTab()
                                    ->icon('heroicon-o-arrow-down-tray')
                                    ->color('primary'),

                            ])

                            ->columns(4)

                            ->contained(false),

                    ])
                    ->collapsible(),

            ]);
    }
}
