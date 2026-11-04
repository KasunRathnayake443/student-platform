{{-- Instant answer feedback shown after a student checks an answer. --}}
@php
    $isCorrectChoice = $selectedOptionId !== null
        && $correctOption
        && (int) $selectedOptionId === (int) $correctOption->id;
    $feedbackClass = $selectedOptionId === null
        ? 'qa-instant-skip'
        : ($isCorrectChoice ? 'qa-instant-correct' : 'qa-instant-wrong');
    $selectedOption = $selectedOptionId !== null
        ? $currentOptions->firstWhere('id', $selectedOptionId)
        : null;
@endphp

<div class="qa-instant-feedback {{ $feedbackClass }}">
    <div class="qa-feedback-head">
        @if($selectedOptionId === null)
            <span>😴</span>
            <span>No answer selected</span>
        @elseif($isCorrectChoice)
            <span>🎉</span>
            <span>Correct! Well done!</span>
        @else
            <span>💡</span>
            <span>Not quite — here is the correct answer</span>
        @endif
    </div>

    @if($selectedOption && ! $isCorrectChoice)
        <div class="qa-feedback-chosen">
            Your answer: <strong>{{ $selectedOption->option_text }}</strong>
        </div>
    @endif

    @if($correctOption)
        <div class="qa-feedback-answer">
            <span class="qa-feedback-letter">{{ $optLetters[$correctIndex] ?? '✓' }}</span>
            <span>{{ $correctOption->option_text }}</span>
        </div>
    @endif

    @if($visibleQuestion->explanation)
        <div class="qa-explanation">
            <strong>💡 Explanation:</strong> {{ $visibleQuestion->explanation }}
        </div>
    @endif
</div>
