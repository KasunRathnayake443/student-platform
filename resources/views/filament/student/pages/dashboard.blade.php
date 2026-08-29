@php
    use Illuminate\Support\Carbon;
    use App\Models\AssignmentSubmission;

    $activeClasses    = $activeContext ? $activeContext['classes'] : collect();
    $activeSchoolName = $activeContext ? ($activeContext['school']->name ?? 'School') : 'School';
    $activeGradeName  = $activeContext ? ($activeContext['grade']->name  ?? 'Grade')  : 'Grade';

    // All enrolled classes across EVERY school + grade (categorized for the My Classes tab / filters)
    $allClassGroups = collect();
    $allClasses     = collect();
    $classSchoolMap = [];
    if ($allContexts) {
        foreach ($allContexts as $schoolGroup) {
            $gradeGroups = [];
            foreach ($schoolGroup['contexts'] as $ctx) {
                $ctxClasses = $ctx['classes'] ?? collect();
                if ($ctxClasses->isNotEmpty()) {
                    $gradeGroups[] = ['grade' => $ctx['grade'], 'classes' => $ctxClasses];
                    foreach ($ctxClasses as $cls) {
                        $allClasses[] = $cls;
                        $classSchoolMap[$cls->id] = $schoolGroup['school']->name ?? '';
                    }
                }
            }
            if ($gradeGroups) {
                $allClassGroups->push(['school' => $schoolGroup['school'], 'groups' => collect($gradeGroups)]);
            }
        }
        $allClasses = $allClasses->keyBy('id')->values();
    }

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

    // Current class filter (set via class-card buttons or the filter dropdown)
    $classFilterId = ! blank($activeClassFilterId) ? (int) $activeClassFilterId : null;
    $filteredClass = $classFilterId ? $allClasses->firstWhere('id', $classFilterId) : null;

    // Assignments (across all schools)
    $allAssignments = collect();
    $assignmentsByClassId = [];
    foreach ($allClasses as $class) {
        $list = $class->assignments()
            ->with(['learningClass', 'teacher.user', 'attachments'])
            ->where('is_published', true)
            ->get()
            ->sortBy(fn ($a) => $a->end_at ? $a->end_at->timestamp : PHP_INT_MAX)
            ->values();
        $assignmentsByClassId[$class->id] = $list;
        $allAssignments = $allAssignments->merge($list);
    }
    $allAssignments = $allAssignments
        ->sortBy(fn ($a) => $a->end_at ? $a->end_at->timestamp : PHP_INT_MAX)
        ->values();

    // Pending (not yet submitted) assignments
    $pendingAssignments = collect();
    foreach ($allClasses as $class) {
        $pend = $class->assignments()
            ->where('is_published', true)
            ->whereDoesntHave('submissions', fn ($q) => $q->where('student_id', $student?->id))
            ->orderBy('end_at')
            ->get();
        $pendingAssignments = $pendingAssignments->merge($pend);
    }
    $pendingAssignments = $pendingAssignments->sortBy('end_at');

    // Submitted count
    $submittedCount = 0;
    $studentSubmissionMap = [];
    if ($student && $allAssignments->count()) {
        foreach (AssignmentSubmission::where('student_id', $student->id)
            ->whereIn('assignment_id', $allAssignments->pluck('id'))
            ->with(['attachments', 'grader.user', 'assignment'])
            ->get() as $sub) {
                $studentSubmissionMap[$sub->assignment_id] = $sub;
            }
        $submittedCount = count($studentSubmissionMap);
    }

    $classHasAssignments = [];
    foreach ($allClasses as $class) {
        $classHasAssignments[$class->id] = ($classFilterId === null || (int) $class->id === $classFilterId)
            && ($assignmentsByClassId[$class->id] ?? collect())->isNotEmpty();
    }

    // Lessons across all enrolled classes (published only), kept per class for grouped render
    $allLessons = collect();
    $lessonsByClassId = [];
    foreach ($allClasses as $class) {
        $published = $class->lessons()
            ->with(['attachments', 'learningClass', 'teacher.user'])
            ->where('is_published', true)
            ->get()
            ->sortBy('sort_order')
            ->values();
        $lessonsByClassId[$class->id] = $published;
        $allLessons = $allLessons->merge($published);
    }
    $allLessons = $allLessons->sortBy('sort_order')->values();
    if ($classFilterId) {
        $allLessons = $allLessons->where('learning_class_id', $classFilterId)->values();
    }
    $classHasLessons = [];
    foreach ($allClasses as $class) {
        $classHasLessons[$class->id] = ($classFilterId === null || (int) $class->id === $classFilterId)
            && ($lessonsByClassId[$class->id] ?? collect())->isNotEmpty();
    }

    // Assignments / quiz attempts honouring the active class filter
    $filteredAssignments = $classFilterId
        ? $allAssignments->where('learning_class_id', $classFilterId)->values()
        : $allAssignments;
    $filteredQuizAttempts = $classFilterId
        ? $quizAttempts->filter(fn ($a) => $a->quiz && (int) $a->quiz->learning_class_id === $classFilterId)
        : $quizAttempts;
@endphp

