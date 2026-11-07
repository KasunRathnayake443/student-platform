<?php

use App\Filament\Teacher\Resources\Quizzes\Pages\EditQuiz as TeacherEditQuiz;
use App\Filament\Teacher\Resources\Quizzes\Pages\ViewQuiz as TeacherViewQuiz;
use App\Models\Quiz;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makePaginationTestQuiz(): Quiz
{
    $teacher = paginationTestTeacher()->teacher;

    $quiz = Quiz::create([
        'learning_class_id' => $teacher->classes()->firstOrFail()->getKey(),
        'teacher_id' => $teacher->getKey(),
        'title' => 'Pagination Test Quiz',
        'max_attempts' => 1,
        'passing_percentage' => 50,
        'total_points' => 25,
        'availability_type' => 'immediate',
        'end_at' => now()->addWeek(),
        'is_published' => true,
    ]);

    foreach (range(1, 25) as $i) {
        $number = str_pad((string) $i, 2, '0', STR_PAD_LEFT);

        $question = $quiz->questions()->create([
            'question_text' => "Pagination Question {$number}",
            'points' => 1,
            'sort_order' => $i,
        ]);

        $question->options()->createMany([
            ['option_text' => 'Correct', 'is_correct' => true, 'sort_order' => 1],
            ['option_text' => 'Wrong', 'is_correct' => false, 'sort_order' => 2],
        ]);
    }

    return $quiz;
}

function paginationTestTeacher(): User
{
    return User::where('email', 'teacher1@example.com')->firstOrFail();
}

test('edit quiz page renders only the current page of questions', function () {
    $this->seed();

    $quiz = makePaginationTestQuiz();

    Filament::setCurrentPanel('teacher');

    $component = Livewire::actingAs(paginationTestTeacher())
        ->test(TeacherEditQuiz::class, ['record' => $quiz->getKey()])
        ->assertSet('questionsPage', 1)
        ->assertSee('Pagination Question 01')
        ->assertSee('Pagination Question 10')
        ->assertDontSee('Pagination Question 11')
        ->assertSee('of')
        ->assertSee('25');

    $component
        ->call('gotoQuestionsPage', 2)
        ->assertSet('questionsPage', 2)
        ->assertSee('Pagination Question 11')
        ->assertSee('Pagination Question 20')
        ->assertDontSee('Pagination Question 01');

    $component
        ->call('gotoQuestionsPage', 3)
        ->assertSet('questionsPage', 3)
        ->assertSee('Pagination Question 21')
        ->assertSee('Pagination Question 25')
        ->assertDontSee('Pagination Question 20');
});

test('view quiz page renders only the current page of questions', function () {
    $this->seed();

    $quiz = makePaginationTestQuiz();

    Filament::setCurrentPanel('teacher');

    $component = Livewire::actingAs(paginationTestTeacher())
        ->test(TeacherViewQuiz::class, ['record' => $quiz->getKey()])
        ->assertSet('questionsPage', 1)
        ->assertSee('Pagination Question 01')
        ->assertDontSee('Pagination Question 11')
        ->assertSeeHtml('wire:click="gotoQuestionsPage(2)"')
        ->assertSeeHtml('wire:target="gotoQuestionsPage"')
        ->assertSeeHtml('wire:loading.flex')
        ->assertSeeHtml('quiz-questions-pagination__button')
        ->assertSeeHtml('quiz-questions-pagination__overlay')
        ->assertSeeHtml('.quiz-questions-pagination {');

    $component
        ->call('gotoQuestionsPage', 2)
        ->assertSet('questionsPage', 2)
        ->assertSee('Pagination Question 11')
        ->assertSee('Pagination Question 20')
        ->assertDontSee('Pagination Question 01');
});

test('adding a question jumps to the page where the new question is rendered', function () {
    $this->seed();

    $quiz = makePaginationTestQuiz();

    Filament::setCurrentPanel('teacher');

    Livewire::actingAs(paginationTestTeacher())
        ->test(TeacherEditQuiz::class, ['record' => $quiz->getKey()])
        ->assertSet('questionsPage', 1)
        ->callFormComponentAction('questions', 'add')
        ->assertSet('questionsPage', 3)
        ->assertSee('❓ New Question');
});

