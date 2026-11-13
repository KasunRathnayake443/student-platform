<?php

namespace App\Filament\Parent\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Support\Enums\Width;

class ParentLogin extends BaseLogin
{
    protected string $view = 'filament.parent.pages.auth.login';

    public function getMaxWidth(): Width|string|null
    {
        return Width::SevenExtraLarge;
    }
}