<div x-data="{ openClassId: null, openAssignmentId: null }">
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    /* Reset Page Container - Modern Light Theme */
    html, body, .fi-layout, .fi-main {
        margin: 0;
        padding: 0;
        width: 100%;
        height: 100vh !important;
        max-height: 100vh !important;
        overflow: hidden !important;
        background-color: #f8fafc;
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        color: #334155;
    }

    .custom-app-container {
        display: flex;
        height: 100vh;
        max-height: 100vh;
        width: 100%;
        background: #f8fafc;
        overflow: hidden;
    }

    /* ── CUSTOM LIGHT SIDEBAR (Fixed Left Navigation) ── */
    .custom-sidebar {
        width: 260px;
        height: 100vh;
        max-height: 100vh;
        background: #ffffff;
        border-right: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 1.5rem 1.15rem;
        flex-shrink: 0;
        z-index: 20;
        box-sizing: border-box;
    }

    .sidebar-scrollable-content {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 0.25rem;
        scrollbar-width: thin;
        scrollbar-color: #c4b5fd transparent;
    }
    .sidebar-scrollable-content::-webkit-scrollbar { width: 5px; }
    .sidebar-scrollable-content::-webkit-scrollbar-track { background: transparent; }
    .sidebar-scrollable-content::-webkit-scrollbar-thumb { background: rgba(124, 58, 237, 0.35); border-radius: 999px; }

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
        gap: 0.75rem;
        margin-top: 0.25rem;
    }
    .ctx-school-group {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .ctx-school-name {
        font-size: 0.78rem;
        font-weight: 800;
        color: #475569;
        padding: 0 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .ctx-grades {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .ctx-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.55rem 0.85rem 0.55rem 1.5rem;
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

    /* User Card Bottom Left */
    .sidebar-user-card {
        padding: 0.9rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.85rem;
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
        margin-top: 1rem;
        flex-shrink: 0;
    }
    .user-profile-row {
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }
    .user-avatar {
        width: 2.2rem;
        height: 2.2rem;
        border-radius: 50%;
        background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        color: #ffffff;
        font-size: 0.85rem;
        box-shadow: 0 4px 10px rgba(124, 58, 237, 0.25);
        flex-shrink: 0;
    }
    .user-avatar-img {
        width: 2.2rem;
        height: 2.2rem;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #ffffff;
        box-shadow: 0 4px 10px rgba(124, 58, 237, 0.3);
        flex-shrink: 0;
    }
    .user-info {
        flex: 1;
        min-width: 0;
    }
    .user-name {
        font-size: 0.85rem;
        font-weight: 700;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .user-role {
        font-size: 0.72rem;
        color: #64748b;
        font-weight: 600;
    }

    .btn-sidebar-signout {
        width: 100%;
        padding: 0.5rem 0.75rem;
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #ef4444;
        border-radius: 0.6rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.8rem;
        font-weight: 700;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .btn-sidebar-signout:hover {
        background: #dc2626;
        color: #ffffff;
        border-color: #dc2626;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
    }

    /* ── MAIN LIGHT WORKSPACE (Scrollable Right Main Workspace) ── */
    .custom-workspace {
        flex: 1;
        height: 100vh;
        max-height: 100vh;
        display: flex;
        flex-direction: column;
        min-width: 0;
        overflow-y: auto;
        overflow-x: hidden;
        background: #f8fafc;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }
    .custom-workspace::-webkit-scrollbar { width: 6px; }
    .custom-workspace::-webkit-scrollbar-track { background: transparent; }
    .custom-workspace::-webkit-scrollbar-thumb { background: rgba(100, 116, 139, 0.35); border-radius: 999px; }

    /* Topbar Header */
    .custom-topbar {
        padding: 1.5rem 2.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
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
        gap: 0.45rem;
        padding: 0.55rem 1.15rem;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 9999px;
        color: #dc2626;
        font-weight: 700;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .btn-signout-top:hover {
        background: #dc2626;
        color: #ffffff;
        border-color: #dc2626;
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(220, 38, 38, 0.3);
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
    .status-warn { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    .status-bad { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }

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

    /* Class Card Action Buttons (Open Lessons / Assignments / Quizzes) */
    .class-stats {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        font-size: 0.82rem;
        color: #475569;
        font-weight: 700;
        background: #f8fafc;
        padding: 0.6rem 0.85rem;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
    }
    .class-actions {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.6rem;
    }
    .class-action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        padding: 0.55rem 0.35rem;
        border-radius: 1rem;
        font-weight: 800;
        border: 2px solid;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .class-action-emoji {
        font-size: 1.05rem;
        line-height: 1;
    }
    .class-action-label {
        font-size: 0.72rem;
        font-weight: 800;
        line-height: 1.25;
        text-align: center;
    }
    .class-action-lesson { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
    .class-action-lesson:hover { background: #dbeafe; transform: translateY(-2px); box-shadow: 0 6px 16px rgba(37, 99, 235, 0.2); }
    .class-action-assign { background: #fefce8; border-color: #fde68a; color: #a16207; }
    .class-action-assign:hover { background: #fef9c3; transform: translateY(-2px); box-shadow: 0 6px 16px rgba(217, 119, 6, 0.2); }
    .class-action-quiz { background: #f5f3ff; border-color: #ddd6fe; color: #7c3aed; }
    .class-action-quiz:hover { background: #ede9fe; transform: translateY(-2px); box-shadow: 0 6px 16px rgba(124, 58, 237, 0.2); }
    .class-action-none {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        color: #94a3b8;
        font-weight: 700;
        text-align: center;
        padding: 0.55rem 0.3rem;
        border: 2px dashed #e2e8f0;
        border-radius: 1rem;
    }

    /* All Classes grouped by school + grade */
    .school-class-block {
        display: flex;
        flex-direction: column;
        gap: 1.75rem;
    }
    .school-class-block + .school-class-block {
        padding-top: 1.75rem;
        border-top: 2px dashed #e2e8f0;
    }
    .school-class-header {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-size: 1.2rem;
        font-weight: 800;
        color: #0f172a;
        flex-wrap: wrap;
    }
    .school-class-count {
        font-size: 0.75rem;
        font-weight: 800;
        background: #f5f3ff;
        color: #7c3aed;
        border: 1px solid #ddd6fe;
        border-radius: 999px;
        padding: 0.15rem 0.7rem;
    }
    .grade-class-block {
        display: flex;
        flex-direction: column;
        gap: 0.9rem;
    }
    .grade-class-header {
        font-size: 0.92rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    /* Tab Filter Bar (Lessons / Assignments / Quizzes) */
    .tab-filter-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .tab-filter-label {
        font-size: 0.85rem;
        font-weight: 800;
        color: #475569;
    }
    .tab-filter-select {
        padding: 0.6rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 0.85rem;
        font-size: 0.9rem;
        font-weight: 700;
        color: #0f172a;
        background: #f8fafc;
        cursor: pointer;
        outline: none;
        min-width: 220px;
        transition: all 0.2s;
    }
    .tab-filter-select:focus {
        border-color: #7c3aed;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.12);
    }
    .tab-filter-empty {
        text-align: center;
        padding: 2.5rem 1rem;
        color: #64748b;
        font-size: 0.95rem;
        font-weight: 600;
    }

    /* Lessons Tab Cards */
    .lesson-list {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    .lesson-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .lesson-item-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .lesson-class-badge {
        font-size: 0.75rem;
        font-weight: 800;
        background: #ede9fe;
        color: #7c3aed;
        padding: 0.25rem 0.65rem;
        border-radius: 9999px;
        white-space: nowrap;
    }
    .lesson-date {
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 600;
    }
    .lesson-item h3 {
        font-size: 1.1rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }
    .lesson-desc {
        font-size: 0.9rem;
        color: #475569;
        margin: 0;
        line-height: 1.5;
        font-weight: 500;
    }
    .lesson-content {
        font-size: 0.9rem;
        color: #334155;
        background: #ffffff;
        padding: 0.85rem;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        line-height: 1.6;
    }
    .lesson-attachments {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
        margin-top: 0.25rem;
    }
    .lesson-attachments .attach-label {
        font-size: 0.78rem;
        font-weight: 800;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .attach-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .attach-link {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #0f172a;
        font-weight: 700;
        font-size: 0.8rem;
        padding: 0.4rem 0.75rem;
        border-radius: 0.65rem;
        text-decoration: none;
    }
    .lesson-video-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #2563eb;
        font-weight: 700;
        font-size: 0.82rem;
        padding: 0.45rem 0.85rem;
        border-radius: 0.65rem;
        text-decoration: none;
        align-self: flex-start;
    }
    .lesson-class-group {
        display: flex;
        flex-direction: column;
        gap: 0.9rem;
        background: #fdfaff;
        border: 1px dashed #ddd6fe;
        border-radius: 1.1rem;
        padding: 1.1rem;
    }
    .lesson-class-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .lesson-class-title {
        font-size: 1rem;
        font-weight: 800;
        color: #4c1d95;
        letter-spacing: 0.01em;
    }
    .lesson-class-meta {
        font-size: 0.75rem;
        font-weight: 800;
        background: #ede9fe;
        color: #7c3aed;
        padding: 0.25rem 0.7rem;
        border-radius: 9999px;
        white-space: nowrap;
    }
    .lesson-open-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        align-self: flex-start;
        background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
        color: #ffffff;
        font-weight: 800;
        font-size: 0.84rem;
        padding: 0.55rem 1.1rem;
        border-radius: 9999px;
        text-decoration: none;
        box-shadow: 0 6px 16px -6px rgba(124, 58, 237, 0.6);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .lesson-open-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 20px -6px rgba(124, 58, 237, 0.7);
        color: #ffffff;
    }
    .assignment-class-group {
        display: flex;
        flex-direction: column;
        gap: 0.9rem;
        background: #fffbfa;
        border: 1px dashed #fecaca;
        border-radius: 1.1rem;
        padding: 1.1rem;
    }
    .assignment-class-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .assignment-class-title {
        font-size: 1rem;
        font-weight: 800;
        color: #9f1239;
        letter-spacing: 0.01em;
    }
    .assignment-class-meta {
        font-size: 0.75rem;
        font-weight: 800;
        background: #ffe4e6;
        color: #be123c;
        padding: 0.25rem 0.7rem;
        border-radius: 9999px;
        white-space: nowrap;
    }
    .assignment-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .assignment-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .assignment-item-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .assignment-status-pill {
        font-size: 0.75rem;
        font-weight: 800;
        padding: 0.3rem 0.75rem;
        border-radius: 9999px;
        white-space: nowrap;
    }
    .assignment-date {
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 600;
    }
    .assignment-item h3 {
        font-size: 1.1rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }
    .assignment-desc {
        font-size: 0.9rem;
        color: #475569;
        margin: 0;
        line-height: 1.5;
        font-weight: 500;
    }
    .assignment-meta-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .assignment-meta-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        background: #ffe4e6;
        border: 1px solid #fecdd3;
        color: #9f1239;
        font-weight: 700;
        font-size: 0.75rem;
        padding: 0.3rem 0.7rem;
        border-radius: 9999px;
    }
    .assignment-open-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        align-self: flex-start;
        background: linear-gradient(135deg, #e11d48 0%, #f43f5e 100%);
        color: #ffffff;
        font-weight: 800;
        font-size: 0.84rem;
        padding: 0.55rem 1.1rem;
        border-radius: 9999px;
        text-decoration: none;
        box-shadow: 0 6px 16px -6px rgba(225, 29, 72, 0.55);
        transition: transform 0.15s ease, box-shadow 0.15s ease, color 0.15s ease;
    }
    .assignment-open-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 20px -6px rgba(225, 29, 72, 0.65);
        color: #ffffff;
    }

    /* ── Profile ── */
    .profile-wrap {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    .profile-alert {
        padding: 1rem 1.25rem;
        border-radius: 1rem;
        font-weight: 700;
        font-size: 0.95rem;
        border: 1px solid;
    }
    .profile-alert-success { background: #ecfdf5; border-color: #a7f3d0; color: #047857; }
    .profile-alert-error { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
    .profile-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }
    @media (max-width: 850px) { .profile-grid { grid-template-columns: 1fr; } }
    .profile-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1.5rem;
        padding: 1.75rem;
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.04);
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    .profile-card-head {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .profile-avatar {
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 50%;
        background: linear-gradient(135deg, #7c3aed, #6366f1);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.4rem;
        flex-shrink: 0;
        box-shadow: 0 8px 20px rgba(124, 58, 237, 0.25);
    }
    .profile-avatar-lock { background: linear-gradient(135deg, #d97706, #f59e0b); box-shadow: 0 8px 20px rgba(245, 158, 11, 0.25); }
    .profile-card-head h3 { margin: 0; font-size: 1.15rem; font-weight: 800; color: #0f172a; }
    .profile-card-head p { margin: 0.2rem 0 0; font-size: 0.85rem; color: #64748b; font-weight: 500; }
    .profile-card form { display: flex; flex-direction: column; gap: 1rem; }
    .profile-form { display: flex; flex-direction: column; gap: 1rem; }
    .profile-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    @media (max-width: 600px) { .profile-field-row { grid-template-columns: 1fr; } }
    .profile-field { display: flex; flex-direction: column; gap: 0.45rem; }
    .profile-field span { font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: #475569; }
    .profile-field input,
    .profile-field select {
        padding: 0.75rem 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.85rem;
        font-size: 0.95rem;
        background: #f8fafc;
        color: #0f172a;
        outline: none;
        transition: all 0.2s;
    }
    .profile-field input:focus,
    .profile-field select:focus { border-color: #7c3aed; background: #ffffff; box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.12); }
    .profile-error { color: #dc2626; font-size: 0.78rem; font-weight: 600; }
    .profile-photo-btn {
        display: inline-block;
        padding: 0.7rem 1.4rem;
        background: #f5f3ff;
        border: 2px dashed #c084fc;
        color: #7c3aed;
        border-radius: 999px;
        font-weight: 800;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s;
        align-self: flex-start;
    }
    .profile-photo-btn:hover { background: #ede9fe; transform: scale(1.02); }
    .profile-upload-progress { font-size: 0.8rem; color: #7c3aed; font-weight: 700; }
    .profile-grid-3 {
        display: grid;
        grid-template-columns: 1.2fr 2fr;
        gap: 1.5rem;
        align-items: start;
    }
    @media (max-width: 850px) { .profile-grid-3 { grid-template-columns: 1fr; } }
    .profile-save-btn {
        align-self: flex-start;
        padding: 0.7rem 1.5rem;
        background: linear-gradient(135deg, #7c3aed, #6366f1);
        color: #ffffff;
        border: none;
        border-radius: 0.85rem;
        font-weight: 800;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 6px 18px rgba(124, 58, 237, 0.3);
    }
    .profile-save-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(124, 58, 237, 0.4); }
    .profile-note {
        padding: 1rem 1.25rem;
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 1rem;
        color: #92400e;
        font-size: 0.88rem;
        font-weight: 600;
        line-height: 1.5;
    }

    /* Kids profile overrides */
    .kids-mode .profile-card { border: 3px solid #e9d5ff; border-radius: 1.75rem; }
    .kids-mode .profile-card-head h3 { font-size: 1.3rem; }
    .kids-mode .profile-field input { border-radius: 1rem; border-width: 2px; font-size: 1rem; }
    .kids-mode .profile-save-btn { border-radius: 999px; font-size: 1rem; padding: 0.75rem 1.75rem; }

    .kids-mode .tab-filter-select {
        border: 3px solid #d8b4fe;
        border-radius: 9999px;
        background: #ffffff;
        font-size: 1rem;
        color: #6b21a8;
    }
    .kids-mode .class-action-btn {
        border-radius: 1.25rem;
        padding: 0.65rem 0.4rem;
    }
    .kids-mode .class-action-emoji { font-size: 1.25rem; }
    .kids-mode .class-action-label { font-size: 0.85rem; }
    .kids-mode .class-action-none { border-radius: 1.25rem; padding: 0.65rem 0.4rem; }
    .kids-mode .school-class-header { font-size: 1.35rem; color: #6b21a8; }
    .kids-mode .grade-class-header { color: #a855f7; font-size: 1rem; }
    .kids-mode .lesson-item {
        border: 3px solid #e9d5ff;
        border-radius: 1.25rem;
    }
    .kids-mode .lesson-item h3 { font-size: 1.2rem; }
    .kids-mode .lesson-class-badge { font-size: 0.85rem; }

    /* ══════════════════════════════════════════════════════════════
       KIDS MODE (Age 5-10): Big, bold, playful, colorful
       ══════════════════════════════════════════════════════════════ */

    /* Kids Sidebar */
    .kids-mode .custom-sidebar {
        width: 250px;
        background: linear-gradient(180deg, #faf5ff 0%, #f0e7ff 40%, #fce7f3 100%);
        border-right: 3px solid #e9d5ff;
        padding: 1.25rem 1rem;
    }
    .kids-mode .brand-header {
        border-bottom: 3px dashed #d8b4fe;
        padding-bottom: 1rem;
        margin-bottom: 0.35rem;
    }
    .kids-mode .brand-icon {
        width: 3.1rem;
        height: 3.1rem;
        background: linear-gradient(135deg, #f472b6, #c084fc, #60a5fa);
        border-radius: 1.1rem;
        font-size: 1.6rem;
        box-shadow: 0 6px 20px rgba(244, 114, 182, 0.35);
        animation: kids-wiggle 3s ease-in-out infinite;
    }
    @keyframes kids-wiggle {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(-5deg); }
        75% { transform: rotate(5deg); }
    }
    .kids-mode .brand-title {
        font-size: 1.2rem;
        font-weight: 900;
        background: linear-gradient(135deg, #7c3aed, #db2777);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .kids-mode .brand-sub {
        font-size: 0.78rem;
        color: #a855f7;
    }

    .kids-mode .nav-group-label {
        font-size: 0.85rem;
        font-weight: 900;
        color: #7c3aed;
        margin: 1.25rem 0 0.6rem 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .kids-mode .nav-menu {
        gap: 0.4rem;
    }
    .kids-mode .nav-link {
        padding: 0.8rem 1rem;
        border-radius: 0.9rem;
        font-size: 1rem;
        font-weight: 800;
        gap: 0.75rem;
        border: 2px solid transparent;
        background: rgba(255, 255, 255, 0.7);
        color: #6b21a8;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .kids-mode .nav-link:hover {
        background: #fff;
        border-color: #c084fc;
        color: #7c3aed;
        transform: translateX(4px) scale(1.02);
        box-shadow: 0 4px 16px rgba(168, 85, 247, 0.15);
    }
    .kids-mode .nav-link.active {
        background: linear-gradient(135deg, #7c3aed, #a855f7);
        color: #fff;
        border-color: #7c3aed;
        box-shadow: 0 6px 24px rgba(124, 58, 237, 0.3);
        transform: scale(1.03);
    }
    .kids-mode .nav-link.active .nav-icon {
        transform: scale(1.2);
    }
    .kids-mode .nav-icon {
        font-size: 1.35rem;
        width: 1.7rem;
        text-align: center;
    }

    .kids-mode .ctx-school-name {
        font-size: 0.9rem;
        color: #7c3aed;
        font-weight: 900;
    }
    .kids-mode .ctx-item {
        padding: 0.55rem 0.85rem 0.55rem 1.4rem;
        border-radius: 0.8rem;
        font-size: 0.9rem;
        font-weight: 700;
        background: rgba(255, 255, 255, 0.6);
        border: 2px solid #e9d5ff;
        color: #6b21a8;
    }
    .kids-mode .ctx-item:hover {
        background: #f5f3ff;
        border-color: #c084fc;
        transform: translateX(3px);
    }
    .kids-mode .ctx-item.active {
        background: linear-gradient(135deg, #ede9fe, #fce7f3);
        border-color: #a855f7;
        color: #7c3aed;
        font-weight: 900;
        box-shadow: 0 2px 12px rgba(168, 85, 247, 0.15);
    }

    /* Kids User Card */
    .kids-mode .sidebar-user-card {
        background: linear-gradient(135deg, #fdf4ff, #fce7f3);
        border: 2px solid #e9d5ff;
        border-radius: 1.25rem;
        padding: 1rem;
    }
    .kids-mode .user-avatar {
        width: 2.6rem;
        height: 2.6rem;
        font-size: 1rem;
        background: linear-gradient(135deg, #f472b6, #c084fc);
        box-shadow: 0 4px 14px rgba(244, 114, 182, 0.3);
    }
    .kids-mode .user-avatar-img {
        width: 2.6rem;
        height: 2.6rem;
    }
    .kids-mode .user-name {
        font-size: 0.95rem;
        font-weight: 900;
        color: #7c3aed;
    }
    .kids-mode .user-role {
        font-size: 0.82rem;
        color: #a855f7;
        font-weight: 700;
    }
    .kids-mode .btn-logout-icon {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.85rem;
        font-size: 1.2rem;
        border: 2px solid #fda4af;
        background: #fff1f2;
    }

    /* Kids Topbar */
    .kids-mode .custom-topbar {
        background: linear-gradient(135deg, #faf5ff 0%, #fdf2f8 50%, #eff6ff 100%);
        border-bottom: 3px solid #e9d5ff;
        padding: 1.75rem 2.5rem;
    }
    .kids-mode .topbar-title h1 {
        font-size: 2rem;
        font-weight: 900;
        background: linear-gradient(135deg, #7c3aed, #db2777);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .kids-mode .topbar-title p {
        font-size: 1.05rem;
        color: #7c3aed;
        font-weight: 600;
    }
    .kids-mode .active-context-badge {
        background: linear-gradient(135deg, #ede9fe, #fce7f3);
        border: 2px solid #c084fc;
        color: #7c3aed;
        font-size: 0.95rem;
        font-weight: 800;
        padding: 0.65rem 1.3rem;
    }
    .kids-mode .btn-signout-top {
        font-size: 1rem;
        font-weight: 800;
        padding: 0.65rem 1.5rem;
        border-radius: 999px;
        border: 2px solid #fda4af;
    }

    /* Kids workspace content — hide topbar, fill edge-to-edge */
    .kids-mode .workspace-content {
        padding: 0;
        max-width: none;
    }
    .kids-mode .workspace-content.kids-tab-space {
        padding: 2.25rem 2.5rem 3.5rem;
    }
    .kids-mode .custom-topbar {
        display: none;
    }

    /* ── Responsive: keep the layout on the screen ── */
    @media (max-width: 1180px) {
        .custom-sidebar, .kids-mode .custom-sidebar { width: 230px; }
    }
    @media (max-width: 860px) {
        .custom-app-container { flex-direction: column; }
        .custom-sidebar, .kids-mode .custom-sidebar {
            width: 100%;
            height: auto;
            max-height: none;
            flex-direction: column;
            padding: 0.75rem 1rem;
            border-right: none;
            border-bottom: 3px solid #e9d5ff;
        }
        .sidebar-scrollable-content {
            width: 100%;
            flex-direction: column;
        }
        .custom-workspace {
            height: auto;
            max-height: none;
            overflow-y: visible;
        }
        .workspace-content { padding: 1.25rem; }
        .kids-mode .workspace-content { padding: 0; }
        .kids-mode .workspace-content.kids-tab-space { padding: 1.5rem 1.25rem 2.5rem; }
        .topbar-title h1 { font-size: 1.3rem; }
    }
    </style>

    <div class="custom-app-container {{ $tier === 'kids' ? 'kids-mode' : '' }}">
        
        <!-- ── CUSTOM LIGHT SIDEBAR ── -->
        <aside class="custom-sidebar">
            <div class="sidebar-scrollable-content">
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
                    <button wire:click="setTab('lessons')" type="button" class="nav-link {{ $activeTab === 'lessons' ? 'active' : '' }}">
                        <span class="nav-icon">📚</span>
                        <span>My Lessons</span>
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
                    <button wire:click="setTab('profile')" type="button" class="nav-link {{ $activeTab === 'profile' ? 'active' : '' }}">
                        <span class="nav-icon">👤</span>
                        <span>My Profile</span>
                    </button>
                </nav>

                <!-- Active Enrolled Schools -->
                <div class="nav-group-label">Enrolled Schools</div>
                <div class="ctx-list">
                    @if($allContexts)
                        @foreach($allContexts as $schoolGroup)
                            <div class="ctx-school-group">
                                <div class="ctx-school-name">🏫 {{ Str::limit($schoolGroup['school']->name, 20) }}</div>
                                <div class="ctx-grades">
                                    @foreach($schoolGroup['contexts'] as $ctx)
                                        @php $isActive = $activeContext && $activeContext['key'] === $ctx['key']; @endphp
                                        <div wire:click="switchContext('{{ $ctx['key'] }}')" class="ctx-item {{ $isActive ? 'active' : '' }}">
                                            <span>{{ $ctx['grade']->name ?? '' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- User Card & Sign Out at Bottom Left -->
            <div class="sidebar-user-card">
                <div class="user-profile-row">
                    @if($student?->profile_photo_url)
                        <img src="{{ $student->profile_photo_url }}" alt="" class="user-avatar-img">
                    @else
                        <div class="user-avatar">
                            {{ strtoupper(substr($firstName, 0, 1)) }}
                        </div>
                    @endif
                    <div class="user-info">
                        <div class="user-name">{{ $student?->user?->name ?? 'Student' }}</div>
                        <div class="user-role">{{ $activeGradeName }} Student</div>
                    </div>
                </div>
                <button wire:click="logout" type="button" class="btn-sidebar-signout" title="Sign Out">
                    Sign Out
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
                    @elseif($activeTab === 'lessons')
                        <h1>My Lessons & Materials 📚</h1>
                        <p>Browse reading materials, videos, and downloads for your classes.</p>
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
                </div>
            </header>

            <!-- Workspace Main Content by Selected Tab -->
            <div class="workspace-content {{ $tier === 'kids' && $activeTab !== 'dashboard' ? 'kids-tab-space' : '' }}">
                
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
                
                {{-- ── TAB 2: MY CLASSES (all schools, grouped) ── --}}
                @elseif($activeTab === 'classes')
                    <div class="glass-card">
                        <div class="glass-card-title">
                            <span>📖 All My Classes</span>
                            <span style="font-size: 0.85rem; color: #7c3aed; font-weight: 700;">{{ $allClasses->count() }} Active Courses · {{ $allClassGroups->count() }} Schools</span>
                        </div>

                        @if($allClasses->isEmpty())
                            <div style="color: #64748b; font-size: 0.95rem; text-align: center; padding: 3rem;">
                                You are not enrolled in any classes yet. Ask your teacher to add you!
                            </div>
                        @else
                            @foreach($allClassGroups as $schoolGroup)
                                <div class="school-class-block">
                                    <div class="school-class-header">
                                        🏫 {{ $schoolGroup['school']->name ?? 'School' }}
                                        <span class="school-class-count">{{ $schoolGroup['groups']->sum(fn ($g) => $g['classes']->count()) }} classes</span>
                                    </div>

                                    @foreach($schoolGroup['groups'] as $gradeGroup)
                                        <div class="grade-class-block">
                                            <div class="grade-class-header">📗 {{ $gradeGroup['grade']->name ?? 'Class Group' }}</div>
                                            <div class="classes-grid">
                                                @foreach($gradeGroup['classes'] as $class)
                                                    @php
                                                        $lessonCount  = $class->lessons()->where('is_published', true)->count();
                                                        $assignCount  = $class->assignments()->where('is_published', true)->count();
                                                        $quizCount    = $class->quizzes()->where('is_published', true)->count();
                                                        $teacherName  = $class->teachers->first()?->user?->name ?? 'Course Instructor';
                                                    @endphp
                                                    <div class="class-card">
                                                        <div style="display:flex; align-items:center; justify-content:space-between;">
                                                            <div class="class-card-icon">📘</div>
                                                            <span class="status-pill status-ok">Active</span>
                                                        </div>
                                                        <div>
                                                            <h3 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0 0 0.35rem 0;">{{ $class->name }}</h3>
                                                            <p style="font-size: 0.85rem; color: #64748b; margin: 0; font-weight: 600; line-height: 1.4;">👨‍🏫 Instructor: {{ $teacherName }}</p>
                                                        </div>
                                                        <div class="class-stats">
                                                            <span>📚 {{ $lessonCount }} Lessons</span>
                                                            <span>📋 {{ $assignCount }} Assignments</span>
                                                            <span>🧠 {{ $quizCount }} Quizzes</span>
                                                        </div>
                                                        <div class="class-actions">
                                                            @if($lessonCount > 0)
                                                                <button wire:click="openTab('lessons', {{ $class->id }})" type="button" class="class-action-btn class-action-lesson">
                                                                    <span class="class-action-emoji">📖</span>
                                                                    <span class="class-action-label">Lessons</span>
                                                                </button>
                                                            @else
                                                                <span class="class-action-none">
                                                                    <span class="class-action-emoji">📖</span>
                                                                    <span class="class-action-label">No lessons yet</span>
                                                                </span>
                                                            @endif
                                                            @if($assignCount > 0)
                                                                <button wire:click="openTab('assignments', {{ $class->id }})" type="button" class="class-action-btn class-action-assign">
                                                                    <span class="class-action-emoji">📋</span>
                                                                    <span class="class-action-label">Assignments</span>
                                                                </button>
                                                            @else
                                                                <span class="class-action-none">
                                                                    <span class="class-action-emoji">📋</span>
                                                                    <span class="class-action-label">No assignments</span>
                                                                </span>
                                                            @endif
                                                            @if($quizCount > 0)
                                                                <button wire:click="openTab('quizzes', {{ $class->id }})" type="button" class="class-action-btn class-action-quiz">
                                                                    <span class="class-action-emoji">🧠</span>
                                                                    <span class="class-action-label">Quizzes</span>
                                                                </button>
                                                            @else
                                                                <span class="class-action-none">
                                                                    <span class="class-action-emoji">🧠</span>
                                                                    <span class="class-action-label">No quizzes</span>
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        @endif
                    </div>

                {{-- ── TAB 3: LESSONS (grouped by school › grade › class) ── --}}
                @elseif($activeTab === 'lessons')
                    <div class="glass-card">
                        <div class="glass-card-title">
                            <span>📚 My Lessons & Materials</span>
                            <span style="font-size: 0.85rem; color: #7c3aed; font-weight: 700;">{{ $allLessons->count() }} Lesson(s)</span>
                        </div>

                        @if($allClasses->count() > 1 || $filteredClass)
                            <div class="tab-filter-bar">
                                <span class="tab-filter-label">🎯 Filter by class</span>
                                <select wire:model.live="activeClassFilterId" class="tab-filter-select">
                                    <option value="">All my classes</option>
                                    @foreach($allClasses as $class)
                                        <option value="{{ $class->id }}" @selected((string) $activeClassFilterId === (string) $class->id)>
                                            {{ $class->name }}{{ ! empty($classSchoolMap[$class->id]) ? ' · ' . $classSchoolMap[$class->id] : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if($allLessons->isEmpty())
                            <div class="tab-filter-empty">
                                @if($filteredClass)
                                    No lessons published yet for <strong>{{ $filteredClass->name }}</strong>. Check back soon!
                                @else
                                    📚 No lessons published yet. Check back soon — your teacher will add study materials here!
                                @endif
                            </div>
                        @else
                            @foreach($allClassGroups as $schoolGroup)
                                @php
                                    $schoolLessonTotal = 0;
                                    foreach ($schoolGroup['groups'] as $sg) {
                                        foreach ($sg['classes'] as $sc) {
                                            if ($classHasLessons[$sc->id] ?? false) {
                                                $schoolLessonTotal += ($lessonsByClassId[$sc->id] ?? collect())->count();
                                            }
                                        }
                                    }
                                @endphp
                                @if($schoolLessonTotal > 0)
                                    <div class="school-class-block">
                                        <div class="school-class-header">
                                            🏫 {{ $schoolGroup['school']->name ?? 'School' }}
                                            <span class="school-class-count">{{ $schoolLessonTotal }} lesson(s)</span>
                                        </div>
                                        @foreach($schoolGroup['groups'] as $gradeGroup)
                                            @php
                                                $gradeLessonTotal = 0;
                                                foreach ($gradeGroup['classes'] as $gc) {
                                                    if ($classHasLessons[$gc->id] ?? false) {
                                                        $gradeLessonTotal += ($lessonsByClassId[$gc->id] ?? collect())->count();
                                                    }
                                                }
                                            @endphp
                                            @if($gradeLessonTotal > 0)
                                                <div class="grade-class-block">
                                                    <div class="grade-class-header">📗 {{ $gradeGroup['grade']->name ?? 'Class Group' }}</div>
                                                    @foreach($gradeGroup['classes'] as $class)
                                                        @if($classHasLessons[$class->id] ?? false)
                                                            <div class="lesson-class-group">
                                                                <div class="lesson-class-head">
                                                                    <span class="lesson-class-title">📘 {{ $class->name }}</span>
                                                                    <span class="lesson-class-meta">
                                                                        {{ $lessonsByClassId[$class->id]->count() }} lesson(s)
                                                                    </span>
                                                                </div>
                                                                <div class="lesson-list">
                                                                    @foreach($lessonsByClassId[$class->id] as $lesson)
                                                                        @php
                                                                            $lessonTeacherName = $lesson->teacher?->user?->name ?? $class->teachers->first()?->user?->name ?? 'Course Teacher';
                                                                        @endphp
                                                                        <div class="lesson-item">
                                                                            <div class="lesson-item-top">
                                                                                <span class="lesson-class-badge">📖 Lesson {{ $loop->iteration }}</span>
                                                                                <span class="lesson-date">{{ $lesson->created_at ? $lesson->created_at->format('M j, Y') : '' }}</span>
                                                                            </div>

                                                                            <h3>{{ $lesson->title }}</h3>

                                                                            <p class="lesson-desc">👨🏫 {{ $lessonTeacherName }}</p>

                                                                            @if($lesson->description)
                                                                                <p class="lesson-desc">{{ \Illuminate\Support\Str::limit(strip_tags($lesson->description), 180) }}</p>
                                                                            @endif

                                                                            <a href="{{ \App\Filament\Student\Pages\LessonView::getUrl(['lesson' => $lesson->id]) }}" class="lesson-open-btn">
                                                                                Read Lesson 📖
                                                                            </a>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>

                {{-- ── TAB 4: ASSIGNMENTS (grouped by school › grade › class) ── --}}
                @elseif($activeTab === 'assignments')
                    <div class="glass-card">
                        <div class="glass-card-title">
                            <span>📋 Homework & Assignments List</span>
                            <span style="font-size: 0.85rem; color: #dc2626; font-weight: 700;">{{ $filteredAssignments->count() }} Task(s)</span>
                        </div>

                        @if($allClasses->count() > 1 || $filteredClass)
                            <div class="tab-filter-bar">
                                <span class="tab-filter-label">🎯 Filter by class</span>
                                <select wire:model.live="activeClassFilterId" class="tab-filter-select">
                                    <option value="">All my classes</option>
                                    @foreach($allClasses as $class)
                                        <option value="{{ $class->id }}" @selected((string) $activeClassFilterId === (string) $class->id)>
                                            {{ $class->name }}{{ ! empty($classSchoolMap[$class->id]) ? ' · ' . $classSchoolMap[$class->id] : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if($filteredAssignments->isEmpty())
                            <div class="tab-filter-empty">
                                @if($filteredClass)
                                    🎉 No assignments in <strong>{{ $filteredClass->name }}</strong> right now!
                                @else
                                    🎉 You currently have no assignments assigned!
                                @endif
                            </div>
                        @else
                            @foreach($allClassGroups as $schoolGroup)
                                @php
                                    $schoolAssignTotal = 0;
                                    foreach ($schoolGroup['groups'] as $sg) {
                                        foreach ($sg['classes'] as $sc) {
                                            if ($classHasAssignments[$sc->id] ?? false) {
                                                $schoolAssignTotal += ($assignmentsByClassId[$sc->id] ?? collect())->count();
                                            }
                                        }
                                    }
                                @endphp
                                @if($schoolAssignTotal > 0)
                                    <div class="school-class-block">
                                        <div class="school-class-header">
                                            🏫 {{ $schoolGroup['school']->name ?? 'School' }}
                                            <span class="school-class-count">{{ $schoolAssignTotal }} task(s)</span>
                                        </div>
                                        @foreach($schoolGroup['groups'] as $gradeGroup)
                                            @php
                                                $gradeAssignTotal = 0;
                                                foreach ($gradeGroup['classes'] as $gc) {
                                                    if ($classHasAssignments[$gc->id] ?? false) {
                                                        $gradeAssignTotal += ($assignmentsByClassId[$gc->id] ?? collect())->count();
                                                    }
                                                }
                                            @endphp
                                            @if($gradeAssignTotal > 0)
                                                <div class="grade-class-block">
                                                    <div class="grade-class-header">📗 {{ $gradeGroup['grade']->name ?? 'Class Group' }}</div>
                                                    @foreach($gradeGroup['classes'] as $class)
                                                        @if($classHasAssignments[$class->id] ?? false)
                                                            <div class="assignment-class-group">
                                                                <div class="assignment-class-head">
                                                                    <span class="assignment-class-title">📘 {{ $class->name }}</span>
                                                                    <span class="assignment-class-meta">{{ $assignmentsByClassId[$class->id]->count() }} task(s)</span>
                                                                </div>
                                                                <div class="assignment-list">
                                                                    @foreach($assignmentsByClassId[$class->id] as $assignment)
                                                                        @php
                                                                            $submission = $studentSubmissionMap[$assignment->id] ?? null;
                                                                            $assignmentTeacher = $assignment->teacher?->user?->name ?? ($class->teachers->first()?->user?->name ?? 'Course Teacher');
                                                                            $assignmentDue = $assignment->end_at;
                                                                            $assignmentStatus = $submission && $submission->isGraded() && $submission->score !== null
                                                                                ? 'graded'
                                                                                : ($submission ? ($submission->status === 'returned' ? 'returned' : 'submitted') : 'pending');
                                                                            $assignmentStatusLabel = match ($assignmentStatus) {
                                                                                'graded' => '✅ Graded · ' . round($submission->percentage() ?: 0) . '%',
                                                                                'returned' => '↩️ Returned',
                                                                                'submitted' => '⏳ Submitted',
                                                                                default => $assignment->isExpired() ? '⛔ Missed deadline' : '📝 Pending',
                                                                            };
                                                                            $assignmentStatusClass = match ($assignmentStatus) {
                                                                                'graded' => 'status-ok',
                                                                                'returned', 'submitted' => 'status-warn',
                                                                                default => $assignment->isExpired() ? 'status-bad' : 'status-pending',
                                                                            };
                                                                        @endphp
                                                                        <div class="assignment-item">
                                                                            <div class="assignment-item-top">
                                                                                <span class="assignment-status-pill {{ $assignmentStatusClass }}">
                                                                                    {{ $assignmentStatusLabel }}
                                                                                </span>
                                                                                <span class="assignment-date">
                                                                                    📅 {{ $assignmentDue ? $assignmentDue->format('M j, Y · g:i A') : 'No deadline' }}
                                                                                </span>
                                                                            </div>

                                                                            <h3>{{ $assignment->title }}</h3>

                                                                            <p class="assignment-desc">👨‍🏫 {{ $assignmentTeacher }}</p>

                                                                            @if($assignment->description)
                                                                                <p class="assignment-desc">{{ \Illuminate\Support\Str::limit(strip_tags($assignment->description), 180) }}</p>
                                                                            @endif

                                                                            <div class="assignment-meta-row">
                                                                                <span class="assignment-meta-chip">🎯 {{ $assignment->max_score }} marks</span>
                                                                            </div>

                                                                            <a href="{{ \App\Filament\Student\Pages\AssignmentView::getUrl(['assignment' => $assignment->id]) }}" class="assignment-open-btn">
                                                                                @if($assignmentStatus === 'graded')
                                                                                    View Grade ✅
                                                                                @elseif($assignmentStatus === 'returned')
                                                                                    Resubmit Assignment ↩️
                                                                                @elseif($assignmentStatus === 'submitted')
                                                                                    View Submission 📄
                                                                                @else
                                                                                    View &amp; Submit Assignment 📝
                                                                                @endif
                                                                            </a>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>

                {{-- ── TAB 5: QUIZZES ── --}}
                @elseif($activeTab === 'quizzes')
                    <div class="glass-card">
                        <div class="glass-card-title">
                            <span>🧠 Quiz History & Results</span>
                            <span style="font-size: 0.85rem; color: #7c3aed; font-weight: 700;">Average Score: {{ $quizAvgPct }}%</span>
                        </div>

                        @if($allClasses->count() > 1 || $filteredClass)
                            <div class="tab-filter-bar">
                                <span class="tab-filter-label">🎯 Filter by class</span>
                                <select wire:model.live="activeClassFilterId" class="tab-filter-select">
                                    <option value="">All my classes</option>
                                    @foreach($allClasses as $class)
                                        <option value="{{ $class->id }}" @selected((string) $activeClassFilterId === (string) $class->id)>
                                            {{ $class->name }}{{ ! empty($classSchoolMap[$class->id]) ? ' · ' . $classSchoolMap[$class->id] : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if($filteredQuizAttempts->isEmpty())
                            <div class="tab-filter-empty">
                                @if($filteredClass)
                                    No quiz attempts for <strong>{{ $filteredClass->name }}</strong> yet. Take your first quiz to track progress!
                                @else
                                    No quiz attempts recorded yet. Take your first quiz to track your progress!
                                @endif
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
                                    @foreach($filteredQuizAttempts as $attempt)
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

                {{-- ── TAB 6: GRADES ── --}}
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

                {{-- ── TAB 7: PROFILE ── --}}
                @elseif($activeTab === 'profile')
                    <div class="profile-wrap">
                        @if($profileMessage)
                            <div class="profile-alert profile-alert-{{ $profileMessageType }}">
                                {{ $profileMessage }}
                            </div>
                        @endif

                        <div class="profile-grid-3">

                            {{-- Photo card --}}
                            <div class="profile-card">
                                <div class="profile-card-head">
                                    <div class="profile-avatar" style="width: 4.5rem; height: 4.5rem; font-size: 1.8rem; overflow: hidden; flex-shrink: 0;">
                                        @if($profilePhoto)
                                            <img src="{{ $profilePhoto->temporaryUrl() }}" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                        @elseif($student?->profile_photo_url)
                                            <img src="{{ $student->profile_photo_url }}" alt="My photo" style="width: 100%; height: 100%; object-fit: cover;">
                                        @else
                                            {{ strtoupper(substr($firstName, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <h3>My Photo</h3>
                                        <p>Pick a picture so your friends know it's you!</p>
                                    </div>
                                </div>
                                <label class="profile-photo-btn">
                                    📸 Choose a Photo
                                    <input type="file" wire:model="profilePhoto" accept="image/*" hidden>
                                </label>
                                <div class="profile-upload-progress" wire:loading wire:target="profilePhoto">
                                    Uploading your photo... ⏳
                                </div>
                                @error('profilePhoto') <small class="profile-error">{{ $message }}</small> @enderror
                                <div class="profile-note" style="margin-top: 0.5rem;">
                                    Your photo stays just for your school account. Ask a parent if you are not sure!
                                </div>
                            </div>

                            {{-- My info card --}}
                            <div class="profile-card">
                                <div class="profile-card-head">
                                    <div class="profile-avatar">👤</div>
                                    <div>
                                        <h3>My Account</h3>
                                        <p>Update your details so we know how to reach you.</p>
                                    </div>
                                </div>

                                <form wire:submit="updateProfile" class="profile-form">
                                    <div class="profile-field-row">
                                        <label class="profile-field">
                                            <span>My Name</span>
                                            <input type="text" wire:model="profileName" placeholder="Your full name">
                                            @error('profileName') <small class="profile-error">{{ $message }}</small> @enderror
                                        </label>
                                        <label class="profile-field">
                                            <span>My Email</span>
                                            <input type="email" wire:model="profileEmail" placeholder="you@school.com">
                                            @error('profileEmail') <small class="profile-error">{{ $message }}</small> @enderror
                                        </label>
                                    </div>

                                    <div class="profile-field-row">
                                        <label class="profile-field">
                                            <span>My Birthday</span>
                                            <input type="date" wire:model="profileDateOfBirth">
                                            @error('profileDateOfBirth') <small class="profile-error">{{ $message }}</small> @enderror
                                        </label>
                                        <label class="profile-field">
                                            <span>I am a</span>
                                            <select wire:model="profileGender">
                                                <option value="male">Boy 👦</option>
                                                <option value="female">Girl 👧</option>
                                                <option value="other">Other 🧑</option>
                                            </select>
                                            @error('profileGender') <small class="profile-error">{{ $message }}</small> @enderror
                                        </label>
                                    </div>

                                    <div class="profile-field-row">
                                        <label class="profile-field">
                                            <span>My Phone</span>
                                            <input type="text" wire:model="profilePhone" placeholder="Your phone number">
                                            @error('profilePhone') <small class="profile-error">{{ $message }}</small> @enderror
                                        </label>
                                        <label class="profile-field">
                                            <span>Address</span>
                                            <input type="text" wire:model="profileAddress" placeholder="Where do you live?">
                                            @error('profileAddress') <small class="profile-error">{{ $message }}</small> @enderror
                                        </label>
                                    </div>

                                    <div class="profile-field-row">
                                        <label class="profile-field">
                                            <span>Parent / Guardian Name</span>
                                            <input type="text" wire:model="profileParentName" placeholder="Parent or guardian name">
                                            @error('profileParentName') <small class="profile-error">{{ $message }}</small> @enderror
                                        </label>
                                        <label class="profile-field">
                                            <span>Parent / Guardian Phone</span>
                                            <input type="text" wire:model="profileParentPhone" placeholder="Parent's phone number">
                                            @error('profileParentPhone') <small class="profile-error">{{ $message }}</small> @enderror
                                        </label>
                                    </div>

                                    <button type="submit" class="profile-save-btn">Save My Info 💾</button>
                                </form>
                            </div>
                        </div>

                        {{-- Password card --}}
                        <div class="profile-card">
                            <div class="profile-card-head">
                                <div class="profile-avatar profile-avatar-lock">🔒</div>
                                <div>
                                    <h3>Change My Password</h3>
                                    <p>Pick a new secret password to keep your account safe.</p>
                                </div>
                            </div>

                            <form wire:submit="updatePassword" class="profile-form">
                                <div class="profile-field-row">
                                    <label class="profile-field">
                                        <span>Current Password</span>
                                        <input type="password" wire:model="currentPassword" placeholder="Your current password">
                                        @error('currentPassword') <small class="profile-error">{{ $message }}</small> @enderror
                                    </label>
                                    <label class="profile-field">
                                        <span>New Password</span>
                                        <input type="password" wire:model="newPassword" placeholder="A brand new password">
                                        @error('newPassword') <small class="profile-error">{{ $message }}</small> @enderror
                                    </label>
                                    <label class="profile-field">
                                        <span>Repeat New Password</span>
                                        <input type="password" wire:model="newPassword_confirmation" placeholder="Same password again">
                                        @error('newPassword_confirmation') <small class="profile-error">{{ $message }}</small> @enderror
                                    </label>
                                </div>
                                <button type="submit" class="profile-save-btn">Update Password 🔑</button>
                            </form>
                        </div>

                        <div class="profile-note">
                            🏫 For any changes to your school, grade or class, please ask your teacher or school admin to help. Students cannot change their own enrollment.
                        </div>
                    </div>
                @endif

            </div>

        </main>

    </div>
</div>

{{-- ── LESSONS & MATERIALS MODAL OVERLAY ── --}}
@if($selectedClass)
    <div style="position: fixed; inset: 0; z-index: 100; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; padding: 1.5rem;" wire:click.self="closeClassLessons">
        <div style="background: #ffffff; border-radius: 1.25rem; width: 100%; max-width: 44rem; max-height: 85vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.2); border: 1px solid #e2e8f0;">
            
            <!-- Modal Header -->
            <div style="padding: 1.5rem 2rem; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; background: linear-gradient(135deg, #faf5ff 0%, #f5f3ff 100%);">
                <div>
                    <h2 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0;">📖 {{ $selectedClass->name }}</h2>
                    <p style="font-size: 0.85rem; color: #64748b; margin: 0.25rem 0 0; font-weight: 600;">
                        Instructor: {{ $selectedClass->teachers->first()?->user?->name ?? 'Course Teacher' }} · {{ $selectedClass->lessons->count() }} Published Lessons
                    </p>
                </div>
                <button wire:click="closeClassLessons" type="button" style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 9999px; width: 2.2rem; height: 2.2rem; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: 800; color: #475569; cursor: pointer; transition: all 0.2s;">
                    ✕
                </button>
            </div>

            <!-- Modal Content (Lessons List) -->
            <div style="flex: 1; overflow-y: auto; padding: 1.75rem 2rem; display: flex; flex-direction: column; gap: 1.25rem;">
                @if($selectedClass->lessons->isEmpty())
                    <div style="text-align: center; padding: 3rem 1rem; color: #64748b;">
                        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📚</div>
                        <h4 style="font-size: 1.1rem; font-weight: 700; color: #334155; margin: 0 0 0.25rem 0;">No Lessons Published Yet</h4>
                        <p style="font-size: 0.88rem; margin: 0;">Check back soon! Your teacher will publish learning materials here.</p>
                    </div>
                @else
                    @foreach($selectedClass->lessons as $lesson)
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.25rem; display: flex; flex-direction: column; gap: 0.75rem;">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <span style="font-size: 0.75rem; font-weight: 800; background: #ede9fe; color: #7c3aed; padding: 0.25rem 0.65rem; border-radius: 9999px;">
                                    Lesson {{ $loop->iteration }}
                                </span>
                                <span style="font-size: 0.78rem; color: #64748b; font-weight: 600;">
                                    {{ $lesson->created_at ? $lesson->created_at->format('M j, Y') : '' }}
                                </span>
                            </div>

                            <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">{{ $lesson->title }}</h3>

                            @if($lesson->description)
                                <p style="font-size: 0.88rem; color: #475569; margin: 0; line-height: 1.5; font-weight: 500;">{{ $lesson->description }}</p>
                            @endif

                            @if($lesson->content)
                                <div style="font-size: 0.88rem; color: #334155; background: #ffffff; padding: 0.85rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; line-height: 1.6;">
                                    {!! nl2br(e($lesson->content)) !!}
                                </div>
                            @endif

                            @if($lesson->video_url)
                                <div style="margin-top: 0.25rem;">
                                    <a href="{{ $lesson->video_url }}" target="_blank" rel="noopener noreferrer" style="display: inline-flex; align-items: center; gap: 0.4rem; background: #eff6ff; border: 1px solid #bfdbfe; color: #2563eb; font-weight: 700; font-size: 0.82rem; padding: 0.45rem 0.85rem; border-radius: 0.65rem; text-decoration: none;">
                                        🎬 Watch Video Lesson ↗
                                    </a>
                                </div>
                            @endif

                            @if($lesson->attachments && $lesson->attachments->count())
                                <div style="margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.4rem;">
                                    <span style="font-size: 0.78rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.05em;">Course Attachments & Downloads</span>
                                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                        @foreach($lesson->attachments as $attachment)
                                            <a href="{{ \Illuminate\Support\Facades\Storage::url($attachment->file_path) }}" download style="display: inline-flex; align-items: center; gap: 0.4rem; background: #ffffff; border: 1px solid #cbd5e1; color: #0f172a; font-weight: 700; font-size: 0.8rem; padding: 0.4rem 0.75rem; border-radius: 0.65rem; text-decoration: none;">
                                                📄 {{ $attachment->original_name ?? 'Download Material' }}
                                                @if($attachment->file_size)
                                                    <span style="font-size: 0.72rem; color: #64748b;">({{ round($attachment->file_size / 1024, 1) }} KB)</span>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Modal Footer -->
            <div style="padding: 1.25rem 2rem; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end;">
                <button wire:click="closeClassLessons" type="button" style="background: #7c3aed; color: #ffffff; border: none; font-weight: 700; font-size: 0.88rem; padding: 0.6rem 1.4rem; border-radius: 0.75rem; cursor: pointer; transition: all 0.2s;">
                    Done / Close
                </button>
            </div>
        </div>
    </div>
@endif
