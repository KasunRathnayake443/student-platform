<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\StudentPanelProvider;
use App\Providers\Filament\TeacherPanelProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    StudentPanelProvider::class,
    TeacherPanelProvider::class,
    FortifyServiceProvider::class,
];
