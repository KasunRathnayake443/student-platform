<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\PlatformLogoController;
use App\Http\Controllers\QuizImportTemplateController;
use App\Http\Controllers\QuizQuestionMediaController;
use App\Http\Controllers\SchoolAdminProfilePhotoController;
use App\Http\Controllers\SchoolLogoController;
use App\Http\Controllers\StudentProfilePhotoController;
use App\Http\Controllers\TeacherProfilePhotoController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

Route::get('/locale/{locale}', function (string $locale) {
    abort_unless(in_array($locale, SetLocale::LOCALES, true), 404);

    session(['locale' => $locale]);

    return back();
})->name('locale.switch');

Route::view('/', 'welcome')->name('home');

Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::get('/platform/logo', PlatformLogoController::class)
    ->name('platform.logo');

Route::middleware(['auth'])->group(function () {
    Route::get('/teachers/{teacher}/profile-photo', TeacherProfilePhotoController::class)
        ->name('teachers.profile-photo');

    Route::get('/students/{student}/profile-photo', StudentProfilePhotoController::class)
        ->name('students.profile-photo');

    Route::get('/school-admins/{schoolAdmin}/profile-photo', SchoolAdminProfilePhotoController::class)
        ->name('school-admins.profile-photo');

    Route::get('/schools/{school}/logo', SchoolLogoController::class)
        ->name('schools.logo');

    Route::get('/quiz-questions/{quizQuestion}/{type}', QuizQuestionMediaController::class)
        ->name('quiz-questions.media')
        ->whereIn('type', ['image', 'video']);

    Route::get('/quiz-questions/import/template', QuizImportTemplateController::class)
        ->name('quiz-questions.import.template');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
