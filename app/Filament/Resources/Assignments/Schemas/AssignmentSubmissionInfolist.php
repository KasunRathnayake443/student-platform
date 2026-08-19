<?php

namespace App\Filament\Resources\Assignments\Schemas;

use App\Models\AssignmentSubmission;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class AssignmentSubmissionInfolist
{
    public static function configure(
        Schema $schema,
        AssignmentSubmission $record
    ): Schema {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | Student
                |--------------------------------------------------------------------------
                */

                Section::make('Student')
                    ->schema([

                        TextEntry::make('student.user.name')
                            ->label('Student Name')
                            ->placeholder('Unknown student'),

                        TextEntry::make('student.admission_no')
                            ->label('Admission Number')
                            ->placeholder('Not available'),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | Submission
                |--------------------------------------------------------------------------
                */

                Section::make('Submission')
                    ->schema([

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(
                                fn ($state) => match ($state) {
                                    'draft' => 'Draft',
                                    'submitted' => 'Submitted',
                                    'graded' => 'Graded',
                                    'returned' => 'Returned',
                                    default => ucfirst((string) $state),
                                }
                            ),

                        TextEntry::make('submitted_at')
                            ->label('Submitted At')
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('Not submitted'),

                        TextEntry::make('is_late')
                            ->label('Submission Timing')
                            ->badge()
                            ->formatStateUsing(
                                fn ($state) => $state
                                        ? 'Late Submission'
                                        : 'Submitted On Time'
                            )
                            ->color(
                                fn ($state) => $state
                                        ? 'danger'
                                        : 'success'
                            ),

                        TextEntry::make('content')
                            ->label('Student Answer')
                            ->html()
                            ->placeholder('No text answer submitted.')
                            ->columnSpanFull(),

                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | Submitted Files
                |--------------------------------------------------------------------------
                */

                Section::make('Submitted Files')
                    ->description(
                        'Files submitted by the student with this assignment.'
                    )
                    ->schema([

                        TextEntry::make('submission_files')
                            ->label('Files')
                            ->state(function () use ($record) {

                                $attachments = $record
                                    ->attachments()
                                    ->orderBy('sort_order')
                                    ->get();

                                if ($attachments->isEmpty()) {
                                    return 'No files submitted.';
                                }

                                return $attachments
                                    ->map(function ($attachment) {

                                        $size = 'Unknown size';

                                        if (
                                            ! is_null(
                                                $attachment->file_size
                                            )
                                        ) {
                                            $bytes = (int) $attachment->file_size;

                                            if ($bytes >= 1048576) {
                                                $size =
                                                    number_format(
                                                        $bytes / 1048576,
                                                        2
                                                    )
                                                    .' MB';
                                            } elseif ($bytes >= 1024) {
                                                $size =
                                                    number_format(
                                                        $bytes / 1024,
                                                        1
                                                    )
                                                    .' KB';
                                            } else {
                                                $size =
                                                    $bytes
                                                    .' bytes';
                                            }
                                        }

                                        $url = Storage::disk('public')
                                            ->url(
                                                $attachment->file_path
                                            );

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
                                                        .e(
                                                            $attachment->original_name
                                                        )
                                                        .'
                                                    </div>

                                                    <div style="
                                                        font-size:12px;
                                                        color:#6b7280;
                                                        margin-top:3px;
                                                    ">
                                                        '
                                                        .e($size)
                                                        .'
                                                    </div>
                                                </div>

                                                <a
                                                    href="'
                                                    .e($url)
                                                    .'"
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

                /*
                |--------------------------------------------------------------------------
                | Result
                |--------------------------------------------------------------------------
                */

                Section::make('Result')
                    ->schema([

                        TextEntry::make('score')
                            ->label('Score')
                            ->state(function () use ($record) {

                                if ($record->score === null) {
                                    return 'Not graded';
                                }

                                return $record->score
                                    .' / '
                                    .$record->assignment->max_score;
                            }),

                        TextEntry::make('percentage')
                            ->label('Percentage')
                            ->state(function () use ($record) {

                                $percentage = $record->percentage();

                                if ($percentage === null) {
                                    return 'Not graded';
                                }

                                return $percentage.'%';
                            }),

                        TextEntry::make('grader.user.name')
                            ->label('Graded By')
                            ->placeholder('Not graded'),

                        TextEntry::make('graded_at')
                            ->label('Graded At')
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('Not graded'),

                        TextEntry::make('feedback')
                            ->label('Teacher Feedback')
                            ->html()
                            ->placeholder('No feedback provided.')
                            ->columnSpanFull(),

                    ])
                    ->columns(3)
                    ->columnSpanFull(),

            ]);
    }
}
