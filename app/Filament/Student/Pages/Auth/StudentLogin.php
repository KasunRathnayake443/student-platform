<?php

namespace App\Filament\Student\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

class StudentLogin extends BaseLogin
{
    protected static string $layout = 'filament-panels::components.layout.base';

    protected string $view = 'filament.student.pages.auth.login';
}
