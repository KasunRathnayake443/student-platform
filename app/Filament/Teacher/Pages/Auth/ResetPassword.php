<?php

namespace App\Filament\Teacher\Pages\Auth;

use Filament\Auth\Pages\PasswordReset\ResetPassword as BaseResetPassword;

class ResetPassword extends BaseResetPassword
{
    protected string $view = 'filament.teacher.pages.auth.reset-password';
}