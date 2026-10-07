<?php

namespace App\Filament\Student\Pages\Auth;

use Filament\Auth\Pages\PasswordReset\ResetPassword as BaseResetPassword;

class ResetPassword extends BaseResetPassword
{
    protected string $view = 'filament.student.pages.auth.reset-password';
}
