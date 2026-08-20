@php
    use Illuminate\Support\Carbon;

    $activeClasses    = $activeContext ? $activeContext['classes'] : collect();
    $activeSchoolName = $activeContext ? ($activeContext['school']->name ?? 'My School') : 'My School';
    $activeGradeName  = $activeContext ? ($activeContext['grade']->name  ?? 'My Grade')  : 'My Grade';

    // Stats
    $quizAttempts = $student?->quizAttempts()->where('status', 'submitted')->get() ?? collect();
    $quizAvgPct   = $quizAttempts->count() ? round($quizAttempts->avg('percentage'), 1) : 0;
    $quizPassed   = $quizAttempts->where('is_passed', true)->count();

    // Pending assignments
    $pendingAssignments = collect();
    $allAssignments = collect();
    foreach ($activeClasses as $class) {
        $pend = $class->assignments()
            ->with('learningClass')
            ->where('is_published', true)
            ->whereDoesntHave('submissions', fn ($q) => $q->where('student_id', $student?->id))
            ->orderBy('end_at')
            ->take(5)
            ->get();
        $pendingAssignments = $pendingAssignments->merge($pend);

        $all = $class->assignments()->where('is_published', true)->get();
        $allAssignments = $allAssignments->merge($all);
    }
    $pendingAssignments = $pendingAssignments->sortBy('end_at');

    // Progress (submitted / total)
    $submittedCount = $allAssignments->isEmpty() ? 0 : 0;
    if ($student && $allAssignments->count()) {
        $submittedCount = \App\Models\AssignmentSubmission::where('student_id', $student->id)
            ->whereIn('assignment_id', $allAssignments->pluck('id'))
            ->count();
    }
    $progressPct = $allAssignments->count() ? round(($submittedCount / $allAssignments->count()) * 100) : 0;

    // Login streak (simple: consecutive days with quiz attempts)
    $streak = 0;
    if ($student) {
        $day = Carbon::today();
        for ($d = 0; $d < 30; $d++) {
            $hasActivity = $student->quizAttempts()->whereDate('created_at', $day->copy()->subDays($d))->exists();
            if ($hasActivity) $streak++;
            elseif ($d > 0) break;
        }
    }

    // Recent quiz results
    $recentQuizzes = $quizAttempts->sortByDesc('completed_at')->take(4);
@endphp

<style>
/* ───── Junior Dashboard Styles (Modern Light Theme) ───── */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

.junior-dash {
    font-family: 'Inter', system-ui, sans-serif;
    min-height: 100vh;
    background: #f8fafc;
    color: #334155;
    margin: -1.5rem;
    padding: 0;
}

