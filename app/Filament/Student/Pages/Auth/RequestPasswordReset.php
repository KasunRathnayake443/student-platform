<?php

namespace App\Filament\Student\Pages\Auth;

use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;

class RequestPasswordReset extends BaseRequestPasswordReset
{
    protected string $view = 'filament.student.pages.auth.request-password-reset';
}
