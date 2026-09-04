@php
    $tier = $tier ?? 'junior';
    $notifications = $notifications ?? collect();
    $notifStats = $notifStats ?? [
        'total' => $notifications->count(),
        'unread' => $notifications->where('is_read', false)->count(),
        'assignments' => $notifications->where('mention_type', 'assignment')->count(),
        'quizzes' => $notifications->where('mention_type', 'quiz')->count(),
        'lessons' => $notifications->where('mention_type', 'lesson')->count(),
        'announcements' => $notifications->whereNull('mention_type')->count(),
    ];
    $notificationFilter = $notificationFilter ?? 'all';
    $notificationSearch = $notificationSearch ?? '';
@endphp

<div class="nv-root nv-tier-{{ $tier }}">
    <style>
        /* ════════════════════════════════════════════════════════════════
           SHARED BASE STYLES
           ════════════════════════════════════════════════════════════════ */
        .nv-root { width: 100%; }

        /* ════════════════════════════════════════════════════════════════
           1. KIDS TIER NOTIFICATIONS (Playful, Colorful, Bubbly)
           ════════════════════════════════════════════════════════════════ */
        .nv-tier-kids {
            font-family: 'Nunito', 'Fredoka One', system-ui, sans-serif;
            display: flex;
            flex-direction: column;
            gap: 1.75rem;
        }

        /* Kids Hero Header */
        .nv-kids-hero {
            background: linear-gradient(135deg, #7c3aed 0%, #a855f7 25%, #db2777 55%, #f97316 100%);
            border-radius: 1.8rem;
            padding: 2.2rem 2.5rem;
            color: #ffffff;
            box-shadow: 0 12px 36px rgba(124, 58, 237, 0.35);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1.5rem;
        }
        .nv-kids-hero::before {
            content: '';
            position: absolute;
            top: -50%; right: -10%;
            width: 320px; height: 320px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        .nv-kids-hero-left {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            position: relative;
            z-index: 1;
        }
        .nv-kids-mascot {
            font-size: 4rem;
            animation: nv-kids-bounce 2s ease-in-out infinite;
            filter: drop-shadow(0 4px 10px rgba(0,0,0,0.2));
            cursor: pointer;
            user-select: none;
        }
        @keyframes nv-kids-bounce {
            0%, 100% { transform: translateY(0) rotate(-4deg) scale(1); }
            50% { transform: translateY(-12px) rotate(4deg) scale(1.08); }
        }
        .nv-kids-hero-text h2 {
            font-size: 2.2rem;
            font-weight: 900;
            color: #ffffff;
            margin: 0 0 0.25rem;
            text-shadow: 2px 2px 0 rgba(0,0,0,0.15);
            line-height: 1.15;
        }
        .nv-kids-hero-text p {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            color: rgba(255,255,255,0.92);
        }
        .nv-kids-stats-row {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }
        .nv-kids-stat-pill {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
            border: 2px solid rgba(255, 255, 255, 0.35);
            padding: 0.6rem 1.1rem;
            border-radius: 999px;
            color: #ffffff;
            font-weight: 900;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .nv-kids-stat-pill.highlight {
            background: #fef08a;
            color: #854d0e;
            border-color: #fde047;
            animation: nv-kids-pulse 2s infinite;
        }
        @keyframes nv-kids-pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        /* Kids Filter Bar */
        .nv-kids-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            background: #ffffff;
            border: 2.5px solid #ede9fe;
            border-radius: 1.5rem;
            padding: 1rem 1.4rem;
            box-shadow: 0 6px 20px rgba(124, 58, 237, 0.08);
        }
        .nv-kids-filters {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .nv-kids-filter-btn {
            border: 2px solid #ede9fe;
            background: #fdfaff;
            color: #7c3aed;
            font-weight: 900;
            font-size: 0.88rem;
            padding: 0.55rem 1.1rem;
            border-radius: 999px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .nv-kids-filter-btn:hover {
            border-color: #c084fc;
            background: #f3e8ff;
            transform: translateY(-2px);
        }
        .nv-kids-filter-btn.active {
            background: linear-gradient(135deg, #7c3aed 0%, #db2777 100%);
            color: #ffffff;
            border-color: transparent;
            box-shadow: 0 4px 14px rgba(219, 39, 119, 0.35);
            transform: scale(1.05);
        }
        .nv-kids-tool-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .nv-kids-search-box {
            position: relative;
            display: flex;
            align-items: center;
        }
        .nv-kids-search-box input {
            background: #faf5ff;
            border: 2px solid #ede9fe;
            border-radius: 999px;
            padding: 0.5rem 1rem 0.5rem 2.2rem;
            font-size: 0.88rem;
            font-weight: 800;
            color: #4c1d95;
            outline: none;
            transition: all 0.2s;
            width: 180px;
        }
        .nv-kids-search-box input:focus {
            border-color: #a855f7;
            width: 220px;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(168, 85, 247, 0.15);
        }
        .nv-kids-search-icon {
            position: absolute;
            left: 0.8rem;
            font-size: 0.95rem;
            pointer-events: none;
        }
        .nv-kids-mark-all {
            border: 2px solid #fde047;
            background: #fef08a;
            color: #854d0e;
            font-weight: 900;
            font-size: 0.85rem;
            padding: 0.55rem 1.1rem;
            border-radius: 999px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(254, 240, 138, 0.5);
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            white-space: nowrap;
        }
        .nv-kids-mark-all:hover {
            transform: translateY(-2px) scale(1.05);
            background: #fde047;
            box-shadow: 0 6px 14px rgba(253, 224, 71, 0.6);
        }

        /* Kids Notification Cards */
        .nv-kids-grid {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .nv-kids-card {
            background: #ffffff;
            border: 2.5px solid #ede9fe;
            border-radius: 1.6rem;
            padding: 1.4rem 1.6rem;
            display: flex;
            align-items: flex-start;
            gap: 1.25rem;
            box-shadow: 0 6px 20px rgba(124, 58, 237, 0.08);
            transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
        }
        .nv-kids-card:hover {
            border-color: #d8b4fe;
            transform: translateY(-3px) scale(1.008);
            box-shadow: 0 12px 28px rgba(139, 92, 246, 0.16);
        }
        .nv-kids-card.nv-unread {
            background: linear-gradient(135deg, #fff5fb 0%, #faf5ff 100%);
            border-color: #f472b6;
            border-left: 6px solid #ec4899;
        }
        .nv-kids-card-icon-disk {
            width: 56px;
            height: 56px;
            border-radius: 18px;
            background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%);
            border: 2.5px solid #ddd6fe;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.15);
        }
        .nv-kids-card-main {
            flex: 1;
            min-width: 0;
        }
        .nv-kids-card-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .nv-kids-card-title {
            font-size: 1.15rem;
            font-weight: 900;
            color: #4c1d95;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .nv-kids-card-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #ec4899;
            box-shadow: 0 0 0 3px #fbcfe8;
        }
        .nv-kids-card-time {
            font-size: 0.78rem;
            font-weight: 800;
            color: #9333ea;
            background: #f3e8ff;
            padding: 0.25rem 0.65rem;
            border-radius: 999px;
        }
        .nv-kids-sender-tag {
            font-size: 0.8rem;
            font-weight: 800;
            color: #db2777;
            margin-top: 0.2rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }
        .nv-kids-card-body {
            font-size: 0.95rem;
            font-weight: 700;
            color: #6b21a8;
            margin: 0.55rem 0 0;
            line-height: 1.5;
        }
        .nv-kids-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 0.9rem;
            padding-top: 0.75rem;
            border-top: 2px dashed #ede9fe;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .nv-kids-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: linear-gradient(135deg, #7c3aed 0%, #db2777 100%);
            color: #ffffff;
            font-weight: 900;
            font-size: 0.85rem;
            padding: 0.5rem 1.25rem;
            border-radius: 999px;
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(219, 39, 119, 0.3);
            transition: all 0.2s ease;
        }
        .nv-kids-action-btn:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 8px 20px rgba(219, 39, 119, 0.45);
        }
        .nv-kids-read-toggle {
            border: 2px solid #ddd6fe;
            background: #ffffff;
            color: #7c3aed;
            font-weight: 800;
            font-size: 0.78rem;
            padding: 0.4rem 0.9rem;
            border-radius: 999px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .nv-kids-read-toggle:hover {
            background: #f3e8ff;
            border-color: #c084fc;
            transform: scale(1.04);
        }

        /* Kids Empty State */
        .nv-kids-empty {
            background: linear-gradient(135deg, #faf5ff 0%, #fff7ed 100%);
            border: 3px dashed #d8b4fe;
            border-radius: 2rem;
            padding: 4.5rem 2rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
        }
        .nv-kids-empty-mascot { font-size: 4.5rem; animation: nv-kids-bounce 2.5s infinite; }
        .nv-kids-empty h3 { font-size: 1.5rem; font-weight: 900; color: #4c1d95; margin: 0; }
        .nv-kids-empty p { font-size: 1rem; font-weight: 700; color: #7c3aed; margin: 0; }


        /* ════════════════════════════════════════════════════════════════
           2. JUNIOR TIER NOTIFICATIONS (Bento Hub, Indigo/Violet)
           ════════════════════════════════════════════════════════════════ */
        .nv-tier-junior {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            display: flex;
            flex-direction: column;
            gap: 1.4rem;
        }

        /* Junior Bento Header */
        .nv-junior-hero {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 1.5rem 1.8rem;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.04);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            flex-wrap: wrap;
        }
        .nv-junior-hero-title h2 {
            margin: 0 0 0.2rem;
            font-size: 1.35rem;
            font-weight: 800;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .nv-junior-hero-title p {
            margin: 0;
            font-size: 0.85rem;
            color: #64748b;
        }

        /* Bento KPI Row */
        .nv-junior-kpis {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
        }
        .nv-junior-kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 1.1rem 1.25rem;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
            display: flex;
            align-items: center;
            gap: 0.9rem;
            transition: all 0.2s ease;
        }
        .nv-junior-kpi-card:hover {
            border-color: #c7d2fe;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.08);
        }
        .nv-junior-kpi-icon {
            width: 42px;
            height: 42px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        .nv-junior-kpi-icon.bg-indigo { background: #eef2ff; color: #4f46e5; }
        .nv-junior-kpi-icon.bg-violet { background: #f5f3ff; color: #7c3aed; }
        .nv-junior-kpi-icon.bg-pink   { background: #fdf2f8; color: #db2777; }
        .nv-junior-kpi-icon.bg-amber  { background: #fefce8; color: #ca8a04; }
        .nv-junior-kpi-info { flex: 1; min-width: 0; }
        .nv-junior-kpi-val { font-size: 1.35rem; font-weight: 800; color: #0f172a; line-height: 1.1; }
        .nv-junior-kpi-label { font-size: 0.75rem; font-weight: 600; color: #64748b; margin-top: 0.15rem; }

        /* Junior Toolbar */
        .nv-junior-toolbar {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 0.85rem 1.2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .nv-junior-filters {
            display: flex;
            background: #f1f5f9;
            padding: 0.25rem;
            border-radius: 10px;
            gap: 0.25rem;
            flex-wrap: wrap;
        }
        .nv-junior-filter-btn {
            border: none;
            background: transparent;
            color: #64748b;
            font-weight: 600;
            font-size: 0.78rem;
            padding: 0.4rem 0.85rem;
            border-radius: 7px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .nv-junior-filter-btn.active {
            background: #ffffff;
            color: #4f46e5;
            font-weight: 700;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.08);
        }
        .nv-junior-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .nv-junior-search input {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.45rem 0.85rem 0.45rem 2rem;
            font-size: 0.82rem;
            color: #0f172a;
            outline: none;
            transition: all 0.15s;
            width: 170px;
        }
        .nv-junior-search input:focus {
            border-color: #6366f1;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
        }
        .nv-junior-mark-all {
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #4f46e5;
            font-weight: 600;
            font-size: 0.78rem;
            padding: 0.45rem 0.9rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.15s;
            white-space: nowrap;
        }
        .nv-junior-mark-all:hover {
            background: #f8fafc;
            border-color: #c7d2fe;
        }

        /* Junior Bento List */
        .nv-junior-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .nv-junior-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 1.15rem 1.4rem;
            display: flex;
            align-items: flex-start;
            gap: 1.1rem;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.03);
            transition: all 0.2s ease;
        }
        .nv-junior-card:hover {
            border-color: #c7d2fe;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(79, 70, 229, 0.08);
        }
        .nv-junior-card.nv-unread {
            background: #fafbfe;
            border-color: #c7d2fe;
            border-left: 4px solid #6366f1;
        }
        .nv-junior-card-icon {
            width: 44px;
            height: 44px;
            border-radius: 11px;
            background: #eef2ff;
            color: #4f46e5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }
        .nv-junior-card-main { flex: 1; min-width: 0; }
        .nv-junior-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }
        .nv-junior-card-title {
            font-size: 0.98rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.45rem;
        }
        .nv-junior-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #6366f1;
            flex-shrink: 0;
        }
        .nv-junior-card-time {
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 500;
        }
        .nv-junior-card-meta {
            font-size: 0.78rem;
            color: #64748b;
            margin-top: 0.2rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .nv-junior-card-body {
            font-size: 0.86rem;
            color: #475569;
            margin: 0.45rem 0 0;
            line-height: 1.5;
        }
        .nv-junior-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 0.75rem;
            padding-top: 0.6rem;
            border-top: 1px solid #f1f5f9;
            gap: 0.5rem;
        }
        .nv-junior-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: #eef2ff;
            color: #4f46e5;
            font-weight: 600;
            font-size: 0.78rem;
            padding: 0.35rem 0.85rem;
            border-radius: 7px;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .nv-junior-action-btn:hover {
            background: #e0e7ff;
            color: #3730a3;
        }
        .nv-junior-read-toggle {
            border: none;
            background: transparent;
            color: #94a3b8;
            font-weight: 600;
            font-size: 0.75rem;
            cursor: pointer;
            transition: color 0.15s ease;
        }
        .nv-junior-read-toggle:hover { color: #4f46e5; }

        /* Junior Empty */
        .nv-junior-empty {
            background: #ffffff;
            border: 1px dashed #cbd5e1;
            border-radius: 16px;
            padding: 4rem 2rem;
            text-align: center;
            color: #64748b;
        }


        /* ════════════════════════════════════════════════════════════════
           3. SENIOR TIER NOTIFICATIONS (Executive Minimalist Pro)
           ════════════════════════════════════════════════════════════════ */
        .nv-tier-senior {
            font-family: 'Inter', system-ui, sans-serif;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        /* Senior Top Bar */
        .nv-senior-hero {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1.25rem 1.6rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .nv-senior-hero h2 {
            margin: 0 0 0.15rem;
            font-size: 1.15rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.01em;
        }
        .nv-senior-hero p {
            margin: 0;
            font-size: 0.8rem;
            color: #64748b;
        }

        /* Senior Metric Row */
        .nv-senior-metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 0.85rem;
        }
        .nv-senior-metric-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.9rem 1.1rem;
        }
        .nv-senior-metric-label {
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
        }
        .nv-senior-metric-val {
            font-size: 1.35rem;
            font-weight: 800;
            color: #0f172a;
            margin-top: 0.2rem;
        }

        /* Senior Controls */
        .nv-senior-toolbar {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem 1.1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.85rem;
            flex-wrap: wrap;
        }
        .nv-senior-filters {
            display: flex;
            gap: 0.35rem;
            flex-wrap: wrap;
        }
        .nv-senior-filter-btn {
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #64748b;
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.35rem 0.75rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .nv-senior-filter-btn.active {
            background: #0f172a;
            border-color: #0f172a;
            color: #ffffff;
        }
        .nv-senior-actions {
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }
        .nv-senior-search input {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 0.4rem 0.75rem 0.4rem 1.8rem;
            font-size: 0.78rem;
            color: #0f172a;
            outline: none;
            width: 160px;
        }
        .nv-senior-mark-all {
            border: none;
            background: transparent;
            color: #7c3aed;
            font-weight: 600;
            font-size: 0.75rem;
            cursor: pointer;
            text-decoration: underline;
        }

        /* Senior Rows */
        .nv-senior-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .nv-senior-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.9rem 1.15rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            transition: all 0.15s ease;
        }
        .nv-senior-card:hover {
            border-color: #cbd5e1;
            background: #fafafa;
        }
        .nv-senior-card.nv-unread {
            background: #fdfaff;
            border-color: #ddd6fe;
            border-left: 3px solid #7c3aed;
        }
        .nv-senior-card-badge {
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            flex-shrink: 0;
            margin-top: 0.15rem;
        }
        .nv-senior-card-badge.tag-assignment { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .nv-senior-card-badge.tag-quiz       { background: #fdf2f8; color: #be185d; border: 1px solid #fbcfe8; }
        .nv-senior-card-badge.tag-lesson     { background: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe; }
        .nv-senior-card-badge.tag-general    { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

        .nv-senior-card-main { flex: 1; min-width: 0; }
        .nv-senior-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }
        .nv-senior-card-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .nv-senior-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #7c3aed;
            flex-shrink: 0;
        }
        .nv-senior-card-time {
            font-size: 0.72rem;
            color: #94a3b8;
        }
        .nv-senior-card-meta {
            font-size: 0.74rem;
            color: #64748b;
            margin-top: 0.15rem;
        }
        .nv-senior-card-body {
            font-size: 0.82rem;
            color: #475569;
            margin: 0.35rem 0 0;
            line-height: 1.45;
        }
        .nv-senior-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 0.6rem;
            padding-top: 0.45rem;
            border-top: 1px solid #f1f5f9;
        }
        .nv-senior-action-btn {
            color: #7c3aed;
            font-weight: 600;
            font-size: 0.75rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        .nv-senior-action-btn:hover { text-decoration: underline; }
        .nv-senior-read-toggle {
            border: none;
            background: transparent;
            color: #94a3b8;
            font-size: 0.72rem;
            cursor: pointer;
        }
        .nv-senior-read-toggle:hover { color: #0f172a; }

        /* Senior Empty */
        .nv-senior-empty {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 3.5rem 1.5rem;
            text-align: center;
            color: #94a3b8;
            font-size: 0.85rem;
        }
    </style>

    {{-- ══════════════════════════════════════════════════════════════
         KIDS TIER NOTIFICATION PAGE
         ══════════════════════════════════════════════════════════════ --}}
    @if($tier === 'kids')
        {{-- Floating playful icons --}}
        <div style="position:relative;">
            
            {{-- Hero Banner --}}
            <div class="nv-kids-hero">
                <div class="nv-kids-hero-left">
                    <div class="nv-kids-mascot" role="button" title="Tap me! 🎉" onclick="if(window.kidsCelebrate) kidsCelebrate()">⭐</div>
                    <div class="nv-kids-hero-text">
                        <h2>Magic Mailbox! 📬✨</h2>
                        <p>All your fun missions, teacher notes, and learning stars!</p>
                    </div>
                </div>
                <div class="nv-kids-stats-row">
                    <div class="nv-kids-stat-pill {{ $notifStats['unread'] > 0 ? 'highlight' : '' }}">
                        📬 {{ $notifStats['unread'] }} New Alert{{ $notifStats['unread'] === 1 ? '' : 's' }}
                    </div>
                    <div class="nv-kids-stat-pill">
                        🎯 {{ $notifStats['assignments'] }} Mission{{ $notifStats['assignments'] === 1 ? '' : 's' }}
                    </div>
                    <div class="nv-kids-stat-pill">
                        🧠 {{ $notifStats['quizzes'] }} Quiz{{ $notifStats['quizzes'] === 1 ? '' : 'zes' }}
                    </div>
                </div>
            </div>

            {{-- Filter & Action Toolbar --}}
            <div class="nv-kids-toolbar" style="margin-top: 1.25rem;">
                <div class="nv-kids-filters">
                    <button type="button" wire:click="setNotificationFilter('all')" class="nv-kids-filter-btn {{ $notificationFilter === 'all' ? 'active' : '' }}">
                        🌟 All ({{ $notifStats['total'] }})
                    </button>
                    <button type="button" wire:click="setNotificationFilter('unread')" class="nv-kids-filter-btn {{ $notificationFilter === 'unread' ? 'active' : '' }}">
                        📬 Unread ({{ $notifStats['unread'] }})
                    </button>
                    <button type="button" wire:click="setNotificationFilter('assignment')" class="nv-kids-filter-btn {{ $notificationFilter === 'assignment' ? 'active' : '' }}">
                        🎯 Missions ({{ $notifStats['assignments'] }})
                    </button>
                    <button type="button" wire:click="setNotificationFilter('quiz')" class="nv-kids-filter-btn {{ $notificationFilter === 'quiz' ? 'active' : '' }}">
                        🧠 Quizzes ({{ $notifStats['quizzes'] }})
                    </button>
                    <button type="button" wire:click="setNotificationFilter('lesson')" class="nv-kids-filter-btn {{ $notificationFilter === 'lesson' ? 'active' : '' }}">
                        📖 Lessons ({{ $notifStats['lessons'] }})
                    </button>
                    <button type="button" wire:click="setNotificationFilter('announcement')" class="nv-kids-filter-btn {{ $notificationFilter === 'announcement' ? 'active' : '' }}">
                        📢 News ({{ $notifStats['announcements'] }})
                    </button>
                </div>

                <div class="nv-kids-tool-actions">
                    <div class="nv-kids-search-box">
                        <span class="nv-kids-search-icon">🔍</span>
                        <input type="text" wire:model.live.debounce.300ms="notificationSearch" placeholder="Find alerts...">
                    </div>
                    @if($notifStats['unread'] > 0)
                        <button type="button" wire:click="markAllNotificationsAsRead" class="nv-kids-mark-all" onclick="if(window.kidsCelebrate) kidsCelebrate()">
                            ✨ Mark All Read!
                        </button>
                    @endif
                </div>
            </div>

            {{-- Kids Notification Cards List --}}
            <div class="nv-kids-grid" style="margin-top: 1.25rem;">
                @if($notifications->isEmpty())
                    <div class="nv-kids-empty">
                        <div class="nv-kids-empty-mascot">🎉</div>
                        <h3>Woohoo! Your Mailbox is Clear! 🌟</h3>
                        <p>No messages in this folder right now. Great job keeping up! 🚀</p>
                    </div>
                @else
                    @foreach($notifications as $n)
                        @php
                            $emoji = match($n['mention_type'] ?? '') {
                                'assignment' => '🎯',
                                'quiz'       => '🧠',
                                'lesson'     => '📖',
                                default      => '📢',
                            };
                        @endphp
                        <div class="nv-kids-card {{ $n['is_read'] ? '' : 'nv-unread' }}">
                            <div class="nv-kids-card-icon-disk">
                                {{ $emoji }}
                            </div>
                            <div class="nv-kids-card-main">
                                <div class="nv-kids-card-header-row">
                                    <h4 class="nv-kids-card-title">
                                        {{ $n['title'] }}
                                        @if(! $n['is_read'])
                                            <span class="nv-kids-card-dot" title="New alert!"></span>
                                        @endif
                                    </h4>
                                    <span class="nv-kids-card-time">{{ $n['created_at']?->diffForHumans() ?? 'recently' }}</span>
                                </div>
                                <div class="nv-kids-sender-tag">
                                    <span>👩‍🏫 From {{ $n['sender'] ?? 'Teacher' }}</span>
                                </div>
                                @if($n['body'])
                                    <p class="nv-kids-card-body">{{ $n['body'] }}</p>
                                @endif
                                <div class="nv-kids-card-footer">
                                    @if($n['mention_url'])
                                        <a href="{{ $n['mention_url'] }}" wire:click="markNotificationAsRead({{ $n['id'] }})" class="nv-kids-action-btn">
                                            @if(($n['mention_type'] ?? '') === 'assignment')
                                                <span>Start Mission! 🚀</span>
                                            @elseif(($n['mention_type'] ?? '') === 'quiz')
                                                <span>Take Quiz! 🧠</span>
                                            @elseif(($n['mention_type'] ?? '') === 'lesson')
                                                <span>Read Lesson! 📖</span>
                                            @else
                                                <span>Open! 🌟</span>
                                            @endif
                                        </a>
                                    @else
                                        <span></span>
                                    @endif

                                    @if(! $n['is_read'])
                                        <button type="button" wire:click="markNotificationAsRead({{ $n['id'] }})" class="nv-kids-read-toggle">
                                            Got it! ⭐
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

        </div>

    {{-- ══════════════════════════════════════════════════════════════
         JUNIOR TIER NOTIFICATION PAGE (BENTO GRID)
         ══════════════════════════════════════════════════════════════ --}}
    @elseif($tier === 'junior')
        <div class="nv-junior-wrap">
            
            {{-- Bento Top Header --}}
            <div class="nv-junior-hero">
                <div class="nv-junior-hero-title">
                    <h2>Notifications Hub 🔔</h2>
                    <p>Track your assignments, upcoming quizzes, lessons, and school announcements.</p>
                </div>
            </div>

            {{-- 4 Bento KPI Metric Cards --}}
            <div class="nv-junior-kpis" style="margin-top: 1rem;">
                <div class="nv-junior-kpi-card">
                    <div class="nv-junior-kpi-icon bg-indigo">📬</div>
                    <div class="nv-junior-kpi-info">
                        <div class="nv-junior-kpi-val">{{ $notifStats['unread'] }}</div>
                        <div class="nv-junior-kpi-label">Unread Alerts</div>
                    </div>
                </div>
                <div class="nv-junior-kpi-card">
                    <div class="nv-junior-kpi-icon bg-pink">📋</div>
                    <div class="nv-junior-kpi-info">
                        <div class="nv-junior-kpi-val">{{ $notifStats['assignments'] }}</div>
                        <div class="nv-junior-kpi-label">Assignments</div>
                    </div>
                </div>
                <div class="nv-junior-kpi-card">
                    <div class="nv-junior-kpi-icon bg-violet">🧠</div>
                    <div class="nv-junior-kpi-info">
                        <div class="nv-junior-kpi-val">{{ $notifStats['quizzes'] }}</div>
                        <div class="nv-junior-kpi-label">Quizzes</div>
                    </div>
                </div>
                <div class="nv-junior-kpi-card">
                    <div class="nv-junior-kpi-icon bg-amber">📢</div>
                    <div class="nv-junior-kpi-info">
                        <div class="nv-junior-kpi-val">{{ $notifStats['announcements'] }}</div>
                        <div class="nv-junior-kpi-label">Announcements</div>
                    </div>
                </div>
            </div>

            {{-- Toolbar --}}
            <div class="nv-junior-toolbar" style="margin-top: 1rem;">
                <div class="nv-junior-filters">
                    <button type="button" wire:click="setNotificationFilter('all')" class="nv-junior-filter-btn {{ $notificationFilter === 'all' ? 'active' : '' }}">
                        All ({{ $notifStats['total'] }})
                    </button>
                    <button type="button" wire:click="setNotificationFilter('unread')" class="nv-junior-filter-btn {{ $notificationFilter === 'unread' ? 'active' : '' }}">
                        Unread ({{ $notifStats['unread'] }})
                    </button>
                    <button type="button" wire:click="setNotificationFilter('assignment')" class="nv-junior-filter-btn {{ $notificationFilter === 'assignment' ? 'active' : '' }}">
                        Assignments ({{ $notifStats['assignments'] }})
                    </button>
                    <button type="button" wire:click="setNotificationFilter('quiz')" class="nv-junior-filter-btn {{ $notificationFilter === 'quiz' ? 'active' : '' }}">
                        Quizzes ({{ $notifStats['quizzes'] }})
                    </button>
                    <button type="button" wire:click="setNotificationFilter('lesson')" class="nv-junior-filter-btn {{ $notificationFilter === 'lesson' ? 'active' : '' }}">
                        Lessons ({{ $notifStats['lessons'] }})
                    </button>
                    <button type="button" wire:click="setNotificationFilter('announcement')" class="nv-junior-filter-btn {{ $notificationFilter === 'announcement' ? 'active' : '' }}">
                        General ({{ $notifStats['announcements'] }})
                    </button>
                </div>

                <div class="nv-junior-actions">
                    <div class="nv-junior-search" style="position:relative;">
                        <span style="position:absolute;left:0.65rem;top:50%;transform:translateY(-50%);font-size:0.8rem;color:#94a3b8;">🔍</span>
                        <input type="text" wire:model.live.debounce.300ms="notificationSearch" placeholder="Search...">
                    </div>
                    @if($notifStats['unread'] > 0)
                        <button type="button" wire:click="markAllNotificationsAsRead" class="nv-junior-mark-all">
                            ✓ Mark all read
                        </button>
                    @endif
                </div>
            </div>

            {{-- Bento Notifications Feed --}}
            <div class="nv-junior-list" style="margin-top: 1rem;">
                @if($notifications->isEmpty())
                    <div class="nv-junior-empty">
                        <div style="font-size:2.2rem;margin-bottom:0.5rem;">✨</div>
                        <h4 style="font-size:1.05rem;font-weight:700;color:#0f172a;margin:0 0 0.25rem;">No notifications</h4>
                        <p style="margin:0;font-size:0.85rem;">You're all caught up on all course updates.</p>
                    </div>
                @else
                    @foreach($notifications as $n)
                        @php
                            $icon = match($n['mention_type'] ?? '') {
                                'assignment' => '📋',
                                'quiz'       => '🧠',
                                'lesson'     => '📖',
                                default      => '📢',
                            };
                        @endphp
                        <div class="nv-junior-card {{ $n['is_read'] ? '' : 'nv-unread' }}">
                            <div class="nv-junior-card-icon">
                                {{ $icon }}
                            </div>
                            <div class="nv-junior-card-main">
                                <div class="nv-junior-card-top">
                                    <h4 class="nv-junior-card-title">
                                        {{ $n['title'] }}
                                        @if(! $n['is_read'])
                                            <span class="nv-junior-dot" title="Unread"></span>
                                        @endif
                                    </h4>
                                    <span class="nv-junior-card-time">{{ $n['created_at']?->diffForHumans() ?? 'recently' }}</span>
                                </div>
                                <div class="nv-junior-card-meta">
                                    <span>By {{ $n['sender'] ?? 'System' }}</span>
                                </div>
                                @if($n['body'])
                                    <p class="nv-junior-card-body">{{ $n['body'] }}</p>
                                @endif
                                <div class="nv-junior-card-footer">
                                    @if($n['mention_url'] && $n['mention_label'])
                                        <a href="{{ $n['mention_url'] }}" wire:click="markNotificationAsRead({{ $n['id'] }})" class="nv-junior-action-btn">
                                            <span>↳ {{ $n['mention_label'] }}</span>
                                        </a>
                                    @else
                                        <span></span>
                                    @endif

                                    @if(! $n['is_read'])
                                        <button type="button" wire:click="markNotificationAsRead({{ $n['id'] }})" class="nv-junior-read-toggle">
                                            Mark as read
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

        </div>

    {{-- ══════════════════════════════════════════════════════════════
         SENIOR TIER NOTIFICATION PAGE (EXECUTIVE MINIMALIST)
         ══════════════════════════════════════════════════════════════ --}}
    @else
        <div class="nv-senior-wrap">
            
            {{-- Executive Top Bar --}}
            <div class="nv-senior-hero">
                <div>
                    <h2>Academic Notifications</h2>
                    <p>Formal course announcements, pending tasks, deadlines, and grade notices.</p>
                </div>
            </div>

            {{-- 4-Card Summary Strip --}}
            <div class="nv-senior-metrics" style="margin-top: 0.85rem;">
                <div class="nv-senior-metric-card">
                    <div class="nv-senior-metric-label">Unread</div>
                    <div class="nv-senior-metric-val">{{ $notifStats['unread'] }}</div>
                </div>
                <div class="nv-senior-metric-card">
                    <div class="nv-senior-metric-label">Assignments</div>
                    <div class="nv-senior-metric-val">{{ $notifStats['assignments'] }}</div>
                </div>
                <div class="nv-senior-metric-card">
                    <div class="nv-senior-metric-label">Quizzes</div>
                    <div class="nv-senior-metric-val">{{ $notifStats['quizzes'] }}</div>
                </div>
                <div class="nv-senior-metric-card">
                    <div class="nv-senior-metric-label">Announcements</div>
                    <div class="nv-senior-metric-val">{{ $notifStats['announcements'] }}</div>
                </div>
            </div>

            {{-- Controls --}}
            <div class="nv-senior-toolbar" style="margin-top: 0.85rem;">
                <div class="nv-senior-filters">
                    <button type="button" wire:click="setNotificationFilter('all')" class="nv-senior-filter-btn {{ $notificationFilter === 'all' ? 'active' : '' }}">
                        All ({{ $notifStats['total'] }})
                    </button>
                    <button type="button" wire:click="setNotificationFilter('unread')" class="nv-senior-filter-btn {{ $notificationFilter === 'unread' ? 'active' : '' }}">
                        Unread ({{ $notifStats['unread'] }})
                    </button>
                    <button type="button" wire:click="setNotificationFilter('assignment')" class="nv-senior-filter-btn {{ $notificationFilter === 'assignment' ? 'active' : '' }}">
                        Assignments ({{ $notifStats['assignments'] }})
                    </button>
                    <button type="button" wire:click="setNotificationFilter('quiz')" class="nv-senior-filter-btn {{ $notificationFilter === 'quiz' ? 'active' : '' }}">
                        Quizzes ({{ $notifStats['quizzes'] }})
                    </button>
                    <button type="button" wire:click="setNotificationFilter('lesson')" class="nv-senior-filter-btn {{ $notificationFilter === 'lesson' ? 'active' : '' }}">
                        Lessons ({{ $notifStats['lessons'] }})
                    </button>
                    <button type="button" wire:click="setNotificationFilter('announcement')" class="nv-senior-filter-btn {{ $notificationFilter === 'announcement' ? 'active' : '' }}">
                        General ({{ $notifStats['announcements'] }})
                    </button>
                </div>

                <div class="nv-senior-actions">
                    <div class="nv-senior-search" style="position:relative;">
                        <span style="position:absolute;left:0.55rem;top:50%;transform:translateY(-50%);font-size:0.75rem;color:#94a3b8;">🔍</span>
                        <input type="text" wire:model.live.debounce.300ms="notificationSearch" placeholder="Filter notices...">
                    </div>
                    @if($notifStats['unread'] > 0)
                        <button type="button" wire:click="markAllNotificationsAsRead" class="nv-senior-mark-all">
                            Mark all as read
                        </button>
                    @endif
                </div>
            </div>

            {{-- Structured List --}}
            <div class="nv-senior-list" style="margin-top: 0.85rem;">
                @if($notifications->isEmpty())
                    <div class="nv-senior-empty">
                        No notifications match the active filter criteria.
                    </div>
                @else
                    @foreach($notifications as $n)
                        @php
                            $tagClass = match($n['mention_type'] ?? '') {
                                'assignment' => 'tag-assignment',
                                'quiz'       => 'tag-quiz',
                                'lesson'     => 'tag-lesson',
                                default      => 'tag-general',
                            };
                            $tagLabel = match($n['mention_type'] ?? '') {
                                'assignment' => 'Assignment',
                                'quiz'       => 'Quiz',
                                'lesson'     => 'Lesson',
                                default      => 'Announcement',
                            };
                        @endphp
                        <div class="nv-senior-card {{ $n['is_read'] ? '' : 'nv-unread' }}">
                            <span class="nv-senior-card-badge {{ $tagClass }}">{{ $tagLabel }}</span>
                            <div class="nv-senior-card-main">
                                <div class="nv-senior-card-top">
                                    <h4 class="nv-senior-card-title">
                                        {{ $n['title'] }}
                                        @if(! $n['is_read'])
                                            <span class="nv-senior-dot" title="Unread"></span>
                                        @endif
                                    </h4>
                                    <span class="nv-senior-card-time">{{ $n['created_at']?->format('M j, Y · g:i A') ?? '' }}</span>
                                </div>
                                <div class="nv-senior-card-meta">
                                    <span>From: {{ $n['sender'] ?? 'System' }}</span>
                                </div>
                                @if($n['body'])
                                    <p class="nv-senior-card-body">{{ $n['body'] }}</p>
                                @endif
                                <div class="nv-senior-card-footer">
                                    @if($n['mention_url'])
                                        <a href="{{ $n['mention_url'] }}" wire:click="markNotificationAsRead({{ $n['id'] }})" class="nv-senior-action-btn">
                                            <span>Open Resource ({{ $n['mention_label'] ?: $tagLabel }}) ↗</span>
                                        </a>
                                    @else
                                        <span></span>
                                    @endif

                                    @if(! $n['is_read'])
                                        <button type="button" wire:click="markNotificationAsRead({{ $n['id'] }})" class="nv-senior-read-toggle">
                                            Mark as read
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

        </div>
    @endif
</div>
