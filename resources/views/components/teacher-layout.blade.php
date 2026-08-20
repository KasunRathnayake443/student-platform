<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Teacher Portal' }} — Student Platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @filamentStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f8fafc; }

        /* Sidebar */
        .teacher-sidebar {
            width: 260px;
            min-height: 100vh;
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 40;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            padding: 24px 20px 20px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-brand-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #6366f1, #10b981);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }

        .sidebar-brand-text h2 {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
        }

        .sidebar-brand-text p {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 2px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 16px 12px;
            overflow-y: auto;
        }

        .nav-section-label {
            font-size: 10px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 8px 8px 4px;
            margin-top: 8px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            color: #64748b;
            text-decoration: none;
            transition: all 0.15s ease;
            margin-bottom: 2px;
            cursor: pointer;
        }

        .nav-item:hover {
            background: #f1f5f9;
            color: #334155;
        }

        .nav-item.active {
            background: #eef2ff;
            color: #4f46e5;
        }

        .nav-item.active .nav-icon {
            color: #4f46e5;
        }

        .nav-icon {
            width: 18px;
            height: 18px;
            color: #94a3b8;
            flex-shrink: 0;
            transition: color 0.15s;
        }

        .nav-item:hover .nav-icon {
            color: #64748b;
        }

        .nav-badge {
            margin-left: auto;
            background: #e0e7ff;
            color: #4f46e5;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 999px;
        }

        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid #f1f5f9;
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            background: #f8fafc;
            border: 1px solid #f1f5f9;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 13px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .user-info h4 {
            font-size: 13px;
            font-weight: 600;
            color: #0f172a;
            line-height: 1.2;
        }

        .user-info p {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 1px;
        }

        .logout-btn {
            margin-left: auto;
            color: #94a3b8;
            padding: 4px;
            border-radius: 6px;
            transition: all 0.15s;
            display: flex;
            cursor: pointer;
            background: none;
            border: none;
        }

        .logout-btn:hover {
            color: #ef4444;
            background: #fef2f2;
        }

        /* Main content */
        .teacher-main {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Topbar */
        .teacher-topbar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 28px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 30;
        }

        .topbar-title {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
        }

        .topbar-subtitle {
            font-size: 13px;
            color: #94a3b8;
            font-weight: 400;
            margin-left: 8px;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .topbar-icon-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            cursor: pointer;
            transition: all 0.15s;
            text-decoration: none;
        }

        .topbar-icon-btn:hover {
            background: #f1f5f9;
            color: #334155;
        }

        /* Page content */
        .teacher-content {
            flex: 1;
            padding: 28px;
        }

        /* Mobile overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 39;
        }

        @media (max-width: 1024px) {
            .teacher-sidebar { transform: translateX(-100%); }
            .teacher-sidebar.open { transform: translateX(0); }
            .teacher-main { margin-left: 0; }
            .sidebar-overlay.open { display: block; }
        }
    </style>
</head>
<body class="h-full antialiased">

<div class="flex h-full">

    <!-- Sidebar Overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <!-- Sidebar -->
    <aside class="teacher-sidebar" id="teacherSidebar">
        <!-- Brand -->
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon">SP</div>
            <div class="sidebar-brand-text">
                <h2>Student Platform</h2>
                <p>Teacher Portal</p>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="sidebar-nav">
            <div class="nav-section-label">Main</div>

            <a href="{{ route('filament.teacher.pages.teacher-dashboard') }}"
               class="nav-item {{ request()->routeIs('filament.teacher.pages.teacher-dashboard') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
            </a>

            <div class="nav-section-label">Academic</div>

            <a href="{{ route('filament.teacher.resources.students.index') }}"
               class="nav-item {{ request()->routeIs('filament.teacher.resources.students.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Students
            </a>

            <a href="#" class="nav-item {{ request()->is('teacher/attendance*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                Attendance
                <span class="nav-badge">Soon</span>
            </a>

            <a href="#" class="nav-item {{ request()->is('teacher/timetable*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Timetable
                <span class="nav-badge">Soon</span>
            </a>

            <a href="#" class="nav-item {{ request()->is('teacher/grades*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Grades & Marks
                <span class="nav-badge">Soon</span>
            </a>

            <div class="nav-section-label">Communication</div>

            <a href="#" class="nav-item {{ request()->is('teacher/announcements*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                </svg>
                Announcements
                <span class="nav-badge">Soon</span>
            </a>

            <div class="nav-section-label">Account</div>

            <a href="#" class="nav-item">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Settings
            </a>
        </nav>

        <!-- User Footer -->
        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-avatar">
                    {{ strtoupper(substr(auth()->user()->name ?? 'T', 0, 2)) }}
                </div>
                <div class="user-info">
                    <h4>{{ auth()->user()->name ?? 'Teacher' }}</h4>
                    <p>{{ auth()->user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('filament.teacher.auth.logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn" title="Sign out">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main content -->
    <div class="teacher-main">

        <!-- Top bar -->
        <header class="teacher-topbar">
            <div class="flex items-center gap-3">
                <!-- Mobile menu toggle -->
                <button class="topbar-icon-btn lg:hidden" onclick="openSidebar()">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div>
                    <span class="topbar-title">{{ $title ?? 'Dashboard' }}</span>
                    @if(isset($subtitle))
                        <span class="topbar-subtitle">/ {{ $subtitle }}</span>
                    @endif
                </div>
            </div>
            <div class="topbar-actions">
                <a href="#" class="topbar-icon-btn" title="Notifications">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </a>
            </div>
        </header>

        <!-- Page content -->
        <main class="teacher-content">
            {{ $slot }}
        </main>
    </div>
</div>

<script>
    function openSidebar() {
        document.getElementById('teacherSidebar').classList.add('open');
        document.getElementById('sidebarOverlay').classList.add('open');
    }
    function closeSidebar() {
        document.getElementById('teacherSidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('open');
    }
</script>

@filamentScripts
@vite('resources/js/app.js')
</body>
</html>
