@php
    $livewire ??= null;
    $title = $livewire ? $livewire->getTitle() : 'Teacher Portal';

    $teacher = auth()->user()?->teacher;
    $schools = collect();
    $totalClasses = 0;
    $totalStudents = 0;

    if ($teacher) {
        $schools = $teacher->schools()->with([
            'grades' => function ($query) use ($teacher) {
                $query
                    ->whereHas('learningClasses', function ($q) use ($teacher) {
                        $q->whereHas('teachers', fn ($t) => $t->where('teachers.id', $teacher->id));
                    })
                    ->orderBy('name')
                    ->with([
                        'learningClasses' => function ($q) use ($teacher) {
                            $q->whereHas('teachers', fn ($t) => $t->where('teachers.id', $teacher->id))
                                ->orderBy('name')
                                ->withCount('students');
                        },
                    ]);
            },
        ])
        ->orderBy('name')
        ->get();

        $totalClasses  = $schools->flatMap(fn ($s) => $s->grades->flatMap(fn ($g) => $g->learningClasses))->count();
        $totalStudents = $schools->flatMap(fn ($s) => $s->grades->flatMap(fn ($g) => $g->learningClasses))->sum('students_count');
    }
@endphp

<x-filament-panels::layout.base :livewire="$livewire">
<div id="teacher-app" style="position:fixed;inset:0;display:flex;background:#f1f5f9;overflow:hidden;font-family:'Inter',sans-serif;z-index:40;">

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    #teacher-app, #teacher-app * { box-sizing:border-box; font-family:'Inter',sans-serif; }

    /* ── Sidebar ────────────────────────────── */
    #t-sidebar {
        width: 265px; min-width: 265px;
        background: #ffffff;
        border-right: 1px solid #e2e8f0;
        display: flex; flex-direction: column;
        height: 100%; overflow: hidden;
        transition: transform .25s ease;
    }

    /* Brand */
    .t-brand {
        padding: 20px 18px 16px;
        border-bottom: 1px solid #e2e8f0;
        display: flex; align-items: center; gap: 11px;
    }
    .t-brand-icon {
        width: 38px; height: 38px; flex-shrink: 0;
        background: linear-gradient(135deg,#6366f1,#10b981);
        border-radius: 10px; display: flex; align-items: center;
        justify-content: center; color:#fff; font-weight:700; font-size:13px;
    }
    .t-brand-name  { font-size:14px; font-weight:700; color:#1e293b; line-height:1.2; }
    .t-brand-label { font-size:11px; color:#94a3b8; margin-top:2px; }

    /* Nav scroll area */
    #t-nav { flex:1; overflow-y:auto; padding:12px 10px; }
    #t-nav::-webkit-scrollbar { width:4px; }
    #t-nav::-webkit-scrollbar-track { background: transparent; }
    #t-nav::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius:4px; }

    /* Section labels */
    .t-section {
        font-size:10px; font-weight:600; color:#94a3b8;
        text-transform:uppercase; letter-spacing:.08em;
        padding: 12px 10px 5px;
    }

    /* Nav links */
    .t-link {
        display:flex; align-items:center; gap:9px;
        padding:8px 11px; border-radius:8px;
        font-size:13.5px; font-weight:500; color:#475569;
        text-decoration:none; transition:all .13s ease; margin-bottom:1px;
    }
    .t-link:hover  { background:#f1f5f9; color:#1e293b; }
    .t-link.active { background:#eef2ff; color:#4f46e5; }
    .t-link svg { width:16px; height:16px; flex-shrink:0; color:#94a3b8; }
    .t-link:hover svg { color:#475569; }
    .t-link.active svg { color:#4f46e5; }

    /* ── School tree ─────────────────────────── */
    .school-block { margin-bottom:4px; }
    .school-toggle {
        display:flex; align-items:center; gap:8px;
        padding:8px 10px; border-radius:8px;
        font-size:13px; font-weight:600; color:#1e293b;
        cursor:pointer; transition:all .13s;
        user-select:none; width:100%;
        background:none; border:none; text-align:left;
    }
    .school-toggle:hover { background:#f1f5f9; color:#0f172a; }
    .school-icon {
        width:26px; height:26px; border-radius:7px; flex-shrink:0;
        background:linear-gradient(135deg,#6366f1,#10b981);
        display:flex; align-items:center; justify-content:center;
        font-size:11px; font-weight:700; color:#fff;
    }
    .school-name { flex:1; min-width:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .school-chevron { width:14px; height:14px; color:#94a3b8; transition:transform .2s; flex-shrink:0; }
    .school-toggle.open .school-chevron { transform:rotate(90deg); }
    .school-children { display:none; padding-left:10px; }
    .school-children.open { display:block; }

    /* Grade toggle */
    .grade-toggle {
        display:flex; align-items:center; gap:7px;
        padding:6px 10px; border-radius:7px;
        font-size:12.5px; font-weight:500; color:#64748b;
        cursor:pointer; transition:all .12s;
        user-select:none; width:100%;
        background:none; border:none; text-align:left;
    }
    .grade-toggle:hover { background:#f8fafc; color:#1e293b; }
    .grade-icon {
        width:20px; height:20px; border-radius:5px; flex-shrink:0;
        background:#f1f5f9; display:flex; align-items:center;
        justify-content:center;
    }
    .grade-icon svg { width:11px; height:11px; color:#94a3b8; }
    .grade-name { flex:1; min-width:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .grade-chevron { width:12px; height:12px; color:#94a3b8; transition:transform .2s; flex-shrink:0; }
    .grade-toggle.open .grade-chevron { transform:rotate(90deg); }
    .grade-children { display:none; padding-left:8px; }
    .grade-children.open { display:block; }

    /* Class links */
    .class-link {
        display:flex; align-items:center; gap:7px;
        padding:5px 10px; border-radius:6px;
        font-size:12px; font-weight:400; color:#64748b;
        text-decoration:none; transition:all .12s; margin-bottom:1px;
    }
    .class-link:hover { background:#f1f5f9; color:#1e293b; }
    .class-link.active { background:#eef2ff; color:#4f46e5; font-weight:500; }
    .class-dot {
        width:5px; height:5px; border-radius:50%; background:#cbd5e1; flex-shrink:0;
    }
    .class-link.active .class-dot { background:#6366f1; }

    /* ── Main area ───────────────────────────── */
    #t-main { flex:1; display:flex; flex-direction:column; overflow:hidden; min-width:0; }

    /* Top bar */
    #t-topbar {
        height:56px; flex-shrink:0;
        display:flex; align-items:center; justify-content:space-between;
        padding:0 22px;
        border-bottom:1px solid #e2e8f0;
        background:#fff;
    }
    .t-topbar-left { display:flex; align-items:center; gap:10px; }
    .t-topbar-title { font-size:14px; font-weight:600; color:#1e293b; }
    .t-topbar-right { display:flex; align-items:center; gap:8px; }
    .t-tb-btn {
        width:34px; height:34px; border-radius:8px;
        display:flex; align-items:center; justify-content:center;
        color:#64748b; border:1px solid #e2e8f0;
        background:#fff; cursor:pointer; transition:all .12s; text-decoration:none;
    }
    .t-tb-btn:hover { background:#f1f5f9; color:#1e293b; }
    .t-avatar {
        width:34px; height:34px; border-radius:50%;
        background:linear-gradient(135deg,#6366f1,#10b981);
        display:flex; align-items:center; justify-content:center;
        font-size:13px; font-weight:700; color:#fff;
    }

    /* Content area */
    #t-content {
        flex:1; overflow-y:auto; overflow-x:hidden;
        padding:0;
    }
    #t-content::-webkit-scrollbar { width:6px; }
    #t-content::-webkit-scrollbar-track { background: transparent; }
    #t-content::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius:4px; }

    /* Filament component resets inside teacher layout */
    #t-content .fi-page {
        padding: 24px !important;
    }

    /* sidebar bottom */
    .t-sidebar-footer {
        padding:14px 14px;
        border-top:1px solid #e2e8f0;
    }
    .t-logout-btn {
        display:flex; align-items:center; gap:9px;
        padding:8px 11px; border-radius:8px;
        font-size:13px; font-weight:500; color:#64748b;
        text-decoration:none; transition:all .13s;
        width:100%; border:none; background:none; cursor:pointer;
    }
    .t-logout-btn:hover { background:#fee2e2; color:#dc2626; }
    .t-logout-btn svg { width:16px; height:16px; color:#94a3b8; }
    .t-logout-btn:hover svg { color:#dc2626; }
</style>

<!-- ── SIDEBAR ──────────────────────────────── -->
<aside id="t-sidebar">
    <!-- Brand -->
    <div class="t-brand">
        <div class="t-brand-icon">SP</div>
        <div>
            <div class="t-brand-name">Student Platform</div>
            <div class="t-brand-label">Teacher Portal</div>
        </div>
    </div>

    <!-- Navigation -->
    <nav id="t-nav">
        <div class="t-section">Main</div>

        <!-- Dashboard -->
        <a href="{{ route('filament.teacher.pages.teacher-dashboard') }}"
           class="t-link {{ request()->routeIs('filament.teacher.pages.teacher-dashboard') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>

        @if($schools->isNotEmpty())
            <div class="t-section">My Classes</div>

            @foreach($schools as $si => $school)
                <div class="school-block">
                    <button class="school-toggle open" onclick="tToggle(this, 'school-{{ $si }}')">
                        <span class="school-icon">{{ strtoupper(substr($school->name, 0, 1)) }}</span>
                        <span class="school-name">{{ $school->name }}</span>
                        <svg class="school-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    <div class="school-children open" id="school-{{ $si }}">
                        @foreach($school->grades as $gi => $grade)
                            <div>
                                <button class="grade-toggle open" onclick="tToggle(this, 'grade-{{ $si }}-{{ $gi }}')">
                                    <span class="grade-icon">
                                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                    </span>
                                    <span class="grade-name">{{ $grade->name }}</span>
                                    <svg class="grade-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                                <div class="grade-children open" id="grade-{{ $si }}-{{ $gi }}">
                                    @foreach($grade->learningClasses as $class)
                                        <a href="{{ \App\Filament\Teacher\Resources\LearningClasses\LearningClassResource::getUrl('view', ['record' => $class]) }}"
                                           class="class-link {{ request()->routeIs('filament.teacher.resources.learning-classes.view') && request()->route('record') == $class->id ? 'active' : '' }}">
                                            <span class="class-dot"></span>
                                            {{ $class->name }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </nav>

    <!-- Footer -->
    <div class="t-sidebar-footer">
        <form method="POST" action="{{ route('filament.teacher.auth.logout') }}">
            @csrf
            <button type="submit" class="t-logout-btn">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Sign out
            </button>
        </form>
    </div>
</aside>

<!-- ── MAIN ──────────────────────────────────── -->
<div id="t-main">
    <!-- Top bar -->
    <header id="t-topbar">
        <div class="t-topbar-left">
            <span class="t-topbar-title">{{ strip_tags((string) $title) }}</span>
        </div>
        <div class="t-topbar-right">
            @if(auth()->user())
                <div class="t-avatar" title="{{ auth()->user()->name }}">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
            @endif
        </div>
    </header>

    <!-- Page Content (Livewire renders the view into $slot) -->
    <div id="t-content">
        {{ $slot }}
    </div>
</div>

</div><!-- end #teacher-app -->

<script>
    function tToggle(btn, id) {
        const el = document.getElementById(id);
        if (!el) return;
        const open = el.classList.toggle('open');
        btn.classList.toggle('open', open);
    }
</script>
</x-filament-panels::layout.base>
