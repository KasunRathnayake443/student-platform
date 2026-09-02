@php
    $livewire ??= null;
    $title = $livewire ? $livewire->getTitle() : 'School Admin Portal';
    $user = auth()->user();
    $schoolAdmin = $user?->schoolAdmin;
@endphp

<x-filament-panels::layout.base :livewire="$livewire">
<div id="sa-app" style="position:fixed;inset:0;display:flex;background:#f4f6fa;overflow:hidden;font-family:'Inter',sans-serif;z-index:40;">

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    #sa-app, #sa-app * { box-sizing:border-box; font-family:'Inter',sans-serif; }

    /* ── Sidebar ────────────────────────────── */
    #sa-sidebar {
        width: 264px; min-width: 264px;
        background: linear-gradient(180deg, #ffffff 0%, #fafafe 100%);
        border-right: 1px solid #e8ecf3;
        display: flex; flex-direction: column;
        height: 100%; overflow: hidden;
        transition: transform .25s ease;
    }

    /* Brand */
    .sa-brand {
        padding: 18px 16px 15px;
        border-bottom: 1px solid #f3eefa;
        display: flex; align-items: center; gap: 11px;
    }
    .sa-brand-icon {
        width: 40px; height: 40px; flex-shrink: 0;
        background: linear-gradient(135deg,#d946ef,#86198f);
        border-radius: 12px; display: flex; align-items: center;
        justify-content: center; color:#fff; font-weight:700; font-size:13px;
        box-shadow: 0 6px 14px rgba(217,70,239,.3);
    }
    .sa-brand-name  { font-size:14px; font-weight:700; color:#0f172a; line-height:1.25; letter-spacing:-.01em; }
    .sa-brand-label { font-size:11px; color:#8b95a9; margin-top:2px; font-weight:500; }

    /* Nav scroll area */
    #sa-nav { flex:1; overflow-y:auto; padding:10px 12px 14px; }
    #sa-nav::-webkit-scrollbar { width:4px; }
    #sa-nav::-webkit-scrollbar-track { background: transparent; }
    #sa-nav::-webkit-scrollbar-thumb { background: #d5dbe7; border-radius:4px; }

    /* Section labels */
    .sa-section {
        display:flex; align-items:center; gap:8px;
        font-size:10px; font-weight:700; color:#9aa4b8;
        text-transform:uppercase; letter-spacing:.1em;
        padding: 14px 10px 7px;
    }
    .sa-section::after { content:''; flex:1; height:1px; background:#f3eefa; }

    /* Nav links */
    .sa-link {
        position:relative;
        display:flex; align-items:center; gap:10px;
        padding:9px 12px; border-radius:10px;
        font-size:13.5px; font-weight:500; color:#55607a;
        text-decoration:none; transition:all .14s ease; margin-bottom:2px;
        border:1px solid transparent;
    }
    .sa-link:hover  { background:#faf5fe; color:#0f172a; border-color:#f3eefa; }
    .sa-link.active { background:#fdf2fe; color:#a21caf; font-weight:600; border-color:#f5d0fe; }
    .sa-link.active::before {
        content:''; position:absolute; left:-12px; top:20%;
        height:60%; width:3px; border-radius:0 3px 3px 0; background:#d946ef;
    }
    .sa-link svg { width:17px; height:17px; flex-shrink:0; color:#98a2b8; transition:color .14s ease; }
    .sa-link:hover svg { color:#86198f; }
    .sa-link.active svg { color:#c026d3; }

    /* sidebar bottom */
    .sa-sidebar-footer {
        padding:12px 14px 14px;
        border-top:1px solid #f3eefa;
        background:#fafafe;
    }
    .sa-user-chip {
        display:flex; align-items:center; gap:10px;
        padding:9px 10px; border-radius:11px;
        margin-bottom:6px;
        background:#fff;
        border:1px solid #f3eefa;
        box-shadow:0 1px 3px rgba(15,23,42,.05);
    }
    .sa-user-chip-photo {
        width:32px; height:32px; border-radius:50%; flex-shrink:0;
        background:linear-gradient(135deg,#d946ef,#86198f);
        display:flex; align-items:center; justify-content:center;
        font-size:12px; font-weight:700; color:#fff;
        overflow:hidden;
    }
    .sa-user-chip-photo img { width:100%; height:100%; object-fit:cover; display:block; }
    .sa-user-chip-info { min-width:0; }
    .sa-user-chip-name { font-size:12.5px; font-weight:600; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .sa-user-chip-role { font-size:10.5px; color:#8b95a9; font-weight:500; }
    .sa-logout-btn {
        display:flex; align-items:center; gap:9px;
        padding:8px 12px; border-radius:9px;
        font-size:13px; font-weight:600; color:#ef4444;
        text-decoration:none; transition:all .18s cubic-bezier(0.4, 0, 0.2, 1);
        width:100%; border:1px solid #fecaca; background:#fef2f2; cursor:pointer;
    }
    .sa-logout-btn:hover { background:#dc2626; color:#ffffff; border-color:#dc2626; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2); }
    .sa-logout-btn svg { width:16px; height:16px; color:#ef4444; transition:color .18s ease, transform .18s ease; }
    .sa-logout-btn:hover svg { color:#ffffff; transform: translateX(2px); }

    /* ── Main area ───────────────────────────── */
    #sa-main { flex:1; display:flex; flex-direction:column; overflow:hidden; min-width:0; }

    /* Top bar */
    #sa-topbar {
        height:62px; flex-shrink:0;
        display:flex; align-items:center; justify-content:space-between;
        padding:0 24px;
        border-bottom:1px solid #e8ecf3;
        background:rgba(255,255,255,.72);
        backdrop-filter:blur(10px);
    }
    .sa-topbar-left { display:flex; align-items:center; gap:12px; min-width:0; }
    .sa-topbar-accent {
        width:4px; height:20px; border-radius:999px; flex-shrink:0;
        background:linear-gradient(180deg,#d946ef,#86198f);
    }
    .sa-topbar-title {
        font-size:15.5px; font-weight:700; color:#0f172a;
        letter-spacing:-.01em;
        white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    }
    .sa-topbar-right { display:flex; align-items:center; gap:14px; flex-shrink:0; }
    .sa-date-pill {
        display:flex; align-items:center; gap:7px;
        font-size:12px; font-weight:600; color:#69748c;
        background:#fff; border:1px solid #f3eefa;
        padding:6px 13px; border-radius:999px;
        box-shadow:0 1px 3px rgba(15,23,42,.04);
    }
    .sa-date-pill svg { width:14px; height:14px; color:#98a2b8; }
    .sa-topbar-divider { width:1px; height:26px; background:#e8ecf3; }
    .sa-tb-btn {
        width:34px; height:34px; border-radius:9px;
        display:flex; align-items:center; justify-content:center;
        color:#64748b; border:1px solid #e8ecf3;
        background:#fff; cursor:pointer; transition:all .13s ease; text-decoration:none;
    }
    .sa-tb-btn:hover { background:#faf5fe; color:#86198f; }
    .sa-tb-user {
        display:flex; align-items:center; gap:10px;
        padding:5px 12px 5px 5px;
        background:#fff; border:1px solid #f3eefa;
        border-radius:999px;
        box-shadow:0 1px 3px rgba(15,23,42,.04);
        transition:box-shadow .15s ease, border-color .15s ease;
        text-decoration:none;
    }
    .sa-tb-user:hover { box-shadow:0 4px 14px rgba(217,70,239,.18); border-color:#f5d0fe; }
    .sa-avatar {
        width:32px; height:32px; border-radius:50%;
        background:linear-gradient(135deg,#d946ef,#86198f);
        display:flex; align-items:center; justify-content:center;
        font-size:12.5px; font-weight:700; color:#fff;
        transition:transform .15s ease;
    }
    .sa-tb-user:hover .sa-avatar { transform:scale(1.05); }
    .sa-avatar-img {
        object-fit:cover;
        border:2px solid #e2e8f0;
        background:#fff;
    }
    .sa-tb-user-info   { line-height:1.25; }
    .sa-tb-user-name   { font-size:12.5px; font-weight:600; color:#0f172a; white-space:nowrap; }
    .sa-tb-user-role   { font-size:10.5px; color:#8b95a9; font-weight:500; }

    /* Content area */
    #sa-content {
        flex:1; overflow-y:auto; overflow-x:hidden;
        padding:0;
    }
    #sa-content::-webkit-scrollbar { width:6px; }
    #sa-content::-webkit-scrollbar-track { background: transparent; }
    #sa-content::-webkit-scrollbar-thumb { background: #d5dbe7; border-radius:4px; }

    /* Filament component resets inside school-admin layout */
    #sa-content .fi-page {
        padding: 24px !important;
    }
</style>

<!-- ── SIDEBAR ──────────────────────────────── -->
<aside id="sa-sidebar">
    <div class="sa-brand">
        <div class="sa-brand-icon">SP</div>
        <div>
            <div class="sa-brand-name">Student Platform</div>
            <div class="sa-brand-label">School Admin Portal</div>
        </div>
    </div>

    <nav id="sa-nav">
        <div class="sa-section">Main</div>

        <a href="{{ route('filament.school-admin.pages.school-admin-dashboard') }}"
           class="sa-link {{ request()->routeIs('filament.school-admin.pages.school-admin-dashboard') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>

        <a href="{{ \App\Filament\SchoolAdmin\Pages\MyProfile::getUrl(panel: 'school-admin') }}"
           class="sa-link {{ request()->routeIs('filament.school-admin.pages.my-profile') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            My Profile
        </a>

        <div class="sa-section">School Management</div>
        <a href="{{ \App\Filament\SchoolAdmin\Resources\Schools\SchoolResource::getUrl('index') }}"
           class="sa-link {{ request()->routeIs('filament.school-admin.resources.schools.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4M9 11h.01M15 11h.01M9 15h.01M15 15h.01"/>
            </svg>
            Schools
        </a>

        <div class="sa-section">Institution Management</div>
        <a href="{{ \App\Filament\SchoolAdmin\Resources\Grades\GradeResource::getUrl('index') }}"
           class="sa-link {{ request()->routeIs('filament.school-admin.resources.grades.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            Grades
        </a>
        <a href="{{ \App\Filament\SchoolAdmin\Resources\LearningClasses\LearningClassResource::getUrl('index') }}"
           class="sa-link {{ request()->routeIs('filament.school-admin.resources.learning-classes.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            Learning Classes
        </a>

        <div class="sa-section">School Users</div>
        <a href="{{ \App\Filament\SchoolAdmin\Resources\Teachers\TeacherResource::getUrl('index') }}"
           class="sa-link {{ request()->routeIs('filament.school-admin.resources.teachers.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            Teachers
        </a>
        <a href="{{ \App\Filament\SchoolAdmin\Resources\Students\StudentResource::getUrl('index') }}"
           class="sa-link {{ request()->routeIs('filament.school-admin.resources.students.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m6-3.5V12"/>
            </svg>
            Students
        </a>

        <div class="sa-section">Teaching Content</div>
        <a href="{{ \App\Filament\SchoolAdmin\Resources\Lessons\LessonResource::getUrl('index') }}"
           class="sa-link {{ request()->routeIs('filament.school-admin.resources.lessons.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6"/>
            </svg>
            Lessons
        </a>
        <a href="{{ \App\Filament\SchoolAdmin\Resources\Assignments\AssignmentResource::getUrl('index') }}"
           class="sa-link {{ request()->routeIs('filament.school-admin.resources.assignments.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            Assignments
        </a>
        <a href="{{ \App\Filament\SchoolAdmin\Resources\Quizzes\QuizResource::getUrl('index') }}"
           class="sa-link {{ request()->routeIs('filament.school-admin.resources.quizzes.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Quizzes
        </a>
    </nav>

    <div class="sa-sidebar-footer">
        @if($user)
            <a href="{{ \App\Filament\SchoolAdmin\Pages\MyProfile::getUrl(panel: 'school-admin') }}"
               class="sa-user-chip"
               title="Manage profile"
               style="text-decoration:none;"
            >
                <span class="sa-user-chip-photo">
                    @if($schoolAdmin?->profile_photo_url)
                        <img src="{{ $schoolAdmin->profile_photo_url }}" alt="">
                    @else
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    @endif
                </span>
                <span class="sa-user-chip-info">
                    <span class="sa-user-chip-name" style="display:block;">{{ $user->name }}</span>
                    <span class="sa-user-chip-role" style="display:block;">School Admin</span>
                </span>
            </a>
        @endif

        <form method="POST" action="{{ route('filament.school-admin.auth.logout') }}">
            @csrf
            <button type="submit" class="sa-logout-btn" style="justify-content: center;">
                Sign out
            </button>
        </form>
    </div>
</aside>

<!-- ── MAIN ──────────────────────────────────── -->
<div id="sa-main">
    <header id="sa-topbar">
        <div class="sa-topbar-left">
            <span class="sa-topbar-accent"></span>
            <span class="sa-topbar-title">{{ strip_tags((string) $title) }}</span>
        </div>
        <div class="sa-topbar-right">
            <span class="sa-date-pill">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ now()->format('D, M j') }}
            </span>

            @if($user)
                <span class="sa-topbar-divider"></span>

                <a href="{{ \App\Filament\SchoolAdmin\Pages\MyProfile::getUrl(panel: 'school-admin') }}"
                   title="Manage profile"
                   class="sa-tb-user"
                >
                    @if($photoUrl = $schoolAdmin?->profile_photo_url)
                        <img src="{{ $photoUrl }}" alt="{{ $user->name }}" class="sa-avatar sa-avatar-img">
                    @else
                        <div class="sa-avatar">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                    <span class="sa-tb-user-info">
                        <span class="sa-tb-user-name" style="display:block;">{{ $user->name }}</span>
                        <span class="sa-tb-user-role" style="display:block;">School Admin</span>
                    </span>
                </a>
            @endif
        </div>
    </header>

    <div id="sa-content">
        {{ $slot }}
    </div>
</div>

</div><!-- end #sa-app -->
</x-filament-panels::layout.base>
