<?php

namespace App\Filament\Resources\Quizzes\Schemas;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Teacher;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class QuizInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | 1. Quiz Overview & Metrics
                |--------------------------------------------------------------------------
                */

                Section::make('Quiz Information')
                    ->description('Overview of quiz details, assigned class, and responsible educators.')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([

                        TextEntry::make('title')
                            ->label('Quiz Title')
                            ->weight('bold')
                            ->size('lg'),

                        TextEntry::make('total_points')
                            ->label('Total Points')
                            ->badge()
                            ->color('primary')
                            ->suffix(' pts'),

                        TextEntry::make('learningClass.name')
                            ->label('Target Class')
                            ->badge()
                            ->color('info')
                            ->placeholder('Not assigned'),

                        TextEntry::make('assignee_names')
                            ->label('Assigned Teachers')
                            ->badge()
                            ->color('success')
                            ->state(function (Quiz $record): array {
                                $names = $record->teachers
                                    ->map(fn (Teacher $teacher) => $teacher->user->name);

                                if ($names->isEmpty() && $record->teacher) {
                                    return [$record->teacher->user->name];
                                }

                                return $names->values()->toArray();
                            }),

                        TextEntry::make('description')
                            ->label('Short Description')
                            ->placeholder('No description provided.')
                            ->columnSpanFull(),

                        TextEntry::make('instructions')
                            ->label('Instructions & Guidelines')
                            ->html()
                            ->placeholder('No instructions provided.')
                            ->columnSpanFull(),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | 2. Rules & Timing
                |--------------------------------------------------------------------------
                */

                Section::make('Timing & Grading Rules')
                    ->description('Configuration for timing, attempts, scoring, and accessibility.')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->schema([

                        TextEntry::make('time_limit_minutes')
                            ->label('Time Limit')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state ? "{$state} minutes" : 'Unlimited time'),

                        TextEntry::make('max_attempts')
                            ->label('Max Attempts')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state ? "{$state} attempt(s)" : 'Unlimited attempts'),

                        TextEntry::make('passing_percentage')
                            ->label('Passing Score')
                            ->badge()
                            ->color('success')
                            ->suffix('%'),

                        IconEntry::make('show_correct_answers_after_submission')
                            ->label('Show Correct Answers')
                            ->boolean(),

                        IconEntry::make('shuffle_questions')
                            ->label('Shuffle Questions')
                            ->boolean(),

                        IconEntry::make('shuffle_options')
                            ->label('Shuffle Options')
                            ->boolean(),

                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | 3. Availability
                |--------------------------------------------------------------------------
                */

                Section::make('Availability & Publishing')
                    ->description('Access window and publication status.')
                    ->icon('heroicon-o-clock')
                    ->schema([

                        TextEntry::make('availability_type')
                            ->label('Access Mode')
                            ->badge()
                            ->color(fn ($state) => $state === 'immediate' ? 'success' : 'warning')
                            ->formatStateUsing(
                                fn ($state) => match ($state) {
                                    'immediate' => 'Available Immediately',
                                    'scheduled' => 'Scheduled Window',
                                    default => ucfirst((string) $state),
                                }
                            ),

                        IconEntry::make('is_published')
                            ->label('Published')
                            ->boolean(),

                        TextEntry::make('start_at')
                            ->label('Start Date & Time')
                            ->dateTime()
                            ->placeholder('Available immediately'),

                        TextEntry::make('end_at')
                            ->label('End Date & Time (Deadline)')
                            ->dateTime()
                            ->placeholder('No end date / open indefinitely'),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | 4. Questions & Answer Keys
                |--------------------------------------------------------------------------
                */

                Section::make('Questions & Answer Keys')
                    ->description('Complete list of questions, media attachments, and marked answer options.')
                    ->icon('heroicon-o-list-bullet')
                    ->schema([

                        RepeatableEntry::make('questions')
                            ->label('')
                            ->schema([

                                TextEntry::make('question_text')
                                    ->label('Question Prompt')
                                    ->weight('bold')
                                    ->size('md')
                                    ->columnSpan(3),

                                TextEntry::make('points')
                                    ->label('Points')
                                    ->badge()
                                    ->color('primary')
                                    ->suffix(' marks')
                                    ->columnSpan(1),

                                ImageEntry::make('question_image_url')
                                    ->label('Question Image')
                                    ->hidden(fn ($state): bool => blank($state))
                                    ->imageHeight(220)
                                    ->checkFileExistence(false)
                                    ->columnSpan(2),

                                TextEntry::make('question_video_url')
                                    ->label('Question Video')
                                    ->hidden(fn ($state): bool => blank($state))
                                    ->formatStateUsing(function (?string $state, QuizQuestion $record): HtmlString {
                                        if (blank($state)) {
                                            return new HtmlString('');
                                        }

                                        return new HtmlString(
                                            '<div class="mt-2">'
                                            .'<video controls preload="metadata" class="rounded-lg max-h-60 w-auto border border-gray-200 dark:border-gray-700" src="'.e($state).'">'
                                            .'Your browser does not support the video tag.'
                                            .'</video>'
                                            .'</div>'
                                        );
                                    })
                                    ->columnSpan(2),

                                TextEntry::make('explanation')
                                    ->label('Explanation / Solution Feedback')
                                    ->placeholder('No explanation provided.')
                                    ->helperText('Displayed to students when reviewing results.')
                                    ->columnSpanFull(),

                                RepeatableEntry::make('options')
                                    ->label('Answer Choices')
                                    ->schema([

                                        TextEntry::make('option_text')
                                            ->label('Choice')
                                            ->columnSpan(3),

                                        IconEntry::make('is_correct')
                                            ->label('Correct Answer')
                                            ->boolean()
                                            ->columnSpan(1),

                                    ])
                                    ->columns(4)
                                    ->columnSpanFull(),

                            ])
                            ->columns(4)
                            ->columnSpanFull(),

                    ])
                    ->columnSpanFull(),

            ]);
    }
}
