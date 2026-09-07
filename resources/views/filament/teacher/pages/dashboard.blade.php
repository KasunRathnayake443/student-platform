<div style="padding:24px 28px; max-width: 100%; box-sizing: border-box;">
<style>
    /* ── Core Typography & Resets ─────────────── */
    .t-dash-wrap { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #0f172a; }

    /* ── Hero Banner ─────────────────────────── */
    .t-hero {
        background: linear-gradient(135deg, #312e81 0%, #4338ca 45%, #4f46e5 80%, #6366f1 100%);
        border-radius: 20px;
        padding: 28px 32px;
        color: #ffffff;
        position: relative;
        overflow: hidden;
        margin-bottom: 24px;
        box-shadow: 0 10px 30px -8px rgba(79, 70, 229, 0.35);
    }
    .t-hero::before {
        content: '';
        position: absolute;
        top: -60px; right: -40px;
        width: 240px; height: 240px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255,255,255,0.16) 0%, rgba(255,255,255,0) 70%);
        pointer-events: none;
    }
    .t-hero::after {
        content: '';
        position: absolute;
        bottom: -80px; right: 180px;
        width: 200px; height: 200px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.22) 0%, rgba(16, 185, 129, 0) 70%);
        pointer-events: none;
    }
    .t-hero-inner {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        flex-wrap: wrap;
    }
    .t-hero-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #e0e7ff;
        margin-bottom: 10px;
    }
    .t-hero-title {
        font-size: 24px;
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.25;
        margin: 0 0 6px;
        color: #ffffff;
    }
    .t-hero-sub {
        font-size: 13.5px;
        color: #e0e7ff;
        line-height: 1.5;
        max-width: 640px;
        margin: 0;
        font-weight: 400;
    }
    .t-hero-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .t-btn-hero-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #ffffff;
        color: #4338ca;
        font-size: 13px;
        font-weight: 700;
        padding: 10px 18px;
        border-radius: 12px;
        text-decoration: none;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
        transition: all .16s ease;
    }
    .t-btn-hero-primary:hover {
        background: #f8fafc;
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.18);
        color: #3730a3;
    }
    .t-btn-hero-secondary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.16);
        color: #ffffff;
        font-size: 13px;
        font-weight: 600;
        padding: 10px 18px;
        border-radius: 12px;
        text-decoration: none;
        border: 1px solid rgba(255, 255, 255, 0.28);
        backdrop-filter: blur(8px);
        transition: all .16s ease;
    }
    .t-btn-hero-secondary:hover {
        background: rgba(255, 255, 255, 0.26);
        color: #ffffff;
        transform: translateY(-2px);
    }

    /* ── Stat cards (3 required by tests) ───── */
    .stat-card {
        background: #ffffff;
        border: 1px solid #eef1f7;
        border-radius: 16px;
        padding: 20px 22px;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.07);
        border-color: #cbd5e1;
    }
    .stat-icon {
        width: 48px; height: 48px; flex-shrink: 0;
        border-radius: 13px;
        display: flex; align-items: center; justify-content: center;
    }
    .stat-icon svg { width: 22px; height: 22px; }
    .stat-icon-indigo  { background: #eef2ff; color: #4f46e5; }
    .stat-icon-emerald { background: #ecfdf5; color: #059669; }
    .stat-icon-violet  { background: #f5f3ff; color: #7c3aed; }
    .stat-body   { min-width: 0; flex: 1; }
    .stat-label  { font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .08em; }
    .stat-value  { font-size: 32px; font-weight: 800; color: #0f172a; line-height: 1.15; letter-spacing: -.02em; margin-top: 2px; }
    .stat-sub    { font-size: 12px; color: #94a3b8; margin-top: 2px; font-weight: 500; }

    /* ── Secondary Highlights Bar ────────────── */
    .t-summary-strip {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 24px;
    }
    .t-summary-pill {
        background: #ffffff;
        border: 1px solid #eef1f7;
        border-radius: 14px;
        padding: 14px 18px;
        display: flex;
        align-items: center;
        gap: 13px;
        transition: all .15s ease;
    }
    .t-summary-pill:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
    }
    .t-pill-icon {
        width: 38px; height: 38px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .t-pill-icon svg { width: 19px; height: 19px; }
    .t-pill-blue   { background: #eff6ff; color: #2563eb; }
    .t-pill-amber  { background: #fffbeb; color: #d97706; }
    .t-pill-rose   { background: #fff1f2; color: #e11d48; }
    .t-pill-teal   { background: #f0fdf4; color: #0d9488; }
    .t-pill-num    { font-size: 20px; font-weight: 800; color: #0f172a; line-height: 1.1; }
    .t-pill-text   { font-size: 11.5px; font-weight: 500; color: #64748b; margin-top: 2px; }

    /* ── Attention / Pending Grading Card ────── */
    .t-attention-card {
        background: #ffffff;
        border: 1px solid #eef1f7;
        border-radius: 16px;
        padding: 20px 24px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
    }
    .t-attention-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f1f5f9;
    }
    .t-attention-title-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .t-attention-badge-alert {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        background: #fef2f2;
        color: #ef4444;
        border: 1px solid #fee2e2;
    }
    .t-attention-badge-ok {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #d1fae5;
    }
    .t-sub-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        margin-bottom: 8px;
        transition: background .13s ease;
    }
    .t-sub-item:hover { background: #f1f5f9; }
    .t-sub-meta { display: flex; align-items: center; gap: 12px; min-width: 0; }
    .t-sub-avatar {
        width: 32px; height: 32px; border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff; font-size: 12px; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .t-sub-name { font-size: 13px; font-weight: 600; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .t-sub-assignment { font-size: 12px; color: #64748b; margin-top: 1px; }
    .t-sub-time { font-size: 11px; color: #94a3b8; font-weight: 500; }
    .t-btn-grade {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 13px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        background: #4f46e5;
        color: #ffffff;
        text-decoration: none;
        transition: background .13s ease;
        flex-shrink: 0;
    }
    .t-btn-grade:hover { background: #4338ca; }

    /* ── Split Grid (Quizzes & Lessons) ─────── */
    .t-split-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }
    .t-card-box {
        background: #ffffff;
        border: 1px solid #eef1f7;
        border-radius: 16px;
        padding: 20px 22px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
    }
    .t-box-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f1f5f9;
    }
    .t-box-title {
        font-size: 14.5px;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .t-box-action {
        font-size: 12px;
        font-weight: 600;
        color: #4f46e5;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .t-box-action:hover { text-decoration: underline; }
    .t-list-card {
        padding: 11px 14px;
        border-radius: 12px;
        border: 1px solid #f1f5f9;
        background: #f8fafc;
        margin-bottom: 9px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        text-decoration: none;
        transition: all .13s ease;
    }
    .t-list-card:hover {
        border-color: #cbd5e1;
        background: #ffffff;
        transform: translateX(3px);
        box-shadow: 0 3px 10px rgba(15, 23, 42, 0.04);
    }
    .t-list-main { min-width: 0; }
    .t-list-title { font-size: 13px; font-weight: 600; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .t-list-meta { font-size: 11.5px; color: #64748b; margin-top: 2px; display: flex; align-items: center; gap: 8px; }
    .t-badge-pill {
        display: inline-flex; align-items: center;
        padding: 2px 8px; border-radius: 999px;
        font-size: 10.5px; font-weight: 600;
    }
    .t-badge-indigo { background: #eef2ff; color: #4f46e5; }
    .t-badge-emerald { background: #ecfdf5; color: #059669; }
    .t-badge-gray { background: #f1f5f9; color: #64748b; }

    /* ── Section Headings ─────────────────────── */
    .section-head { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
    .section-title { font-size: 16px; font-weight: 700; color: #0f172a; letter-spacing: -.01em; }
    .section-line  { flex: 1; height: 1px; background: #e2e8f0; }
    .section-count {
        font-size: 11.5px; font-weight: 600; color: #4f46e5;
        background: #eef2ff; padding: 4px 12px; border-radius: 999px;
    }

    /* ── Class Search & Filter ───────────────── */
    .t-class-filter-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }
    .t-search-input-wrap {
        position: relative;
        flex: 1;
        max-width: 380px;
        min-width: 240px;
    }
    .t-search-input-wrap svg {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 16px; height: 16px;
        color: #94a3b8;
    }
    .t-search-input {
        width: 100%;
        padding: 9px 12px 9px 36px;
        border-radius: 11px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        font-size: 13px;
        color: #0f172a;
        outline: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .t-search-input:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
    }

    /* ── School cards (preserves test classes) ── */
    .school-card {
        background: #ffffff;
        border: 1px solid #eef1f7;
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 20px;
        transition: box-shadow .16s ease;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
    }
    .school-card:hover { box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06); }
    .school-header {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        display: flex; align-items: center; gap: 14px;
        background: linear-gradient(180deg, #ffffff 0%, #fafbfe 100%);
    }
    .school-avatar {
        width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0;
        background: linear-gradient(135deg, #6366f1, #10b981);
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-weight: 700; font-size: 17px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.18);
    }
    .school-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .school-name  { font-size: 15px; font-weight: 700; color: #0f172a; }
    .school-meta  { font-size: 12px; color: #94a3b8; margin-top: 2px; }
    .school-badge {
        margin-left: auto; font-size: 11.5px; font-weight: 600; color: #4f46e5;
        background: #eef2ff; padding: 5px 13px; border-radius: 999px; flex-shrink: 0;
    }

    .grade-section { padding: 0 20px; }
    .grade-label {
        font-size: 11px; font-weight: 700; color: #64748b;
        text-transform: uppercase; letter-spacing: .08em;
        padding: 16px 0 10px;
        display: flex; align-items: center; gap: 8px;
    }
    .grade-label::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: #6366f1; }

    .classes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 14px; padding: 4px 0 20px;
    }

    .class-card {
        border: 1px solid #eef1f7; border-radius: 14px; padding: 16px 18px;
        transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
        text-decoration: none; display: block; background: #ffffff;
        position: relative;
    }
    .class-card:hover {
        border-color: #818cf8;
        box-shadow: 0 10px 24px rgba(99, 102, 241, 0.12);
        transform: translateY(-2px);
    }
    .class-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
    .class-grade-badge {
        font-size: 10.5px; font-weight: 600; color: #6366f1;
        background: #eef2ff; padding: 3px 9px; border-radius: 999px;
    }
    .class-status-active   { font-size: 10.5px; font-weight: 600; color: #059669; background: #d1fae5; padding: 3px 9px; border-radius: 999px; }
    .class-status-inactive { font-size: 10.5px; font-weight: 600; color: #dc2626; background: #fee2e2; padding: 3px 9px; border-radius: 999px; }
    .class-name   { font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 3px; }
    .class-medium { font-size: 12px; color: #94a3b8; margin-bottom: 12px; }
    .class-footer {
        display: flex; align-items: center; justify-content: space-between;
        font-size: 12px; color: #64748b; padding-top: 12px; border-top: 1px solid #f1f5f9;
    }
    .class-action-link {
        font-size: 11.5px; font-weight: 600; color: #4f46e5;
        display: inline-flex; align-items: center; gap: 3px;
    }

    /* ── Info notice ──────────────────────────── */
    .info-notice {
        background: linear-gradient(135deg, #fffbeb, #fefce8);
        border: 1px solid #fde68a;
        border-radius: 12px;
        padding: 12px 16px;
        display: flex; align-items: center; gap: 10px;
        margin-bottom: 24px;
    }

    /* ── Empty state (preserves test classes) ─── */
    .empty-state {
        background: #ffffff; border: 1px solid #eef1f7; border-radius: 16px;
        padding: 64px 24px; text-align: center;
    }
    .empty-icon {
        width: 56px; height: 56px; background: #eef2ff; border-radius: 16px;
        display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;
        color: #6366f1;
    }
    .empty-title { font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 6px; }
    .empty-text  { font-size: 13px; color: #94a3b8; max-width: 320px; margin: 0 auto; line-height: 1.65; }

    /* ── Responsive rules ────────────────────── */
    @media (max-width: 960px) {
        .t-summary-strip { grid-template-columns: repeat(2, 1fr); }
        .t-split-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .t-summary-strip { grid-template-columns: 1fr; }
        .t-hero-inner { flex-direction: column; align-items: flex-start; }
    }
</style>

<div class="t-dash-wrap">

    {{-- ── Hero Welcome Banner ──────────────────────────────── --}}
    <div class="t-hero">
        <div class="t-hero-inner">
            <div>
                <div class="t-hero-tag">
                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span>Teacher Workspace</span>
                </div>
                <h1 class="t-hero-title">Welcome back, {{ auth()->user()->name }}! 👋</h1>
                <p class="t-hero-sub">
                    You are guiding {{ $totalClasses }} {{ Str::plural('class', $totalClasses) }} with {{ number_format($totalStudents) }} students across {{ $schools->count() }} {{ Str::plural('school', $schools->count()) }}. Here is today's overview.
                </p>
            </div>
            <div class="t-hero-actions">
                <a href="{{ \App\Filament\Teacher\Resources\Quizzes\QuizResource::getUrl('create') }}" class="t-btn-hero-primary">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Quiz
                </a>
                <a href="{{ \App\Filament\Teacher\Pages\Notifications::getUrl(panel: 'teacher') }}" class="t-btn-hero-secondary">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    Notifications
                </a>
            </div>
        </div>
    </div>

    {{-- ── Three Primary Stat Cards (preserves exact test contract) ── --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">

        <div class="stat-card">
            <div class="stat-icon stat-icon-indigo">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="stat-body">
                <div class="stat-label">Schools</div>
                <div class="stat-value">{{ $schools->count() }}</div>
                <div class="stat-sub">Assigned to you</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon stat-icon-emerald">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <div class="stat-body">
                <div class="stat-label">Grades</div>
                <div class="stat-value">{{ $schools->flatMap->grades->count() }}</div>
                <div class="stat-sub">Across all schools</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon stat-icon-violet">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                </svg>
            </div>
            <div class="stat-body">
                <div class="stat-label">Classes</div>
                <div class="stat-value">{{ $totalClasses }}</div>
                <div class="stat-sub">You teach</div>
            </div>
        </div>

    </div>

    {{-- ── Secondary Metrics Strip ───────────────────────────── --}}
    <div class="t-summary-strip">
        <div class="t-summary-pill">
            <div class="t-pill-icon t-pill-blue">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <div class="t-pill-num">{{ number_format($totalStudents) }}</div>
                <div class="t-pill-text">Active Students</div>
            </div>
        </div>

        <div class="t-summary-pill">
            <div class="t-pill-icon t-pill-amber">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <div class="t-pill-num">{{ $pendingSubmissionsCount }}</div>
                <div class="t-pill-text">Pending Submissions</div>
            </div>
        </div>

        <div class="t-summary-pill">
            <div class="t-pill-icon t-pill-rose">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <div>
                <div class="t-pill-num">{{ $totalQuizzes }}</div>
                <div class="t-pill-text">Quizzes Conducted</div>
            </div>
        </div>

        <div class="t-summary-pill">
            <div class="t-pill-icon t-pill-teal">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <div class="t-pill-num">{{ $totalLessons }}</div>
                <div class="t-pill-text">Lessons Published</div>
            </div>
        </div>
    </div>

    {{-- ── Needs Attention: Pending Grading ─────────────────────── --}}
    <div class="t-attention-card">
        <div class="t-attention-header">
            <div class="t-attention-title-wrap">
                <span style="font-size:15px; font-weight:700; color:#0f172a;">Action Center · Assignment Submissions</span>
                @if($pendingSubmissionsCount > 0)
                    <span class="t-attention-badge-alert">
                        <span style="width:6px;height:6px;border-radius:50%;background:#ef4444;display:inline-block;"></span>
                        {{ $pendingSubmissionsCount }} {{ Str::plural('submission', $pendingSubmissionsCount) }} to grade
                    </span>
                @else
                    <span class="t-attention-badge-ok">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        All caught up
                    </span>
                @endif
            </div>
        </div>

        @if($pendingSubmissions->isNotEmpty())
            <div>
                @foreach($pendingSubmissions as $sub)
                    <div class="t-sub-item">
                        <div class="t-sub-meta">
                            <div class="t-sub-avatar">
                                {{ strtoupper(substr($sub->student?->user?->name ?? 'S', 0, 1)) }}
                            </div>
                            <div style="min-width:0;">
                                <div class="t-sub-name">{{ $sub->student?->user?->name ?? 'Student' }}</div>
                                <div class="t-sub-assignment">
                                    <span style="font-weight:600; color:#334155;">{{ $sub->assignment?->title ?? 'Assignment' }}</span>
                                    @if($sub->assignment?->learningClass)
                                        · <span style="color:#6366f1;">{{ $sub->assignment->learningClass->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:14px;">
                            <span class="t-sub-time">
                                {{ $sub->submitted_at ? $sub->submitted_at->diffForHumans() : 'Recently' }}
                            </span>
                            <a href="{{ \App\Filament\Teacher\Resources\Assignments\AssignmentResource::getUrl('view', ['record' => $sub->assignment_id]) }}" class="t-btn-grade">
                                Grade Now
                                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="display:flex; align-items:center; gap:12px; padding:12px 14px; background:#f0fdf4; border-radius:12px; border:1px solid #dcfce7;">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2" style="flex-shrink:0;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div style="font-size:13px; color:#15803d; font-weight:500;">
                    Great job! All student submissions across your classes have been reviewed and graded.
                </div>
            </div>
        @endif
    </div>

    {{-- ── Two-Column Grid: Recent Quizzes & Recent Lessons ────── --}}
    <div class="t-split-grid">

        {{-- Left: Recent Quizzes --}}
        <div class="t-card-box">
            <div class="t-box-header">
                <div class="t-box-title">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#6366f1" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Recent Quizzes
                </div>
                <a href="{{ \App\Filament\Teacher\Resources\Quizzes\QuizResource::getUrl('create') }}" class="t-box-action">
                    <span>+ New Quiz</span>
                </a>
            </div>

            @if($recentQuizzes->isNotEmpty())
                <div>
                    @foreach($recentQuizzes as $quiz)
                        <a href="{{ \App\Filament\Teacher\Resources\Quizzes\QuizResource::getUrl('view', ['record' => $quiz]) }}" class="t-list-card">
                            <div class="t-list-main">
                                <div class="t-list-title">{{ $quiz->title }}</div>
                                <div class="t-list-meta">
                                    <span class="t-badge-pill t-badge-indigo">{{ $quiz->learningClass?->name ?? 'Class' }}</span>
                                    <span>{{ $quiz->questions_count }} {{ Str::plural('question', $quiz->questions_count) }}</span>
                                    <span>·</span>
                                    <span>{{ $quiz->attempts_count }} {{ Str::plural('attempt', $quiz->attempts_count) }}</span>
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                @if($quiz->is_published)
                                    <span class="t-badge-pill t-badge-emerald">Published</span>
                                @else
                                    <span class="t-badge-pill t-badge-gray">Draft</span>
                                @endif
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div style="padding:28px 14px; text-align:center; color:#94a3b8; font-size:13px;">
                    No quizzes created yet. Create a quiz to assess your students.
                </div>
            @endif
        </div>

        {{-- Right: Recent Lessons --}}
        <div class="t-card-box">
            <div class="t-box-header">
                <div class="t-box-title">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#059669" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    Recent Lessons
                </div>
                <span style="font-size:12px; color:#94a3b8; font-weight:500;">Latest content</span>
            </div>

            @if($recentLessons->isNotEmpty())
                <div>
                    @foreach($recentLessons as $lesson)
                        <a href="{{ \App\Filament\Teacher\Resources\Lessons\LessonResource::getUrl('view', ['record' => $lesson]) }}" class="t-list-card">
                            <div class="t-list-main">
                                <div class="t-list-title">{{ $lesson->title }}</div>
                                <div class="t-list-meta">
                                    <span class="t-badge-pill t-badge-emerald">{{ $lesson->learningClass?->name ?? 'Class' }}</span>
                                    @if($lesson->video_url)
                                        <span class="t-badge-pill t-badge-indigo">Video</span>
                                    @endif
                                    @if($lesson->attachments_count > 0)
                                        <span>{{ $lesson->attachments_count }} files</span>
                                    @endif
                                </div>
                            </div>
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @endforeach
                </div>
            @else
                <div style="padding:28px 14px; text-align:center; color:#94a3b8; font-size:13px;">
                    No lessons created yet. Open any of your classes below to post lesson notes.
                </div>
            @endif
        </div>

    </div>

    {{-- ── Info notice ─────────────────────────────────────────── --}}
    <div class="info-notice">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="2" style="flex-shrink:0;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span style="font-size:12.5px;color:#92400e;">
            School, grade and class assignments are managed by your administrator. Contact them if you need to be added to a new class.
        </span>
    </div>

    {{-- ── Schools & Classes Section ───────────────────────────── --}}
    @if($schools->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m0-6l9-5M3 9l9 5"/>
                </svg>
            </div>
            <div class="empty-title">No schools assigned yet</div>
            <p class="empty-text">Contact your administrator to get assigned to schools, grades, and classes.</p>
        </div>
    @else
        <div class="section-head">
            <span class="section-title">My Schools &amp; Classes</span>
            <span class="section-line"></span>
            <span class="section-count">{{ $schools->count() }} {{ Str::plural('school', $schools->count()) }}</span>
        </div>

        {{-- Instant Search Bar --}}
        <div class="t-class-filter-bar">
            <div class="t-search-input-wrap">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text"
                       id="t-class-search"
                       class="t-search-input"
                       placeholder="Filter classes or grades..."
                       oninput="filterClasses(this.value)">
            </div>
            <div style="font-size:12px; color:#64748b; font-weight:500;">
                Showing {{ $totalClasses }} classes across {{ $schools->flatMap->grades->count() }} grades
            </div>
        </div>

        @foreach($schools as $school)
            @php $classCount = $school->grades->flatMap->learningClasses->count(); @endphp
            <div class="school-card school-container-node">
                {{-- School Header --}}
                <div class="school-header">
                    <div class="school-avatar">
                        @if($school->logo_url)
                            <img src="{{ $school->logo_url }}" alt="{{ $school->name }}">
                        @else
                            {{ strtoupper(substr($school->name, 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <div class="school-name">{{ $school->name }}</div>
                        <div class="school-meta">
                            {{ $school->code }}
                            @if($school->address) · {{ $school->address }} @endif
                        </div>
                    </div>
                    <div class="school-badge">
                        {{ $classCount }} {{ Str::plural('class', $classCount) }}
                    </div>
                </div>

                {{-- Grades & Classes --}}
                @if($school->grades->isEmpty())
                    <div style="padding:20px;font-size:13px;color:#94a3b8;text-align:center;">
                        No classes assigned in this school.
                    </div>
                @else
                    <div class="grade-section">
                        @foreach($school->grades as $grade)
                            <div class="grade-group-node">
                                <div class="grade-label">Grade {{ $grade->name }}</div>
                                <div class="classes-grid">
                                    @foreach($grade->learningClasses as $class)
                                        <a href="{{ \App\Filament\Teacher\Resources\LearningClasses\LearningClassResource::getUrl('view', ['record' => $class]) }}"
                                           class="class-card class-card-node"
                                           data-search="{{ strtolower($school->name . ' ' . $grade->name . ' ' . $class->name . ' ' . ($class->medium ?? '')) }}">
                                            <div class="class-top">
                                                <span class="class-grade-badge">Grade {{ $grade->name }}</span>
                                                @if($class->is_active)
                                                    <span class="class-status-active">Active</span>
                                                @else
                                                    <span class="class-status-inactive">Inactive</span>
                                                @endif
                                            </div>
                                            <div class="class-name">{{ $class->name }}</div>
                                            @if($class->medium)
                                                <div class="class-medium">{{ $class->medium }} Medium</div>
                                            @endif
                                            <div class="class-footer">
                                                <div style="display:flex; align-items:center; gap:6px;">
                                                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                    <span>{{ $class->students_count }} {{ Str::plural('student', $class->students_count) }}</span>
                                                </div>
                                                <span class="class-action-link">
                                                    Manage
                                                    <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    @endif

</div><!-- end .t-dash-wrap -->

<script>
    function filterClasses(query) {
        query = (query || '').toLowerCase().trim();
        const cards = document.querySelectorAll('.class-card-node');
        cards.forEach(card => {
            const data = card.getAttribute('data-search') || '';
            if (!query || data.includes(query)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });

        // Hide empty grade groups
        document.querySelectorAll('.grade-group-node').forEach(group => {
            const visible = group.querySelectorAll('.class-card-node[style*="display: block"], .class-card-node:not([style*="display: none"])');
            group.style.display = visible.length > 0 ? 'block' : 'none';
        });

        // Hide empty school cards
        document.querySelectorAll('.school-container-node').forEach(school => {
            const visible = school.querySelectorAll('.class-card-node[style*="display: block"], .class-card-node:not([style*="display: none"])');
            school.style.display = visible.length > 0 ? 'block' : 'none';
        });
    }
</script>
</div>
