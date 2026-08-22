@php
    $title = $title ?? 'Teacher Portal';
    
    $teacher = auth()->user()->teacher;
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

{{-- Single root div required by Livewire --}}
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
    .t-soon {
        margin-left:auto; font-size:9.5px; font-weight:600;
        background:#f1f5f9; color:#94a3b8;
        padding:2px 7px; border-radius:999px;
    }

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
        cursor:pointer; transition:all .13s;
        user-select:none; width:100%;
        background:none; border:none; text-align:left;
    }
    .grade-toggle:hover { background:#f1f5f9; color:#1e293b; }
    .grade-dot { width:6px; height:6px; border-radius:50%; background:#cbd5e1; flex-shrink:0; }
    .grade-chevron { width:12px; height:12px; color:#cbd5e1; transition:transform .2s; margin-left:auto; flex-shrink:0; }
    .grade-toggle.open .grade-chevron { transform:rotate(90deg); }

    .grade-children { display:none; padding-left:16px; }
    .grade-children.open { display:block; }

    /* Class link */
    .class-link {
        display:flex; align-items:center; gap:7px;
        padding:5px 10px 5px 8px; border-radius:6px;
        font-size:12.5px; color:#64748b; margin-bottom:1px;
        text-decoration:none; transition:all .13s;
    }
    .class-link:hover { background:#f1f5f9; color:#1e293b; }
    .class-link.active { background:#eef2ff; color:#4f46e5; }
    .class-dot { width:5px; height:5px; border-radius:50%; background:#cbd5e1; flex-shrink:0; }
    .class-count {
        margin-left:auto; font-size:10px; font-weight:600;
        background:#f1f5f9; color:#64748b;
        padding:1px 6px; border-radius:999px; flex-shrink:0;
    }

    /* ── Sidebar footer ──────────────────────── */
    #t-footer { padding:12px 10px; border-top:1px solid #e2e8f0; }
    .t-user {
        display:flex; align-items:center; gap:9px;
        padding:9px 11px; border-radius:9px;
        background:#f8fafc; border:1px solid #e2e8f0;
    }
    .t-avatar {
        width:32px; height:32px; border-radius:8px; flex-shrink:0;
        background:linear-gradient(135deg,#6366f1,#8b5cf6);
        display:flex; align-items:center; justify-content:center;
        color:#fff; font-size:12px; font-weight:700;
    }
    .t-uname  { font-size:12.5px; font-weight:600; color:#1e293b; line-height:1.2; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:140px; }
    .t-urole  { font-size:10.5px; color:#94a3b8; margin-top:1px; }
    .t-logout {
        margin-left:auto; color:#94a3b8; background:none; border:none;
        padding:5px; border-radius:6px; cursor:pointer; display:flex; transition:all .13s;
    }
    .t-logout:hover { color:#ef4444; background:#fef2f2; }

    /* ── Main ───────────────────────────────── */
    #t-main { flex:1; display:flex; flex-direction:column; overflow:hidden; }

    /* Topbar */
    #t-topbar {
        background:#fff; border-bottom:1px solid #e2e8f0;
        height:58px; padding:0 24px;
        display:flex; align-items:center; justify-content:space-between;
        flex-shrink:0; gap:12px;
    }
    #t-hamburger {
        width:34px; height:34px; border-radius:8px;
        background:#f8fafc; border:1px solid #e2e8f0;
        display:none; align-items:center; justify-content:center;
        color:#64748b; cursor:pointer; transition:all .13s; flex-shrink:0;
    }
    #t-hamburger:hover { background:#f1f5f9; }
    .t-page-title { font-size:16px; font-weight:700; color:#0f172a; }
    .t-topbar-right { display:flex; align-items:center; gap:9px; }
    .t-tb-btn {
        width:34px; height:34px; border-radius:8px;
        background:#f8fafc; border:1px solid #e2e8f0;
        display:flex; align-items:center; justify-content:center;
        color:#64748b; cursor:pointer; transition:all .13s; text-decoration:none;
    }
    .t-tb-btn:hover { background:#f1f5f9; color:#334155; }

    /* Breadcrumb */
    #t-crumb {
        background:#fff; border-bottom:1px solid #f1f5f9;
        padding:8px 24px; display:flex; align-items:center; gap:6px;
        font-size:12px; color:#94a3b8; flex-shrink:0;
    }
    #t-crumb a { color:#6366f1; text-decoration:none; font-weight:500; }
    #t-crumb a:hover { text-decoration:underline; }

    /* Content */
    #t-content { flex:1; overflow-y:auto; padding:24px; }

    /* Mobile overlay */
    #t-overlay {
        display:none; position:fixed; inset:0;
        background:rgba(0,0,0,.45); z-index:200;
    }

    @media (max-width:1024px) {
        #t-sidebar { position:fixed; top:0; left:0; height:100%; z-index:210; transform:translateX(-100%); }
        #t-sidebar.open { transform:translateX(0); }
        #t-overlay.open { display:block; }
        #t-hamburger { display:flex; }
    }
</style>

    <!-- Sidebar -->
    <aside id="t-sidebar">
        <!-- Brand -->
        <div class="t-brand">
            <div class="t-brand-icon">SP</div>
            <div>
                <div class="t-brand-name">Student Platform</div>
                <div class="t-brand-label">Teacher Portal</div>
            </div>
        </div>

        <!-- Nav -->
        <nav id="t-nav">

            <!-- Overview -->
            <div class="t-section">Overview</div>

            <a href="{{ route('filament.teacher.pages.teacher-dashboard') }}"
               class="t-link {{ request()->routeIs('filament.teacher.pages.teacher-dashboard') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>


            <!-- ── My Schools tree ────────────────── -->
            @if($schools->isNotEmpty())
                <div class="t-section" style="margin-top:4px;">My Schools</div>

                @foreach($schools as $si => $school)
                    <div class="school-block">
                        <!-- School toggle -->
                        <button class="school-toggle open" onclick="tToggle(this, 'sc-{{ $si }}')">
                            <span class="school-icon">{{ strtoupper(substr($school->name, 0, 1)) }}</span>
                            <span class="school-name" title="{{ $school->name }}">{{ $school->name }}</span>
                            <svg class="school-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>

                        <!-- Grades -->
                        <div class="school-children open" id="sc-{{ $si }}">
                            @foreach($school->grades as $gi => $grade)
                                <div>
                                    <button class="grade-toggle open" onclick="tToggle(this, 'gr-{{ $si }}-{{ $gi }}')">
                                        <span class="grade-dot"></span>
                                        Grade {{ $grade->name }}
                                        <svg class="grade-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>

                                    <!-- Classes -->
                                    <div class="grade-children open" id="gr-{{ $si }}-{{ $gi }}">
                                        @foreach($grade->learningClasses as $class)
                                            <a href="#" class="class-link">
                                                <span class="class-dot"></span>
                                                {{ $class->name }}
                                                <span class="class-count">{{ $class->students_count }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                <div style="padding:12px 10px;font-size:12px;color:#334155;line-height:1.5;">
                    No schools assigned yet.<br>Contact your administrator.
                </div>
            @endif

            <!-- ── Tools ──────────────────────────── -->
            <div class="t-section" style="margin-top:6px;">Tools</div>

            <a href="#" class="t-link">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                Attendance
                <span class="t-soon">Soon</span>
            </a>

            <a href="#" class="t-link">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Timetable
                <span class="t-soon">Soon</span>
            </a>

            <a href="#" class="t-link">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Grades & Marks
                <span class="t-soon">Soon</span>
            </a>

            <a href="#" class="t-link">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                Announcements
                <span class="t-soon">Soon</span>
            </a>

            <!-- ── Account ──────────────────────── -->
            <div class="t-section" style="margin-top:6px;">Account</div>

            <a href="#" class="t-link">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Settings
            </a>

        </nav>

        <!-- Footer / User -->
        <div id="t-footer">
            <div class="t-user">
                <div class="t-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'T', 0, 2)) }}</div>
                <div style="min-width:0;flex:1;">
                    <div class="t-uname">{{ auth()->user()->name ?? 'Teacher' }}</div>
                    <div class="t-urole">Teacher</div>
                </div>
                <form method="POST" action="{{ route('filament.teacher.auth.logout') }}">
                    @csrf
                    <button type="submit" class="t-logout" title="Sign out">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Mobile overlay -->
    <div id="t-overlay" onclick="tCloseSidebar()"></div>

    <!-- Main -->
    <div id="t-main">
        <!-- Topbar -->
        <header id="t-topbar">
            <div style="display:flex;align-items:center;gap:10px;">
                <button id="t-hamburger" onclick="tOpenSidebar()">
                    <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <span class="t-page-title">{{ $title }}</span>
            </div>
            <div class="t-topbar-right">
                <a href="#" class="t-tb-btn" title="Notifications">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </a>
            </div>
        </header>

        <!-- Content -->
        <main id="t-content">
            {{ $slot }}
        </main>
    </div>

    <script>
        function tToggle(btn, id) {
            const el = document.getElementById(id);
            if (!el) return;
            const open = el.classList.toggle('open');
            btn.classList.toggle('open', open);
        }
        function tOpenSidebar()  {
            document.getElementById('t-sidebar').classList.add('open');
            document.getElementById('t-overlay').classList.add('open');
        }
        function tCloseSidebar() {
            document.getElementById('t-sidebar').classList.remove('open');
            document.getElementById('t-overlay').classList.remove('open');
        }
    </script>
</div>
