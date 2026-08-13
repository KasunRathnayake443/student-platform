<?php

namespace App\Filament\Resources\Assignments\Schemas;

use Illuminate\Support\Facades\Storage;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AssignmentInfolist
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
                    ->schema([

                        TextEntry::make('title')
                            ->label('Assignment Title'),

                        TextEntry::make('description')
                            ->label('Description')
                            ->placeholder('No description provided.')
                            ->columnSpanFull(),

                        TextEntry::make('instructions')
                            ->label('Instructions')
                            ->html()
                            ->placeholder('No instructions provided.')
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
                    ->schema([

                        TextEntry::make('learningClass.name')
                            ->label('Learning Class')
                            ->placeholder('Not specified'),

                        TextEntry::make('teacher.user.name')
                            ->label('Responsible Teacher')
                            ->placeholder('Not specified'),

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

                        TextEntry::make('max_score')
                            ->label('Maximum Score')
                            ->suffix(' marks'),

                    ])
                    ->columnSpanFull(),


                /*
                |--------------------------------------------------------------------------
                | Availability
                |--------------------------------------------------------------------------
                */

                Section::make('Availability')
                    ->schema([

                        TextEntry::make('availability_type')
                            ->label('Availability')
                            ->formatStateUsing(
                                fn ($state) =>
                                    match ($state) {
                                        'immediate' => 'Available Immediately',
                                        'scheduled' => 'Scheduled',
                                        default => ucfirst((string) $state),
                                    }
                            ),

                        TextEntry::make('start_at')
                            ->label('Start Date & Time')
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('Immediately'),

                        TextEntry::make('end_at')
                            ->label('End Date & Time')
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('No deadline'),

                    ])
                    ->columns(3)
                    ->columnSpanFull(),


                /*
                |--------------------------------------------------------------------------
                | Late Submissions
                |--------------------------------------------------------------------------
                */

                Section::make('Late Submissions')
                    ->schema([

                        TextEntry::make('allow_late_submissions')
                            ->label('Late Submissions')
                            ->formatStateUsing(
                                fn ($state) =>
                                    $state
                                        ? 'Allowed'
                                        : 'Not Allowed'
                            ),

                        TextEntry::make('late_submission_period')
                            ->label('Late Submission Period')
                            ->state(function ($record) {

                                if (
                                    ! $record->allow_late_submissions ||
                                    ! $record->late_submission_value ||
                                    ! $record->late_submission_unit
                                ) {
                                    return 'Not applicable';
                                }

                                return $record->late_submission_value
                                    . ' '
                                    . $record->late_submission_unit;
                            }),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),


                /*
                |--------------------------------------------------------------------------
                | Student Submission Types
                |--------------------------------------------------------------------------
                */

                Section::make('Student Submission Types')
                    ->schema([

                        TextEntry::make('allowed_submission_types')
                            ->label('Allowed Types')
                            ->state(function ($record) {

                                $types = $record->allowed_submission_types;

                                if (! is_array($types) || empty($types)) {
                                    return 'None specified';
                                }

                                $labels = [

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
                                ];

                                return collect($types)
                                    ->map(
                                        fn ($type) =>
                                            $labels[$type] ?? $type
                                    )
                                    ->implode(', ');
                            })
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

                        TextEntry::make('attachments')
                            ->label('Files')
                            ->state(function ($record) {

                                $attachments = $record
                                    ->attachments()
                                    ->orderBy('sort_order')
                                    ->get();

                                if ($attachments->isEmpty()) {
                                    return 'No attachments available.';
                                }

                                return $attachments
                                    ->map(function ($attachment) {

                                        $url = Storage::disk('public')
                                            ->url(
                                                $attachment->file_path
                                            );

                                        $size = 'Unknown size';

                                        if (
                                            ! is_null(
                                                $attachment->file_size
                                            )
                                        ) {

                                            $bytes =
                                                (int) $attachment->file_size;

                                            if ($bytes >= 1048576) {

                                                $size =
                                                    number_format(
                                                        $bytes / 1048576,
                                                        2
                                                    )
                                                    . ' MB';

                                            } elseif ($bytes >= 1024) {

                                                $size =
                                                    number_format(
                                                        $bytes / 1024,
                                                        1
                                                    )
                                                    . ' KB';

                                            } else {

                                                $size =
                                                    $bytes . ' bytes';
                                            }
                                        }

                                        return
                                            '<div style="
                                                display:flex;
                                                align-items:center;
                                                justify-content:space-between;
                                                gap:12px;
                                                padding:12px 14px;
                                                margin-bottom:8px;
                                                border:1px solid #e5e7eb;
                                                border-radius:8px;
                                                background:#fafafa;
                                            ">
                                                <div>
                                                    <div style="
                                                        font-weight:600;
                                                        color:#111827;
                                                    ">
                                                        '
                                                        . e(
                                                            $attachment->original_name
                                                        )
                                                        . '
                                                    </div>

                                                    <div style="
                                                        font-size:12px;
                                                        color:#6b7280;
                                                        margin-top:3px;
                                                    ">
                                                        '
                                                        . e($size)
                                                        . '
                                                    </div>
                                                </div>

                                                <a
                                                    href="'
                                                    . e($url)
                                                    . '"
                                                    download
                                                    style="
                                                        display:inline-block;
                                                        padding:7px 12px;
                                                        border-radius:6px;
                                                        background:#111827;
                                                        color:white;
                                                        text-decoration:none;
                                                        font-size:13px;
                                                        font-weight:600;
                                                    "
                                                >
                                                    Download
                                                </a>
                                            </div>';

                                    })
                                    ->implode('');

                            })
                            ->html()
                            ->columnSpanFull(),

                    ])
                    ->columnSpanFull(),

            ]);
    }
}