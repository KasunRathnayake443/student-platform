<x-teacher-layout
    title="Overview"
    :schools="$schools"
    :total-classes="$totalClasses"
    :total-students="$totalStudents"
>
<style>
    .stat-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:20px 22px; }
    .stat-label { font-size:11px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:.06em; margin-bottom:10px; }
    .stat-value { font-size:30px; font-weight:700; color:#0f172a; line-height:1; }
    .stat-sub   { font-size:12px; color:#94a3b8; margin-top:5px; }

    .school-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; overflow:hidden; margin-bottom:18px; }
    .school-header {
        padding:16px 20px; border-bottom:1px solid #f1f5f9;
        background:#fafbfd; display:flex; align-items:center; gap:14px;
    }
    .school-avatar {
        width:42px; height:42px; border-radius:11px; flex-shrink:0;
        background:linear-gradient(135deg,#6366f1,#10b981);
        display:flex; align-items:center; justify-content:center;
        color:#fff; font-weight:700; font-size:16px;
    }
    .school-name  { font-size:15px; font-weight:700; color:#0f172a; }
    .school-meta  { font-size:12px; color:#94a3b8; margin-top:2px; }
    .school-badge {
        margin-left:auto; font-size:11.5px; color:#4f46e5;
        background:#eef2ff; padding:4px 12px; border-radius:999px; font-weight:600; flex-shrink:0;
    }

    .grade-section { padding:0 20px; }
    .grade-label {
        font-size:11px; font-weight:700; color:#94a3b8;
        text-transform:uppercase; letter-spacing:.07em;
        padding:14px 0 8px; border-bottom:1px solid #f8fafc;
        display:flex; align-items:center; gap:6px;
    }
    .grade-label::before { content:''; display:inline-block; width:6px; height:6px; border-radius:50%; background:#cbd5e1; }

    .classes-grid {
        display:grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap:12px; padding:12px 0 16px;
    }

    .class-card {
        border:1px solid #e2e8f0; border-radius:11px; padding:14px 16px;
        transition:border-color .15s, box-shadow .15s; cursor:pointer;
        text-decoration:none; display:block; background:#fff;
    }
    .class-card:hover {
        border-color:#a5b4fc;
        box-shadow:0 4px 16px rgba(99,102,241,.1);
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

    .empty-state {
        background:#fff; border:1px solid #e2e8f0; border-radius:14px;
        padding:60px 24px; text-align:center;
    }
    .empty-icon {
        width:52px; height:52px; background:#f1f5f9; border-radius:14px;
        display:inline-flex; align-items:center; justify-content:center; margin-bottom:14px;
    }
    .empty-title { font-size:15px; font-weight:600; color:#0f172a; margin-bottom:6px; }
    .empty-text  { font-size:13px; color:#94a3b8; max-width:320px; margin:0 auto; line-height:1.6; }
</style>

{{-- ── Stats row ──────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px;">

    <div class="stat-card">
        <div class="stat-label">Schools</div>
        <div class="stat-value">{{ $schools->count() }}</div>
        <div class="stat-sub">Assigned to you</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Grades</div>
        <div class="stat-value">{{ $schools->flatMap->grades->count() }}</div>
        <div class="stat-sub">Across all schools</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Classes</div>
        <div class="stat-value">{{ $totalClasses }}</div>
        <div class="stat-sub">You teach</div>
    </div>

    <div class="stat-card" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);border-color:transparent;">
        <div class="stat-label" style="color:rgba(255,255,255,.6);">Students</div>
        <div class="stat-value" style="color:#fff;">{{ $totalStudents }}</div>
        <div class="stat-sub" style="color:rgba(255,255,255,.6);">Total enrolled</div>
    </div>

</div>

{{-- ── Info notice ─────────────────────────────────────────── --}}
<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:11px 16px;display:flex;align-items:center;gap:10px;margin-bottom:22px;">
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
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m0-6l9-5M3 9l9 5"/>
            </svg>
        </div>
        <div class="empty-title">No schools assigned yet</div>
        <p class="empty-text">Contact your administrator to get assigned to schools, grades, and classes.</p>
    </div>
@else
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
        <h2 style="font-size:15px;font-weight:700;color:#0f172a;">My Schools &amp; Classes</h2>
        <span style="font-size:12px;color:#94a3b8;">{{ $schools->count() }} {{ Str::plural('school', $schools->count()) }}</span>
    </div>

    @foreach($schools as $school)
        @php $classCount = $school->grades->flatMap->learningClasses->count(); @endphp
        <div class="school-card">
            {{-- School Header --}}
            <div class="school-header">
                <div class="school-avatar">{{ strtoupper(substr($school->name, 0, 1)) }}</div>
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
                                <a href="#" class="class-card">
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

</x-teacher-layout>
