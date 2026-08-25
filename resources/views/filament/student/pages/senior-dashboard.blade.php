@php
    use Illuminate\Support\Carbon;
    use App\Models\AssignmentSubmission;

    $activeClasses    = $activeContext ? $activeContext['classes'] : collect();
    $activeSchoolName = $activeContext ? ($activeContext['school']->name ?? 'School') : 'School';
    $activeGradeName  = $activeContext ? ($activeContext['grade']->name  ?? 'Grade')  : 'Grade';

    // Quiz stats
    $quizAttempts   = $student?->quizAttempts()->where('status', 'submitted')->with('quiz')->get() ?? collect();
    $quizAvgPct     = $quizAttempts->count() ? round($quizAttempts->avg('percentage'), 1) : 0;
    $quizPassed     = $quizAttempts->where('is_passed', true)->count();
    $recentQuizzes  = $quizAttempts->sortByDesc('completed_at')->take(6);

    // Chart data: quiz scores by week (last 8 weeks)
    $chartData = [];
    for ($w = 7; $w >= 0; $w--) {
        $weekStart = Carbon::now()->startOfWeek()->subWeeks($w);
        $weekEnd   = $weekStart->copy()->endOfWeek();
        $weekAttempts = $quizAttempts->filter(
            fn ($a) => $a->completed_at && $a->completed_at->between($weekStart, $weekEnd)
        );
        $chartData[] = [
            'label' => $weekStart->format('M j'),
            'value' => $weekAttempts->count() ? round($weekAttempts->avg('percentage'), 1) : 0,
        ];
    }

    // Assignments
    $allAssignments     = collect();
    $pendingAssignments = collect();
    foreach ($activeClasses as $class) {
        $all  = $class->assignments()->where('is_published', true)->get();
        $pend = $class->assignments()
            ->with('learningClass')
            ->where('is_published', true)
            ->whereDoesntHave('submissions', fn ($q) => $q->where('student_id', $student?->id))
            ->orderBy('end_at')
            ->get();
        $allAssignments     = $allAssignments->merge($all);
        $pendingAssignments = $pendingAssignments->merge($pend);
    }
    $pendingAssignments = $pendingAssignments->sortBy('end_at');

    // Submitted count
    $submittedCount = 0;
    if ($student && $allAssignments->count()) {
        $submittedCount = AssignmentSubmission::where('student_id', $student->id)
            ->whereIn('assignment_id', $allAssignments->pluck('id'))
            ->count();
    }
@endphp

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

/* ── Senior Dashboard (Modern Light Theme) ── */
.sd-wrap {
    font-family: 'Inter', system-ui, sans-serif;
    min-height: 100vh;
    background: #f8fafc;
    color: #334155;
    display: flex;
    margin: -1.5rem;
}

