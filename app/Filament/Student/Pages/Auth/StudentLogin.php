<?php

namespace App\Filament\Student\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Support\Enums\Width;

class StudentLogin extends BaseLogin
{
    protected string $view = 'filament.student.pages.auth.login';

    public function getMaxWidth(): Width | string | null
    {
        return Width::SevenExtraLarge;
    }
}