/* ── Header ── */
.jd-header {
    background: linear-gradient(135deg, #e0f2fe 0%, #e0e7ff 50%, #f3e8ff 100%);
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    position: relative;
    overflow: hidden;
}
.jd-header::after {
    content: '';
    position: absolute;
    bottom: -40px; right: -40px;
    width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(99,102,241,0.08) 0%, transparent 70%);
    border-radius: 50%;
}
.jd-greeting h1 {
    font-size: 1.75rem;
    font-weight: 900;
    background: linear-gradient(90deg, #6d28d9, #1d4ed8, #059669);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0 0 0.2rem;
}
.jd-greeting p {
    color: #475569;
    font-size: 0.875rem;
    margin: 0;
}
.jd-header-right {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
    z-index: 1;
}
.jd-streak-badge {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    background: #ffedd5;
    border: 1px solid #fed7aa;
    border-radius: 999px;
    padding: 0.4rem 0.9rem;
    font-size: 0.875rem;
    font-weight: 700;
    color: #ea580c;
}
.jd-context-badge {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    background: #e0e7ff;
    border: 1px solid #c7d2fe;
    border-radius: 999px;
    padding: 0.4rem 0.9rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: #4f46e5;
    cursor: pointer;
}

/* ── School Tabs ── */
.jd-school-tabs {
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    padding: 0.75rem 2rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    overflow-x: auto;
}
.jd-school-tab {
    padding: 0.45rem 1.1rem;
    border-radius: 999px;
    font-size: 0.85rem;
    font-weight: 700;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.2s;
    white-space: nowrap;
    background: transparent;
    color: #64748b;
}
.jd-school-tab.active {
    background: #e0e7ff;
    border-color: #6366f1;
    color: #4f46e5;
}
.jd-school-tab:hover:not(.active) {
    border-color: #cbd5e1;
    color: #334155;
}

/* ── Main ── */
.jd-main {
    padding: 1.5rem 2rem;
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* ── Stat cards ── */
.jd-stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
}
.jd-stat-card {
    background: #ffffff;
    border-radius: 1rem;
    padding: 1.25rem;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.04);
    position: relative;
    overflow: hidden;
    transition: transform 0.2s, border-color 0.2s;
}
.jd-stat-card:hover {
    transform: translateY(-2px);
    border-color: #cbd5e1;
}
.jd-stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 1rem 1rem 0 0;
}
.jd-stat-card.orange::before  { background: linear-gradient(90deg, #f97316, #fb923c); }
.jd-stat-card.green::before   { background: linear-gradient(90deg, #22c55e, #4ade80); }
.jd-stat-card.purple::before  { background: linear-gradient(90deg, #a855f7, #c084fc); }
.jd-stat-label {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #64748b;
    margin-bottom: 0.5rem;
}
.jd-stat-value {
    font-size: 1.75rem;
    font-weight: 900;
    line-height: 1;
}
.jd-stat-card.orange .jd-stat-value { color: #ea580c; }
.jd-stat-card.green  .jd-stat-value { color: #16a34a; }
.jd-stat-card.purple .jd-stat-value { color: #7c3aed; }
.jd-stat-sub {
    font-size: 0.8rem;
    color: #64748b;
    margin-top: 0.35rem;
    font-weight: 500;
}

/* Progress ring */
.jd-progress-ring-wrap {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.jd-ring {
    width: 56px;
    height: 56px;
    transform: rotate(-90deg);
    flex-shrink: 0;
}
.jd-ring circle { transition: stroke-dashoffset 1s ease; }

/* ── Two column grid ── */
.jd-two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}
@media (max-width: 768px) { .jd-two-col { grid-template-columns: 1fr; } }

/* Section header */
.jd-section-head {
    font-size: 1rem;
    font-weight: 800;
    color: #1e293b;
    margin: 0 0 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.jd-section-head span.badge {
    font-size: 0.7rem;
    background: #e0e7ff;
    color: #4f46e5;
    border-radius: 999px;
    padding: 0.1rem 0.55rem;
    font-weight: 700;
}

/* Class cards */
.jd-classes-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}
.jd-class-card {
    background: #ffffff;
    border-radius: 0.875rem;
    padding: 1rem;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.03);
    border-left: 3px solid;
    transition: transform 0.2s, border-color 0.2s;
    cursor: pointer;
    text-decoration: none;
}
.jd-class-card:hover { transform: translateX(2px); }
.jd-class-card:nth-child(1) { border-left-color: #f97316; }
.jd-class-card:nth-child(2) { border-left-color: #22c55e; }
.jd-class-card:nth-child(3) { border-left-color: #a855f7; }
.jd-class-card:nth-child(4) { border-left-color: #3b82f6; }
.jd-class-card:nth-child(5) { border-left-color: #ec4899; }
.jd-class-card:nth-child(6) { border-left-color: #14b8a6; }
.jd-class-name {
    font-size: 0.95rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.25rem;
}
.jd-class-teacher {
    font-size: 0.775rem;
    color: #64748b;
    font-weight: 500;
}
.jd-class-bar-bg {
    margin-top: 0.6rem;
    height: 4px;
    background: #f1f5f9;
    border-radius: 999px;
    overflow: hidden;
}
.jd-class-bar { height: 100%; border-radius: 999px; }

/* Deadline timeline */
.jd-deadline-panel {
    background: #ffffff;
    border-radius: 1rem;
    padding: 1.25rem;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.03);
}
.jd-deadline-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.65rem 0;
    border-bottom: 1px solid #f1f5f9;
}
.jd-deadline-item:last-child { border-bottom: none; }
.jd-deadline-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    margin-top: 5px;
    flex-shrink: 0;
}
.jd-deadline-dot.urgent  { background: #ef4444; box-shadow: 0 0 6px rgba(239,68,68,0.3); }
.jd-deadline-dot.soon    { background: #f97316; }
.jd-deadline-dot.normal  { background: #22c55e; }
.jd-deadline-title { font-size: 0.875rem; font-weight: 600; color: #1e293b; }
.jd-deadline-meta  { font-size: 0.75rem;  color: #64748b; margin-top: 0.15rem; }
.jd-no-deadlines { color: #16a34a; font-size: 0.875rem; font-weight: 600; text-align: center; padding: 1rem; }

/* Quiz result cards */
.jd-quiz-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 0.75rem;
}
.jd-quiz-card {
    background: #ffffff;
    border-radius: 0.875rem;
    padding: 1rem 1.25rem;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: transform 0.2s;
}
.jd-quiz-card:hover { transform: translateY(-2px); }
.jd-quiz-score {
    font-size: 1.5rem;
    font-weight: 900;
    min-width: 60px;
}
.jd-quiz-score.passed { color: #16a34a; }
.jd-quiz-score.failed { color: #dc2626; }
.jd-quiz-name { font-size: 0.875rem; font-weight: 600; color: #1e293b; }
.jd-quiz-badge {
    margin-top: 0.3rem;
    display: inline-block;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.15rem 0.6rem;
    border-radius: 999px;
}
.jd-quiz-badge.passed { background: #dcfce7; color: #15803d; }
.jd-quiz-badge.failed { background: #fee2e2; color: #b91c1c; }
.jd-empty { color: #64748b; font-size: 0.875rem; font-weight: 500; padding: 1rem; }
</style>

<div class="junior-dash">

    {{-- ── HEADER ── --}}
    <div class="jd-header">
        <div class="jd-greeting">
            <h1>Hey {{ $firstName }}! 👋</h1>
            <p>{{ now()->format('l, F j') }} · Keep it up!</p>
        </div>
        <div class="jd-header-right">
            @if($streak > 0)
            <div class="jd-streak-badge">🔥 {{ $streak }}-day streak</div>
            @endif
            <div class="jd-context-badge">
                {{ $activeSchoolName }} › {{ $activeGradeName }} ▾
            </div>
            <button wire:click="logout" type="button" style="background: #fee2e2; border: 1px solid #fca5a5; color: #dc2626; border-radius: 999px; padding: 0.4rem 0.9rem; font-size: 0.8rem; font-weight: 700; cursor: pointer;">
                🚪 Sign Out
            </button>
        </div>
    </div>

    {{-- ── SCHOOL TABS ── --}}
    @if($allContexts && $allContexts->count() > 0)
    <div class="jd-school-tabs">
        @foreach($allContexts as $schoolGroup)
            @foreach($schoolGroup['contexts'] as $ctx)
                @php $isActive = $activeContext && $activeContext['key'] === $ctx['key']; @endphp
                <button
                    wire:click="switchContext('{{ $ctx['key'] }}')"
                    class="jd-school-tab {{ $isActive ? 'active' : '' }}"
                >
                    {{ $schoolGroup['school']->name }}
                    @if($schoolGroup['contexts']->count() > 1)
                        · {{ $ctx['grade']->name ?? '' }}
                    @endif
                </button>
            @endforeach
        @endforeach
    </div>
    @endif

    {{-- ── MAIN CONTENT ── --}}
    <div class="jd-main">

        {{-- Stat row --}}
        <div class="jd-stats-row">
            {{-- Assignments --}}
            <div class="jd-stat-card orange">
                <div class="jd-stat-label">📋 Assignments</div>
                <div class="jd-stat-value">{{ $pendingAssignments->count() }}</div>
                <div class="jd-stat-sub">due · {{ $allAssignments->count() }} total</div>
            </div>

            {{-- Quiz avg --}}
            <div class="jd-stat-card green">
                <div class="jd-stat-label">🧠 Quiz Avg</div>
                <div class="jd-stat-value">{{ $quizAvgPct }}%</div>
                <div class="jd-stat-sub">{{ $quizPassed }} passed · {{ $quizAttempts->count() }} attempts</div>
            </div>

            {{-- Progress --}}
            <div class="jd-stat-card purple">
                <div class="jd-stat-label">📊 Progress</div>
                <div class="jd-progress-ring-wrap">
                    <svg class="jd-ring" viewBox="0 0 56 56">
                        <circle cx="28" cy="28" r="22" fill="none" stroke="#1e1b4b" stroke-width="6"/>
                        <circle cx="28" cy="28" r="22" fill="none" stroke="#a855f7" stroke-width="6"
                            stroke-dasharray="{{ round(2 * pi() * 22, 2) }}"
                            stroke-dashoffset="{{ round(2 * pi() * 22 * (1 - $progressPct / 100), 2) }}"
                            stroke-linecap="round"/>
                    </svg>
                    <div>
                        <div class="jd-stat-value">{{ $progressPct }}%</div>
                        <div class="jd-stat-sub">assignments done</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Two column: classes + deadlines --}}
        <div class="jd-two-col">

            {{-- My Classes --}}
            <div>
                <div class="jd-section-head">
                    📖 My Classes
                    <span class="badge">{{ $activeClasses->count() }}</span>
                </div>
                @if($activeClasses->isEmpty())
                    <div class="jd-empty">No classes yet in this context.</div>
                @else
                    <div class="jd-classes-grid">
                        @foreach($activeClasses->take(6) as $class)
                            <div class="jd-class-card">
                                <div class="jd-class-name">{{ $class->name }}</div>
                                <div class="jd-class-teacher">
                                    👤 {{ $class->teachers->first()?->user?->name ?? 'Teacher' }}
                                </div>
                                <div class="jd-class-bar-bg">
                                    <div class="jd-class-bar" style="width: {{ rand(30, 100) }}%; background: linear-gradient(90deg, #6366f1, #a855f7);"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Upcoming Deadlines --}}
            <div class="jd-deadline-panel">
                <div class="jd-section-head">📅 Upcoming Deadlines</div>
                @if($pendingAssignments->isEmpty())
                    <div class="jd-no-deadlines">✅ You're all caught up!</div>
                @else
                    @foreach($pendingAssignments->take(6) as $assignment)
                        @php
                            $dueDate = $assignment->end_at ? \Carbon\Carbon::parse($assignment->end_at) : null;
                            $daysLeft = $dueDate ? now()->diffInDays($dueDate, false) : null;
                            $dotClass = match(true) {
                                $daysLeft !== null && $daysLeft <= 1  => 'urgent',
                                $daysLeft !== null && $daysLeft <= 3  => 'soon',
                                default                               => 'normal',
                            };
                        @endphp
                        <div class="jd-deadline-item">
                            <div class="jd-deadline-dot {{ $dotClass }}"></div>
                            <div>
                                <div class="jd-deadline-title">{{ $assignment->title }}</div>
                                <div class="jd-deadline-meta">
                                    {{ $assignment->learningClass->name ?? 'Class' }}
                                    @if($dueDate)
                                        · Due {{ $dueDate->format('M j') }}
                                        @if($daysLeft !== null && $daysLeft >= 0)
                                            ({{ $daysLeft === 0 ? 'Today!' : $daysLeft . 'd left' }})
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- Recent quiz results --}}
        <div>
            <div class="jd-section-head">🧠 Recent Quiz Results</div>
            @if($recentQuizzes->isEmpty())
                <div class="jd-empty">No quiz attempts yet. Give one a try!</div>
            @else
                <div class="jd-quiz-grid">
                    @foreach($recentQuizzes as $attempt)
                        <div class="jd-quiz-card">
                            <div class="jd-quiz-score {{ $attempt->is_passed ? 'passed' : 'failed' }}">
                                {{ round($attempt->percentage) }}%
                            </div>
                            <div>
                                <div class="jd-quiz-name">{{ $attempt->quiz->title ?? 'Quiz' }}</div>
                                <span class="jd-quiz-badge {{ $attempt->is_passed ? 'passed' : 'failed' }}">
                                    {{ $attempt->is_passed ? '✓ PASSED' : '✗ FAILED' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>
