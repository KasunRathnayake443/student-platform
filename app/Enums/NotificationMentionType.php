<?php

namespace App\Enums;

enum NotificationMentionType: string
{
    case Lesson = 'lesson';
    case Assignment = 'assignment';
    case Quiz = 'quiz';

    public static function options(): array
    {
        return [
            self::Lesson->value => 'Lesson',
            self::Assignment->value => 'Assignment',
            self::Quiz->value => 'Quiz',
        ];
    }
}
