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
<div id="teacher-app" style="position:fixed;inset:0;display:flex;background:#f6f8fc;overflow:hidden;font-family:'Inter',sans-serif;z-index:40;">

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
    #teacher-app, #teacher-app * { box-sizing:border-box; font-family:'Inter',sans-serif; }

    /* ══ Sidebar ═══════════════════════════════ */
    #t-sidebar {
        width: 264px; min-width: 264px;
        background: #ffffff;
        border-right: 1px solid #eef2f7;
        display: flex; flex-direction: column;
        height: 100%; overflow: hidden;
        transition: transform .25s ease;
    }

    /* Brand */
    .t-brand {
        padding: 20px 18px 18px;
        display: flex; align-items: center; gap: 11px;
    }
    .t-brand-icon {
        width: 40px; height: 40px; flex-shrink: 0;
        background: linear-gradient(135deg,#6366f1,#10b981);
        border-radius: 12px; display: flex; align-items: center;
        justify-content: center; color:#fff; font-weight:700; font-size:13px;
        box-shadow: 0 6px 14px rgba(99,102,241,.24);
        overflow: hidden;
    }
    .t-brand-icon img { width: 100%; height: 100%; border-radius: inherit; object-fit: cover; }
    .t-brand-name  { font-size:14px; font-weight:700; color:#0f172a; line-height:1.25; letter-spacing:-.01em; }
    .t-brand-label { font-size:11px; color:#94a3b8; margin-top:2px; font-weight:500; }

    /* Nav scroll area */
    #t-nav { flex:1; overflow-y:auto; padding:4px 12px 16px; }
    #t-nav::-webkit-scrollbar { width:4px; }
    #t-nav::-webkit-scrollbar-track { background: transparent; }
    #t-nav::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius:4px; }

    /* Section labels */
    .t-section {
        display:flex; align-items:center; gap:8px;
        font-size:10px; font-weight:700; color:#a3adc2;
        text-transform:uppercase; letter-spacing:.1em;
        padding: 16px 10px 8px;
    }

    /* Nav links */
    .t-link {
        position:relative;
        display:flex; align-items:center; gap:11px;
        padding:9px 12px; border-radius:10px;
        font-size:13.5px; font-weight:500; color:#5a657d;
        text-decoration:none; transition:background .14s ease, color .14s ease;
        margin-bottom:2px;
    }
    .t-link:hover  { background:#f6f8fc; color:#0f172a; }
    .t-link.active { background:#eef2ff; color:#4f46e5; font-weight:600; }
    .t-link svg { width:18px; height:18px; flex-shrink:0; color:#9aa5bb; transition:color .14s ease; }
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

    /* School tree */
    .school-block { margin-bottom:4px; }
    .school-toggle {
        display:flex; align-items:center; gap:10px;
        padding:8px 10px; border-radius:10px;
        font-size:13px; font-weight:600; color:#334155;
        cursor:pointer; transition:background .14s ease;
        user-select:none; width:100%;
        background:none; border:none; text-align:left;
    }
    .school-toggle:hover { background:#f6f8fc; }
    .school-toggle.open { background:#f6f8fc; }
    .school-logo {
        width:30px; height:30px; border-radius:9px; flex-shrink:0;
        display:flex; align-items:center; justify-content:center;
        font-size:12px; font-weight:700; color:#fff;
        background:linear-gradient(135deg,#6366f1,#10b981);
        overflow:hidden;
    }
    .school-logo img { width:100%; height:100%; object-fit:cover; display:block; }
    .school-name { flex:1; min-width:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .school-chevron { width:14px; height:14px; color:#b6c0d4; transition:transform .22s ease; flex-shrink:0; }
    .school-toggle.open .school-chevron { transform:rotate(90deg); }
    .school-children { display:none; padding-left:12px; margin-top:2px; }
    .school-children.open { display:block; }

    /* Grade toggle */
    .grade-toggle {
        display:flex; align-items:center; gap:8px;
        padding:7px 10px; border-radius:8px;
        font-size:12.5px; font-weight:600; color:#69748c;
        cursor:pointer; transition:background .13s ease, color .13s ease;
        user-select:none; width:100%;
        background:none; border:none; text-align:left;
        position:relative;
    }
    .grade-toggle:hover { background:#f6f8fc; color:#1e293b; }
    .grade-toggle.open { color:#334155; }
    .grade-chevron { width:12px; height:12px; color:#b6c0d4; transition:transform .2s ease; flex-shrink:0; }
    .grade-toggle.open .grade-chevron { transform:rotate(90deg); }
    .grade-name { flex:1; min-width:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .grade-count {
        font-size:10.5px; font-weight:600; color:#8b95a9;
        background:#f1f5f9; border-radius:999px; padding:1px 7px;
    }
    .grade-children { display:none; padding-left:10px; }
    .grade-children.open { display:block; }

    /* Class links */
    .class-link {
        display:flex; align-items:center; gap:8px;
        padding:6px 10px; border-radius:8px;
        font-size:12.5px; font-weight:400; color:#6b7690;
        text-decoration:none; transition:background .13s ease, color .13s ease; margin-bottom:1px;
    }
    .class-link:hover { background:#f6f8fc; color:#1e293b; }
    .class-link.active { background:#eef2ff; color:#4f46e5; font-weight:600; }
    .class-dot {
        width:5px; height:5px; border-radius:50%; background:#cbd5e1; flex-shrink:0;
        transition:background .13s ease;
    }
    .class-link.active .class-dot { background:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.15); }

    /* ══ Main area ═════════════════════════════ */
    #t-main { flex:1; display:flex; flex-direction:column; overflow:hidden; min-width:0; }

    /* Top bar */
    #t-topbar {
        height:64px; flex-shrink:0;
        display:flex; align-items:center; justify-content:space-between;
        padding:0 26px;
        border-bottom:1px solid #eef2f7;
        background:#ffffff;
    }
    .t-topbar-left { display:flex; align-items:center; gap:12px; min-width:0; }
    .t-topbar-accent {
        width:4px; height:22px; border-radius:999px; flex-shrink:0;
        background:linear-gradient(180deg,#6366f1,#10b981);
    }
    .t-topbar-title {
        font-size:16px; font-weight:700; color:#0f172a;
        letter-spacing:-.01em;
        white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    }
    .t-topbar-right { display:flex; align-items:center; gap:14px; flex-shrink:0; }
    .t-date-pill {
        display:flex; align-items:center; gap:7px;
        font-size:12px; font-weight:600; color:#69748c;
        background:#f6f8fc; border:1px solid #eef2f7;
        padding:6px 13px; border-radius:999px;
    }
    .t-date-pill svg { width:14px; height:14px; color:#98a2b8; }
    .t-topbar-divider { width:1px; height:26px; background:#eef2f7; }
    .t-tb-btn {
        width:36px; height:36px; border-radius:10px;
        display:flex; align-items:center; justify-content:center;
        color:#64748b; border:1px solid #eef2f7;
        background:#fff; cursor:pointer; transition:all .13s ease; text-decoration:none;
    }
    .t-tb-btn:hover { background:#f6f8fc; color:#1e293b; }
    .t-tb-user {
        display:flex; align-items:center; gap:10px;
        padding:5px 12px 5px 5px;
        background:#f6f8fc; border:1px solid #eef2f7;
        border-radius:999px;
        transition:box-shadow .15s ease, border-color .15s ease, background .15s ease;
        text-decoration:none;
    }
    .t-tb-user:hover { background:#fff; box-shadow:0 6px 16px rgba(99,102,241,.14); border-color:#e0e7ff; }
    .t-avatar {
        width:34px; height:34px; border-radius:50%;
        background:linear-gradient(135deg,#6366f1,#10b981);
        display:flex; align-items:center; justify-content:center;
        font-size:13px; font-weight:700; color:#fff;
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
        background:#f6f8fc;
    }
    #t-content::-webkit-scrollbar { width:8px; }
    #t-content::-webkit-scrollbar-track { background: transparent; }
    #t-content::-webkit-scrollbar-thumb { background: #dbe2ee; border-radius:8px; }

    /* ── Filament content theme (simplified) ─────────────────── */
    #t-content .fi-page { padding: 24px 26px 48px !important; gap: 22px !important; }
    #t-content .fi-page-main { gap: 22px !important; }
    #t-content .fi-page-content { gap: 20px !important; }

    /* Page header */
    #t-content .fi-header { padding: 0 0 4px !important; }
    #t-content .fi-header-heading {
        font-size: 22px !important; font-weight: 800 !important;
        letter-spacing: -.02em !important; color: #0f172a !important;
    }
    #t-content .fi-header-subheading { color: #64748b !important; font-size: 13px !important; }
    #t-content .fi-breadcrumbs { display: none !important; }

    /* Sections / cards */
    #t-content .fi-section {
        background: #ffffff !important;
        border: 1px solid #eef2f7 !important;
        border-radius: 16px !important;
        box-shadow: 0 1px 2px rgba(15,23,42,.03) !important;
        overflow: hidden;
    }
    #t-content .fi-section-header { padding: 16px 20px !important; gap: 6px !important; }
    #t-content .fi-section-header-heading { font-size: 15px !important; font-weight: 700 !important; color: #0f172a !important; }
    #t-content .fi-section-header-description { font-size: 12.5px !important; color: #94a3b8 !important; }
    #t-content .fi-section-content { padding: 18px 20px !important; }

    /* Infolist */
    #t-content .fi-in-entry { padding: 12px 0 !important; }
    #t-content .fi-in-entry-label { font-size: 12px !important; font-weight: 600 !important; color: #64748b !important; }
    #t-content .fi-in-text-item { font-size: 13.5px !important; color: #1e293b !important; }
    #t-content .fi-in-image img { border-radius: 12px !important; }

    /* Tables */
    #t-content .fi-ta-ctn {
        border-radius: 14px !important;
        border: 1px solid #eef2f7 !important;
        background: #ffffff !important;
        box-shadow: 0 1px 2px rgba(15,23,42,.03) !important;
        overflow: hidden;
    }
    #t-content .fi-ta-header { padding: 14px 18px !important; }
    #t-content .fi-ta-header-heading { font-size: 15px !important; font-weight: 700 !important; color: #0f172a !important; }
    #t-content .fi-ta-header-description { font-size: 12.5px !important; color: #94a3b8 !important; }
    #t-content .fi-ta-header-cell {
        font-size: 11px !important; font-weight: 700 !important;
        text-transform: uppercase !important; letter-spacing: .06em !important;
        color: #94a3b8 !important;
        background: #fafbfe !important;
    }
    #t-content .fi-ta-cell { font-size: 13px !important; color: #334155 !important; }
    #t-content .fi-ta-row { transition: background .12s ease; }
    #t-content .fi-ta-row:hover { background: #f8fafc !important; }
    #t-content .fi-ta-actions { gap: 4px !important; }
    #t-content .fi-ta-empty-state-heading { font-size: 14px !important; font-weight: 700 !important; color: #334155 !important; }
    #t-content .fi-ta-empty-state-description { font-size: 12.5px !important; color: #94a3b8 !important; }

    /* Forms */
    #t-content .fi-fo-field-label { font-size: 12.5px !important; font-weight: 600 !important; color: #334155 !important; }
    #t-content .fi-input-wrp {
        border-radius: 10px !important;
        box-shadow: 0 0 0 1px #d8e0ea, 0 1px 2px rgba(15,23,42,.02) !important;
        transition: box-shadow .15s ease !important;
    }
    #t-content .fi-input-wrp:focus-within {
        box-shadow: 0 0 0 1px #6366f1, 0 0 0 4px rgba(99,102,241,.12) !important;
    }
    #t-content .fi-input-wrp.fi-invalid {
        box-shadow: 0 0 0 1px #ef4444, 0 1px 2px rgba(15,23,42,.02) !important;
    }
    #t-content .fi-input-wrp.fi-invalid:focus-within {
        box-shadow: 0 0 0 1px #ef4444, 0 0 0 4px rgba(239,68,68,.15) !important;
    }
    #t-content .fi-input, #t-content .fi-select-input { font-size: 13.5px !important; }
    #t-content .fi-fo-field { gap: 6px !important; }
    #t-content .fi-fo-field-wrp { margin-bottom: 2px; }
    #t-content .fi-fo-repeater-item {
        border-radius: 12px !important;
        border-color: #eef2f7 !important;
        background: #fbfcfe !important;
    }
    #t-content .fi-fo-repeater-item-header { padding: 10px 14px !important; }
    #t-content .fi-fo-rich-editor { border-radius: 12px !important; overflow: hidden; }
    #t-content .fi-fo-toggle { color: #4f46e5 !important; }
    #t-content .fi-fo-file-upload { border-radius: 12px !important; }
    #t-content .fi-in-repeatable-item { border-radius: 12px !important; border-color: #eef2f7 !important; }
    #t-content .fi-in-icon { color: #4f46e5 !important; }
    #t-content .fi-ac { gap: 8px !important; }
    #t-content .fi-sc { gap: 20px !important; }
    #t-content .fi-sc-dense { gap: 12px !important; }

    /* Buttons */
    #t-content .fi-btn { border-radius: 10px !important; font-weight: 600 !important; font-size: 13px !important; }
    #t-content .fi-btn-color-primary { box-shadow: 0 6px 16px rgba(79,70,229,.2) !important; }
    #t-content .fi-icon-btn { border-radius: 9px !important; }
    #t-content .fi-link { font-weight: 600 !important; font-size: 13px !important; }

    /* Badges */
    #t-content .fi-badge { border-radius: 999px !important; font-weight: 600 !important; }

    /* Tabs (relation managers) */
    #t-content .fi-tabs {
        background: #f1f5f9 !important;
        border: 1px solid #eef2f7 !important;
        padding: 5px !important;
        border-radius: 12px !important;
        gap: 4px !important;
    }
    #t-content .fi-tabs-item {
        border-radius: 9px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #64748b !important;
    }
    #t-content .fi-tabs-item.fi-active {
        background: #ffffff !important;
        color: #4f46e5 !important;
        box-shadow: 0 1px 3px rgba(15,23,42,.08) !important;
    }

    /* Dropdowns / modals */
    #t-content .fi-dropdown-panel { border-radius: 14px !important; box-shadow: 0 16px 40px rgba(15,23,42,.14) !important; }
    #t-content .fi-modal-content { border-radius: 16px !important; }
    #t-content .fi-pagination { padding-top: 4px !important; }

    /* sidebar bottom */
    .t-sidebar-footer {
        padding:12px 14px 14px;
        border-top:1px solid #eef2f7;
    }
    .t-user-chip {
        display:flex; align-items:center; gap:10px;
        padding:9px 10px; border-radius:11px;
        margin-bottom:6px;
        background:#f6f8fc;
        border:1px solid #eef2f7;
        transition:background .14s ease;
    }
    .t-user-chip:hover { background:#eef2ff; }
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
        padding:9px 12px; border-radius:10px;
        font-size:13px; font-weight:600; color:#ef4444;
        text-decoration:none; transition:all .16s ease;
        width:100%; border:1px solid #fee2e2; background:#fef2f2; cursor:pointer;
    }
    .t-logout-btn:hover { background:#dc2626; color:#ffffff; border-color:#dc2626; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2); }
    .t-logout-btn svg { width:16px; height:16px; color:#ef4444; transition:color .18s ease, transform .18s ease; }
    .t-logout-btn:hover svg { color:#ffffff; transform: translateX(2px); }

    /* ── Mobile Navigation Controls (hidden on desktop) ── */
    #t-hamburger,
    #t-sidebar-close,
    #t-overlay,
    #t-mobile-nav {
        display: none;
    }

    @media (max-width: 1024px) {
        #t-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: min(285px, 86vw);
            min-width: 0;
            height: 100vh;
            height: 100dvh;
            z-index: 210;
            transform: translateX(-105%);
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.25);
        }
        #t-sidebar.open {
            transform: translateX(0);
        }
        #t-overlay.open {
            display: block;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(2px);
            z-index: 200;
        }
        #t-hamburger {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #334155;
            cursor: pointer;
            flex-shrink: 0;
            transition: all .15s ease;
        }
        #t-hamburger:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }
        #t-sidebar-close {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: auto;
            width: 32px;
            height: 32px;
            border-radius: 999px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #475569;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            flex-shrink: 0;
        }
        #t-mobile-nav {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: #ffffff;
            border-bottom: 1px solid #eef2f7;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            flex-shrink: 0;
        }
        #t-mobile-nav::-webkit-scrollbar { display: none; }
        .t-mnav-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 999px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #475569;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
            flex-shrink: 0;
            transition: all .15s ease;
        }
        .t-mnav-pill.active {
            background: #4f46e5;
            border-color: #4f46e5;
            color: #ffffff;
            box-shadow: 0 3px 10px rgba(79, 70, 229, 0.25);
        }
    }

    @media (max-width: 640px) {
        #t-topbar {
            height: 58px;
            padding: 0 14px;
        }
        .t-topbar-left { gap: 9px; }
        .t-topbar-accent { display: none; }
        .t-topbar-title { font-size: 14.5px; }
        .t-topbar-right { gap: 8px; }
        .t-date-pill { display: none; }
        .t-topbar-divider { display: none; }
        .t-tb-user { padding: 3px; }
        .t-tb-user-info { display: none; }
        #t-content .fi-page { padding: 16px 14px 40px !important; }
        #t-content .fi-header-heading { font-size: 19px !important; }
    }
</style>

<!-- ── SIDEBAR ──────────────────────────────── -->
<aside id="t-sidebar">
    <!-- Brand -->
    <div class="t-brand">
        <div class="t-brand-icon">
            <img src="{{ \App\Services\PlatformSettings::logoUrl() }}" alt="">
        </div>
        <div style="min-width:0;">
            <div class="t-brand-name">{{ \App\Services\PlatformSettings::name() }}</div>
            <div class="t-brand-label">Teacher Portal</div>
        </div>
        <button type="button" id="t-sidebar-close" onclick="tCloseSidebar()" aria-label="Close navigation">✕</button>
    </div>

    <!-- Navigation -->
    <nav id="t-nav">
        <div class="t-section">Menu</div>

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

        <!-- Email Students -->
        <a href="{{ \App\Filament\Teacher\Pages\ComposeStudentEmail::getUrl(panel: 'teacher') }}"
           class="t-link {{ request()->routeIs('filament.teacher.pages.compose-student-email') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
            </svg>
            <span class="t-link-text">Email Students</span>
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

<!-- Mobile backdrop overlay -->
<div id="t-overlay" onclick="tCloseSidebar()"></div>

<!-- ── MAIN ──────────────────────────────────── -->
<div id="t-main">
    <!-- Top bar -->
    <header id="t-topbar">
        <div class="t-topbar-left">
            <button type="button" id="t-hamburger" onclick="tOpenSidebar()" aria-label="Open navigation menu">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
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

    <!-- Mobile Quick Navigation Strip (<= 1024px) -->
    <nav id="t-mobile-nav" aria-label="Quick navigation">
        <a href="{{ route('filament.teacher.pages.teacher-dashboard') }}"
           class="t-mnav-pill {{ request()->routeIs('filament.teacher.pages.teacher-dashboard') && request('tab') !== 'grades' ? 'active' : '' }}">
            <span>📊</span><span>Dashboard</span>
        </a>
        <a href="{{ route('filament.teacher.pages.teacher-dashboard', ['tab' => 'grades']) }}"
           class="t-mnav-pill {{ request()->routeIs('filament.teacher.pages.teacher-dashboard') && request('tab') === 'grades' ? 'active' : '' }}">
            <span>🏆</span><span>Grades</span>
        </a>
        <a href="{{ \App\Filament\Teacher\Pages\Notifications::getUrl(panel: 'teacher') }}"
           class="t-mnav-pill {{ request()->routeIs('filament.teacher.pages.notifications*') ? 'active' : '' }}">
            <span>🔔</span><span>Alerts</span>
            @if(($navUnreadNotificationsCount ?? 0) > 0)
                <span class="t-nav-badge" style="min-width:17px;height:17px;font-size:10px;padding:0 5px;">{{ $navUnreadNotificationsCount > 99 ? '99+' : $navUnreadNotificationsCount }}</span>
            @endif
        </a>
        <a href="{{ \App\Filament\Teacher\Pages\ComposeStudentEmail::getUrl(panel: 'teacher') }}"
           class="t-mnav-pill {{ request()->routeIs('filament.teacher.pages.compose-student-email') ? 'active' : '' }}">
            <span>✉️</span><span>Email</span>
        </a>
        <a href="{{ \App\Filament\Teacher\Pages\MyProfile::getUrl(panel: 'teacher') }}"
           class="t-mnav-pill {{ request()->routeIs('filament.teacher.pages.my-profile') ? 'active' : '' }}">
            <span>👤</span><span>Profile</span>
        </a>
    </nav>

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
    function tOpenSidebar() {
        const sb = document.getElementById('t-sidebar');
        const ov = document.getElementById('t-overlay');
        if (sb) sb.classList.add('open');
        if (ov) ov.classList.add('open');
    }
    function tCloseSidebar() {
        const sb = document.getElementById('t-sidebar');
        const ov = document.getElementById('t-overlay');
        if (sb) sb.classList.remove('open');
        if (ov) ov.classList.remove('open');
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') tCloseSidebar();
    });
</script>
</x-filament-panels::layout.base>
