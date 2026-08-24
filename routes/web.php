<?php

use App\Http\Controllers\SchoolLogoController;
use App\Http\Controllers\TeacherProfilePhotoController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/teachers/{teacher}/profile-photo', TeacherProfilePhotoController::class)
        ->name('teachers.profile-photo');

    Route::get('/schools/{school}/logo', SchoolLogoController::class)
        ->name('schools.logo');
});

// TEMPORARY dev-only auto-login for headless diagnostics (remove after use)
if (config('app.debug')) {
    Route::get('/dev-login-teacher1', function () {
        $user = App\Models\User::where('email', 'teacher1@example.com')->firstOrFail();
        auth()->login($user);

        return redirect('/teacher/my-profile');
    });

    Route::get('/dev-login-admin1', function () {
        $user = App\Models\User::where('email', 'admin1@example.com')->firstOrFail();
        auth()->login($user);

        return redirect('/admin/teachers/1/edit');
    });
}

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