/* Sidebar */
.sd-sidebar {
    width: 220px;
    min-height: 100vh;
    background: #ffffff;
    border-right: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
}
.sd-logo {
    padding: 1.5rem 1.25rem 1rem;
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #7c3aed;
    border-bottom: 1px solid #e2e8f0;
}
.sd-nav { padding: 0.75rem 0; flex: 1; }
.sd-nav-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.7rem 1.25rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #64748b;
    cursor: pointer;
    transition: all 0.15s;
    border-left: 3px solid transparent;
    text-decoration: none;
}
.sd-nav-item:hover { color: #1e293b; background: #f1f5f9; }
.sd-nav-item.active {
    color: #7c3aed;
    background: #f5f3ff;
    border-left-color: #7c3aed;
    font-weight: 600;
}
.sd-nav-icon { width: 18px; opacity: 0.8; }

/* School context in sidebar */
.sd-contexts {
    padding: 0.75rem 1.25rem 1.25rem;
    border-top: 1px solid #e2e8f0;
}
.sd-ctx-label {
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #94a3b8;
    font-weight: 700;
    margin-bottom: 0.5rem;
}
.sd-ctx-pill {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.45rem 0.65rem;
    border-radius: 0.5rem;
    font-size: 0.78rem;
    font-weight: 600;
    margin-bottom: 0.35rem;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all 0.15s;
}
.sd-ctx-pill.active {
    background: #f5f3ff;
    border-color: #ddd6fe;
    color: #7c3aed;
}
.sd-ctx-pill:not(.active) { color: #64748b; }
.sd-ctx-pill:not(.active):hover { background: #f1f5f9; color: #334155; }
.sd-ctx-grade { font-size: 0.65rem; opacity: 0.7; }

/* Main content area */
.sd-content {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}

/* Topbar */
.sd-topbar {
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    padding: 1rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.sd-topbar-left h2 {
    font-size: 1.25rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 0.2rem;
}
.sd-topbar-left p { font-size: 0.8rem; color: #64748b; margin: 0; }
.sd-context-pill {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    background: #f5f3ff;
    border: 1px solid #e9d5ff;
    border-radius: 0.5rem;
    padding: 0.4rem 0.8rem;
    font-size: 0.8rem;
    color: #7c3aed;
    font-weight: 600;
}

/* Main padding */
.sd-main {
    padding: 1.5rem 2rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* KPI cards */
.sd-kpi-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}
@media (max-width: 900px) { .sd-kpi-row { grid-template-columns: repeat(2, 1fr); } }
.sd-kpi {
    background: #ffffff;
    border-radius: 0.875rem;
    padding: 1.25rem;
    border: 1px solid #e2e8f0;
    border-top: 3px solid;
    transition: transform 0.15s;
}
.sd-kpi:hover { transform: translateY(-2px); }
.sd-kpi:nth-child(1) { border-top-color: #7c3aed; }
.sd-kpi:nth-child(2) { border-top-color: #3b82f6; }
.sd-kpi:nth-child(3) { border-top-color: #14b8a6; }
.sd-kpi:nth-child(4) { border-top-color: #f59e0b; }
.sd-kpi-label { font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: #64748b; margin-bottom: 0.4rem; }
.sd-kpi-value { font-size: 2rem; font-weight: 800; color: #0f172a; line-height: 1; }
.sd-kpi-sub   { font-size: 0.775rem; color: #64748b; margin-top: 0.3rem; }

/* Two column */
.sd-two-col {
    display: grid;
    grid-template-columns: 1.4fr 1fr;
    gap: 1.5rem;
}
@media (max-width: 900px) { .sd-two-col { grid-template-columns: 1fr; } }

/* Cards */
.sd-card {
    background: #ffffff;
    border-radius: 0.875rem;
    padding: 1.25rem;
    border: 1px solid #e2e8f0;
}
.sd-card-title {
    font-size: 0.875rem;
    font-weight: 700;
    color: #94a3b8;
    margin: 0 0 1rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

/* Chart */
.sd-chart-container {
    position: relative;
    height: 160px;
    display: flex;
    align-items: flex-end;
    gap: 4px;
}
.sd-chart-bar-wrap {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
    gap: 4px;
    height: 100%;
}
.sd-chart-bar {
    width: 100%;
    min-height: 4px;
    background: linear-gradient(180deg, #c084fc, #7c3aed);
    border-radius: 3px 3px 0 0;
    opacity: 0.85;
    transition: opacity 0.2s;
}
.sd-chart-bar:hover { opacity: 1; }
.sd-chart-label { font-size: 0.6rem; color: #94a3b8; white-space: nowrap; }

/* Assignment list */
.sd-assign-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.7rem 0;
    border-bottom: 1px solid #f1f5f9;
}
.sd-assign-item:last-child { border-bottom: none; }
.sd-assign-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    margin-top: 6px;
    flex-shrink: 0;
}
.sd-assign-dot.urgent { background: #ef4444; box-shadow: 0 0 4px rgba(239,68,68,0.3); }
.sd-assign-dot.soon   { background: #f97316; }
.sd-assign-dot.normal { background: #22c55e; }
.sd-assign-title { font-size: 0.875rem; font-weight: 600; color: #1e293b; }
.sd-assign-meta  { font-size: 0.75rem;  color: #64748b; margin-top: 0.15rem; }
.sd-assign-due {
    margin-left: auto;
    font-size: 0.75rem;
    font-weight: 600;
    color: #94a3b8;
    flex-shrink: 0;
    padding-left: 0.5rem;
}

/* Class table */
.sd-class-table { width: 100%; border-collapse: collapse; }
.sd-class-table th {
    text-align: left;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #94a3b8;
    font-weight: 700;
    padding: 0 0 0.6rem;
    border-bottom: 1px solid #e2e8f0;
}
.sd-class-table td {
    padding: 0.7rem 0;
    font-size: 0.85rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.sd-class-table tr:last-child td { border-bottom: none; }
.sd-class-name-cell { font-weight: 600; color: #1e293b; }
.sd-class-teacher-cell { color: #64748b; font-size: 0.8rem; }
.sd-status-tag {
    display: inline-block;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.2rem 0.6rem;
    border-radius: 0.375rem;
}
.sd-status-tag.ok   { background: #dcfce7;  color: #15803d; }
.sd-status-tag.warn { background: #fee2e2;  color: #b91c1c; }
.sd-status-tag.pend { background: #f5f3ff;  color: #7c3aed; }

/* Quiz history table */
.sd-quiz-table { width: 100%; border-collapse: collapse; }
.sd-quiz-table th {
    text-align: left;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #94a3b8;
    font-weight: 700;
    padding: 0 0 0.5rem;
    border-bottom: 1px solid #e2e8f0;
}
.sd-quiz-table td {
    padding: 0.65rem 0;
    font-size: 0.85rem;
    border-bottom: 1px solid #f1f5f9;
}
.sd-quiz-table tr:last-child td { border-bottom: none; }
.sd-quiz-table .pct { font-weight: 700; color: #1e293b; }
.sd-badge {
    display: inline-block;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.15rem 0.55rem;
    border-radius: 0.375rem;
}
.sd-badge.pass { background: #dcfce7;  color: #15803d; }
.sd-badge.fail { background: #fee2e2; color: #b91c1c; }

.sd-empty { color: #94a3b8; font-size: 0.875rem; padding: 1rem 0; text-align: center; }
</style>

<div class="sd-wrap">



    {{-- ── CONTENT ── --}}
    <div class="sd-content">

        {{-- Topbar --}}
        <div class="sd-topbar">
            <div class="sd-topbar-left">
                <h2>{{ $firstName }}</h2>
                <p>{{ now()->format('l, F j, Y') }}</p>
            </div>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div class="sd-context-pill">
                    📍 {{ $activeSchoolName }} › {{ $activeGradeName }}
                </div>
                <button wire:click="logout" type="button" style="background: #fee2e2; border: 1px solid #fca5a5; color: #dc2626; border-radius: 0.5rem; padding: 0.45rem 0.85rem; font-size: 0.8rem; font-weight: 700; cursor: pointer;">
                    🚪 Sign Out
                </button>
            </div>
        </div>

        {{-- Main --}}
        <div class="sd-main">

            {{-- KPI row --}}
            <div class="sd-kpi-row">
                <div class="sd-kpi">
                    <div class="sd-kpi-label">Avg Score</div>
                    <div class="sd-kpi-value">{{ $quizAvgPct }}%</div>
                    <div class="sd-kpi-sub">{{ $quizPassed }} quizzes passed</div>
                </div>
                <div class="sd-kpi">
                    <div class="sd-kpi-label">Assignments</div>
                    <div class="sd-kpi-value">{{ $submittedCount }}/{{ $allAssignments->count() }}</div>
                    <div class="sd-kpi-sub">{{ $pendingAssignments->count() }} pending</div>
                </div>
                <div class="sd-kpi">
                    <div class="sd-kpi-label">Active Classes</div>
                    <div class="sd-kpi-value">{{ $activeClasses->count() }}</div>
                    <div class="sd-kpi-sub">{{ $activeGradeName }}</div>
                </div>
                <div class="sd-kpi">
                    <div class="sd-kpi-label">Quiz Attempts</div>
                    <div class="sd-kpi-value">{{ $quizAttempts->count() }}</div>
                    <div class="sd-kpi-sub">{{ $recentQuizzes->first()?->completed_at?->diffForHumans() ?? 'None yet' }}</div>
                </div>
            </div>

            {{-- Chart + Pending assignments --}}
            <div class="sd-two-col">
                <div class="sd-card">
                    <div class="sd-card-title">Performance Trend · Quiz Scores</div>
                    @php $maxVal = max(collect($chartData)->pluck('value')->max(), 1); @endphp
                    <div class="sd-chart-container">
                        @foreach($chartData as $point)
                            <div class="sd-chart-bar-wrap">
                                <div class="sd-chart-bar"
                                     style="height: {{ max(round(($point['value'] / 100) * 100), 2) }}%;"
                                     title="{{ $point['label'] }}: {{ $point['value'] }}%">
                                </div>
                                <div class="sd-chart-label">{{ $point['label'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="sd-card">
                    <div class="sd-card-title">Pending Assignments</div>
                    @if($pendingAssignments->isEmpty())
                        <div class="sd-empty">✅ All assignments submitted!</div>
                    @else
                        @foreach($pendingAssignments->take(5) as $assignment)
                            @php
                                $dueDate  = $assignment->end_at ? Carbon::parse($assignment->end_at) : null;
                                $daysLeft = $dueDate ? now()->diffInDays($dueDate, false) : null;
                                $dotCls   = match(true) {
                                    $daysLeft !== null && $daysLeft <= 1 => 'urgent',
                                    $daysLeft !== null && $daysLeft <= 3 => 'soon',
                                    default => 'normal',
                                };
                            @endphp
                            <div class="sd-assign-item">
                                <div class="sd-assign-dot {{ $dotCls }}"></div>
                                <div style="flex:1;min-width:0;">
                                    <div class="sd-assign-title">{{ $assignment->title }}</div>
                                    <div class="sd-assign-meta">{{ $assignment->learningClass->name ?? 'Class' }}</div>
                                </div>
                                @if($dueDate)
                                <div class="sd-assign-due">
                                    {{ $dueDate->format('M j') }}
                                </div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- Classes table --}}
            <div class="sd-card">
                <div class="sd-card-title">My Classes & Subjects</div>
                @if($activeClasses->isEmpty())
                    <div class="sd-empty">No classes in this context yet.</div>
                @else
                    <table class="sd-class-table">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th>Teacher</th>
                                <th>Lessons</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activeClasses as $class)
                                @php
                                    $lessonCount  = $class->lessons()->count();
                                    $pendingCount = $class->assignments()
                                        ->where('is_published', true)
                                        ->whereDoesntHave('submissions', fn($q) => $q->where('student_id', $student?->id))
                                        ->count();
                                @endphp
                                <tr>
                                    <td class="sd-class-name-cell">{{ $class->name }}</td>
                                    <td class="sd-class-teacher-cell">{{ $class->teachers->first()?->user?->name ?? '—' }}</td>
                                    <td style="color:#94a3b8;">{{ $lessonCount }} lessons</td>
                                    <td>
                                        @if($pendingCount === 0)
                                            <span class="sd-status-tag ok">Up to date</span>
                                        @else
                                            <span class="sd-status-tag warn">{{ $pendingCount }} pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            {{-- Quiz history --}}
            <div class="sd-card">
                <div class="sd-card-title">Quiz History</div>
                @if($recentQuizzes->isEmpty())
                    <div class="sd-empty">No quiz attempts yet.</div>
                @else
                    <table class="sd-quiz-table">
                        <thead>
                            <tr>
                                <th>Quiz</th>
                                <th>Score</th>
                                <th>Date</th>
                                <th>Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentQuizzes as $attempt)
                                <tr>
                                    <td style="color:#cbd5e1;">{{ $attempt->quiz->title ?? 'Quiz' }}</td>
                                    <td class="pct">{{ round($attempt->percentage) }}%</td>
                                    <td style="color:#64748b;font-size:0.8rem;">{{ $attempt->completed_at?->format('M j, Y') ?? '—' }}</td>
                                    <td>
                                        <span class="sd-badge {{ $attempt->is_passed ? 'pass' : 'fail' }}">
                                            {{ $attempt->is_passed ? 'PASSED' : 'FAILED' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

        </div>{{-- /.sd-main --}}
    </div>{{-- /.sd-content --}}
</div>{{-- /.sd-wrap --}}
