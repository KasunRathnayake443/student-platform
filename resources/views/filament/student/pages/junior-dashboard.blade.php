@php
    use Illuminate\Support\Carbon;

    $activeClasses    = $activeContext ? $activeContext['classes'] : collect();
    $activeSchoolName = $activeContext ? ($activeContext['school']->name ?? 'My School') : 'My School';
    $activeGradeName  = $activeContext ? ($activeContext['grade']->name  ?? 'My Grade')  : 'My Grade';
    $activeGradeKey   = $activeContext ? ($activeContext['key'] ?? '') : '';

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
            ->take(8)
            ->get();
        $pendingAssignments = $pendingAssignments->merge($pend);

        $all = $class->assignments()->where('is_published', true)->get();
        $allAssignments = $allAssignments->merge($all);
    }
    $pendingAssignments = $pendingAssignments->sortBy('end_at')->values();

    // Progress (submitted / total)
    $submittedCount = 0;
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
    $recentQuizzes = $quizAttempts->sortByDesc('completed_at')->take(5);

    // Best quiz score for metric card
    $quizResultsForGrades = $student?->quizAttempts()
        ->whereIn('status', ['submitted', 'time_expired'])
        ->get() ?? collect();
    $bestQuizPct = $quizResultsForGrades->max('percentage');
    $bestQuizPct = $bestQuizPct !== null ? (int) round((float) $bestQuizPct) : null;
@endphp

<style>
/* ═════════ BENTO JUNIOR DASHBOARD (Modern Light Theme) ═════════ */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

.junior-dash {
    font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
    width: 100%;
    min-height: 100%;
    background: #f8fafc;
    color: #334155;
    display: flex;
    flex-direction: column;
}

/* ── Global header row (greeting + search + pills) ── */
.bento-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 1.75rem 2.5rem 0.75rem;
}
.bento-greeting h1 {
    font-size: 1.5rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.bento-greeting h1 .welcome-badge {
    font-size: 1.1rem;
}
.bento-greeting p {
    color: #64748b;
    font-size: 0.85rem;
    margin: 0.2rem 0 0;
    font-weight: 500;
}
.bento-header-center {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}
.bento-search {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    background: #e2e8f0;
    border: 1px solid #e2e8f0;
    border-radius: 9999px;
    padding: 0.5rem 1.1rem;
    color: #64748b;
    font-size: 0.85rem;
    width: 22rem;
    max-width: 100%;
}
.bento-search input {
    border: none;
    background: transparent;
    outline: none;
    font-family: inherit;
    font-size: 0.85rem;
    color: #334155;
    width: 100%;
}
.bento-search input::placeholder { color: #94a3b8; }
.bento-header-right {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-wrap: wrap;
}
.bento-streak-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    background: #fffbeb;
    color: #b45309;
    border: 1px solid #fde68a;
    border-radius: 9999px;
    padding: 0.35rem 0.9rem;
    font-size: 0.78rem;
    font-weight: 700;
    white-space: nowrap;
}
.bento-context-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: #eef2ff;
    color: #4f46e5;
    border: 1px solid #e0e7ff;
    border-radius: 9999px;
    padding: 0.35rem 0.9rem;
    font-size: 0.78rem;
    font-weight: 700;
    white-space: nowrap;
}

/* ── School & grade filter tabs ── */
.bento-filter-bar {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    padding: 0.75rem 2.5rem 1.25rem;
}
.bento-filter-school {
    font-size: 0.78rem;
    font-weight: 800;
    color: #475569;
    display: flex;
    align-items: center;
    gap: 0.35rem;
    margin-right: 0.25rem;
}
.bento-filter-pill {
    padding: 0.4rem 1rem;
    border-radius: 9999px;
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #64748b;
    transition: all 0.15s ease;
    white-space: nowrap;
    font-family: inherit;
}
.bento-filter-pill:hover { border-color: #c7d2fe; color: #0f172a; }
.bento-filter-pill.active {
    background: #4f46e5;
    border-color: #4f46e5;
    color: #ffffff;
}

/* ── Bento main ── */
.bento-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    padding: 0.5rem 2.5rem 3rem;
    width: 100%;
    box-sizing: border-box;
}

