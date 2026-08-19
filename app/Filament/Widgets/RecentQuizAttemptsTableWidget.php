<?php

namespace App\Filament\Widgets;

use App\Models\QuizAttempt;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentQuizAttemptsTableWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent Quiz & Assessment Activity')
            ->description('Real-time tracking of student exam attempts and evaluations across schools')
            ->query(
                QuizAttempt::query()
                    ->with(['quiz.learningClass', 'student.user'])
                    ->latest()
            )
            ->columns([
                TextColumn::make('student.user.name')
                    ->label('Student')
                    ->searchable()
                    ->weight('semibold')
                    ->default('N/A'),

                TextColumn::make('quiz.title')
                    ->label('Quiz Title')
                    ->searchable()
                    ->limit(30)
                    ->default('Untitled Quiz'),

                TextColumn::make('quiz.learningClass.name')
                    ->label('Class')
                    ->badge()
                    ->color('gray')
                    ->default('-'),

                TextColumn::make('score')
                    ->label('Score')
                    ->formatStateUsing(function (QuizAttempt $record): string {
                        $points = $record->quiz?->total_points;

                        return ($record->score !== null ? $record->score : '-').($points ? " / {$points}" : '');
                    }),

                TextColumn::make('percentage')
                    ->label('Percentage')
                    ->formatStateUsing(fn ($state) => $state !== null ? "{$state}%" : '-')
                    ->badge()
                    ->color(fn (QuizAttempt $record): string => match (true) {
                        $record->percentage >= 75 => 'success',
                        $record->percentage >= 50 => 'warning',
                        $record->percentage !== null => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'submitted' => 'Submitted',
                        'in_progress' => 'In Progress',
                        'time_expired' => 'Time Expired',
                        'abandoned' => 'Abandoned',
                        default => $state ? ucfirst($state) : 'Pending',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'submitted' => 'success',
                        'in_progress' => 'info',
                        'time_expired' => 'danger',
                        default => 'gray',
                    }),

                IconColumn::make('is_passed')
                    ->label('Passed')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Attempted')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('view_quiz')
                    ->label('View Quiz')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (QuizAttempt $record): ?string => $record->quiz_id ? url("/admin/quizzes/{$record->quiz_id}") : null),
            ])
            ->paginated([5, 10, 20])
            ->defaultPaginationPageOption(5);
    }
}
