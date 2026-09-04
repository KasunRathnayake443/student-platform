@php
    $tier = $tier ?? 'junior';
@endphp

<div class="np-root np-t-{{ $tier }}" x-data="{ open: @entangle('open') }">
    <style>
        /* ── Base Drawer & Bell Styles ── */
        .np-root { position: relative; display: inline-flex; align-items: center; font-family: inherit; }

        /* Trigger Bell Buttons by Tier */
        .np-bell {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
            user-select: none;
            outline: none;
        }
        .np-bell:active { transform: scale(0.95); }

        /* Kids Bell */
        .np-t-kids .np-bell {
            width: 48px;
            height: 48px;
            border-radius: 18px;
            background: linear-gradient(135deg, #ffffff 0%, #fdf4ff 100%);
            border: 2.5px solid #f472b6;
            color: #db2777;
            box-shadow: 0 8px 20px -4px rgba(244, 114, 182, 0.4), inset 0 2px 4px rgba(255,255,255,0.8);
            font-size: 1.4rem;
        }
        .np-t-kids .np-bell:hover {
            transform: translateY(-2px) rotate(6deg) scale(1.08);
            box-shadow: 0 12px 24px -4px rgba(219, 39, 119, 0.5);
            background: linear-gradient(135deg, #fff1f2 0%, #fae8ff 100%);
        }
        .np-t-kids .np-bell-icon-svg { display: none; }
        .np-t-kids .np-bell-emoji { display: inline-block; animation: np-kids-bell-wiggle 3s ease-in-out infinite; }
        @keyframes np-kids-bell-wiggle {
            0%, 85%, 100% { transform: rotate(0deg); }
            88% { transform: rotate(-15deg) scale(1.15); }
            92% { transform: rotate(15deg) scale(1.15); }
            96% { transform: rotate(-8deg); }
        }

        /* Junior Bell */
        .np-t-junior .np-bell {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #4f46e5;
            box-shadow: 0 2px 8px -2px rgba(15, 23, 42, 0.08);
        }
        .np-t-junior .np-bell:hover {
            transform: translateY(-2px);
            border-color: #c7d2fe;
            color: #4338ca;
            box-shadow: 0 6px 18px -4px rgba(79, 70, 229, 0.2);
            background: #f8fafc;
        }
        .np-t-junior .np-bell-emoji { display: none; }
        .np-t-junior .np-bell-icon-svg { width: 22px; height: 22px; }

        /* Senior Bell */
        .np-t-senior .np-bell {
            width: 38px;
            height: 38px;
            border-radius: 9px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #475569;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
        }
        .np-t-senior .np-bell:hover {
            transform: translateY(-1px);
            border-color: #ddd6fe;
            color: #7c3aed;
            background: #fdfaff;
            box-shadow: 0 4px 12px -2px rgba(124, 58, 237, 0.15);
        }
        .np-t-senior .np-bell-emoji { display: none; }
        .np-t-senior .np-bell-icon-svg { width: 19px; height: 19px; }

        /* Badge */
        .np-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            line-height: 1;
            z-index: 2;
        }
        .np-t-kids .np-badge {
            min-width: 22px;
            height: 22px;
            padding: 0 5px;
            border-radius: 999px;
            background: linear-gradient(135deg, #f43f5e 0%, #fb7185 100%);
            color: #ffffff;
            font-size: 11px;
            border: 2px solid #ffffff;
            box-shadow: 0 3px 8px rgba(244, 63, 94, 0.5);
            animation: np-badge-pop 2s ease-in-out infinite;
        }
        @keyframes np-badge-pop {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.15); }
        }
        .np-t-junior .np-badge {
            min-width: 19px;
            height: 19px;
            padding: 0 4px;
            border-radius: 999px;
            background: #6366f1;
            color: #ffffff;
            font-size: 10.5px;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 6px rgba(99, 102, 241, 0.4);
        }
        .np-t-senior .np-badge {
            min-width: 18px;
            height: 18px;
            padding: 0 4px;
            border-radius: 999px;
            background: #7c3aed;
            color: #ffffff;
            font-size: 10px;
            border: 2px solid #ffffff;
            box-shadow: 0 1px 4px rgba(124, 58, 237, 0.3);
        }

        /* ── Overlay & Drawer ── */
        .np-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
            z-index: 9998;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.25s ease, visibility 0.25s ease;
        }
        .np-overlay.np-show { opacity: 1; visibility: visible; }

        .np-drawer {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            width: min(440px, 92vw);
            background: #ffffff;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            box-shadow: -16px 0 40px -10px rgba(15, 23, 42, 0.25);
            transform: translateX(100%);
            transition: transform 0.32s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
        }
        .np-drawer.np-open { transform: translateX(0); }

        /* ── KIDS TIER DRAWER STYLING ── */
        .np-t-kids .np-drawer {
            background: linear-gradient(180deg, #fdf4ff 0%, #faf5ff 40%, #ffffff 100%);
            border-left: 5px solid #a855f7;
            font-family: 'Nunito', 'Fredoka One', system-ui, sans-serif;
        }
        .np-t-kids .np-drawer-head {
            background: linear-gradient(135deg, #7c3aed 0%, #db2777 60%, #f97316 100%);
            padding: 1.4rem 1.4rem 1.2rem;
            color: #ffffff;
            position: relative;
            box-shadow: 0 6px 20px rgba(124, 58, 237, 0.25);
        }
        .np-t-kids .np-head-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }
        .np-t-kids .np-title-wrap {
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }
        .np-t-kids .np-head-icon {
            font-size: 1.8rem;
            animation: np-kids-bounce 2s ease-in-out infinite;
        }
        @keyframes np-kids-bounce {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-4px) scale(1.1); }
        }
        .np-t-kids .np-head-text h3 {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 900;
            color: #ffffff;
            text-shadow: 1px 2px 0 rgba(0,0,0,0.15);
            line-height: 1.1;
        }
        .np-t-kids .np-head-text p {
            margin: 0.2rem 0 0;
            font-size: 0.8rem;
            font-weight: 700;
            color: rgba(255,255,255,0.9);
        }
        .np-t-kids .np-close {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.22);
            backdrop-filter: blur(4px);
            border: 1.5px solid rgba(255,255,255,0.4);
            color: #ffffff;
            font-size: 1.3rem;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .np-t-kids .np-close:hover {
            background: rgba(255, 255, 255, 0.35);
            transform: rotate(90deg) scale(1.1);
        }
        .np-t-kids .np-filter-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 1rem;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .np-t-kids .np-filter-pills {
            display: flex;
            gap: 0.4rem;
        }
        .np-t-kids .np-filter-btn {
            border: none;
            background: rgba(255,255,255,0.2);
            color: #ffffff;
            font-weight: 800;
            font-size: 0.78rem;
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .np-t-kids .np-filter-btn.active {
            background: #ffffff;
            color: #7c3aed;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            transform: scale(1.05);
        }
        .np-t-kids .np-mark-all-btn {
            border: none;
            background: #fef08a;
            color: #854d0e;
            font-weight: 800;
            font-size: 0.75rem;
            padding: 0.35rem 0.8rem;
            border-radius: 999px;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,0.12);
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        .np-t-kids .np-mark-all-btn:hover {
            background: #fde047;
            transform: translateY(-1px) scale(1.04);
        }

        /* Kids Items */
        .np-t-kids .np-drawer-body {
            flex: 1;
            overflow-y: auto;
            padding: 1.1rem;
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }
        .np-t-kids .np-item {
            background: #ffffff;
            border: 2px solid #ede9fe;
            border-radius: 1.3rem;
            padding: 1rem 1.15rem;
            display: flex;
            flex-direction: column;
            gap: 0.55rem;
            box-shadow: 0 4px 14px rgba(168, 85, 247, 0.08);
            transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            text-decoration: none;
        }
        .np-t-kids .np-item:hover {
            border-color: #d8b4fe;
            transform: translateY(-2px) scale(1.01);
            box-shadow: 0 8px 22px rgba(139, 92, 246, 0.15);
        }
        .np-t-kids .np-item.np-unread {
            background: linear-gradient(135deg, #fff5fb 0%, #faf5ff 100%);
            border-color: #f472b6;
            border-left: 5px solid #ec4899;
        }
        .np-t-kids .np-item-head {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }
        .np-t-kids .np-item-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%);
            border: 2px solid #ddd6fe;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(124,58,237,0.12);
        }
        .np-t-kids .np-item-meta-top {
            flex: 1;
            min-width: 0;
        }
        .np-t-kids .np-item-title {
            font-size: 0.96rem;
            font-weight: 900;
            color: #4c1d95;
            line-height: 1.25;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .np-t-kids .np-item-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #ec4899;
            box-shadow: 0 0 0 2px #fbcfe8;
            flex-shrink: 0;
        }
        .np-t-kids .np-item-submeta {
            font-size: 0.73rem;
            font-weight: 700;
            color: #9333ea;
            margin-top: 0.15rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }
        .np-t-kids .np-item-body {
            font-size: 0.85rem;
            font-weight: 700;
            color: #6b21a8;
            margin: 0;
            line-height: 1.45;
        }
        .np-t-kids .np-item-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            padding-top: 0.4rem;
            border-top: 1px dashed #f3e8ff;
            flex-wrap: wrap;
        }
        .np-t-kids .np-action-link {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
            color: #ffffff;
            font-weight: 900;
            font-size: 0.75rem;
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            text-decoration: none;
            box-shadow: 0 3px 8px rgba(124, 58, 237, 0.3);
            transition: all 0.15s ease;
        }
        .np-t-kids .np-action-link:hover {
            transform: translateY(-1px) scale(1.05);
            background: linear-gradient(135deg, #6d28d9 0%, #9333ea 100%);
        }
        .np-t-kids .np-read-btn {
            border: 1.5px solid #ddd6fe;
            background: #ffffff;
            color: #7c3aed;
            font-weight: 800;
            font-size: 0.72rem;
            padding: 0.25rem 0.65rem;
            border-radius: 999px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .np-t-kids .np-read-btn:hover {
            background: #f3e8ff;
            border-color: #c084fc;
        }

        /* Kids Drawer Foot */
        .np-t-kids .np-drawer-foot {
            padding: 1.1rem 1.25rem;
            background: #ffffff;
            border-top: 2px solid #f3e8ff;
        }
        .np-t-kids .np-view-all-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            width: 100%;
            background: linear-gradient(135deg, #7c3aed 0%, #db2777 50%, #f97316 100%);
            color: #ffffff;
            font-weight: 900;
            font-size: 0.95rem;
            padding: 0.85rem;
            border-radius: 999px;
            border: none;
            cursor: pointer;
            box-shadow: 0 6px 18px rgba(219, 39, 119, 0.35);
            transition: all 0.2s ease;
        }
        .np-t-kids .np-view-all-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(219, 39, 119, 0.45);
        }

        /* ── JUNIOR TIER DRAWER STYLING (BENTO MODERN) ── */
        .np-t-junior .np-drawer {
            background: #f8fafc;
            border-left: 1px solid #e2e8f0;
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
        }
        .np-t-junior .np-drawer-head {
            background: #ffffff;
            padding: 1.25rem 1.4rem 1.1rem;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.03);
        }
        .np-t-junior .np-head-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }
        .np-t-junior .np-title-wrap {
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .np-t-junior .np-head-icon {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            background: #eef2ff;
            color: #4f46e5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            font-weight: 800;
        }
        .np-t-junior .np-head-text h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 800;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 0.45rem;
        }
        .np-t-junior .np-head-badge {
            font-size: 0.7rem;
            font-weight: 700;
            background: #e0e7ff;
            color: #4338ca;
            padding: 0.15rem 0.5rem;
            border-radius: 999px;
        }
        .np-t-junior .np-head-text p {
            margin: 0.15rem 0 0;
            font-size: 0.78rem;
            color: #64748b;
        }
        .np-t-junior .np-close {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 1.15rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .np-t-junior .np-close:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .np-t-junior .np-filter-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 0.85rem;
            gap: 0.5rem;
        }
        .np-t-junior .np-filter-pills {
            display: flex;
            background: #f1f5f9;
            padding: 0.2rem;
            border-radius: 8px;
            gap: 0.2rem;
        }
        .np-t-junior .np-filter-btn {
            border: none;
            background: transparent;
            color: #64748b;
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.3rem 0.7rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .np-t-junior .np-filter-btn.active {
            background: #ffffff;
            color: #4f46e5;
            font-weight: 700;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
        }
        .np-t-junior .np-mark-all-btn {
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #4f46e5;
            font-weight: 600;
            font-size: 0.73rem;
            padding: 0.3rem 0.65rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .np-t-junior .np-mark-all-btn:hover {
            background: #f8fafc;
            border-color: #c7d2fe;
        }

        /* Junior Items */
        .np-t-junior .np-drawer-body {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
        }
        .np-t-junior .np-item {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.9rem 1.05rem;
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.04);
            transition: all 0.18s ease;
            text-decoration: none;
        }
        .np-t-junior .np-item:hover {
            border-color: #c7d2fe;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.08);
        }
        .np-t-junior .np-item.np-unread {
            background: #fbfbfe;
            border-color: #c7d2fe;
            border-left: 4px solid #6366f1;
        }
        .np-t-junior .np-item-head {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
        }
        .np-t-junior .np-item-icon-box {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            background: #eef2ff;
            color: #4f46e5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .np-t-junior .np-item-meta-top {
            flex: 1;
            min-width: 0;
        }
        .np-t-junior .np-item-title {
            font-size: 0.88rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.3;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }
        .np-t-junior .np-item-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #6366f1;
            flex-shrink: 0;
        }
        .np-t-junior .np-item-submeta {
            font-size: 0.72rem;
            color: #64748b;
            margin-top: 0.15rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }
        .np-t-junior .np-item-body {
            font-size: 0.8rem;
            color: #475569;
            margin: 0;
            line-height: 1.4;
        }
        .np-t-junior .np-item-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.4rem;
            padding-top: 0.35rem;
            border-top: 1px solid #f1f5f9;
        }
        .np-t-junior .np-action-link {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            background: #eef2ff;
            color: #4f46e5;
            font-weight: 600;
            font-size: 0.73rem;
            padding: 0.25rem 0.65rem;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .np-t-junior .np-action-link:hover {
            background: #e0e7ff;
            color: #3730a3;
        }
        .np-t-junior .np-read-btn {
            border: none;
            background: transparent;
            color: #94a3b8;
            font-weight: 600;
            font-size: 0.7rem;
            cursor: pointer;
            transition: color 0.15s ease;
        }
        .np-t-junior .np-read-btn:hover { color: #4f46e5; }

        /* Junior Drawer Foot */
        .np-t-junior .np-drawer-foot {
            padding: 0.9rem 1.15rem;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
        }
        .np-t-junior .np-view-all-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            width: 100%;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: #ffffff;
            font-weight: 700;
            font-size: 0.86rem;
            padding: 0.7rem;
            border-radius: 9px;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
            transition: all 0.15s ease;
        }
        .np-t-junior .np-view-all-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.35);
        }

        /* ── SENIOR TIER DRAWER STYLING (EXECUTIVE SLATE) ── */
        .np-t-senior .np-drawer {
            background: #ffffff;
            border-left: 1px solid #e2e8f0;
            font-family: 'Inter', system-ui, sans-serif;
        }
        .np-t-senior .np-drawer-head {
            background: #ffffff;
            padding: 1.15rem 1.4rem 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .np-t-senior .np-head-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }
        .np-t-senior .np-title-wrap {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .np-t-senior .np-head-icon { display: none; }
        .np-t-senior .np-head-text h3 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.01em;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .np-t-senior .np-head-badge {
            font-size: 0.68rem;
            font-weight: 700;
            background: #f5f3ff;
            color: #7c3aed;
            border: 1px solid #ddd6fe;
            padding: 0.1rem 0.45rem;
            border-radius: 4px;
        }
        .np-t-senior .np-head-text p {
            margin: 0.15rem 0 0;
            font-size: 0.74rem;
            color: #64748b;
        }
        .np-t-senior .np-close {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .np-t-senior .np-close:hover {
            background: #f8fafc;
            color: #0f172a;
        }
        .np-t-senior .np-filter-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 0.75rem;
            gap: 0.5rem;
        }
        .np-t-senior .np-filter-pills {
            display: flex;
            gap: 0.3rem;
        }
        .np-t-senior .np-filter-btn {
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #64748b;
            font-weight: 600;
            font-size: 0.72rem;
            padding: 0.25rem 0.6rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .np-t-senior .np-filter-btn.active {
            background: #0f172a;
            border-color: #0f172a;
            color: #ffffff;
        }
        .np-t-senior .np-mark-all-btn {
            border: none;
            background: transparent;
            color: #7c3aed;
            font-weight: 600;
            font-size: 0.72rem;
            cursor: pointer;
            text-decoration: underline;
        }
        .np-t-senior .np-mark-all-btn:hover { color: #6d28d9; }

        /* Senior Items */
        .np-t-senior .np-drawer-body {
            flex: 1;
            overflow-y: auto;
            padding: 0.9rem 1.1rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .np-t-senior .np-item {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem 0.9rem;
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            transition: all 0.15s ease;
            text-decoration: none;
        }
        .np-t-senior .np-item:hover {
            border-color: #cbd5e1;
            background: #fafafa;
        }
        .np-t-senior .np-item.np-unread {
            background: #fdfaff;
            border-color: #ddd6fe;
            border-left: 3px solid #7c3aed;
        }
        .np-t-senior .np-item-head {
            display: flex;
            align-items: flex-start;
            gap: 0.55rem;
        }
        .np-t-senior .np-item-icon-box {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            flex-shrink: 0;
            color: #475569;
        }
        .np-t-senior .np-item-meta-top {
            flex: 1;
            min-width: 0;
        }
        .np-t-senior .np-item-title {
            font-size: 0.84rem;
            font-weight: 600;
            color: #0f172a;
            line-height: 1.3;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }
        .np-t-senior .np-item-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #7c3aed;
            flex-shrink: 0;
        }
        .np-t-senior .np-item-submeta {
            font-size: 0.7rem;
            color: #94a3b8;
            margin-top: 0.1rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        .np-t-senior .np-item-body {
            font-size: 0.78rem;
            color: #475569;
            margin: 0;
            line-height: 1.4;
        }
        .np-t-senior .np-item-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.4rem;
            padding-top: 0.3rem;
            border-top: 1px solid #f1f5f9;
        }
        .np-t-senior .np-action-link {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            color: #7c3aed;
            font-weight: 600;
            font-size: 0.72rem;
            text-decoration: none;
        }
        .np-t-senior .np-action-link:hover { text-decoration: underline; }
        .np-t-senior .np-read-btn {
            border: none;
            background: transparent;
            color: #94a3b8;
            font-size: 0.68rem;
            cursor: pointer;
        }
        .np-t-senior .np-read-btn:hover { color: #0f172a; }

        /* Senior Drawer Foot */
        .np-t-senior .np-drawer-foot {
            padding: 0.85rem 1.1rem;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
        }
        .np-t-senior .np-view-all-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            width: 100%;
            background: #0f172a;
            color: #ffffff;
            font-weight: 600;
            font-size: 0.82rem;
            padding: 0.6rem;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .np-t-senior .np-view-all-btn:hover {
            background: #1e293b;
        }

        /* ── Empty State ── */
        .np-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 3.5rem 1.5rem;
            gap: 0.5rem;
            color: #94a3b8;
        }
        .np-empty-icon { font-size: 2.5rem; line-height: 1; }
        .np-empty-title { font-weight: 800; font-size: 0.95rem; color: #334155; margin: 0; }
        .np-empty-desc { font-size: 0.8rem; margin: 0; color: #64748b; }
    </style>

    {{-- Bell Trigger Button --}}
    <button type="button" wire:click="toggle" class="np-bell" aria-label="Notifications" title="Notifications">
        <span class="np-bell-emoji">🔔</span>
        <svg class="np-bell-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if ($unread > 0)
            <span class="np-badge">{{ $unread > 99 ? '99+' : $unread }}</span>
        @endif
    </button>

    {{-- Backdrop Overlay --}}
    <div class="np-overlay {{ $open ? 'np-show' : '' }}" wire:click="toggle"></div>

    {{-- Slide-in Drawer --}}
    <div class="np-drawer {{ $open ? 'np-open' : '' }}" aria-hidden="{{ $open ? 'false' : 'true' }}">
        
        {{-- Drawer Header --}}
        <div class="np-drawer-head">
            <div class="np-head-top">
                <div class="np-title-wrap">
                    @if($tier === 'kids')
                        <div class="np-head-icon">📬</div>
                        <div class="np-head-text">
                            <h3>Magic Mailbox! ⭐</h3>
                            <p>All your fun alerts & missions</p>
                        </div>
                    @elseif($tier === 'junior')
                        <div class="np-head-icon">🔔</div>
                        <div class="np-head-text">
                            <h3>Notifications @if($unread > 0)<span class="np-head-badge">{{ $unread }} new</span>@endif</h3>
                            <p>Stay on track with your classes</p>
                        </div>
                    @else
                        <div class="np-head-text">
                            <h3>Notifications @if($unread > 0)<span class="np-head-badge">{{ $unread }} new</span>@endif</h3>
                            <p>Academic announcements & course alerts</p>
                        </div>
                    @endif
                </div>
                <button type="button" class="np-close" wire:click="toggle" aria-label="Close">&times;</button>
            </div>

            {{-- Filter & Actions Toolbar --}}
            <div class="np-filter-bar">
                <div class="np-filter-pills">
                    <button type="button" wire:click="setFilter('all')" class="np-filter-btn {{ $filter === 'all' ? 'active' : '' }}">
                        {{ $tier === 'kids' ? '🌟 All' : 'All' }}
                    </button>
                    <button type="button" wire:click="setFilter('unread')" class="np-filter-btn {{ $filter === 'unread' ? 'active' : '' }}">
                        {{ $tier === 'kids' ? '📬 Unread' : 'Unread' }} {{ $unread > 0 ? "($unread)" : '' }}
                    </button>
                </div>
                @if($unread > 0)
                    <button type="button" wire:click="markAllAsRead" class="np-mark-all-btn">
                        {{ $tier === 'kids' ? '✨ Mark All Read' : 'Mark all read' }}
                    </button>
                @endif
            </div>
        </div>

        {{-- Drawer Body / List --}}
        <div class="np-drawer-body">
            @if ($notifications->isEmpty())
                <div class="np-empty-state">
                    @if($tier === 'kids')
                        <div class="np-empty-icon">🎉</div>
                        <h4 class="np-empty-title" style="color:#7c3aed;font-size:1.1rem;font-weight:900;">Woohoo! All Caught Up! 🌟</h4>
                        <p class="np-empty-desc" style="color:#9333ea;font-weight:700;">No unread messages right now. Go have fun learning! 🚀</p>
                    @elseif($tier === 'junior')
                        <div class="np-empty-icon">✨</div>
                        <h4 class="np-empty-title">All caught up!</h4>
                        <p class="np-empty-desc">No notifications to display right now.</p>
                    @else
                        <div class="np-empty-icon">📭</div>
                        <h4 class="np-empty-title">Inbox Zero</h4>
                        <p class="np-empty-desc">No notifications to review at this time.</p>
                    @endif
                </div>
            @else
                @foreach ($notifications as $n)
                    @php
                        $iconEmoji = match($n['mention_type'] ?? '') {
                            'assignment' => '📝',
                            'quiz'       => '🧠',
                            'lesson'     => '📖',
                            default      => ($n['icon'] && !str_starts_with($n['icon'], 'heroicon') ? $n['icon'] : '🔔'),
                        };
                    @endphp

                    <div class="np-item {{ $n['is_read'] ? 'np-read' : 'np-unread' }}">
                        <div class="np-item-head">
                            <div class="np-item-icon-box">
                                {{ $iconEmoji }}
                            </div>
                            <div class="np-item-meta-top">
                                <h4 class="np-item-title">
                                    {{ $n['title'] ?? 'Notification' }}
                                    @if (! $n['is_read'])
                                        <span class="np-item-dot" title="Unread"></span>
                                    @endif
                                </h4>
                                <div class="np-item-submeta">
                                    <span>{{ $n['sender'] ?? 'Teacher' }}</span>
                                    <span>·</span>
                                    <span>{{ $n['created_at']?->diffForHumans() ?? 'recently' }}</span>
                                </div>
                            </div>
                        </div>

                        @if ($n['body'])
                            <p class="np-item-body">{{ $n['body'] }}</p>
                        @endif

                        <div class="np-item-footer">
                            @if ($n['mention_url'] && $n['mention_label'])
                                <a class="np-action-link" href="{{ $n['mention_url'] }}" wire:click="markAsRead({{ $n['id'] }})">
                                    <span>{{ $tier === 'kids' ? 'Start! 🚀' : '↳ ' . $n['mention_label'] }}</span>
                                </a>
                            @else
                                <span></span>
                            @endif

                            @if (! $n['is_read'])
                                <button type="button" class="np-read-btn" wire:click="markAsRead({{ $n['id'] }})">
                                    {{ $tier === 'kids' ? 'Got it! ⭐' : 'Mark as read' }}
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- Drawer Footer --}}
        <div class="np-drawer-foot">
            <button
                type="button"
                class="np-view-all-btn"
                wire:click="viewAllNotifications"
                x-on:click="open = false; if (window.location.pathname !== '/student' && !document.querySelector('.custom-app-container')) { window.location.href = '/student?tab=notifications'; }"
            >
                @if($tier === 'kids')
                    <span>🌟 Open Full Mailbox 📬</span>
                @elseif($tier === 'junior')
                    <span>Open Notification Hub →</span>
                @else
                    <span>View All Notifications →</span>
                @endif
            </button>
        </div>

    </div>
</div>