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

<div>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    /* Reset Page Container - Modern Light Theme */
    html, body {
        margin: 0;
        padding: 0;
        width: 100%;
        height: 100%;
        background-color: #f8fafc;
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        color: #334155;
    }

    .custom-app-container {
        display: flex;
        min-height: 100vh;
        width: 100%;
        background: #f8fafc;
    }

    /* ── CUSTOM LIGHT SIDEBAR ── */
    .custom-sidebar {
        width: 260px;
        background: #ffffff;
        border-right: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 1.75rem 1.25rem;
        flex-shrink: 0;
        z-index: 20;
    }

    .brand-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
    }
    .brand-icon {
        width: 2.75rem;
        height: 2.75rem;
        background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%);
        border-radius: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        box-shadow: 0 8px 20px rgba(124, 58, 237, 0.25);
    }
    .brand-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.01em;
    }
    .brand-sub {
        font-size: 0.72rem;
        color: #7c3aed;
        font-weight: 700;
    }

    /* Nav Section */
    .nav-group-label {
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #94a3b8;
        margin: 1.5rem 0 0.6rem 0.5rem;
    }

    .nav-menu {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .nav-link {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.75rem 1rem;
        border-radius: 0.85rem;
        color: #64748b;
        font-weight: 600;
        font-size: 0.92rem;
        text-decoration: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        border: 1px solid transparent;
        background: transparent;
        width: 100%;
        text-align: left;
    }

    .nav-link:hover {
        color: #0f172a;
        background: #f1f5f9;
        border-color: #e2e8f0;
    }

    .nav-link.active {
        color: #7c3aed;
        background: #f5f3ff;
        border-color: #ddd6fe;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(124, 58, 237, 0.06);
    }

    .nav-icon {
        font-size: 1.15rem;
    }

    /* School Context Switcher List */
    .ctx-list {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        margin-top: 0.25rem;
    }
    .ctx-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.65rem 0.85rem;
        border-radius: 0.75rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.82rem;
        color: #475569;
    }
    .ctx-item:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #0f172a;
    }
    .ctx-item.active {
        background: #f5f3ff;
        border-color: #ddd6fe;
        color: #7c3aed;
        font-weight: 700;
    }

    /* User Card Bottom */
    .sidebar-user-card {
        padding: 1rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }
    .user-avatar {
        width: 2.4rem;
        height: 2.4rem;
        border-radius: 50%;
        background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        color: #ffffff;
        font-size: 0.9rem;
        box-shadow: 0 4px 10px rgba(124, 58, 237, 0.25);
    }
    .user-info {
        flex: 1;
        min-width: 0;
    }
    .user-name {
        font-size: 0.88rem;
        font-weight: 700;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .user-role {
        font-size: 0.73rem;
        color: #64748b;
        font-weight: 600;
    }

    .btn-logout-icon {
        background: #fee2e2;
        border: 1px solid #fca5a5;
        color: #dc2626;
        width: 2.2rem;
        height: 2.2rem;
        border-radius: 0.6rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-logout-icon:hover {
        background: #ef4444;
        color: #ffffff;
        border-color: #ef4444;
    }

    /* ── MAIN LIGHT WORKSPACE ── */
    .custom-workspace {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-width: 0;
        overflow-y: auto;
        background: #f8fafc;
    }

    /* Topbar Header */
    .custom-topbar {
        padding: 1.5rem 2.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #e2e8f0;
        background: #ffffff;
        gap: 1.5rem;
    }
    .topbar-title h1 {
        font-size: 1.6rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        letter-spacing: -0.02em;
    }
    .topbar-title p {
        font-size: 0.88rem;
        color: #64748b;
        margin: 0.2rem 0 0;
        font-weight: 500;
    }

    .topbar-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .active-context-badge {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.55rem 1.1rem;
        background: #f5f3ff;
        border: 1px solid #ddd6fe;
        border-radius: 9999px;
        color: #7c3aed;
        font-weight: 700;
        font-size: 0.85rem;
    }

    .btn-signout-top {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.55rem 1.1rem;
        background: #fee2e2;
        border: 1px solid #fca5a5;
        border-radius: 9999px;
        color: #dc2626;
        font-weight: 700;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-signout-top:hover {
        background: #ef4444;
        color: #ffffff;
        border-color: #ef4444;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    }

    /* Content Container */
    .workspace-content {
        padding: 2rem 2.5rem;
        display: flex;
        flex-direction: column;
        gap: 2rem;
        max-width: 1400px;
        margin: 0 auto;
        width: 100%;
        box-sizing: border-box;
    }

    /* 4 Light KPI Cards Grid */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
    }
    @media (max-width: 1100px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 600px)  { .kpi-grid { grid-template-columns: 1fr; } }

    .kpi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1.25rem;
        padding: 1.35rem 1.5rem;
        position: relative;
        overflow: hidden;
        transition: all 0.25s ease;
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.03);
    }
    .kpi-card:hover {
        transform: translateY(-3px);
        border-color: #cbd5e1;
        box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.1);
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        border-radius: 1.25rem 1.25rem 0 0;
    }
    .kpi-card-purple::before { background: linear-gradient(90deg, #7c3aed, #c084fc); }
    .kpi-card-blue::before   { background: linear-gradient(90deg, #2563eb, #38bdf8); }
    .kpi-card-emerald::before{ background: linear-gradient(90deg, #059669, #34d399); }
    .kpi-card-amber::before  { background: linear-gradient(90deg, #d97706, #fbbf24); }

    .kpi-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.75rem;
    }
    .kpi-label {
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
    }
    .kpi-icon {
        font-size: 1.25rem;
    }
    .kpi-value {
        font-size: 2.2rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1;
        margin-bottom: 0.4rem;
    }
    .kpi-sub {
        font-size: 0.83rem;
        color: #64748b;
        font-weight: 600;
    }

    /* 2-Column Split Workspace Layout */
    .grid-two-col {
        display: grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 1.75rem;
    }
    @media (max-width: 1000px) { .grid-two-col { grid-template-columns: 1fr; } }

    .glass-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1.5rem;
        padding: 1.75rem;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.03);
    }
    .glass-card-title {
        font-size: 1.1rem;
        font-weight: 800;
        color: #0f172a;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Performance Trend Chart Widget */
    .chart-bars-wrap {
        height: 160px;
        display: flex;
        align-items: flex-end;
        gap: 0.85rem;
        padding-top: 1rem;
    }
    .chart-col {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        height: 100%;
        justify-content: flex-end;
        gap: 0.5rem;
    }
    .bar-track {
        width: 100%;
        background: #f1f5f9;
        border-radius: 0.5rem;
        height: 100%;
        display: flex;
        align-items: flex-end;
        overflow: hidden;
        position: relative;
    }
    .bar-fill {
        width: 100%;
        background: linear-gradient(180deg, #a855f7 0%, #7c3aed 100%);
        border-radius: 0.5rem 0.5rem 0 0;
        transition: height 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .bar-fill:hover {
        background: linear-gradient(180deg, #c084fc 0%, #6d28d9 100%);
    }
    .chart-label {
        font-size: 0.72rem;
        color: #64748b;
        font-weight: 700;
    }

    /* My Classes Grid Cards */
    .classes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.25rem;
    }
    .class-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1.25rem;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        transition: all 0.25s ease;
    }
    .class-card:hover {
        transform: translateY(-3px);
        border-color: #c7d2fe;
        box-shadow: 0 10px 25px -5px rgba(124, 58, 237, 0.12);
    }
    .class-card-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 1rem;
        background: #f5f3ff;
        border: 1px solid #ddd6fe;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    /* My Classes Table */
    .custom-table {
        width: 100%;
        border-collapse: collapse;
    }
    .custom-table th {
        text-align: left;
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid #e2e8f0;
    }
    .custom-table td {
        padding: 1rem 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.92rem;
    }
    .custom-table tr:last-child td {
        border-bottom: none;
    }
    .class-title {
        font-weight: 700;
        color: #0f172a;
    }
    .teacher-name {
        color: #64748b;
        font-size: 0.85rem;
        font-weight: 500;
    }
    .status-pill {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 700;
    }
    .status-ok { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .status-pending { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }

    /* Deadline Timeline Checklist */
    .deadline-list {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }
    .deadline-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1rem 1.15rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        transition: all 0.2s;
    }
    .deadline-item:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    .deadline-info h4 {
        font-size: 0.93rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 0.25rem 0;
    }
    .deadline-info p {
        font-size: 0.8rem;
        color: #64748b;
        margin: 0;
        font-weight: 500;
    }
    .btn-action-start {
        padding: 0.45rem 0.95rem;
        background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%);
        border: none;
        border-radius: 0.65rem;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(124, 58, 237, 0.25);
        white-space: nowrap;
    }
    .btn-action-start:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(124, 58, 237, 0.35);
    }
    </style>

    <div class="custom-app-container">
        
        <!-- ── CUSTOM LIGHT SIDEBAR ── -->
        <aside class="custom-sidebar">
            <div>
                <!-- Brand -->
                <div class="brand-header">
                    <div class="brand-icon">🎓</div>
                    <div>
                        <div class="brand-title">Student Portal</div>
                        <div class="brand-sub">Academic Workspace</div>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="nav-group-label">Navigation</div>
                <nav class="nav-menu">
                    <button wire:click="setTab('dashboard')" type="button" class="nav-link {{ $activeTab === 'dashboard' ? 'active' : '' }}">
                        <span class="nav-icon">📊</span>
                        <span>Dashboard</span>
                    </button>
                    <button wire:click="setTab('classes')" type="button" class="nav-link {{ $activeTab === 'classes' ? 'active' : '' }}">
                        <span class="nav-icon">📖</span>
                        <span>My Classes</span>
                    </button>
                    <button wire:click="setTab('assignments')" type="button" class="nav-link {{ $activeTab === 'assignments' ? 'active' : '' }}">
                        <span class="nav-icon">📋</span>
                        <span>Assignments</span>
                    </button>
                    <button wire:click="setTab('quizzes')" type="button" class="nav-link {{ $activeTab === 'quizzes' ? 'active' : '' }}">
                        <span class="nav-icon">🧠</span>
                        <span>Quizzes</span>
                    </button>
                    <button wire:click="setTab('grades')" type="button" class="nav-link {{ $activeTab === 'grades' ? 'active' : '' }}">
                        <span class="nav-icon">🏆</span>
                        <span>Grades</span>
                    </button>
                </nav>

                <!-- Active Enrolled Schools -->
                <div class="nav-group-label">Enrolled Schools</div>
                <div class="ctx-list">
                    @if($allContexts)
                        @foreach($allContexts as $schoolGroup)
                            @foreach($schoolGroup['contexts'] as $ctx)
                                @php $isActive = $activeContext && $activeContext['key'] === $ctx['key']; @endphp
                                <div wire:click="switchContext('{{ $ctx['key'] }}')" class="ctx-item {{ $isActive ? 'active' : '' }}">
                                    <span>🏫 {{ Str::limit($schoolGroup['school']->name, 16) }}</span>
                                    <span style="font-size: 0.72rem; opacity: 0.85;">{{ $ctx['grade']->name ?? '' }}</span>
                                </div>
                            @endforeach
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- User Card Bottom -->
            <div class="sidebar-user-card">
                <div class="user-avatar">
                    {{ strtoupper(substr($firstName, 0, 1)) }}
                </div>
                <div class="user-info">
                    <div class="user-name">{{ $student?->user?->name ?? 'Student' }}</div>
                    <div class="user-role">{{ $activeGradeName }} Student</div>
                </div>
                <button wire:click="logout" type="button" class="btn-logout-icon" title="Sign Out">
                    🚪
                </button>
            </div>
        </aside>

        <!-- ── CUSTOM MAIN LIGHT WORKSPACE ── -->
        <main class="custom-workspace">
            
            <!-- Topbar -->
            <header class="custom-topbar">
                <div class="topbar-title">
                    @if($activeTab === 'classes')
                        <h1>My Classes & Learning Modules 📖</h1>
                        <p>Browse your enrolled subjects, teachers, and course materials.</p>
                    @elseif($activeTab === 'assignments')
                        <h1>Assignments & Submissions 📋</h1>
                        <p>Track your homework deadlines, view scores, and submit completed tasks.</p>
                    @elseif($activeTab === 'quizzes')
                        <h1>Quizzes & Exam Practice 🧠</h1>
                        <p>Review quiz attempts, test results, and test your subject knowledge.</p>
                    @elseif($activeTab === 'grades')
                        <h1>Grades & Report Card 🏆</h1>
                        <p>Comprehensive summary of your academic progress and subject performance.</p>
                    @else
                        <h1>Welcome back, {{ $firstName }}! 👋</h1>
                        <p>{{ now()->format('l, F j, Y') }} · Have a great study session today!</p>
                    @endif
                </div>

                <div class="topbar-actions">
                    <div class="active-context-badge">
                        📍 {{ $activeSchoolName }} › {{ $activeGradeName }}
                    </div>
                    <button wire:click="logout" type="button" class="btn-signout-top">
                        🚪 Sign Out
                    </button>
                </div>
            </header>

            <!-- Workspace Main Content by Selected Tab -->
            <div class="workspace-content">
                
                {{-- ── TAB 1: DASHBOARD OVERVIEW (BY AGE CATEGORY) ── --}}
                @if($activeTab === 'dashboard')
                    @if($tier === 'kids')
                        {{-- Age 5 to 10: Kids Dashboard --}}
                        @include('filament.student.pages.kids-dashboard', [
                            'student'       => $student,
                            'firstName'     => $firstName,
                            'activeContext' => $activeContext,
                            'allContexts'   => $allContexts,
                        ])
                    @elseif($tier === 'junior')
                        {{-- Age 11 to 15: Teens Dashboard --}}
                        @include('filament.student.pages.junior-dashboard', [
                            'student'       => $student,
                            'firstName'     => $firstName,
                            'activeContext' => $activeContext,
                            'allContexts'   => $allContexts,
                        ])
                    @else
                        {{-- Age 16+: Seniors Dashboard --}}
                        @include('filament.student.pages.senior-dashboard', [
                            'student'       => $student,
                            'firstName'     => $firstName,
                            'activeContext' => $activeContext,
                            'allContexts'   => $allContexts,
                        ])
                    @endif
                
                {{-- ── TAB 2: MY CLASSES ── --}}
                @elseif($activeTab === 'classes')
                    <div class="glass-card">
                        <div class="glass-card-title">
                            <span>📖 Enrolled Classes in {{ $activeSchoolName }} ({{ $activeGradeName }})</span>
                            <span style="font-size: 0.85rem; color: #7c3aed; font-weight: 700;">{{ $activeClasses->count() }} Active Courses</span>
                        </div>

                        @if($activeClasses->isEmpty())
                            <div style="color: #64748b; font-size: 0.95rem; text-align: center; padding: 3rem;">
                                No enrolled classes found for this school context.
                            </div>
                        @else
                            <div class="classes-grid">
                                @foreach($activeClasses as $class)
                                    @php
                                        $lessonCount  = $class->lessons()->count();
                                        $assignCount  = $class->assignments()->where('is_published', true)->count();
                                        $teacherName  = $class->teachers->first()?->user?->name ?? 'Course Instructor';
                                    @endphp
                                    <div class="class-card">
                                        <div style="display:flex; align-items:center; justify-content:space-between;">
                                            <div class="class-card-icon">📘</div>
                                            <span class="status-pill status-ok">Active</span>
                                        </div>
                                        <div>
                                            <h3 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0 0 0.35rem 0;">{{ $class->name }}</h3>
                                            <p style="font-size: 0.85rem; color: #64748b; margin: 0; font-weight: 600;">👨‍🏫 Instructor: {{ $teacherName }}</p>
                                        </div>
                                        <div style="display:flex; align-items:center; justify-content:space-between; font-size: 0.82rem; color: #475569; font-weight: 700; background: #f8fafc; padding: 0.6rem 0.85rem; border-radius: 0.75rem; border: 1px solid #e2e8f0;">
                                            <span>📚 {{ $lessonCount }} Lessons</span>
                                            <span>📋 {{ $assignCount }} Assignments</span>
                                        </div>
                                        <button wire:click="launchAssignment(null)" type="button" class="btn-action-start" style="width: 100%; text-align: center; padding: 0.7rem; font-size: 0.85rem;">
                                            View Lessons & Materials 🚀
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                {{-- ── TAB 3: ASSIGNMENTS ── --}}
                @elseif($activeTab === 'assignments')
                    <div class="glass-card">
                        <div class="glass-card-title">
                            <span>📋 Homework & Assignments List</span>
                            <span style="font-size: 0.85rem; color: #dc2626; font-weight: 700;">{{ $allAssignments->count() }} Total Tasks</span>
                        </div>

                        @if($allAssignments->isEmpty())
                            <div style="color: #16a34a; font-weight: 700; text-align: center; padding: 3rem; font-size: 1rem;">
                                🎉 You currently have no assignments assigned!
                            </div>
                        @else
                            <table class="custom-table">
                                <thead>
                                    <tr>
                                        <th>Assignment Title</th>
                                        <th>Subject / Class</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allAssignments as $assignment)
                                        @php
                                            $isSubmitted = AssignmentSubmission::where('student_id', $student?->id)->where('assignment_id', $assignment->id)->exists();
                                            $dueDate = $assignment->end_at ? \Carbon\Carbon::parse($assignment->end_at) : null;
                                        @endphp
                                        <tr>
                                            <td class="class-title">{{ $assignment->title }}</td>
                                            <td class="teacher-name">{{ $assignment->learningClass->name ?? 'General Class' }}</td>
                                            <td style="color: #475569; font-weight: 600;">
                                                {{ $dueDate ? $dueDate->format('M j, Y') : 'No Deadline' }}
                                            </td>
                                            <td>
                                                @if($isSubmitted)
                                                    <span class="status-pill status-ok">Submitted</span>
                                                @else
                                                    <span class="status-pill status-pending">Pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button wire:click="launchAssignment({{ $assignment->id }})" type="button" class="btn-action-start">
                                                    {{ $isSubmitted ? 'View Submission 📄' : 'Submit Homework 🚀' }}
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>

                {{-- ── TAB 4: QUIZZES ── --}}
                @elseif($activeTab === 'quizzes')
                    <div class="glass-card">
                        <div class="glass-card-title">
                            <span>🧠 Quiz History & Results</span>
                            <span style="font-size: 0.85rem; color: #7c3aed; font-weight: 700;">Average Score: {{ $quizAvgPct }}%</span>
                        </div>

                        @if($quizAttempts->isEmpty())
                            <div style="color: #64748b; font-size: 0.95rem; text-align: center; padding: 3rem;">
                                No quiz attempts recorded yet. Take your first quiz to track your progress!
                            </div>
                        @else
                            <table class="custom-table">
                                <thead>
                                    <tr>
                                        <th>Quiz Title</th>
                                        <th>Completion Date</th>
                                        <th>Score %</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($quizAttempts as $attempt)
                                        <tr>
                                            <td class="class-title">{{ $attempt->quiz->title ?? 'Subject Quiz' }}</td>
                                            <td class="teacher-name">{{ $attempt->completed_at?->format('M j, Y · g:i A') ?? '—' }}</td>
                                            <td style="font-size: 1.15rem; font-weight: 800; color: {{ $attempt->is_passed ? '#15803d' : '#b91c1c' }};">
                                                {{ round($attempt->percentage) }}%
                                            </td>
                                            <td>
                                                <span class="status-pill {{ $attempt->is_passed ? 'status-ok' : 'status-pending' }}">
                                                    {{ $attempt->is_passed ? '✓ PASSED' : '✗ NEEDS IMPROVEMENT' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>

                {{-- ── TAB 5: GRADES ── --}}
                @elseif($activeTab === 'grades')
                    <div style="display: flex; flex-direction: column; gap: 1.75rem;">
                        
                        <!-- Grade Overview Cards -->
                        <div class="kpi-grid">
                            <div class="kpi-card kpi-card-purple">
                                <div class="kpi-header">
                                    <span class="kpi-label">OVERALL AVERAGE</span>
                                    <span class="kpi-icon">🏆</span>
                                </div>
                                <div class="kpi-value">{{ $quizAvgPct }}%</div>
                                <div class="kpi-sub">Academic Grade: {{ $quizAvgPct >= 75 ? 'A (Distinction)' : 'B (Credit)' }}</div>
                            </div>

                            <div class="kpi-card kpi-card-emerald">
                                <div class="kpi-header">
                                    <span class="kpi-label">PASSED QUIZZES</span>
                                    <span class="kpi-icon">✓</span>
                                </div>
                                <div class="kpi-value">{{ $quizPassed }}/{{ $quizAttempts->count() }}</div>
                                <div class="kpi-sub">Tests Cleared</div>
                            </div>

                            <div class="kpi-card kpi-card-blue">
                                <div class="kpi-header">
                                    <span class="kpi-label">SUBMISSIONS</span>
                                    <span class="kpi-icon">📄</span>
                                </div>
                                <div class="kpi-value">{{ $submittedCount }}</div>
                                <div class="kpi-sub">Assignments Turned In</div>
                            </div>

                            <div class="kpi-card kpi-card-amber">
                                <div class="kpi-header">
                                    <span class="kpi-label">STATUS</span>
                                    <span class="kpi-icon">🌟</span>
                                </div>
                                <div class="kpi-value">Good</div>
                                <div class="kpi-sub">Active Student</div>
                            </div>
                        </div>

                        <!-- Grades Breakdown Table -->
                        <div class="glass-card">
                            <div class="glass-card-title">
                                <span>🏆 Subject Grades & Academic Report</span>
                            </div>

                            <table class="custom-table">
                                <thead>
                                    <tr>
                                        <th>Class Name</th>
                                        <th>Teacher</th>
                                        <th>Assignments Done</th>
                                        <th>Subject Performance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activeClasses as $class)
                                        @php
                                            $pendingCount = $class->assignments()
                                                ->where('is_published', true)
                                                ->whereDoesntHave('submissions', fn($q) => $q->where('student_id', $student?->id))
                                                ->count();
                                        @endphp
                                        <tr>
                                            <td class="class-title">{{ $class->name }}</td>
                                            <td class="teacher-name">{{ $class->teachers->first()?->user?->name ?? '—' }}</td>
                                            <td style="color: #475569; font-weight: 600;">
                                                {{ $pendingCount === 0 ? 'All Completed ✅' : $pendingCount . ' Pending Tasks' }}
                                            </td>
                                            <td>
                                                <span class="status-pill status-ok">Satisfactory</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>
                @endif

            </div>

        </main>

    </div>
</div>
