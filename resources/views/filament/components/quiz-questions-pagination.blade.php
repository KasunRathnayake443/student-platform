@php
    $total = max(0, (int) ($total ?? 0));
    $perPage = max(1, (int) ($perPage ?? 10));
    $lastPage = max(1, (int) ceil($total / $perPage));
    $currentPage = min(max(1, (int) ($currentPage ?? 1)), $lastPage);
    $from = $total === 0 ? 0 : (($currentPage - 1) * $perPage) + 1;
    $to = min($total, $currentPage * $perPage);

    if ($lastPage <= 7) {
        $pageNumbers = range(1, $lastPage);
    } else {
        $start = max(1, $currentPage - 1);
        $end = min($lastPage, $currentPage + 1);

        if ($currentPage <= 4) {
            $start = 1;
            $end = 4;
        } elseif ($currentPage >= $lastPage - 3) {
            $start = $lastPage - 3;
            $end = $lastPage;
        }

        $pageNumbers = [];

        if ($start > 1) {
            $pageNumbers[] = 1;

            if ($start > 2) {
                $pageNumbers[] = '...';
            }
        }

        foreach (range($start, $end) as $pageNumber) {
            $pageNumbers[] = $pageNumber;
        }

        if ($end < $lastPage) {
            if ($end < $lastPage - 1) {
                $pageNumbers[] = '...';
            }

            $pageNumbers[] = $lastPage;
        }
    }
@endphp

