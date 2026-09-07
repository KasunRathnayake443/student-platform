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
<div id="teacher-app" style="position:fixed;inset:0;display:flex;background:#f4f6fa;overflow:hidden;font-family:'Inter',sans-serif;z-index:40;">

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    #teacher-app, #teacher-app * { box-sizing:border-box; font-family:'Inter',sans-serif; }

    /* ── Sidebar ────────────────────────────── */
    #t-sidebar {
        width: 272px; min-width: 272px;
        background: linear-gradient(180deg, #ffffff 0%, #fafbfe 100%);
        border-right: 1px solid #e8ecf3;
        display: flex; flex-direction: column;
        height: 100%; overflow: hidden;
        transition: transform .25s ease;
    }

    /* Brand */
    .t-brand {
        padding: 18px 16px 15px;
        border-bottom: 1px solid #eef1f7;
        display: flex; align-items: center; gap: 11px;
    }
    .t-brand-icon {
        width: 40px; height: 40px; flex-shrink: 0;
        background: linear-gradient(135deg,#6366f1,#10b981);
        border-radius: 12px; display: flex; align-items: center;
        justify-content: center; color:#fff; font-weight:700; font-size:13px;
        box-shadow: 0 6px 14px rgba(99,102,241,.28);
    }
    .t-brand-name  { font-size:14px; font-weight:700; color:#0f172a; line-height:1.25; letter-spacing:-.01em; }
    .t-brand-label { font-size:11px; color:#8b95a9; margin-top:2px; font-weight:500; }

    /* Nav scroll area */
    #t-nav { flex:1; overflow-y:auto; padding:10px 12px 14px; }
    #t-nav::-webkit-scrollbar { width:4px; }
    #t-nav::-webkit-scrollbar-track { background: transparent; }
    #t-nav::-webkit-scrollbar-thumb { background: #d5dbe7; border-radius:4px; }

    /* Section labels */
    .t-section {
        display:flex; align-items:center; gap:8px;
        font-size:10px; font-weight:700; color:#9aa4b8;
        text-transform:uppercase; letter-spacing:.1em;
        padding: 14px 10px 7px;
    }
    .t-section::after { content:''; flex:1; height:1px; background:#eef1f7; }

    /* Nav links */
    .t-link {
        position:relative;
        display:flex; align-items:center; gap:10px;
        padding:9px 12px; border-radius:10px;
        font-size:13.5px; font-weight:500; color:#55607a;
        text-decoration:none; transition:all .14s ease; margin-bottom:2px;
        border:1px solid transparent;
    }
    .t-link:hover  { background:#f4f6fb; color:#0f172a; border-color:#eef1f7; }
    .t-link.active { background:#eef2ff; color:#4f46e5; font-weight:600; border-color:#e0e7ff; }
    .t-link.active::before {
        content:''; position:absolute; left:-12px; top:20%;
        height:60%; width:3px; border-radius:0 3px 3px 0; background:#6366f1;
    }
    .t-link svg { width:17px; height:17px; flex-shrink:0; color:#98a2b8; transition:color .14s ease; }
    .t-link:hover svg { color:#475569; }
    .t-link.active svg { color:#4f46e5; }
    .t-link-text { flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .t-nav-badge {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 20px; height: 20px; padding: 0 6px;
        border-radius: 999px; font-size: 11px; font-weight: 700;
        background: #ef4444; color: #fff;
        line-height: 1; flex-shrink: 0;
        box-shadow: 0 2px 6px rgba(239, 68, 68, 0.3);
    }

    /* ── School tree ─────────────────────────── */
    .school-block { margin-bottom:6px; }
    .school-toggle {
        display:flex; align-items:center; gap:10px;
        padding:9px 10px; border-radius:11px;
        font-size:13px; font-weight:600; color:#1e293b;
        cursor:pointer; transition:all .14s ease;
        user-select:none; width:100%;
        background:none; border:none; text-align:left;
    }
    .school-toggle:hover { background:#f4f6fb; }
    .school-toggle.open { background:#fff; box-shadow:0 1px 3px rgba(15,23,42,.06); border:1px solid #eef1f7; }
    .school-logo {
        width:30px; height:30px; border-radius:9px; flex-shrink:0;
        display:flex; align-items:center; justify-content:center;
        font-size:12px; font-weight:700; color:#fff;
        background:linear-gradient(135deg,#6366f1,#10b981);
        overflow:hidden;
    }
    .school-logo img { width:100%; height:100%; object-fit:cover; display:block; }
    .school-name { flex:1; min-width:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .school-chevron { width:14px; height:14px; color:#a5aec2; transition:transform .22s ease; flex-shrink:0; }
    .school-toggle.open .school-chevron { transform:rotate(90deg); }
    .school-children { display:none; padding-left:14px; margin-top:2px; }
    .school-children.open { display:block; }

    /* Grade toggle */
    .grade-toggle {
        display:flex; align-items:center; gap:8px;
        padding:7px 10px; border-radius:8px;
        font-size:12.5px; font-weight:600; color:#69748c;
        cursor:pointer; transition:all .13s ease;
        user-select:none; width:100%;
        background:none; border:none; text-align:left;
        position:relative;
    }
    .grade-toggle:hover { background:#f4f6fb; color:#1e293b; }
    .grade-toggle.open { color:#334155; }
    .grade-chevron { width:12px; height:12px; color:#a5aec2; transition:transform .2s ease; flex-shrink:0; }
    .grade-toggle.open .grade-chevron { transform:rotate(90deg); }
    .grade-name { flex:1; min-width:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .grade-count {
        font-size:10.5px; font-weight:600; color:#8b95a9;
        background:#eef1f7; border-radius:999px; padding:1px 7px;
    }
    .grade-children { display:none; padding-left:10px; }
    .grade-children.open { display:block; }

    /* Class links */
    .class-link {
        display:flex; align-items:center; gap:8px;
        padding:6px 10px; border-radius:8px;
        font-size:12.5px; font-weight:400; color:#6b7690;
        text-decoration:none; transition:all .13s ease; margin-bottom:1px;
    }
    .class-link:hover { background:#f4f6fb; color:#1e293b; }
    .class-link.active { background:#eef2ff; color:#4f46e5; font-weight:600; }
    .class-dot {
        width:5px; height:5px; border-radius:50%; background:#cbd5e1; flex-shrink:0;
        transition:background .13s ease;
    }
    .class-link.active .class-dot { background:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.15); }

    /* ── Main area ───────────────────────────── */
    #t-main { flex:1; display:flex; flex-direction:column; overflow:hidden; min-width:0; }

    /* Top bar */
    #t-topbar {
        height:62px; flex-shrink:0;
        display:flex; align-items:center; justify-content:space-between;
        padding:0 24px;
        border-bottom:1px solid #e8ecf3;
        background:rgba(255,255,255,.72);
        backdrop-filter:blur(10px);
    }
    .t-topbar-left { display:flex; align-items:center; gap:12px; min-width:0; }
    .t-topbar-accent {
        width:4px; height:20px; border-radius:999px; flex-shrink:0;
        background:linear-gradient(180deg,#6366f1,#10b981);
    }
    .t-topbar-title {
        font-size:15.5px; font-weight:700; color:#0f172a;
        letter-spacing:-.01em;
        white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    }
    .t-topbar-right { display:flex; align-items:center; gap:14px; flex-shrink:0; }
    .t-date-pill {
        display:flex; align-items:center; gap:7px;
        font-size:12px; font-weight:600; color:#69748c;
        background:#fff; border:1px solid #eef1f7;
        padding:6px 13px; border-radius:999px;
        box-shadow:0 1px 3px rgba(15,23,42,.04);
    }
    .t-date-pill svg { width:14px; height:14px; color:#98a2b8; }
    .t-topbar-divider { width:1px; height:26px; background:#e8ecf3; }
    .t-tb-btn {
        width:34px; height:34px; border-radius:9px;
        display:flex; align-items:center; justify-content:center;
        color:#64748b; border:1px solid #e8ecf3;
        background:#fff; cursor:pointer; transition:all .13s ease; text-decoration:none;
    }
    .t-tb-btn:hover { background:#f4f6fb; color:#1e293b; }
    .t-tb-user {
        display:flex; align-items:center; gap:10px;
        padding:5px 12px 5px 5px;
        background:#fff; border:1px solid #eef1f7;
        border-radius:999px;
        box-shadow:0 1px 3px rgba(15,23,42,.04);
        transition:box-shadow .15s ease, border-color .15s ease;
        text-decoration:none;
    }
    .t-tb-user:hover { box-shadow:0 4px 14px rgba(99,102,241,.16); border-color:#e0e7ff; }
    .t-avatar {
        width:32px; height:32px; border-radius:50%;
        background:linear-gradient(135deg,#6366f1,#10b981);
        display:flex; align-items:center; justify-content:center;
        font-size:12.5px; font-weight:700; color:#fff;
        transition:transform .15s ease;
    }
    .t-tb-user:hover .t-avatar { transform:scale(1.05); }
    .t-avatar-img {
        object-fit:cover;
        border:2px solid #e2e8f0;
        background:#fff;
    }
    .t-tb-user-info   { line-height:1.25; }
    .t-tb-user-name   { font-size:12.5px; font-weight:600; color:#0f172a; white-space:nowrap; }
    .t-tb-user-role   { font-size:10.5px; color:#8b95a9; font-weight:500; }

    /* Content area */
    #t-content {
        flex:1; overflow-y:auto; overflow-x:hidden;
        padding:0;
    }
    #t-content::-webkit-scrollbar { width:6px; }
    #t-content::-webkit-scrollbar-track { background: transparent; }
    #t-content::-webkit-scrollbar-thumb { background: #d5dbe7; border-radius:4px; }

    /* Filament component resets inside teacher layout */
    #t-content .fi-page {
        padding: 24px !important;
    }

    /* sidebar bottom */
    .t-sidebar-footer {
        padding:12px 14px 14px;
        border-top:1px solid #eef1f7;
        background:#fafbfe;
    }
    .t-user-chip {
        display:flex; align-items:center; gap:10px;
        padding:9px 10px; border-radius:11px;
        margin-bottom:6px;
        background:#fff;
        border:1px solid #eef1f7;
        box-shadow:0 1px 3px rgba(15,23,42,.05);
    }
    .t-user-chip-photo {
        width:32px; height:32px; border-radius:50%; flex-shrink:0;
        background:linear-gradient(135deg,#6366f1,#10b981);
        display:flex; align-items:center; justify-content:center;
        font-size:12px; font-weight:700; color:#fff;
        overflow:hidden;
    }
    .t-user-chip-photo img { width:100%; height:100%; object-fit:cover; display:block; }
    .t-user-chip-info { min-width:0; }
    .t-user-chip-name { font-size:12.5px; font-weight:600; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .t-user-chip-role { font-size:10.5px; color:#8b95a9; font-weight:500; }
    .t-logout-btn {
        display:flex; align-items:center; gap:9px;
        padding:8px 12px; border-radius:9px;
        font-size:13px; font-weight:600; color:#ef4444;
        text-decoration:none; transition:all .18s cubic-bezier(0.4, 0, 0.2, 1);
        width:100%; border:1px solid #fecaca; background:#fef2f2; cursor:pointer;
    }
    .t-logout-btn:hover { background:#dc2626; color:#ffffff; border-color:#dc2626; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2); }
    .t-logout-btn svg { width:16px; height:16px; color:#ef4444; transition:color .18s ease, transform .18s ease; }
    .t-logout-btn:hover svg { color:#ffffff; transform: translateX(2px); }
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
           class="t-link {{ request()->routeIs('filament.teacher.pages.teacher-dashboard') && request('tab') !== 'grades' ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="t-link-text">Dashboard</span>
        </a>

        <!-- Student Grades -->
        <a href="{{ route('filament.teacher.pages.teacher-dashboard', ['tab' => 'grades']) }}"
           class="t-link {{ request()->routeIs('filament.teacher.pages.teacher-dashboard') && request('tab') === 'grades' ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
            </svg>
            <span class="t-link-text">Student Grades</span>
        </a>

        <!-- Notifications -->
        @php
            $navUnreadNotificationsCount = auth()->user()
                ? app(\App\Services\NotificationService::class)->unreadCount(auth()->user())
                : 0;
        @endphp
        <a href="{{ \App\Filament\Teacher\Pages\Notifications::getUrl(panel: 'teacher') }}"
           class="t-link {{ request()->routeIs('filament.teacher.pages.notifications*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span class="t-link-text">Notifications</span>
            @if($navUnreadNotificationsCount > 0)
                <span class="t-nav-badge">{{ $navUnreadNotificationsCount > 99 ? '99+' : $navUnreadNotificationsCount }}</span>
            @endif
        </a>

        <!-- My Profile -->
        <a href="{{ \App\Filament\Teacher\Pages\MyProfile::getUrl(panel: 'teacher') }}"
           class="t-link {{ request()->routeIs('filament.teacher.pages.my-profile') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span class="t-link-text">My Profile</span>
        </a>

        @if($schools->isNotEmpty())
            <div class="t-section">My Classes</div>

            @foreach($schools as $si => $school)
                <div class="school-block">
                    <button class="school-toggle open" onclick="tToggle(this, 'school-{{ $si }}')">
                        <span class="school-logo">
                            @if($school->logo_url)
                                <img src="{{ $school->logo_url }}" alt="{{ $school->name }}">
                            @else
                                {{ strtoupper(substr($school->name, 0, 1)) }}
                            @endif
                        </span>
                        <span class="school-name">{{ $school->name }}</span>
                        <svg class="school-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    <div class="school-children open" id="school-{{ $si }}">
                        @foreach($school->grades as $gi => $grade)
                            <div>
                                <button class="grade-toggle open" onclick="tToggle(this, 'grade-{{ $si }}-{{ $gi }}')">
                                    <svg class="grade-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                    <span class="grade-name">{{ $grade->name }}</span>
                                    <span class="grade-count">{{ $grade->learningClasses->count() }}</span>
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
        @if(auth()->user())
            <a href="{{ \App\Filament\Teacher\Pages\MyProfile::getUrl(panel: 'teacher') }}"
               class="t-user-chip"
               title="Manage profile"
               style="text-decoration:none;"
            >
                <span class="t-user-chip-photo">
                    @if(auth()->user()->teacher?->profile_photo_url)
                        <img src="{{ auth()->user()->teacher->profile_photo_url }}" alt="">
                    @else
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    @endif
                </span>
                <span class="t-user-chip-info">
                    <span class="t-user-chip-name" style="display:block;">{{ auth()->user()->name }}</span>
                    <span class="t-user-chip-role" style="display:block;">Teacher</span>
                </span>
            </a>
        @endif

        <form method="POST" action="{{ route('filament.teacher.auth.logout') }}">
            @csrf
            <button type="submit" class="t-logout-btn" style="justify-content: center;">
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
            <span class="t-topbar-accent"></span>
            <span class="t-topbar-title">{{ strip_tags((string) $title) }}</span>
        </div>
        <div class="t-topbar-right">
            @if(auth()->user())
                <livewire:notification-bell />
            @endif

            <span class="t-date-pill">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ now()->format('D, M j') }}
            </span>

            @if(auth()->user())
                <span class="t-topbar-divider"></span>

                <a href="{{ \App\Filament\Teacher\Pages\MyProfile::getUrl(panel: 'teacher') }}"
                   title="Manage profile"
                   class="t-tb-user"
                >
                    @if($photoUrl = auth()->user()->teacher?->profile_photo_url)
                        <img src="{{ $photoUrl }}" alt="{{ auth()->user()->name }}" class="t-avatar t-avatar-img">
                    @else
                        <div class="t-avatar">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @endif
                    <span class="t-tb-user-info">
                        <span class="t-tb-user-name" style="display:block;">{{ auth()->user()->name }}</span>
                        <span class="t-tb-user-role" style="display:block;">Teacher</span>
                    </span>
                </a>
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
