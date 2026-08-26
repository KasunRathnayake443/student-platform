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
        padding-right: 0.25rem;
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

    /* ══════════════════════════════════════════════════════════════
       KIDS MODE (Age 5-10): Big, bold, playful, colorful
       ══════════════════════════════════════════════════════════════ */

    /* Kids Sidebar */
    .kids-mode .custom-sidebar {
        width: 300px;
        background: linear-gradient(180deg, #faf5ff 0%, #f0e7ff 40%, #fce7f3 100%);
        border-right: 3px solid #e9d5ff;
        padding: 1.5rem 1.25rem;
    }
    .kids-mode .brand-header {
        border-bottom: 3px dashed #d8b4fe;
        padding-bottom: 1.25rem;
        margin-bottom: 0.5rem;
    }
    .kids-mode .brand-icon {
        width: 3.75rem;
        height: 3.75rem;
        background: linear-gradient(135deg, #f472b6, #c084fc, #60a5fa);
        border-radius: 1.1rem;
        font-size: 2rem;
        box-shadow: 0 6px 20px rgba(244, 114, 182, 0.35);
        animation: kids-wiggle 3s ease-in-out infinite;
    }
    @keyframes kids-wiggle {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(-5deg); }
        75% { transform: rotate(5deg); }
    }
    .kids-mode .brand-title {
        font-size: 1.4rem;
        font-weight: 900;
        background: linear-gradient(135deg, #7c3aed, #db2777);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .kids-mode .brand-sub {
        font-size: 0.82rem;
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
        gap: 0.5rem;
    }
    .kids-mode .nav-link {
        padding: 1rem 1.15rem;
        border-radius: 1rem;
        font-size: 1.15rem;
        font-weight: 800;
        gap: 0.85rem;
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
        font-size: 1.65rem;
        width: 2rem;
        text-align: center;
    }

    .kids-mode .ctx-school-name {
        font-size: 0.9rem;
        color: #7c3aed;
        font-weight: 900;
    }
    .kids-mode .ctx-item {
        padding: 0.75rem 1rem 0.75rem 1.75rem;
        border-radius: 0.9rem;
        font-size: 1rem;
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
        width: 3rem;
        height: 3rem;
        font-size: 1.15rem;
        background: linear-gradient(135deg, #f472b6, #c084fc);
        box-shadow: 0 4px 14px rgba(244, 114, 182, 0.3);
    }
    .kids-mode .user-name {
        font-size: 1.05rem;
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
    .kids-mode .custom-topbar {
        display: none;
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
                    <div class="user-avatar">
                        {{ strtoupper(substr($firstName, 0, 1)) }}
                    </div>
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
                                        <button @click="openClassId = {{ $class->id }}" type="button" class="btn-action-start" style="width: 100%; text-align: center; padding: 0.7rem; font-size: 0.85rem; cursor: pointer;">
                                            View Lessons & Materials 🚀
                                        </button>

                                        <!-- Class Lessons Modal -->
                                        <template x-teleport="body">
                                            <div x-show="openClassId === {{ $class->id }}" x-cloak style="position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(5px); display: flex; align-items: center; justify-content: center; padding: 1.5rem;" @keydown.escape.window="openClassId = null">
                                                <div @click.outside="openClassId = null" style="background: #ffffff; border-radius: 1.25rem; width: 100%; max-width: 46rem; max-height: 85vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); border: 1px solid #e2e8f0;">
                                                    
                                                    <!-- Header -->
                                                    <div style="padding: 1.5rem 2rem; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; background: linear-gradient(135deg, #faf5ff 0%, #f5f3ff 100%);">
                                                        <div>
                                                            <h2 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0;">📖 {{ $class->name }}</h2>
                                                            <p style="font-size: 0.85rem; color: #64748b; margin: 0.25rem 0 0; font-weight: 600;">
                                                                Instructor: {{ $teacherName }} · {{ $lessonCount }} Lessons Available
                                                            </p>
                                                        </div>
                                                        <button @click="openClassId = null" type="button" style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 9999px; width: 2.2rem; height: 2.2rem; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: 800; color: #475569; cursor: pointer;">
                                                            ✕
                                                        </button>
                                                    </div>

                                                    <!-- Lessons Body -->
                                                    <div style="flex: 1; overflow-y: auto; padding: 1.75rem 2rem; display: flex; flex-direction: column; gap: 1.25rem;">
                                                        @php
                                                            $publishedLessons = $class->lessons()->where('is_published', true)->with('attachments')->orderBy('sort_order')->get();
                                                        @endphp

                                                        @if($publishedLessons->isEmpty())
                                                            <div style="text-align: center; padding: 3.5rem 1rem; color: #64748b;">
                                                                <div style="font-size: 2.75rem; margin-bottom: 0.5rem;">📚</div>
                                                                <h4 style="font-size: 1.15rem; font-weight: 800; color: #334155; margin: 0 0 0.25rem 0;">No Lessons Published Yet</h4>
                                                                <p style="font-size: 0.88rem; margin: 0;">Your teacher has not published materials for this course yet.</p>
                                                            </div>
                                                        @else
                                                            @foreach($publishedLessons as $lesson)
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

                                                    <!-- Footer -->
                                                    <div style="padding: 1.25rem 2rem; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end;">
                                                        <button @click="openClassId = null" type="button" style="background: #7c3aed; color: #ffffff; border: none; font-weight: 700; font-size: 0.88rem; padding: 0.6rem 1.4rem; border-radius: 0.75rem; cursor: pointer;">
                                                            Close
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
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
                                                <button @click="openAssignmentId = {{ $assignment->id }}" type="button" class="btn-action-start" style="cursor: pointer;">
                                                    {{ $isSubmitted ? 'View Submission 📄' : 'View Details 🚀' }}
                                                </button>

                                                <!-- Assignment Details Modal -->
                                                <template x-teleport="body">
                                                    <div x-show="openAssignmentId === {{ $assignment->id }}" x-cloak style="position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(5px); display: flex; align-items: center; justify-content: center; padding: 1.5rem;" @keydown.escape.window="openAssignmentId = null">
                                                        <div @click.outside="openAssignmentId = null" style="background: #ffffff; border-radius: 1.25rem; width: 100%; max-width: 44rem; max-height: 85vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); border: 1px solid #e2e8f0;">
                                                            
                                                            <!-- Header -->
                                                            <div style="padding: 1.5rem 2rem; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; background: linear-gradient(135deg, #fef2f2 0%, #fff1f2 100%);">
                                                                <div>
                                                                    <h2 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0;">📋 {{ $assignment->title }}</h2>
                                                                    <p style="font-size: 0.85rem; color: #64748b; margin: 0.25rem 0 0; font-weight: 600;">
                                                                        Class: {{ $assignment->learningClass->name ?? 'General Class' }} · Due: {{ $dueDate ? $dueDate->format('M j, Y') : 'No Deadline' }}
                                                                    </p>
                                                                </div>
                                                                <button @click="openAssignmentId = null" type="button" style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 9999px; width: 2.2rem; height: 2.2rem; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: 800; color: #475569; cursor: pointer;">
                                                                    ✕
                                                                </button>
                                                            </div>

                                                            <!-- Body -->
                                                            <div style="flex: 1; overflow-y: auto; padding: 1.75rem 2rem; display: flex; flex-direction: column; gap: 1.25rem;">
                                                                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.25rem; display: flex; flex-direction: column; gap: 0.75rem;">
                                                                    <h4 style="font-size: 0.82rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin: 0;">Assignment Instructions</h4>
                                                                    <p style="font-size: 0.92rem; color: #334155; margin: 0; line-height: 1.6; font-weight: 500;">
                                                                        {{ $assignment->description ?? 'No specific instructions provided for this assignment.' }}
                                                                    </p>
                                                                </div>

                                                                @if($assignment->attachments && $assignment->attachments->count())
                                                                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                                                        <h4 style="font-size: 0.82rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin: 0;">Reference Materials & Handouts</h4>
                                                                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                                                            @foreach($assignment->attachments as $att)
                                                                                <a href="{{ \Illuminate\Support\Facades\Storage::url($att->file_path) }}" download style="display: inline-flex; align-items: center; gap: 0.4rem; background: #ffffff; border: 1px solid #cbd5e1; color: #0f172a; font-weight: 700; font-size: 0.8rem; padding: 0.4rem 0.75rem; border-radius: 0.65rem; text-decoration: none;">
                                                                                    📄 {{ $att->original_name ?? 'Download Attachment' }}
                                                                                </a>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                                @php
                                                                    $submission = \App\Models\AssignmentSubmission::where('student_id', $student?->id)->where('assignment_id', $assignment->id)->first();
                                                                @endphp

                                                                <div style="background: {{ $submission ? '#f0fdf4' : '#fff1f2' }}; border: 1px solid {{ $submission ? '#bbf7d0' : '#fecaca' }}; border-radius: 1rem; padding: 1.25rem;">
                                                                    <h4 style="font-size: 0.88rem; font-weight: 800; color: {{ $submission ? '#166534' : '#991b1b' }}; margin: 0 0 0.5rem 0;">
                                                                        {{ $submission ? '✓ Submission Received' : '⏳ Pending Homework Submission' }}
                                                                    </h4>
                                                                    @if($submission)
                                                                        <p style="font-size: 0.82rem; color: #15803d; margin: 0 0 0.5rem 0; font-weight: 600;">
                                                                            Submitted on {{ \Carbon\Carbon::parse($submission->created_at)->format('M j, Y · g:i A') }}
                                                                        </p>
                                                                        @if($submission->notes)
                                                                            <p style="font-size: 0.88rem; color: #334155; margin: 0; font-weight: 500;">Notes: {{ $submission->notes }}</p>
                                                                        @endif
                                                                    @else
                                                                        <p style="font-size: 0.85rem; color: #991b1b; margin: 0; font-weight: 500;">
                                                                            Please submit your completed assignment file directly to your subject instructor or upload it via your student portal.
                                                                        </p>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <!-- Footer -->
                                                            <div style="padding: 1.25rem 2rem; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end;">
                                                                <button @click="openAssignmentId = null" type="button" style="background: #dc2626; color: #ffffff; border: none; font-weight: 700; font-size: 0.88rem; padding: 0.6rem 1.4rem; border-radius: 0.75rem; cursor: pointer;">
                                                                    Close
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
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