/* Card base */
.bento-card {
    background: #ffffff;
    border: 1px solid #f1f5f9;
    border-radius: 1.25rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(15,23,42,0.06);
    transition: box-shadow 0.2s ease, transform 0.2s ease;
}
.bento-card:hover { box-shadow: 0 8px 20px rgba(15,23,42,0.08); }

/* ── Section 1: Metrics (3 columns) ── */
.bento-metrics {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
}
@media (max-width: 1000px) { .bento-metrics { grid-template-columns: 1fr; } }

.metric-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.5rem 1.75rem;
}
.metric-label {
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #94a3b8;
    margin-bottom: 0.6rem;
}
.metric-value {
    font-size: 2.2rem;
    font-weight: 900;
    line-height: 1;
    color: #0f172a;
}
.metric-value.mv-orange { color: #ea580c; }
.metric-value.mv-green  { color: #16a34a; }
.metric-sub {
    font-size: 0.8rem;
    color: #94a3b8;
    font-weight: 500;
    margin-top: 0.6rem;
}
.metric-visual {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* ── Section 2: Classes (70%) + Deadlines (30%) ── */
.bento-split {
    display: grid;
    grid-template-columns: 1.6fr 1fr;
    gap: 1.5rem;
    align-items: start;
}
@media (max-width: 1000px) { .bento-split { grid-template-columns: 1fr; } }

.bento-section-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
}
.bento-section-head h2 {
    font-size: 1rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.bento-section-head .count-badge {
    font-size: 0.7rem;
    background: #eef2ff;
    color: #4f46e5;
    border-radius: 9999px;
    padding: 0.15rem 0.6rem;
    font-weight: 800;
}

/* Class cards horizontal row */
.bento-classes-row {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 1rem;
}
.bento-class-card {
    background: #ffffff;
    border: 1px solid #f1f5f9;
    border-radius: 1.1rem;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(15,23,42,0.05);
    transition: all 0.2s ease;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 1rem;
    cursor: pointer;
}
.bento-class-card:hover { box-shadow: 0 8px 18px rgba(15,23,42,0.08); transform: translateY(-2px); }
.bento-class-name { font-size: 1rem; font-weight: 800; color: #0f172a; }
.bento-class-teacher {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.78rem;
    color: #64748b;
    font-weight: 500;
    margin-top: 0.4rem;
}
.bento-teacher-avatar {
    width: 1.6rem;
    height: 1.6rem;
    border-radius: 9999px;
    background: linear-gradient(135deg, #6366f1, #a855f7);
    color: #fff;
    font-size: 0.7rem;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.bento-class-bar {
    height: 5px;
    background: #f1f5f9;
    border-radius: 999px;
    overflow: hidden;
}
.bento-class-bar-fill { height: 100%; border-radius: 999px; }
.bento-class-bar-fill.cb-purple { background: linear-gradient(90deg, #4f46e5, #818cf8); }
.bento-class-bar-fill.cb-teal   { background: linear-gradient(90deg, #0d9488, #2dd4bf); }

/* Deadline panel */
.bento-deadlines-list { display: flex; flex-direction: column; }
.bento-deadline-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.7rem 0;
    border-bottom: 1px solid #f8fafc;
}
.bento-deadline-item:last-child { border-bottom: none; }
.bento-deadline-node {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    margin-top: 5px;
    flex-shrink: 0;
    background: #6366f1;
    box-shadow: 0 0 0 3px #e0e7ff;
}
.bento-deadline-title { font-size: 0.875rem; font-weight: 600; color: #0f172a; }
.bento-deadline-meta { font-size: 0.75rem; color: #94a3b8; margin-top: 0.15rem; }
.bento-deadline-bar {
    height: 4px;
    background: #f1f5f9;
    border-radius: 999px;
    overflow: hidden;
    margin-top: 0.4rem;
    max-width: 100%;
}
.bento-deadline-bar-fill { height: 100%; border-radius: 999px; background: #6366f1; }
.bento-no-deadlines { color: #16a34a; font-size: 0.875rem; font-weight: 600; text-align: center; padding: 1.5rem; }

/* ── Section 3: Recent quiz results (full width) ── */
.bento-quiz-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
}
.bento-quiz-card {
    background: #ffffff;
    border: 1px solid #f1f5f9;
    border-radius: 1.1rem;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(15,23,42,0.05);
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    overflow: hidden;
    position: relative;
}
.bento-quiz-card:hover { box-shadow: 0 8px 18px rgba(15,23,42,0.08); transform: translateY(-2px); }
.bento-quiz-score { font-size: 1.9rem; font-weight: 900; color: #0f172a; }
.bento-quiz-score.qs-green { color: #16a34a; }
.bento-quiz-score.qs-red   { color: #e11d48; }
.bento-quiz-name { font-size: 0.8rem; font-weight: 600; color: #64748b; }
.bento-quiz-badge {
    display: inline-block;
    font-size: 0.68rem;
    font-weight: 800;
    padding: 0.18rem 0.7rem;
    border-radius: 9999px;
    width: fit-content;
}
.bento-quiz-badge.qb-pass { background: #d1fae5; color: #059669; }
.bento-quiz-badge.qb-fail { background: #ffe4e6; color: #e11d48; }
.bento-quiz-wave { margin-top: auto; width: 100%; height: 34px; }

.bento-empty { color: #94a3b8; font-size: 0.875rem; font-weight: 500; padding: 1.5rem 0; }
</style>

<div class="junior-dash">

    {{-- ── GLOBAL HEADER ROW ── --}}
    <div class="bento-header">
        <div class="bento-greeting">
            <h1>Hey {{ $firstName }}! <span class="welcome-badge">🟣</span></h1>
            <p>{{ now()->format('l, F j') }} - Here's how your week is going</p>
        </div>
        <div class="bento-header-center">
            <div class="bento-search">
                <span>🔍</span>
                <input type="text" placeholder="Search courses, assignments...">
            </div>
            <div class="bento-header-right">
                @if($streak > 0)
                <span class="bento-streak-pill">🔥 {{ $streak }}-day streak</span>
                @endif
                <span class="bento-context-badge">📍 {{ $activeSchoolName }} › {{ $activeGradeName }}</span>
            </div>
        </div>
    </div>

    {{-- ── SCHOOL & GRADE FILTER TABS ── --}}
    @if($allContexts)
    <div class="bento-filter-bar">
        @foreach($allContexts as $schoolGroup)
            <span class="bento-filter-school">🏫 {{ $schoolGroup['school']->name }}</span>
            @foreach($schoolGroup['contexts'] as $ctx)
                @php $isActive = $activeContext && $activeContext['key'] === $ctx['key']; @endphp
                <button
                    wire:click="switchContext('{{ $ctx['key'] }}')"
                    class="bento-filter-pill {{ $isActive ? 'active' : '' }}"
                >
                    {{ $ctx['grade']->name ?? '' }}
                </button>
            @endforeach
        @endforeach
    </div>
    @endif

    {{-- ── BENTO MAIN ── --}}
    <div class="bento-main">

        {{-- SECTION 1: METRICS --}}
        <div class="bento-metrics">
            {{-- Assigned --}}
            <div class="bento-card metric-card">
                <div>
                    <div class="metric-label">📋 Assigned</div>
                    <div class="metric-value mv-orange">{{ $pendingAssignments->count() }}</div>
                    <div class="metric-sub">pending · {{ $submittedCount }} done</div>
                </div>
                <div class="metric-visual">
                    <svg width="54" height="54" viewBox="0 0 54 54">
                        <circle cx="27" cy="27" r="22" fill="none" stroke="#f1f5f9" stroke-width="6"/>
                        @php
                            $pendCircle = $allAssignments->count() ? ($pendingAssignments->count() / max(1, $allAssignments->count())) : 0;
                            $pendCirc = round(2 * pi() * 22 * $pendCircle, 2);
                        @endphp
                        <circle cx="27" cy="27" r="22" fill="none" stroke="#f97316" stroke-width="6"
                            stroke-dasharray="{{ round(2 * pi() * 22, 2) }}"
                            stroke-dashoffset="{{ $pendCirc }}"
                            stroke-linecap="round" transform="rotate(-90 27 27)"/>
                    </svg>
                </div>
            </div>

            {{-- Quiz Average --}}
            <div class="bento-card metric-card">
                <div>
                    <div class="metric-label">🧠 Quiz Average</div>
                    <div class="metric-value mv-green">{{ $quizAvgPct }}%</div>
                    <div class="metric-sub">{{ $quizPassed }} passed · {{ $quizAttempts->count() }} attempts</div>
                </div>
                <div class="metric-visual">
                    <svg width="44" height="48" viewBox="0 0 44 48">
                        <rect x="4" y="24" width="8" height="20" rx="2" fill="#a7f3d0"/>
                        <rect x="16" y="12" width="8" height="32" rx="2" fill="#34d399"/>
                        <rect x="28" y="4" width="8" height="40" rx="2" fill="#10b981"/>
                    </svg>
                </div>
            </div>

            {{-- Progress --}}
            <div class="bento-card metric-card">
                <div>
                    <div class="metric-label">📊 Progress</div>
                    <div class="metric-value" style="color:#4f46e5;">{{ $progressPct }}%</div>
                    <div class="metric-sub">assignments done</div>
                </div>
                <div class="metric-visual">
                    <svg width="64" height="64" viewBox="0 0 64 64">
                        <circle cx="32" cy="32" r="26" fill="none" stroke="#f1f5f9" stroke-width="8"/>
                        <circle cx="32" cy="32" r="26" fill="none" stroke="#4f46e5" stroke-width="8"
                            stroke-dasharray="{{ round(2 * pi() * 26, 2) }}"
                            stroke-dashoffset="{{ round(2 * pi() * 26 * (1 - $progressPct / 100), 2) }}"
                            stroke-linecap="round" transform="rotate(-90 32 32)"/>
                        <text x="32" y="38" text-anchor="middle" font-size="16" font-weight="900" fill="#4f46e5" font-family="Inter, sans-serif">{{ $progressPct }}%</text>
                    </svg>
                </div>
            </div>
        </div>

        {{-- SECTION 2: CLASSES + DEADLINES --}}
        <div class="bento-split">

            {{-- My Classes (70%) --}}
            <div class="bento-card">
                <div class="bento-section-head">
                    <h2>📖 My Classes <span class="count-badge">{{ $activeClasses->count() }}</span></h2>
                </div>
                @if($activeClasses->isEmpty())
                    <div class="bento-empty">No classes yet in this context.</div>
                @else
                    <div class="bento-classes-row">
                        @php $clsIdx = 0; @endphp
                        @foreach($activeClasses->take(6) as $class)
                            @php
                                $clsIdx++;
                                $teacherName = $class->teachers->first()?->user?->name ?? 'Teacher';
                                $initials = strtoupper(collect(explode(' ', $teacherName))->map(fn ($n) => substr($n, 0, 1))->take(2)->implode(''));
                                $barColor = $clsIdx % 2 === 0 ? 'cb-teal' : 'cb-purple';
                                $classProgress = rand(30, 100);
                            @endphp
                            <a href="{{ \App\Filament\Student\Pages\Dashboard::getUrl(['tab' => 'classes']) }}" class="bento-class-card">
                                <div>
                                    <div class="bento-class-name">{{ $class->name }}</div>
                                    <div class="bento-class-teacher">
                                        <span class="bento-teacher-avatar">{{ $initials }}</span>
                                        {{ $teacherName }}
                                    </div>
                                </div>
                                <div class="bento-class-bar">
                                    <div class="bento-class-bar-fill {{ $barColor }}" style="width: {{ $classProgress }}%;"></div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Upcoming Deadlines (30%) --}}
            <div class="bento-card">
                <div class="bento-section-head">
                    <h2>🏆 Upcoming Deadlines</h2>
                </div>
                @if($pendingAssignments->isEmpty())
                    <div class="bento-no-deadlines">✅ You're all caught up!</div>
                @else
                    <div class="bento-deadlines-list">
                        @foreach($pendingAssignments->take(6) as $assignment)
                            @php
                                $dueDate = $assignment->end_at ? \Carbon\Carbon::parse($assignment->end_at) : null;
                                $daysLeft = $dueDate ? now()->diffInDays($dueDate, false) : null;
                            @endphp
                            <div class="bento-deadline-item">
                                <div class="bento-deadline-node"></div>
                                <div style="width: 100%;">
                                    <div class="bento-deadline-title">{{ $assignment->title }}</div>
                                    <div class="bento-deadline-meta">
                                        {{ $assignment->learningClass->name ?? 'Class' }}
                                        @if($dueDate)
                                            · Due {{ $dueDate->format('M j') }}
                                            @if($daysLeft !== null && $daysLeft >= 0)
                                                ({{ $daysLeft === 0 ? 'Today!' : $daysLeft . 'd left' }})
                                            @endif
                                        @endif
                                    </div>
                                    <div class="bento-deadline-bar">
                                        <div class="bento-deadline-bar-fill" style="width: {{ rand(15, 75) }}%;"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- SECTION 3: RECENT QUIZ RESULTS --}}
        <div class="bento-card">
            <div class="bento-section-head">
                <h2>🧠 Recent Quiz Results</h2>
            </div>
            @if($recentQuizzes->isEmpty())
                <div class="bento-empty">No quiz attempts yet. Give one a try!</div>
            @else
                <div class="bento-quiz-grid">
                    @foreach($recentQuizzes as $attempt)
                        @php
                            $passed = (bool) $attempt->is_passed;
                            $pct = (int) round((float) $attempt->percentage);
                        @endphp
                        <div class="bento-quiz-card">
                            <div class="bento-quiz-score {{ $passed ? 'qs-green' : 'qs-red' }}">{{ $pct }}%</div>
                            <div class="bento-quiz-name">{{ $attempt->quiz->title ?? 'Quiz' }}</div>
                            <span class="bento-quiz-badge {{ $passed ? 'qb-pass' : 'qb-fail' }}">
                                {{ $passed ? 'PASSED' : 'FAILED' }}
                            </span>
                            <div class="bento-quiz-wave">
                                <svg width="100%" height="34" viewBox="0 0 200 34" preserveAspectRatio="none">
                                    @php
                                        $waveColors = $passed
                                            ? 'linear-gradient(90deg, #6366f1, #a855f7)'
                                            : 'linear-gradient(90deg, #f43f5e, #fb7185)';
                                        $waveFill = $passed ? '#6366f1' : '#f43f5e';
                                    @endphp
                                    <defs>
                                        <linearGradient id="wave-{{ $attempt->id }}" x1="0" y1="0" x2="1" y2="0">
                                            <stop offset="0%" stop-color="{{ $passed ? '#6366f1' : '#f43f5e' }}"/>
                                            <stop offset="100%" stop-color="{{ $passed ? '#a855f7' : '#fb7185' }}"/>
                                        </linearGradient>
                                    </defs>
                                    <path d="M0,20 C25,8 40,28 60,18 C80,8 95,22 110,14 C130,4 150,20 165,12 C180,6 190,14 200,10 L200,34 L0,34 Z"
                                        fill="url(#wave-{{ $attempt->id }})" opacity="0.15"/>
                                    <path d="M0,26 C25,16 40,32 60,24 C80,16 95,28 110,22 C130,14 150,26 165,20 C180,15 190,20 200,18"
                                        fill="none" stroke="{{ $waveFill }}" stroke-width="1.5" opacity="0.5"/>
                                </svg>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>
