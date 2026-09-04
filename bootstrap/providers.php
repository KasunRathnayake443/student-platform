<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\SchoolAdminPanelProvider;
use App\Providers\Filament\StudentPanelProvider;
use App\Providers\Filament\TeacherPanelProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\NotificationBellServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    SchoolAdminPanelProvider::class,
    StudentPanelProvider::class,
    TeacherPanelProvider::class,
    FortifyServiceProvider::class,
    NotificationBellServiceProvider::class,
];