test('cloning a question jumps to the page where the clone is rendered', function () {
    $this->seed();

    $quiz = makePaginationTestQuiz();

    Filament::setCurrentPanel('teacher');

    $component = Livewire::actingAs(paginationTestTeacher())
        ->test(TeacherEditQuiz::class, ['record' => $quiz->getKey()])
        ->assertSet('questionsPage', 1);

    $questionKeys = array_keys($component->instance()->data['questions']);

    $component
        ->callFormComponentAction(
            ['questions', $questionKeys[0]],
            'clone',
            arguments: ['item' => $questionKeys[0]],
        )
        ->assertSet('questionsPage', 3);

    expect($quiz->fresh()->questions()->count())->toBe(25);
});

test('drag reordering a page keeps items in their global slots', function () {
    $this->seed();

    $quiz = makePaginationTestQuiz();

    Filament::setCurrentPanel('teacher');

    $component = Livewire::actingAs(paginationTestTeacher())
        ->test(TeacherEditQuiz::class, ['record' => $quiz->getKey()])
        ->call('gotoQuestionsPage', 2);

    $originalKeys = array_keys($component->instance()->data['questions']);
    $visibleKeys = array_slice($originalKeys, 10, 10);
    $reversedVisibleKeys = array_reverse($visibleKeys);

    $component->callFormComponentAction(
        'questions',
        'reorder',
        arguments: ['items' => $reversedVisibleKeys],
    );

    $reorderedKeys = array_keys($component->instance()->data['questions']);

    expect(array_slice($reorderedKeys, 0, 10))->toBe(array_slice($originalKeys, 0, 10))
        ->and(array_slice($reorderedKeys, 10, 10))->toBe($reversedVisibleKeys)
        ->and(array_slice($reorderedKeys, 20))->toBe(array_slice($originalKeys, 20));
});

test('a validation error on a hidden page moves the user to that page', function () {
    $this->seed();

    $quiz = makePaginationTestQuiz();

    Filament::setCurrentPanel('teacher');

    $component = Livewire::actingAs(paginationTestTeacher())
        ->test(TeacherEditQuiz::class, ['record' => $quiz->getKey()])
        ->assertSet('questionsPage', 1);

    $questionKeys = array_keys($component->instance()->data['questions']);
    $hiddenQuestionKey = $questionKeys[10];

    $component
        ->set("data.questions.{$hiddenQuestionKey}.question_text", '')
        ->call('save')
        ->assertSet('questionsPage', 2)
        ->assertSee('Pagination Question 12')
        ->assertDontSee('Pagination Question 01');

    expect($quiz->fresh()->questions()->count())->toBe(25);
});

test('saving from a later page still saves every question in the quiz', function () {
    $this->seed();

    $quiz = makePaginationTestQuiz();

    Filament::setCurrentPanel('teacher');

    $component = Livewire::actingAs(paginationTestTeacher())
        ->test(TeacherEditQuiz::class, ['record' => $quiz->getKey()])
        ->call('gotoQuestionsPage', 2);

    $questionKeys = array_keys($component->instance()->data['questions']);
    $visibleQuestionKey = $questionKeys[10];

    $component
        ->set("data.questions.{$visibleQuestionKey}.question_text", 'Updated Question 11')
        ->call('save')
        ->assertHasNoFormErrors();

    $savedQuiz = $quiz->fresh();

    expect($savedQuiz->questions()->count())->toBe(25)
        ->and($savedQuiz->questions()->where('question_text', 'Updated Question 11')->exists())->toBeTrue()
        ->and($savedQuiz->questions()->where('question_text', 'Pagination Question 01')->exists())->toBeTrue()
        ->and($savedQuiz->questions()->where('question_text', 'Pagination Question 21')->exists())->toBeTrue();
});

test('pagination keeps numeric question keys produced by CSV imports', function () {
    $this->seed();

    $quiz = makePaginationTestQuiz();

    Filament::setCurrentPanel('teacher');

    $component = Livewire::actingAs(paginationTestTeacher())
        ->test(TeacherEditQuiz::class, ['record' => $quiz->getKey()]);

    // The CSV importer replaces the question list with a numerically keyed array.
    $component->set('data.questions', array_values($component->instance()->data['questions']));

    $component
        ->call('gotoQuestionsPage', 2)
        ->assertSee('Pagination Question 11')
        ->assertSee('Pagination Question 20')
        ->assertDontSee('Pagination Question 01')
        ->set('data.questions.10.question_text', 'Numeric Key Updated')
        ->call('save')
        ->assertHasNoFormErrors();

    expect($quiz->fresh()->questions()->where('question_text', 'Numeric Key Updated')->exists())->toBeTrue();
});
