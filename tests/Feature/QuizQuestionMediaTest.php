<?php

use App\Models\LearningClass;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function qmUser(string $email): User
{
    return User::where('email', $email)->firstOrFail();
}

function qmClass(): LearningClass
{
    return LearningClass::where('name', '10-B Science & Physics')->firstOrFail();
}

function qmQuizWithQuestion(): Quiz
{
    $class = qmClass();
    $teacher = qmUser('teacher1@example.com')->teacher;

    $quiz = Quiz::create([
        'learning_class_id' => $class->getKey(),
        'teacher_id' => $teacher->getKey(),
        'title' => 'Media Test Quiz',
        'passing_percentage' => 50,
        'max_attempts' => 1,
        'total_points' => 0,
        'availability_type' => 'immediate',
        'is_published' => true,
    ]);

    $quiz->teachers()->sync([$teacher->getKey()]);

    $quiz->questions()->create([
        'question_text' => 'What is shown in the image?',
        'points' => 2,
        'sort_order' => 1,
    ]);

    return $quiz;
}

test('quiz question has question_image and question_video fillable attributes', function () {
    $this->seed();

    $quiz = qmQuizWithQuestion();
    $question = $quiz->questions()->first();

    expect($question)->not->toBeNull()
        ->and($question->question_image)->toBeNull()
        ->and($question->question_video)->toBeNull();
});

test('quiz question media columns can be set and persisted', function () {
    $this->seed();

    $quiz = qmQuizWithQuestion();
    $question = $quiz->questions()->first();

    $question->update([
        'question_image' => 'quiz_questions/images/test-image.jpg',
        'question_video' => 'quiz_questions/videos/test-video.mp4',
    ]);

    $fresh = $question->fresh();

    expect($fresh->question_image)->toBe('quiz_questions/images/test-image.jpg')
        ->and($fresh->question_video)->toBe('quiz_questions/videos/test-video.mp4');
});

test('quiz question image url accessor returns route when disk is local', function () {
    $this->seed();

    $quiz = qmQuizWithQuestion();
    $question = $quiz->questions()->first();

    $question->update(['question_image' => 'quiz_questions/images/photo.jpg']);

    $url = $question->question_image_url;

    expect($url)->not->toBeNull()
        ->and($url)->toContain('/quiz-questions/'.$question->getKey().'/image');
});

test('quiz question video url accessor returns route when disk is local', function () {
    $this->seed();

    $quiz = qmQuizWithQuestion();
    $question = $quiz->questions()->first();

    $question->update(['question_video' => 'quiz_questions/videos/clip.mp4']);

    $url = $question->question_video_url;

    expect($url)->not->toBeNull()
        ->and($url)->toContain('/quiz-questions/'.$question->getKey().'/video');
});

test('quiz question image url accessor returns null when no image set', function () {
    $this->seed();

    $quiz = qmQuizWithQuestion();
    $question = $quiz->questions()->first();

    expect($question->question_image_url)->toBeNull();
});

test('quiz question video url accessor returns null when no video set', function () {
    $this->seed();

    $quiz = qmQuizWithQuestion();
    $question = $quiz->questions()->first();

    expect($question->question_video_url)->toBeNull();
});

test('quiz question media route returns 404 when no media set', function () {
    $this->seed();
    $this->actingAs(qmUser('teacher1@example.com'));

    $quiz = qmQuizWithQuestion();
    $question = $quiz->questions()->first();

    $this->get("/quiz-questions/{$question->getKey()}/image")->assertNotFound();
    $this->get("/quiz-questions/{$question->getKey()}/video")->assertNotFound();
});

test('quiz question media route returns 404 for invalid type', function () {
    $this->seed();
    $this->actingAs(qmUser('teacher1@example.com'));

    $quiz = qmQuizWithQuestion();
    $question = $quiz->questions()->first();

    $this->get("/quiz-questions/{$question->getKey()}/audio")->assertNotFound();
});

test('quiz question media route requires authentication', function () {
    $this->seed();

    $quiz = qmQuizWithQuestion();
    $question = $quiz->questions()->first();

    $this->get("/quiz-questions/{$question->getKey()}/image")->assertRedirect('/login');
});

test('quiz question media route serves file when it exists on disk', function () {
    $this->seed();
    $this->actingAs(qmUser('teacher1@example.com'));

    Storage::fake(config('filament.default_filesystem_disk'));

    $fakeImage = UploadedFile::fake()->image('question.jpg', 200, 200)->size(100);
    $path = $fakeImage->store('quiz_questions/images', config('filament.default_filesystem_disk'));

    $quiz = qmQuizWithQuestion();
    $question = $quiz->questions()->first();
    $question->update(['question_image' => $path]);

    $response = $this->get("/quiz-questions/{$question->getKey()}/image");

    $response->assertOk();
});

test('quiz question has both media columns in migration', function () {
    $this->seed();

    $quiz = qmQuizWithQuestion();
    $question = $quiz->questions()->first();

    $attributes = $question->getAttributes();

    expect(array_key_exists('question_image', $attributes))->toBeTrue()
        ->and(array_key_exists('question_video', $attributes))->toBeTrue();
});

test('quiz question media persists through save and reload cycle', function () {
    $this->seed();

    $quiz = qmQuizWithQuestion();
    $question = $quiz->questions()->first();

    $question->question_image = 'quiz_questions/images/diagram.png';
    $question->question_video = 'quiz_questions/videos/lecture.mp4';
    $question->save();

    $reloaded = QuizQuestion::find($question->getKey());

    expect($reloaded->question_image)->toBe('quiz_questions/images/diagram.png')
        ->and($reloaded->question_video)->toBe('quiz_questions/videos/lecture.mp4');
});
