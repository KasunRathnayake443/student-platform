<?php

namespace App\Filament\Parent\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

class ParentLogin extends BaseLogin
{
    protected string $view = 'filament.parent.pages.auth.login';
}
