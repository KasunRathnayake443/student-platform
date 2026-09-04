<?php

namespace App\Enums;

enum NotificationRecipientType: string
{
    case Student = 'student';
    case Teacher = 'teacher';
    case SchoolAdmin = 'school_admin';
    case SuperAdmin = 'super_admin';

    public static function options(): array
    {
        return [
            self::Student->value => 'Students',
            self::Teacher->value => 'Teachers',
            self::SchoolAdmin->value => 'School Admins',
            self::SuperAdmin->value => 'Super Admins',
        ];
    }
}
