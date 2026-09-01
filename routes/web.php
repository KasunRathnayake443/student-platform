<?php

use App\Http\Controllers\QuizImportTemplateController;
use App\Http\Controllers\QuizQuestionMediaController;
use App\Http\Controllers\SchoolLogoController;
use App\Http\Controllers\StudentProfilePhotoController;
use App\Http\Controllers\TeacherProfilePhotoController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::view('school-admin/login', 'school-admin.login-placeholder')->name('school-admin.login');

Route::middleware(['auth'])->group(function () {
    Route::get('/teachers/{teacher}/profile-photo', TeacherProfilePhotoController::class)
        ->name('teachers.profile-photo');

    Route::get('/students/{student}/profile-photo', StudentProfilePhotoController::class)
        ->name('students.profile-photo');

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
