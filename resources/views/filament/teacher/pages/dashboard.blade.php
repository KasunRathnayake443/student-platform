<div style="padding:24px;">
<style>
    /* ── Stat cards ───────────────────────────── */
    .stat-card {
        background:#fff;
        border:1px solid #eef1f7;
        border-radius:16px;
        padding:20px 22px;
        display:flex; align-items:center; gap:16px;
        transition:transform .16s ease, box-shadow .16s ease, border-color .16s ease;
    }
    .stat-card:hover {
        transform:translateY(-2px);
        box-shadow:0 10px 28px rgba(15,23,42,.07);
        border-color:#e2e8f0;
    }
    .stat-icon {
        width:48px; height:48px; flex-shrink:0;
        border-radius:13px;
        display:flex; align-items:center; justify-content:center;
    }
    .stat-icon svg { width:22px; height:22px; }
    .stat-icon-indigo  { background:#eef2ff; color:#4f46e5; }
    .stat-icon-emerald { background:#ecfdf5; color:#059669; }
    .stat-icon-violet  { background:#f5f3ff; color:#7c3aed; }
    .stat-body   { min-width:0; }
    .stat-label  { font-size:11.5px; font-weight:600; color:#8b95a9; text-transform:uppercase; letter-spacing:.07em; }
    .stat-value  { font-size:30px; font-weight:700; color:#0f172a; line-height:1.15; letter-spacing:-.02em; }
    .stat-sub    { font-size:12px; color:#94a3b8; margin-top:1px; }

    /* ── Section heading row ──────────────────── */
    .section-head { display:flex; align-items:center; gap:12px; margin-bottom:14px; }
    .section-title { font-size:15px; font-weight:700; color:#0f172a; letter-spacing:-.01em; }
    .section-line  { flex:1; height:1px; background:#e8ecf3; }
    .section-count {
        font-size:11.5px; font-weight:600; color:#4f46e5;
        background:#eef2ff; padding:4px 12px; border-radius:999px;
    }

    /* ── School cards ─────────────────────────── */
    .school-card {
        background:#fff;
        border:1px solid #eef1f7;
        border-radius:16px;
        overflow:hidden;
        margin-bottom:18px;
        transition:box-shadow .16s ease;
    }
    .school-card:hover { box-shadow:0 10px 28px rgba(15,23,42,.06); }
    .school-header {
        padding:16px 20px;
        border-bottom:1px solid #f1f5f9;
        display:flex; align-items:center; gap:14px;
    }
    .school-avatar {
        width:44px; height:44px; border-radius:12px; flex-shrink:0;
        background:linear-gradient(135deg,#6366f1,#10b981);
        display:flex; align-items:center; justify-content:center;
        color:#fff; font-weight:700; font-size:17px;
        overflow:hidden;
        box-shadow:0 4px 12px rgba(99,102,241,.18);
    }
    .school-avatar img { width:100%; height:100%; object-fit:cover; display:block; }
    .school-name  { font-size:15px; font-weight:700; color:#0f172a; }
    .school-meta  { font-size:12px; color:#94a3b8; margin-top:2px; }
    .school-badge {
        margin-left:auto; font-size:11.5px; font-weight:600; color:#4f46e5;
        background:#eef2ff; padding:5px 13px; border-radius:999px; flex-shrink:0;
    }

    .grade-section { padding:0 20px; }
    .grade-label {
        font-size:11px; font-weight:700; color:#9aa4b8;
        text-transform:uppercase; letter-spacing:.08em;
        padding:14px 0 8px;
        display:flex; align-items:center; gap:7px;
    }
    .grade-label::before { content:''; width:6px; height:6px; border-radius:50%; background:#c7d2fe; }

    .classes-grid {
        display:grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap:12px; padding:4px 0 18px;
    }

    .class-card {
        border:1px solid #eef1f7; border-radius:12px; padding:14px 16px;
        transition:border-color .15s ease, box-shadow .15s ease, transform .15s ease;
        cursor:pointer; text-decoration:none; display:block; background:#fff;
    }
    .class-card:hover {
        border-color:#a5b4fc;
        box-shadow:0 8px 22px rgba(99,102,241,.12);
        transform:translateY(-1px);
    }
    .class-top { display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; }
    .class-grade-badge {
        font-size:10.5px; font-weight:600; color:#6366f1;
        background:#eef2ff; padding:3px 9px; border-radius:999px;
    }
    .class-status-active   { font-size:10.5px; font-weight:600; color:#059669; background:#d1fae5; padding:3px 9px; border-radius:999px; }
    .class-status-inactive { font-size:10.5px; font-weight:600; color:#dc2626; background:#fee2e2; padding:3px 9px; border-radius:999px; }
    .class-name   { font-size:14.5px; font-weight:700; color:#0f172a; margin-bottom:3px; }
    .class-medium { font-size:12px; color:#94a3b8; margin-bottom:10px; }
    .class-footer { display:flex; align-items:center; gap:5px; font-size:12px; color:#64748b; padding-top:10px; border-top:1px solid #f1f5f9; }

    /* ── Info notice ──────────────────────────── */
    .info-notice {
        background:linear-gradient(135deg,#fffbeb,#fefce8);
        border:1px solid #fde68a;
        border-radius:12px;
        padding:11px 16px;
        display:flex; align-items:center; gap:10px;
        margin-bottom:24px;
    }

    /* ── Empty state ──────────────────────────── */
    .empty-state {
        background:#fff; border:1px solid #eef1f7; border-radius:16px;
        padding:64px 24px; text-align:center;
    }
    .empty-icon {
        width:56px; height:56px; background:#eef2ff; border-radius:16px;
        display:inline-flex; align-items:center; justify-content:center; margin-bottom:16px;
        color:#6366f1;
    }
    .empty-title { font-size:15px; font-weight:700; color:#0f172a; margin-bottom:6px; }
    .empty-text  { font-size:13px; color:#94a3b8; max-width:320px; margin:0 auto; line-height:1.65; }
</style>


{{-- ── Stats row ──────────────────────────────────────────── --}}
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

{{-- ── Info notice ─────────────────────────────────────────── --}}
<div class="info-notice">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="2" style="flex-shrink:0;">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <span style="font-size:12.5px;color:#92400e;">
        School, grade and class assignments are managed by your administrator.
        Contact them if you need to be added to a class.
    </span>
</div>

{{-- ── Schools & Classes ───────────────────────────────────── --}}
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

    @foreach($schools as $school)
        @php $classCount = $school->grades->flatMap->learningClasses->count(); @endphp
        <div class="school-card">
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
                        <div class="grade-label">Grade {{ $grade->name }}</div>
                        <div class="classes-grid">
                            @foreach($grade->learningClasses as $class)
                                <a href="{{ \App\Filament\Teacher\Resources\LearningClasses\LearningClassResource::getUrl('view', ['record' => $class]) }}" class="class-card">
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
                                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        {{ $class->students_count }} {{ Str::plural('student', $class->students_count) }}
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
@endif

</div>