@once
    <style>
        .quiz-questions-pagination {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            border: 1px solid var(--gray-200, #e5e7eb);
            border-radius: 0.75rem;
            background: #fff;
            padding: 0.75rem 1rem;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            font-size: 0.75rem;
            line-height: 1.25rem;
            color: var(--gray-600, #4b5563);
        }

        @media (min-width: 640px) {
            .quiz-questions-pagination {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }

        .quiz-questions-pagination__summary {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            column-gap: 0.75rem;
            row-gap: 0.25rem;
            font-weight: 500;
        }

        .quiz-questions-pagination__summary strong {
            color: var(--gray-900, #111827);
            font-weight: 600;
        }

        .quiz-questions-pagination__dot {
            color: var(--gray-400, #9ca3af);
        }

        .quiz-questions-pagination__loading {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            border-radius: 9999px;
            background: var(--primary-50, #eef2ff);
            color: var(--primary-700, #4338ca);
            padding: 0.125rem 0.5rem;
        }

        .quiz-questions-pagination__spinner {
            width: 0.75rem;
            height: 0.75rem;
            animation: quiz-questions-pagination-spin 1s linear infinite;
        }

        @keyframes quiz-questions-pagination-spin {
            to {
                transform: rotate(360deg);
            }
        }

        .quiz-questions-pagination__nav {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.25rem;
        }

        .quiz-questions-pagination__button {
            display: inline-flex;
            height: 2rem;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            border: 1px solid var(--gray-200, #e5e7eb);
            background: #fff;
            color: var(--gray-700, #374151);
            padding: 0 0.625rem;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            cursor: pointer;
            transition: background-color 150ms ease, color 150ms ease, border-color 150ms ease, opacity 150ms ease;
        }

        .quiz-questions-pagination__button:hover:not(:disabled) {
            background: var(--gray-50, #f9fafb);
        }

        .quiz-questions-pagination__button:disabled {
            cursor: not-allowed;
            opacity: 0.4;
        }

        .quiz-questions-pagination__button--number {
            min-width: 2rem;
            padding: 0 0.5rem;
        }

        .quiz-questions-pagination__button--current,
        .quiz-questions-pagination__button--current:hover:not(:disabled) {
            border-color: var(--primary-600, #4f46e5);
            background: var(--primary-600, #4f46e5);
            color: #fff;
        }

        .quiz-questions-pagination__ellipsis {
            display: inline-flex;
            height: 2rem;
            width: 2rem;
            align-items: center;
            justify-content: center;
            color: var(--gray-400, #9ca3af);
        }

        .quiz-questions-pagination__nav--loading {
            pointer-events: none;
            opacity: 0.6;
        }

        .quiz-questions-pagination__overlay {
            position: fixed;
            inset: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgb(3 7 18 / 0.4);
            padding: 1rem;
            backdrop-filter: blur(4px);
        }

        .quiz-questions-pagination__overlay-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
            border-radius: 1rem;
            background: #fff;
            padding: 1.5rem 2rem;
            box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);
        }

        .quiz-questions-pagination__overlay-spinner {
            width: 2.5rem;
            height: 2.5rem;
            color: var(--primary-600, #4f46e5);
            animation: quiz-questions-pagination-spin 1s linear infinite;
        }

        .quiz-questions-pagination__overlay-title {
            margin: 0;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-900, #111827);
            text-align: center;
        }

        .quiz-questions-pagination__overlay-text {
            margin: 0.125rem 0 0;
            font-size: 0.75rem;
            color: var(--gray-500, #6b7280);
            text-align: center;
        }

        html.dark .quiz-questions-pagination {
            border-color: rgb(255 255 255 / 0.1);
            background: var(--gray-900, #111827);
            color: var(--gray-300, #d1d5db);
        }

        html.dark .quiz-questions-pagination__summary strong {
            color: #fff;
        }

        html.dark .quiz-questions-pagination__loading {
            background: color-mix(in oklab, var(--primary-500, #6366f1) 10%, transparent);
            color: var(--primary-400, #818cf8);
        }

        html.dark .quiz-questions-pagination__button {
            border-color: rgb(255 255 255 / 0.1);
            background: var(--gray-800, #1f2937);
            color: var(--gray-200, #e5e7eb);
        }

        html.dark .quiz-questions-pagination__button:hover:not(:disabled) {
            background: var(--gray-700, #374151);
        }

        html.dark .quiz-questions-pagination__overlay-card {
            background: var(--gray-900, #111827);
        }

        html.dark .quiz-questions-pagination__overlay-title {
            color: #fff;
        }

        html.dark .quiz-questions-pagination__overlay-text {
            color: var(--gray-400, #9ca3af);
        }
    </style>
@endonce

<div class="quiz-questions-pagination">
    <div class="quiz-questions-pagination__summary">
        <span>
            Showing
            <strong>{{ $from }}–{{ $to }}</strong>
            of
            <strong>{{ $total }}</strong>
            {{ \Illuminate\Support\Str::plural('question', $total) }}
        </span>

        @if ($lastPage > 1)
            <span class="quiz-questions-pagination__dot" aria-hidden="true">•</span>
            <span>Page <strong>{{ $currentPage }}</strong> of <strong>{{ $lastPage }}</strong></span>
        @endif

        <span
            wire:loading
            wire:target="gotoQuestionsPage"
            class="quiz-questions-pagination__loading"
        >
            <svg class="quiz-questions-pagination__spinner" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Loading…
        </span>
    </div>

    @if ($lastPage > 1)
        <nav
            class="quiz-questions-pagination__nav"
            aria-label="Question pagination"
            wire:loading.class="quiz-questions-pagination__nav--loading"
            wire:target="gotoQuestionsPage"
        >
            <button
                type="button"
                wire:click="gotoQuestionsPage({{ $currentPage - 1 }})"
                @disabled($currentPage === 1)
                class="quiz-questions-pagination__button"
                aria-label="Previous question page"
            >
                Prev
            </button>

            @foreach ($pageNumbers as $pageNumber)
                @if ($pageNumber === '...')
                    <span class="quiz-questions-pagination__ellipsis" aria-hidden="true">…</span>
                @else
                    <button
                        type="button"
                        wire:click="gotoQuestionsPage({{ $pageNumber }})"
                        @if ($pageNumber === $currentPage) aria-current="page" @endif
                        class="quiz-questions-pagination__button quiz-questions-pagination__button--number {{ $pageNumber === $currentPage ? 'quiz-questions-pagination__button--current' : '' }}"
                        aria-label="Go to question page {{ $pageNumber }}"
                    >
                        {{ $pageNumber }}
                    </button>
                @endif
            @endforeach

            <button
                type="button"
                wire:click="gotoQuestionsPage({{ $currentPage + 1 }})"
                @disabled($currentPage === $lastPage)
                class="quiz-questions-pagination__button"
                aria-label="Next question page"
            >
                Next
            </button>
        </nav>
    @endif
</div>

@if ($withOverlay ?? false)
    <div
        wire:loading.flex
        wire:target="gotoQuestionsPage"
        class="quiz-questions-pagination__overlay"
        aria-live="polite"
        aria-busy="true"
    >
        <div class="quiz-questions-pagination__overlay-card">
            <svg class="quiz-questions-pagination__overlay-spinner" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>

            <div>
                <p class="quiz-questions-pagination__overlay-title">Loading questions…</p>
                <p class="quiz-questions-pagination__overlay-text">Question details load first. Images and videos continue in the background.</p>
            </div>
        </div>
    </div>
@endif
