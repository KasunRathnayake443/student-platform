<?php

namespace App\Filament\SchoolAdmin\Pages\Auth;

use Filament\Auth\Pages\PasswordReset\ResetPassword as BaseResetPassword;

class ResetPassword extends BaseResetPassword
{
    protected string $view = 'filament.school-admin.pages.auth.reset-password';
}
