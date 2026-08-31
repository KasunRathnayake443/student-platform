<div class="{{ $tier === 'kids' ? 'qa qa-kids' : 'qa' }}" style="min-height:100vh; background: linear-gradient(180deg, #faf5ff 0%, #fdf2f8 45%, #ffffff 100%); font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif; color: #334155; display: flex; flex-direction: column;">

    @php
        $orderedQuestions = $this->orderedQuestions();
    @endphp

    <style>
        .qa { --accent: #7c3aed; --ink: #0f172a; }
        .qa .qa-topbar {
            display: flex; align-items: center; justify-content: space-between;
            gap: 1rem; padding: 1.1rem 2.5rem; flex-wrap: wrap;
            background: rgba(255,255,255,0.7); backdrop-filter: blur(8px);
            border-bottom: 1px solid #ede9fe; position: sticky; top: 0; z-index: 20;
        }
        .qa .qa-back {
            display: inline-flex; align-items: center; gap: 0.45rem;
            background: #ffffff; border: 1px solid #e2e8f0; color: #4c1d95;
            font-weight: 700; font-size: 0.9rem; padding: 0.6rem 1.15rem;
            border-radius: 9999px; text-decoration: none; transition: all 0.15s ease;
        }
        .qa .qa-back:hover { border-color: #c4b5fd; background: #f5f3ff; }
        .qa .qa-brand { font-weight: 800; color: #4c1d95; font-size: 1rem; letter-spacing: 0.01em; }

        .qa .qa-hero {
            padding: 2.75rem 2.5rem 2.25rem; text-align: center;
            background: linear-gradient(135deg, #f5f3ff 0%, #fdf2f8 100%);
            border-bottom: 1px solid #f1e8fd;
        }
        .qa .qa-breadcrumb {
            font-size: 0.85rem; font-weight: 700; color: #8b5cf6; letter-spacing: 0.02em;
            margin-bottom: 1rem; display: inline-flex; gap: 0.45rem; align-items: center; flex-wrap: wrap;
            justify-content: center;
        }
        .qa .qa-breadcrumb .sep { color: #c4b5fd; }
        .qa .qa-emoji { font-size: 2.6rem; margin-bottom: 0.75rem; }
        .qa .qa-title {
            font-size: clamp(1.7rem, 4vw, 2.6rem); font-weight: 900; color: var(--ink);
            line-height: 1.2; margin: 0 0 1.15rem; letter-spacing: -0.01em;
        }
        .qa .qa-meta { display: inline-flex; gap: 0.55rem; flex-wrap: wrap; justify-content: center; }
        .qa .qa-chip {
            display: inline-flex; align-items: center; gap: 0.4rem;
            background: #ffffff; border: 1px solid #ede9fe; color: #4c1d95;
            font-weight: 700; font-size: 0.85rem; padding: 0.5rem 1rem; border-radius: 9999px;
            box-shadow: 0 2px 8px -4px rgba(124,58,237,0.15);
        }

        .qa .qa-body {
            flex: 1; width: 100%; max-width: 760px; margin: 0 auto; padding: 2.5rem 1.5rem 3.5rem;
            display: flex; flex-direction: column; gap: 1.5rem;
        }
        .qa .qa-card {
            background: #ffffff; border: 1px solid #f1e8fd; border-radius: 1.5rem;
            padding: 2rem 2.25rem; box-shadow: 0 12px 32px -18px rgba(76,29,149,0.25);
        }
        .qa .qa-card h2 {
            font-size: 1.1rem; font-weight: 800; color: #4c1d95; margin: 0 0 0.9rem;
            display: flex; align-items: center; gap: 0.45rem;
        }
        .qa .qa-text { margin: 0; font-size: 0.98rem; line-height: 1.7; color: #475569; }
        .qa .qa-text strong { color: #334155; }

        .qa .qa-alert {
            border-radius: 1.1rem; padding: 1rem 1.25rem; font-weight: 700; font-size: 0.95rem;
            border: 1px solid #bfdbfe; background: #eff6ff; color: #1e40af;
        }
        .qa .qa-alert-info { border-color: #ddd6fe; background: #f5f3ff; color: #5b21b6; }
        .qa .qa-alert-err { border-color: #fecaca; background: #fef2f2; color: #991b1b; }

        .qa .qa-start-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
            background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%); color: #ffffff;
            font-weight: 900; font-size: 1.15rem; padding: 1rem 2.5rem; border-radius: 9999px;
            border: none; cursor: pointer; text-decoration: none;
            box-shadow: 0 12px 26px -10px rgba(124,58,237,0.6);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .qa .qa-start-btn:hover { transform: translateY(-3px) scale(1.03); box-shadow: 0 16px 32px -10px rgba(124,58,237,0.7); }
        .qa .qa-start-btn:disabled { background: #cbd5e1; cursor: not-allowed; box-shadow: none; transform: none; }

        .qa .qa-cta-wrap { text-align: center; margin-top: 0.5rem; }
        .qa .qa-sub-note { font-size: 0.9rem; color: #7c6ba6; font-weight: 700; margin-top: 0.85rem; }

        .qa .qa-score-row { display: flex; align-items: center; gap: 1.25rem; flex-wrap: wrap; }
        .qa .qa-ring {
            width: 6.5rem; height: 6.5rem; border-radius: 9999px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            background: #ecfdf5; border: 6px solid #a7f3d0; color: #065f46;
            font-size: 1.7rem; font-weight: 900;
        }
        .qa .qa-ring-fail { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
        .qa .qa-ring-locked { background: #f8fafc; border-color: #e2e8f0; color: #64748b; }

        .qa .qa-bullets { margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 0.55rem; }
        .qa .qa-bullets li { font-size: 0.98rem; color: #475569; display: flex; gap: 0.5rem; align-items: flex-start; font-weight: 600; }

        /* ---------- Take screen ---------- */
        .qa .qa-take-head {
            display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
            margin-bottom: 1.1rem;
        }
        .qa .qa-take-title { font-size: 1.15rem; font-weight: 900; color: var(--ink); margin: 0; }
        .qa .qa-take-sub { font-size: 0.85rem; font-weight: 700; color: #8b5cf6; }
        .qa .qa-timer {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: #f1f9ff; border: 1px solid #bae6fd; color: #0369a1;
            font-weight: 900; font-size: 1.25rem; padding: 0.6rem 1.2rem; border-radius: 9999px;
            font-variant-numeric: tabular-nums; box-shadow: 0 4px 14px -8px rgba(2,132,199,0.4);
        }
        .qa .qa-timer.qa-timer-low { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
        .qa .qa-timer .qa-timer-glyph { font-size: 1.1rem; }

        .qa .qa-progress {
            display: flex; align-items: center; gap: 0.45rem; flex-wrap: wrap; margin-bottom: 1.1rem;
        }
        .qa .qa-progress-label { font-size: 0.85rem; font-weight: 800; color: #7c6ba6; margin-right: 0.35rem; }
        .qa .qa-dot {
            width: 1.55rem; height: 1.55rem; border-radius: 9999px; border: 2px solid #e2e8f0;
            background: #ffffff; color: #cbd5e1; font-size: 0.7rem; font-weight: 900;
            display: inline-flex; align-items: center; justify-content: center; cursor: pointer;
            transition: all 0.15s ease; padding: 0;
        }
        .qa .qa-dot.qa-dot-answered { background: #ede9fe; border-color: #c4b5fd; color: #7c3aed; }
        .qa .qa-dot.qa-dot-current { background: #7c3aed; border-color: #7c3aed; color: #ffffff; transform: scale(1.15); }

        .qa .qa-qcard { margin: 0; }
        .qa .qa-qnum { font-size: 0.85rem; font-weight: 800; color: #8b5cf6; margin-bottom: 0.5rem; }
        .qa .qa-qtext { font-size: 1.4rem; font-weight: 900; color: var(--ink); line-height: 1.35; margin: 0 0 1.25rem; }

        .qa .qa-media { margin: 0 0 1.25rem; border-radius: 1.25rem; overflow: hidden; border: 1px solid #ede9fe; }
        .qa .qa-media img { width: 100%; max-height: 420px; object-fit: cover; display: block; background: #f5f3ff; }
        .qa .qa-media video { width: 100%; max-height: 420px; display: block; background: #0f172a; }

        .qa .qa-opts { display: flex; flex-direction: column; gap: 0.8rem; margin-top: 0.25rem; }
        .qa .qa-opt {
            display: flex; align-items: center; gap: 0.85rem; width: 100%; text-align: left;
            background: #fbfaff; border: 3px solid #ede9fe; border-radius: 1.15rem;
            padding: 1rem 1.25rem; font-size: 1.05rem; font-weight: 700; color: #334155;
            cursor: pointer; transition: all 0.15s ease; font-family: inherit;
        }
        .qa .qa-opt .qa-opt-letter {
            flex-shrink: 0; width: 2.1rem; height: 2.1rem; border-radius: 9999px; background: #ede9fe;
            color: #4c1d95; font-weight: 900; display: inline-flex; align-items: center; justify-content: center; font-size: 0.95rem;
        }
        .qa .qa-opt:hover { border-color: #c4b5fd; background: #f5f3ff; transform: translateY(-1px); }
        .qa .qa-opt.qa-opt-selected { border-color: #7c3aed; background: #ede9fe; box-shadow: 0 6px 18px -10px rgba(124,58,237,0.5); }
        .qa .qa-opt.qa-opt-selected .qa-opt-letter { background: #7c3aed; color: #ffffff; }

        .qa .qa-nav {
            display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-top: 1.6rem;
        }
        .qa .qa-nav-btn {
            display: inline-flex; align-items: center; gap: 0.4rem; background: #ffffff;
            border: 2px solid #e2e8f0; color: #4c1d95; font-weight: 900; font-size: 0.95rem;
            padding: 0.7rem 1.4rem; border-radius: 9999px; cursor: pointer; transition: all 0.15s ease; font-family: inherit;
        }
        .qa .qa-nav-btn:hover { border-color: #c4b5fd; background: #f5f3ff; }
        .qa .qa-nav-btn.qa-nav-primary {
            background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%); color: #ffffff; border: none;
            box-shadow: 0 10px 22px -12px rgba(124,58,237,0.6);
        }
        .qa .qa-nav-btn.qa-nav-primary:hover { transform: translateY(-2px); }
        .qa .qa-nav-btn:disabled { opacity: 0.45; cursor: not-allowed; transform: none; }

        /* ---------- Result screen ---------- */
        .qa .qa-answer {
            border: 2px solid #e2e8f0; border-left-width: 6px; border-radius: 1.15rem;
            padding: 1.1rem 1.3rem; background: #ffffff; margin-top: 0.9rem;
        }
        .qa .qa-answer-correct { border-color: #a7f3d0; border-left-color: #10b981; background: #f0fdf4; }
        .qa .qa-answer-wrong { border-color: #fecaca; border-left-color: #f43f5e; background: #fff7f7; }
        .qa .qa-answer-skip { border-color: #fde68a; border-left-color: #f59e0b; background: #fffbeb; }
        .qa .qa-answer-badge {
            font-size: 0.78rem; font-weight: 900; padding: 0.3rem 0.8rem; border-radius: 9999px; letter-spacing: 0.02em;
            display: inline-flex; align-items: center; gap: 0.3rem; margin-bottom: 0.7rem;
        }
        .qa .qa-badge-correct { background: #a7f3d0; color: #065f46; }
        .qa .qa-badge-wrong { background: #fecaca; color: #991b1b; }
        .qa .qa-badge-skip { background: #fde68a; color: #92400e; }
        .qa .qa-answer-num { font-size: 0.8rem; font-weight: 800; color: #8b5cf6; margin-bottom: 0.4rem; }
        .qa .qa-answer-text { font-size: 1.05rem; font-weight: 800; color: var(--ink); margin: 0 0 0.9rem; line-height: 1.4; }
        .qa .qa-res-row { display: flex; align-items: center; gap: 0.6rem; font-weight: 700; font-size: 0.92rem; color: #475569; margin-top: 0.5rem; }
        .qa .qa-res-row .qa-res-tag {
            background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 9999px; padding: 0.35rem 0.9rem; font-size: 0.85rem;
        }
        .qa .qa-res-row.qa-res-correct { color: #047857; }
        .qa .qa-res-row.qa-res-correct .qa-res-tag { background: #d1fae5; border-color: #a7f3d0; color: #065f46; }
        .qa .qa-res-row.qa-res-chosen { color: #9f1239; }
        .qa .qa-res-row.qa-res-chosen .qa-res-tag { background: #ffe4e6; border-color: #fecdd3; color: #9f1239; }
        .qa .qa-explanation {
            margin-top: 1rem; padding: 0.9rem 1.1rem; background: #f5f3ff; border: 1px solid #e4d8f7; border-radius: 0.9rem;
            font-size: 0.92rem; line-height: 1.6; color: #4c1d95; font-weight: 600;
        }

        .qa .qa-hint { text-align: center; padding: 1.6rem; font-weight: 700; color: #7c6ba6; }

        .qa-kids .qa-title { font-size: clamp(2rem, 5.5vw, 3rem); }
        .qa-kids .qa-back { font-size: 0.95rem; padding: 0.7rem 1.3rem; }
        .qa-kids .qa-brand { font-size: 1.05rem; }
        .qa-kids .qa-chip { font-size: 0.9rem; padding: 0.55rem 1.15rem; }
        .qa-kids .qa-card { border-radius: 1.75rem; padding: 2.25rem 2.5rem; }
        .qa-kids .qa-card h2 { font-size: 1.15rem; }
        .qa-kids .qa-text, .qa-kids .qa-bullets li { font-size: 1.05rem; line-height: 1.85; }
        .qa-kids .qa-emoji { font-size: 3.2rem; }
        .qa-kids .qa-start-btn { font-size: 1.3rem; padding: 1.15rem 3rem; }
        .qa-kids .qa-qtext { font-size: 1.55rem; }
        .qa-kids .qa-opt { padding: 1.15rem 1.4rem; font-size: 1.15rem; border-radius: 1.35rem; }
        .qa-kids .qa-timer { font-size: 1.45rem; padding: 0.75rem 1.4rem; }
        .qa-kids .qa-nav-btn { font-size: 1.05rem; padding: 0.85rem 1.7rem; }
    </style>

    {{-- Top bar --}}
    <div class="qa-topbar">
        <a href="{{ \App\Filament\Student\Pages\Dashboard::getUrl(['tab' => 'quizzes']) }}" class="qa-back">◀ Back to My Quizzes</a>
        <div class="qa-brand">🧠 Student Portal</div>
    </div>

    {{-- ====================== HELPERS ====================== --}}
    @php
        $optLetters = ['A', 'B', 'C', 'D', 'E', 'F'];
    @endphp

    {{-- ====================== RESULT SCREEN ====================== --}}
    @if($finished)

        <header class="qa-hero">
            <div class="qa-breadcrumb">
                <span>🏫 {{ $schoolName }}</span>
                @if($gradeName)
                    <span class="sep">›</span>
                    <span>{{ $gradeName }}</span>
                @endif
                <span class="sep">›</span>
                <span>📘 {{ $className }}</span>
            </div>
            <div class="qa-emoji">{{ $passed ? '🎉' : '💪' }}</div>
            <h1 class="qa-title">Quiz finished!</h1>
            <div class="qa-meta">
                @if($wasTimeExpired)
                    <span class="qa-chip">⏰ Auto-submitted</span>
                @endif
                @if($attemptNumber)
                    <span class="qa-chip">#{{ $attemptNumber }} attempt</span>
                @endif
                <span class="qa-chip">🧩 {{ $answeredCount }} of {{ $questionCount }} answered</span>
            </div>
        </header>

        <div class="qa-body">

            @if($notice)
                <div class="qa-alert qa-alert-info">{{ $notice }}</div>
            @endif

            <div class="qa-card">
                <div class="qa-score-row">
                    <div class="qa-ring {{ ! $passed ? 'qa-ring-fail' : '' }}">{{ round($finalPercentage) }}%</div>
                    <div>
                        <h2 style="margin-bottom: 0.3rem;">{{ $passed ? 'You passed! 🎉' : 'Great effort!' }}</h2>
                        <p class="qa-text" style="margin: 0;">
                            You scored <strong>{{ (float) $finalScore }} points</strong> on {{ $questionCount }} questions.
                            @if($passed)
                                Nice work, superstar — keep it up! 🌟
                            @else
                                You need <strong>{{ $passingPercentage }}%</strong> to pass. Want to give it another go? 💪
                            @endif
                        </p>
                    </div>
                </div>

                <div class="qa-cta-wrap" style="margin-top: 1.4rem; display: flex; gap: 0.8rem; justify-content: center; flex-wrap: wrap;">
                    @if($canTryAgain)
                        <button type="button" class="qa-start-btn" wire:click="startAttempt">🔁 Try Again</button>
                    @endif
                    <a href="{{ \App\Filament\Student\Pages\Dashboard::getUrl(['tab' => 'quizzes']) }}" class="qa-back" style="padding: 1rem 2.2rem; font-size: 1.05rem;">🧠 Back to My Quizzes</a>
                </div>
            </div>

            @if($showCorrect)
                <div class="qa-card">
                    <h2>🔍 How did I do?</h2>

                    @foreach($orderedQuestions as $index => $q)
                        @php
                            $chosenId = $this->answers[$q->id] ?? null;
                            $correctOpt = $q->options->firstWhere('is_correct', true);
                            $isRight = $chosenId !== null && $correctOpt && $chosenId === $correctOpt->id;
                            $skip = $chosenId === null;
                        @endphp
                        <div class="qa-answer {{ $skip ? 'qa-answer-skip' : ($isRight ? 'qa-answer-correct' : 'qa-answer-wrong') }}">
                            @if($skip)
                                <span class="qa-answer-badge qa-badge-skip">😴 No answer</span>
                            @elseif($isRight)
                                <span class="qa-answer-badge qa-badge-correct">✓ Correct</span>
                            @else
                                <span class="qa-answer-badge qa-badge-wrong">✗ Not quite</span>
                            @endif

                            <div class="qa-answer-num">Question {{ $index + 1 }} of {{ $questionCount }}</div>
                            <p class="qa-answer-text">{{ $q->question_text }}</p>

                            @if($q->question_image_url)
                                <div class="qa-media">
                                    <img src="{{ $q->question_image_url }}" alt="Question visual">
                                </div>
                            @endif
                            @if($q->question_video_url)
                                <div class="qa-media">
                                    <video controls preload="metadata" src="{{ $q->question_video_url }}"></video>
                                </div>
                            @endif

                            @foreach($this->orderedOptions($q) as $o)
                                @php
                                    $isCorrectOpt = (bool) $o->is_correct;
                                    $isChosen = $chosenId === (int) $o->id;
                                @endphp
                                <div class="qa-res-row {{ $isCorrectOpt ? 'qa-res-correct' : ($isChosen ? 'qa-res-chosen' : '') }}">
                                    <span class="qa-res-tag">
                                        {{ $isCorrectOpt ? '✓' : ($isChosen ? '✗' : '·') }} {{ $optLetters[$loop->index] }}. {{ $o->option_text }}
                                    </span>
                                    @if($isCorrectOpt)
                                        <span>Correct answer</span>
                                    @elseif($isChosen)
                                        <span>Your answer</span>
                                    @endif
                                </div>
                            @endforeach

                            @if($q->explanation)
                                <div class="qa-explanation">🤓 {{ $q->explanation }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="qa-card">
                    <h2>🔒 Answers hidden</h2>
                    <p class="qa-text">
                        Your teacher has chosen to keep the correct answers private for now.
                        Ask <strong>{{ $quiz->teacher?->user?->name ?? 'your teacher' }}</strong> to talk through this quiz.
                    </p>
                </div>
            @endif
        </div>

    {{-- ====================== TAKE SCREEN ====================== --}}
    @elseif($attempt)

        @php
            $visibleQuestion = $orderedQuestions[$currentIndex] ?? $orderedQuestions->first();
        @endphp

        <header class="qa-hero" style="padding: 1.5rem 2.5rem;">
            <div class="qa-take-head" style="margin-bottom: 0;">
                <div>
                    <div class="qa-take-title">🧠 {{ $quiz->title }}</div>
                    <div class="qa-take-sub">Attempt #{{ $attemptNumber }} · Question {{ $currentIndex + 1 }} of {{ $questionCount }}</div>
                </div>
                @if($remainingSeconds !== null)
                    <div class="qa-timer"
                         x-data="qaTimer({{ $remainingSeconds }})"
                         x-init="qaStart()"
                         :class="clock !== null && clock <= 60 ? 'qa-timer-low' : ''">
                        <span class="qa-timer-glyph">⏱️</span>
                        <span x-text="qaClock"></span>
                    </div>
                @endif
            </div>
        </header>

        <div class="qa-body" style="max-width: 820px;">

            @if($notice)
                <div class="qa-alert {{ (str_contains($notice, 'run out') || str_contains($notice, 'not available')) ? 'qa-alert-err' : 'qa-alert-info' }}">
                    {{ $notice }}
                </div>
            @endif

            <div class="qa-card qa-qcard">

                <div class="qa-progress">
                    <span class="qa-progress-label">Questions</span>
                    @foreach($orderedQuestions as $qIndex => $_q)
                        <button type="button" class="qa-dot {{ ($this->answers[$_q->id] ?? null) !== null ? 'qa-dot-answered' : '' }} {{ $qIndex === $currentIndex ? 'qa-dot-current' : '' }}"
                                wire:click="goToQuestion({{ $qIndex }})">{{ $qIndex + 1 }}</button>
                    @endforeach
                </div>

                @if($visibleQuestion)
                    <div class="qa-qnum">Question {{ $currentIndex + 1 }}</div>
                    <p class="qa-qtext">{{ $visibleQuestion->question_text }}</p>

                    @if($visibleQuestion->question_image_url)
                        <div class="qa-media">
                            <img src="{{ $visibleQuestion->question_image_url }}" alt="Question visual">
                        </div>
                    @endif
                    @if($visibleQuestion->question_video_url)
                        <div class="qa-media">
                            <video controls preload="metadata" src="{{ $visibleQuestion->question_video_url }}"></video>
                        </div>
                    @endif

                    <div class="qa-opts">
                        @foreach($this->orderedOptions($visibleQuestion) as $o)
                            <button type="button"
                                    class="qa-opt {{ ($this->answers[$visibleQuestion->id] ?? null) === (int) $o->id ? 'qa-opt-selected' : '' }}"
                                    wire:click="pickAnswer({{ $visibleQuestion->id }}, {{ $o->id }})">
                                <span class="qa-opt-letter">{{ $optLetters[$loop->index] }}</span>
                                <span>{{ $o->option_text }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div class="qa-nav">
                        <button type="button" class="qa-nav-btn" wire:click="previousQuestion"
                                @if($currentIndex === 0) disabled @endif>◀ Prev</button>

                        @if($this->isLastQuestion())
                            <button type="button" class="qa-nav-btn qa-nav-primary" wire:click="submitAttempt">✅ Submit my answers</button>
                        @else
                            <button type="button" class="qa-nav-btn qa-nav-primary" wire:click="nextOrSubmit">Next ▶</button>
                        @endif
                    </div>
                @endif
            </div>
        </div>

    {{-- ====================== COVER SCREEN ====================== --}}
    @else

        @php
            $displayBtn = $coverState === 'available';
        @endphp

        <header class="qa-hero">
            <div class="qa-breadcrumb">
                <span>🏫 {{ $schoolName }}</span>
                @if($gradeName)
                    <span class="sep">›</span>
                    <span>{{ $gradeName }}</span>
                @endif
                <span class="sep">›</span>
                <span>📘 {{ $className }}</span>
            </div>
            <div class="qa-emoji">🧠</div>
            <h1 class="qa-title">{{ $quiz->title }}</h1>
            <div class="qa-meta">
                <span class="qa-chip">🧩 {{ $questionCount }} {{ \Illuminate\Support\Str::plural('question', $questionCount) }}</span>
                @if($timeLimit)
                    <span class="qa-chip">⏱️ {{ $timeLimit }} min</span>
                @endif
                <span class="qa-chip">🎯 Pass {{ $passingPercentage }}%</span>
                @if($maxAttempts)
                    <span class="qa-chip">🔁 {{ $maxAttempts }} @if($maxAttempts > 1) attempts @else attempt @endif</span>
                @endif
            </div>
        </header>

        <div class="qa-body">

            @if($notice)
                <div class="qa-alert {{ $notice === 'This quiz is not available to take right now.' ? 'qa-alert-err' : 'qa-alert-info' }}">
                    {{ $notice }}
                </div>
            @endif

            @if($coverState === 'locked')
                <div class="qa-card">
                    <h2>🔒 Not open yet</h2>
                    <p class="qa-text">
                        This quiz opens on <strong>{{ $quiz->start_at?->format('M j, Y · g:i A') ?? 'soon' }}</strong>.
                        Check back then — your teacher will have it ready!
                    </p>
                </div>
            @elseif($coverState === 'closed')
                <div class="qa-card">
                    <h2>⛔ This quiz has ended</h2>
                    <p class="qa-text">
                        The deadline for this quiz has passed, so it is no longer available.
                        Ask your teacher if you have questions about your results.
                    </p>
                </div>
            @elseif($coverState === 'available')
                <div class="qa-card">
                    <h2>📖 Get ready to play!</h2>
                    <ul class="qa-bullets">
                        <li><span>🧩</span> {{ $questionCount }} questions, one at a time.</li>
                        @if($timeLimit)
                            <li><span>⏱️</span> You have {{ $timeLimit }} minutes in total.</li>
                        @endif
                        <li><span>🎯</span> You need at least {{ $passingPercentage }}% to pass.</li>
                        <li><span>💜</span> Your answers are saved as you go — close the tab and come back to continue!</li>
                    </ul>
                    <div class="qa-cta-wrap">
                        <button type="button" class="qa-start-btn" wire:click="startAttempt">▶️ Start the Quiz!</button>
                        <div class="qa-sub-note">Ready when you are 😊</div>
                    </div>
                </div>
            @endif

            @if($quiz->instructions)
                <div class="qa-card">
                    <h2>📜 Instructions</h2>
                    <p class="qa-text">{!! nl2br(e($quiz->instructions)) !!}</p>
                </div>
            @endif
        </div>

    @endif
</div>

<script>
    function qaTimer(seconds) {
        return {
            clock: seconds,
            timerId: null,
            qaStart() {
                clearInterval(this.timerId);
                this.timerId = setInterval(() => {
                    this.clock -= 1;
                    if (this.clock <= 0) {
                        clearInterval(this.timerId);
                        if (this.$wire && this.$wire.timeUp) {
                            this.$wire.timeUp();
                        }
                    }
                }, 1000);
            },
            get qaClock() {
                const m = Math.max(0, Math.floor(this.clock / 60));
                const s = Math.max(0, this.clock % 60);
                return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
            }
        };
    }
</script>