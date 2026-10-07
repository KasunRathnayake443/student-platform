<?php

namespace App\Filament\SchoolAdmin\Pages\Auth;

use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;

class RequestPasswordReset extends BaseRequestPasswordReset
{
    protected string $view = 'filament.school-admin.pages.auth.request-password-reset';
}
