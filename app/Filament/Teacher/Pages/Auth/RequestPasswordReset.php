<?php

namespace App\Filament\Teacher\Pages\Auth;

use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;

class RequestPasswordReset extends BaseRequestPasswordReset
{
    protected string $view = 'filament.teacher.pages.auth.request-password-reset';
}