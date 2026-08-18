<?php

namespace App\Filament\Resources\Quizzes\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuizInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | Quiz Information
                |--------------------------------------------------------------------------
                */

                Section::make('Quiz Information')
                    ->schema([

                        TextEntry::make('title')
                            ->label('Quiz Title'),

                        TextEntry::make('total_points')
                            ->label('Total Points')
                            ->suffix(' points'),

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
                | Rules & Timing
                |--------------------------------------------------------------------------
                */

                Section::make('Timing & Rules')
                    ->schema([

                        TextEntry::make('time_limit_minutes')
                            ->label('Time Limit')
                            ->formatStateUsing(fn ($state) => $state ? "{$state} minutes" : 'Unlimited time'),

                        TextEntry::make('max_attempts')
                            ->label('Max Attempts')
                            ->formatStateUsing(fn ($state) => $state ? "{$state} attempt(s)" : 'Unlimited attempts'),

                        TextEntry::make('passing_percentage')
                            ->label('Passing Percentage')
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
                | Availability
                |--------------------------------------------------------------------------
                */

                Section::make('Availability')
                    ->schema([

                        TextEntry::make('availability_type')
                            ->label('Availability')
                            ->formatStateUsing(
                                fn ($state) => match ($state) {
                                    'immediate' => 'Available Immediately',
                                    'scheduled' => 'Scheduled',
                                    default => ucfirst((string) $state),
                                }
                            ),

                        IconEntry::make('is_published')
                            ->label('Published')
                            ->boolean(),

                        TextEntry::make('start_at')
                            ->label('Start Date & Time')
                            ->dateTime()
                            ->placeholder('None (Immediate)'),

                        TextEntry::make('end_at')
                            ->label('End Date & Time')
                            ->dateTime()
                            ->placeholder('No end date'),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | Questions List
                |--------------------------------------------------------------------------
                */

                Section::make('Questions & Answer Keys')
                    ->schema([

                        RepeatableEntry::make('questions')
                            ->label('')
                            ->schema([

                                TextEntry::make('question_text')
                                    ->label('Question')
                                    ->weight('bold'),

                                TextEntry::make('points')
                                    ->label('Points')
                                    ->suffix(' pts'),

                                TextEntry::make('explanation')
                                    ->label('Explanation')
                                    ->placeholder('No explanation provided.')
                                    ->columnSpanFull(),

                                RepeatableEntry::make('options')
                                    ->label('Options')
                                    ->schema([

                                        TextEntry::make('option_text')
                                            ->label('Option'),

                                        IconEntry::make('is_correct')
                                            ->label('Correct')
                                            ->boolean(),

                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),

                            ])
                            ->columns(2)
                            ->columnSpanFull(),

                    ])
                    ->columnSpanFull(),

            ]);
    }
}
