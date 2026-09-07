<div style="padding:24px 28px; max-width: 100%; box-sizing: border-box;">
@php $activeTab = request('tab') === 'grades' ? 'tab-grades' : 'tab-overview'; @endphp
<style>
    /* ── Core Typography & Resets ─────────────── */
    .t-dash-wrap { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #0f172a; }

    /* ── Hero Banner ─────────────────────────── */
    .t-hero {
        background: linear-gradient(135deg, #312e81 0%, #4338ca 45%, #4f46e5 80%, #6366f1 100%);
        border-radius: 20px;
        padding: 28px 32px;
        color: #ffffff;
        position: relative;
        overflow: hidden;
        margin-bottom: 24px;
        box-shadow: 0 10px 30px -8px rgba(79, 70, 229, 0.35);
    }
    .t-hero::before {
        content: '';
        position: absolute;
        top: -60px; right: -40px;
        width: 240px; height: 240px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255,255,255,0.16) 0%, rgba(255,255,255,0) 70%);
        pointer-events: none;
    }
    .t-hero::after {
        content: '';
        position: absolute;
        bottom: -80px; right: 180px;
        width: 200px; height: 200px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.22) 0%, rgba(16, 185, 129, 0) 70%);
        pointer-events: none;
    }
    .t-hero-inner {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        flex-wrap: wrap;
    }
    .t-hero-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #e0e7ff;
        margin-bottom: 10px;
    }
    .t-hero-title {
        font-size: 24px;
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.25;
        margin: 0 0 6px;
        color: #ffffff;
    }
    .t-hero-sub {
        font-size: 13.5px;
        color: #e0e7ff;
        line-height: 1.5;
        max-width: 640px;
        margin: 0;
        font-weight: 400;
    }
    .t-hero-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .t-btn-hero-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #ffffff;
        color: #4338ca;
        font-size: 13px;
        font-weight: 700;
        padding: 10px 18px;
        border-radius: 12px;
        text-decoration: none;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
        transition: all .16s ease;
    }
    .t-btn-hero-primary:hover {
        background: #f8fafc;
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.18);
        color: #3730a3;
    }
    .t-btn-hero-secondary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.16);
        color: #ffffff;
        font-size: 13px;
        font-weight: 600;
        padding: 10px 18px;
        border-radius: 12px;
        text-decoration: none;
        border: 1px solid rgba(255, 255, 255, 0.28);
        backdrop-filter: blur(8px);
        transition: all .16s ease;
    }
    .t-btn-hero-secondary:hover {
        background: rgba(255, 255, 255, 0.26);
        color: #ffffff;
        transform: translateY(-2px);
    }

    /* ── Stat cards (3 required by tests) ───── */
    .stat-card {
        background: #ffffff;
        border: 1px solid #eef1f7;
        border-radius: 16px;
        padding: 20px 22px;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.07);
        border-color: #cbd5e1;
    }
    .stat-icon {
        width: 48px; height: 48px; flex-shrink: 0;
        border-radius: 13px;
        display: flex; align-items: center; justify-content: center;
    }
    .stat-icon svg { width: 22px; height: 22px; }
    .stat-icon-indigo  { background: #eef2ff; color: #4f46e5; }
    .stat-icon-emerald { background: #ecfdf5; color: #059669; }
    .stat-icon-violet  { background: #f5f3ff; color: #7c3aed; }
    .stat-body   { min-width: 0; flex: 1; }
    .stat-label  { font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .08em; }
    .stat-value  { font-size: 32px; font-weight: 800; color: #0f172a; line-height: 1.15; letter-spacing: -.02em; margin-top: 2px; }
    .stat-sub    { font-size: 12px; color: #94a3b8; margin-top: 2px; font-weight: 500; }

    /* ── Secondary Highlights Bar ────────────── */
    .t-summary-strip {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 24px;
    }
    .t-summary-pill {
        background: #ffffff;
        border: 1px solid #eef1f7;
        border-radius: 14px;
        padding: 14px 18px;
        display: flex;
        align-items: center;
        gap: 13px;
        transition: all .15s ease;
    }
    .t-summary-pill:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
    }
    .t-pill-icon {
        width: 38px; height: 38px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .t-pill-icon svg { width: 19px; height: 19px; }
    .t-pill-blue   { background: #eff6ff; color: #2563eb; }
    .t-pill-amber  { background: #fffbeb; color: #d97706; }
    .t-pill-rose   { background: #fff1f2; color: #e11d48; }
    .t-pill-teal   { background: #f0fdf4; color: #0d9488; }
    .t-pill-num    { font-size: 20px; font-weight: 800; color: #0f172a; line-height: 1.1; }
    .t-pill-text   { font-size: 11.5px; font-weight: 500; color: #64748b; margin-top: 2px; }

    /* ── Attention / Pending Grading Card ────── */
    .t-attention-card {
        background: #ffffff;
        border: 1px solid #eef1f7;
        border-radius: 16px;
        padding: 20px 24px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
    }
    .t-attention-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f1f5f9;
    }
    .t-attention-title-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .t-attention-badge-alert {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        background: #fef2f2;
        color: #ef4444;
        border: 1px solid #fee2e2;
    }
    .t-attention-badge-ok {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #d1fae5;
    }
    .t-sub-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        margin-bottom: 8px;
        transition: background .13s ease;
    }
    .t-sub-item:hover { background: #f1f5f9; }
    .t-sub-meta { display: flex; align-items: center; gap: 12px; min-width: 0; }
    .t-sub-avatar {
        width: 32px; height: 32px; border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff; font-size: 12px; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .t-sub-name { font-size: 13px; font-weight: 600; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .t-sub-assignment { font-size: 12px; color: #64748b; margin-top: 1px; }
    .t-sub-time { font-size: 11px; color: #94a3b8; font-weight: 500; }
    .t-btn-grade {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 13px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        background: #4f46e5;
        color: #ffffff;
        text-decoration: none;
        transition: background .13s ease;
        flex-shrink: 0;
    }
    .t-btn-grade:hover { background: #4338ca; }

    /* ── Split Grid (Quizzes & Lessons) ─────── */
    .t-split-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }
    .t-card-box {
        background: #ffffff;
        border: 1px solid #eef1f7;
        border-radius: 16px;
        padding: 20px 22px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
    }
    .t-box-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f1f5f9;
    }
    .t-box-title {
        font-size: 14.5px;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .t-box-action {
        font-size: 12px;
        font-weight: 600;
        color: #4f46e5;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .t-box-action:hover { text-decoration: underline; }
    .t-list-card {
        padding: 11px 14px;
        border-radius: 12px;
        border: 1px solid #f1f5f9;
        background: #f8fafc;
        margin-bottom: 9px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        text-decoration: none;
        transition: all .13s ease;
    }
    .t-list-card:hover {
        border-color: #cbd5e1;
        background: #ffffff;
        transform: translateX(3px);
        box-shadow: 0 3px 10px rgba(15, 23, 42, 0.04);
    }
    .t-list-main { min-width: 0; }
    .t-list-title { font-size: 13px; font-weight: 600; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .t-list-meta { font-size: 11.5px; color: #64748b; margin-top: 2px; display: flex; align-items: center; gap: 8px; }
    .t-badge-pill {
        display: inline-flex; align-items: center;
        padding: 2px 8px; border-radius: 999px;
        font-size: 10.5px; font-weight: 600;
    }
    .t-badge-indigo { background: #eef2ff; color: #4f46e5; }
    .t-badge-emerald { background: #ecfdf5; color: #059669; }
    .t-badge-gray { background: #f1f5f9; color: #64748b; }

    /* ── Section Headings ─────────────────────── */
    .section-head { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
    .section-title { font-size: 16px; font-weight: 700; color: #0f172a; letter-spacing: -.01em; }
    .section-line  { flex: 1; height: 1px; background: #e2e8f0; }
    .section-count {
        font-size: 11.5px; font-weight: 600; color: #4f46e5;
        background: #eef2ff; padding: 4px 12px; border-radius: 999px;
    }

    /* ── Class Search & Filter ───────────────── */
    .t-class-filter-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }
    .t-search-input-wrap {
        position: relative;
        flex: 1;
        max-width: 380px;
        min-width: 240px;
    }
    .t-search-input-wrap svg {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 16px; height: 16px;
        color: #94a3b8;
    }
    .t-search-input {
        width: 100%;
        padding: 9px 12px 9px 36px;
        border-radius: 11px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        font-size: 13px;
        color: #0f172a;
        outline: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .t-search-input:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
    }

    /* ── School cards (preserves test classes) ── */
    .school-card {
        background: #ffffff;
        border: 1px solid #eef1f7;
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 20px;
        transition: box-shadow .16s ease;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
    }
    .school-card:hover { box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06); }
    .school-header {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        display: flex; align-items: center; gap: 14px;
        background: linear-gradient(180deg, #ffffff 0%, #fafbfe 100%);
    }
    .school-avatar {
        width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0;
        background: linear-gradient(135deg, #6366f1, #10b981);
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-weight: 700; font-size: 17px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.18);
    }
    .school-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .school-name  { font-size: 15px; font-weight: 700; color: #0f172a; }
    .school-meta  { font-size: 12px; color: #94a3b8; margin-top: 2px; }
    .school-badge {
        margin-left: auto; font-size: 11.5px; font-weight: 600; color: #4f46e5;
        background: #eef2ff; padding: 5px 13px; border-radius: 999px; flex-shrink: 0;
    }

    .grade-section { padding: 0 20px; }
    .grade-label {
        font-size: 11px; font-weight: 700; color: #64748b;
        text-transform: uppercase; letter-spacing: .08em;
        padding: 16px 0 10px;
        display: flex; align-items: center; gap: 8px;
    }
    .grade-label::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: #6366f1; }

    .classes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 14px; padding: 4px 0 20px;
    }

    .class-card {
        border: 1px solid #eef1f7; border-radius: 14px; padding: 16px 18px;
        transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
        text-decoration: none; display: block; background: #ffffff;
        position: relative;
    }
    .class-card:hover {
        border-color: #818cf8;
        box-shadow: 0 10px 24px rgba(99, 102, 241, 0.12);
        transform: translateY(-2px);
    }
    .class-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
    .class-grade-badge {
        font-size: 10.5px; font-weight: 600; color: #6366f1;
        background: #eef2ff; padding: 3px 9px; border-radius: 999px;
    }
    .class-status-active   { font-size: 10.5px; font-weight: 600; color: #059669; background: #d1fae5; padding: 3px 9px; border-radius: 999px; }
    .class-status-inactive { font-size: 10.5px; font-weight: 600; color: #dc2626; background: #fee2e2; padding: 3px 9px; border-radius: 999px; }
    .class-name   { font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 3px; }
    .class-medium { font-size: 12px; color: #94a3b8; margin-bottom: 12px; }
    .class-footer {
        display: flex; align-items: center; justify-content: space-between;
        font-size: 12px; color: #64748b; padding-top: 12px; border-top: 1px solid #f1f5f9;
    }
    .class-action-link {
        font-size: 11.5px; font-weight: 600; color: #4f46e5;
        display: inline-flex; align-items: center; gap: 3px;
    }

    /* ── Info notice ──────────────────────────── */
    .info-notice {
        background: linear-gradient(135deg, #fffbeb, #fefce8);
        border: 1px solid #fde68a;
        border-radius: 12px;
        padding: 12px 16px;
        display: flex; align-items: center; gap: 10px;
        margin-bottom: 24px;
    }

    /* ── Empty state (preserves test classes) ─── */
    .empty-state {
        background: #ffffff; border: 1px solid #eef1f7; border-radius: 16px;
        padding: 64px 24px; text-align: center;
    }
    .empty-icon {
        width: 56px; height: 56px; background: #eef2ff; border-radius: 16px;
        display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;
        color: #6366f1;
    }
    .empty-title { font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 6px; }
    .empty-text  { font-size: 13px; color: #94a3b8; max-width: 320px; margin: 0 auto; line-height: 1.65; }

    /* ── Dashboard tabs ──────────────────────────── */
    .t-tabs {
        display: flex; gap: 8px; margin-bottom: 24px;
        background: #f8fafc; border: 1px solid #eef1f7; border-radius: 14px;
        padding: 6px; width: 100%; max-width: 100%; flex-wrap: wrap;
    }
    .t-tab {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 9px 16px; border-radius: 10px;
        border: 1px solid transparent; background: transparent;
        font: inherit; font-size: 13px; font-weight: 600; color: #64748b;
        cursor: pointer; transition: all .15s ease;
    }
    .t-tab svg { width: 16px; height: 16px; flex-shrink: 0; }
    .t-tab:hover { color: #0f172a; background: #ffffff; }
    .t-tab-active, .t-tab-active:hover {
        background: #4f46e5; color: #ffffff;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
    }
    .t-tab-panel { display: block; }

    /* ── Grades tab ──────────────────────────────── */
    .t-grades-head {
        display: flex; align-items: center; justify-content: space-between;
        gap: 14px; flex-wrap: wrap; margin-bottom: 18px;
    }
    .t-grades-count {
        font-size: 12px; font-weight: 700; color: #4f46e5;
        background: #eef2ff; padding: 6px 14px; border-radius: 999px;
    }
    .t-grades-toolbar {
        display: flex; align-items: center; gap: 14px; flex-wrap: wrap; margin-bottom: 20px;
    }
    .t-grade-card {
        background: #ffffff; border: 1px solid #eef1f7; border-radius: 16px;
        overflow: hidden; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .t-grade-card:hover { border-color: #cbd5e1; box-shadow: 0 8px 22px rgba(15, 23, 42, 0.05); }
    .t-grade-head-row {
        display: flex; align-items: center; gap: 16px;
        padding: 16px 20px; cursor: pointer; flex-wrap: wrap;
    }
    .t-grade-head-row:focus-visible { outline: 2px solid #6366f1; outline-offset: -2px; border-radius: 16px; }
    .t-grade-avatar {
        width: 46px; height: 46px; border-radius: 13px; flex-shrink: 0;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff; font-weight: 700; font-size: 16px;
        display: flex; align-items: center; justify-content: center;
    }
    .t-grade-id { min-width: 0; flex: 1 1 220px; }
    .t-grade-name { font-size: 14.5px; font-weight: 700; color: #0f172a; }
    .t-grade-email { font-size: 12.5px; color: #64748b; margin-top: 2px; word-break: break-word; }
    .t-grade-classes { margin-top: 6px; display: flex; flex-wrap: wrap; gap: 6px; }
    .t-class-chip {
        font-size: 11px; font-weight: 600; color: #6366f1;
        background: #eef2ff; border: 1px solid #e0e7ff;
        padding: 2px 9px; border-radius: 999px;
    }
    .t-grade-stats {
        display: grid; grid-template-columns: repeat(4, minmax(110px, 1fr));
        gap: 10px; flex: 1 1 540px;
    }
    .t-gstat { background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 10px 14px; }
    .t-gstat-label { font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .07em; }
    .t-gstat-value { font-size: 20px; font-weight: 800; color: #0f172a; line-height: 1.1; margin-top: 3px; }
    .t-gstat-sub { font-size: 11px; color: #94a3b8; margin-top: 2px; font-weight: 500; }
    .t-gstat-emerald .t-gstat-value { color: #059669; }
    .t-gstat-amber  .t-gstat-value { color: #d97706; }
    .t-gstat-rose   .t-gstat-value { color: #e11d48; }
    .t-grade-chevron {
        width: 32px; height: 32px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        background: #f1f5f9; color: #64748b; transition: all .15s ease; flex-shrink: 0;
    }
    .t-grade-chevron svg { width: 18px; height: 18px; transition: transform .18s ease; }
    .t-grade-chevron.open { background: #eef2ff; color: #4f46e5; }
    .t-grade-chevron.open svg { transform: rotate(180deg); }
    .t-grade-detail { display: none; border-top: 1px solid #f1f5f9; background: #fbfcfe; padding: 20px 20px 22px; }
    .t-gd-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    .t-gd-box { background: #ffffff; border: 1px solid #eef1f7; border-radius: 14px; overflow: hidden; }
    .t-gd-box-title {
        display: flex; align-items: center; gap: 8px;
        padding: 12px 16px; font-size: 12.5px; font-weight: 700; color: #0f172a;
        border-bottom: 1px solid #f1f5f9;
    }
    .t-gd-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
    .t-gd-table th {
        text-align: left; padding: 9px 16px;
        font-size: 10.5px; font-weight: 700; color: #94a3b8;
        text-transform: uppercase; letter-spacing: .07em;
        background: #fafbfe; border-bottom: 1px solid #f1f5f9; white-space: nowrap;
    }
    .t-gd-table td { padding: 10px 16px; border-bottom: 1px solid #f8fafc; color: #0f172a; vertical-align: middle; }
    .t-gd-table tr:last-child td { border-bottom: 0; }
    .t-gd-title { font-weight: 600; color: #0f172a; }
    .t-gd-muted { color: #64748b; }
    .t-gd-empty { padding: 14px 16px; color: #94a3b8; font-size: 12.5px; }
    .t-gp { font-weight: 700; color: #4f46e5; }
    .t-gd-badge {
        display: inline-flex; align-items: center;
        padding: 2px 9px; border-radius: 999px;
        font-size: 10.5px; font-weight: 700; white-space: nowrap;
    }
    .t-gd-pass   { background: #ecfdf5; color: #059669; }
    .t-gd-fail   { background: #fef2f2; color: #dc2626; }
    .t-gd-graded { background: #ecfdf5; color: #059669; }
    .t-gd-submitted { background: #fffbeb; color: #b45309; }
    .t-gd-pending { background: #f1f5f9; color: #64748b; }
    @media (max-width: 1080px) {
        .t-gd-grid { grid-template-columns: 1fr; }
        .t-grade-stats { grid-template-columns: repeat(2, minmax(120px, 1fr)); }
    }
    @media (max-width: 640px) {
        .t-grade-stats { grid-template-columns: repeat(2, 1fr); }
        .t-grade-head-row { gap: 12px; }
        .t-gd-box { overflow-x: auto; }
        .t-gd-table { min-width: 560px; }
    }

    /* ── Responsive rules ────────────────────── */
    @media (max-width: 960px) {
        .t-summary-strip { grid-template-columns: repeat(2, 1fr); }
        .t-split-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .t-summary-strip { grid-template-columns: 1fr; }
        .t-hero-inner { flex-direction: column; align-items: flex-start; }
    }
</style>

<div class="t-dash-wrap">

    {{-- ── Dashboard Tabs ───────────────────────────────────── --}}
    <div class="t-tabs" role="tablist" aria-label="Teacher workspace sections">
        <button type="button" class="t-tab {{ $activeTab === 'tab-overview' ? 't-tab-active' : '' }}" role="tab" aria-selected="{{ $activeTab === 'tab-overview' ? 'true' : 'false' }}" data-tab-target="tab-overview">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
            </svg>
            Overview
        </button>
        <button type="button" class="t-tab {{ $activeTab === 'tab-grades' ? 't-tab-active' : '' }}" role="tab" aria-selected="{{ $activeTab === 'tab-grades' ? 'true' : 'false' }}" data-tab-target="tab-grades">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
            </svg>
            Student Grades
        </button>
    </div>

    <div class="t-tab-panel" id="tab-overview" style="{{ $activeTab === 'tab-grades' ? 'display:none;' : '' }}">

    {{-- ── Hero Welcome Banner ──────────────────────────────── --}}
    <div class="t-hero">
        <div class="t-hero-inner">
            <div>
                <div class="t-hero-tag">
                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span>Teacher Workspace</span>
                </div>
                <h1 class="t-hero-title">Welcome back, {{ auth()->user()->name }}! 👋</h1>
                <p class="t-hero-sub">
                    You are guiding {{ $totalClasses }} {{ Str::plural('class', $totalClasses) }} with {{ number_format($totalStudents) }} students across {{ $schools->count() }} {{ Str::plural('school', $schools->count()) }}. Here is today's overview.
                </p>
            </div>
            <div class="t-hero-actions">
                <a href="{{ \App\Filament\Teacher\Resources\Quizzes\QuizResource::getUrl('create') }}" class="t-btn-hero-primary">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Quiz
                </a>
                <a href="{{ \App\Filament\Teacher\Pages\Notifications::getUrl(panel: 'teacher') }}" class="t-btn-hero-secondary">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    Notifications
                </a>
            </div>
        </div>
    </div>

    {{-- ── Three Primary Stat Cards (preserves exact test contract) ── --}}
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

    {{-- ── Secondary Metrics Strip ───────────────────────────── --}}
    <div class="t-summary-strip">
        <div class="t-summary-pill">
            <div class="t-pill-icon t-pill-blue">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <div class="t-pill-num">{{ number_format($totalStudents) }}</div>
                <div class="t-pill-text">Active Students</div>
            </div>
        </div>

        <div class="t-summary-pill">
            <div class="t-pill-icon t-pill-amber">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <div class="t-pill-num">{{ $pendingSubmissionsCount }}</div>
                <div class="t-pill-text">Pending Submissions</div>
            </div>
        </div>

        <div class="t-summary-pill">
            <div class="t-pill-icon t-pill-rose">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <div>
                <div class="t-pill-num">{{ $totalQuizzes }}</div>
                <div class="t-pill-text">Quizzes Conducted</div>
            </div>
        </div>

        <div class="t-summary-pill">
            <div class="t-pill-icon t-pill-teal">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <div class="t-pill-num">{{ $totalLessons }}</div>
                <div class="t-pill-text">Lessons Published</div>
            </div>
        </div>
    </div>

    {{-- ── Needs Attention: Pending Grading ─────────────────────── --}}
    <div class="t-attention-card">
        <div class="t-attention-header">
            <div class="t-attention-title-wrap">
                <span style="font-size:15px; font-weight:700; color:#0f172a;">Action Center · Assignment Submissions</span>
                @if($pendingSubmissionsCount > 0)
                    <span class="t-attention-badge-alert">
                        <span style="width:6px;height:6px;border-radius:50%;background:#ef4444;display:inline-block;"></span>
                        {{ $pendingSubmissionsCount }} {{ Str::plural('submission', $pendingSubmissionsCount) }} to grade
                    </span>
                @else
                    <span class="t-attention-badge-ok">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        All caught up
                    </span>
                @endif
            </div>
        </div>

        @if($pendingSubmissions->isNotEmpty())
            <div>
                @foreach($pendingSubmissions as $sub)
                    <div class="t-sub-item">
                        <div class="t-sub-meta">
                            <div class="t-sub-avatar">
                                {{ strtoupper(substr($sub->student?->user?->name ?? 'S', 0, 1)) }}
                            </div>
                            <div style="min-width:0;">
                                <div class="t-sub-name">{{ $sub->student?->user?->name ?? 'Student' }}</div>
                                <div class="t-sub-assignment">
                                    <span style="font-weight:600; color:#334155;">{{ $sub->assignment?->title ?? 'Assignment' }}</span>
                                    @if($sub->assignment?->learningClass)
                                        · <span style="color:#6366f1;">{{ $sub->assignment->learningClass->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:14px;">
                            <span class="t-sub-time">
                                {{ $sub->submitted_at ? $sub->submitted_at->diffForHumans() : 'Recently' }}
                            </span>
                            <a href="{{ \App\Filament\Teacher\Resources\Assignments\AssignmentResource::getUrl('view', ['record' => $sub->assignment_id]) }}" class="t-btn-grade">
                                Grade Now
                                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="display:flex; align-items:center; gap:12px; padding:12px 14px; background:#f0fdf4; border-radius:12px; border:1px solid #dcfce7;">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2" style="flex-shrink:0;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div style="font-size:13px; color:#15803d; font-weight:500;">
                    Great job! All student submissions across your classes have been reviewed and graded.
                </div>
            </div>
        @endif
    </div>

    {{-- ── Two-Column Grid: Recent Quizzes & Recent Lessons ────── --}}
    <div class="t-split-grid">

        {{-- Left: Recent Quizzes --}}
        <div class="t-card-box">
            <div class="t-box-header">
                <div class="t-box-title">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#6366f1" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Recent Quizzes
                </div>
                <a href="{{ \App\Filament\Teacher\Resources\Quizzes\QuizResource::getUrl('create') }}" class="t-box-action">
                    <span>+ New Quiz</span>
                </a>
            </div>

            @if($recentQuizzes->isNotEmpty())
                <div>
                    @foreach($recentQuizzes as $quiz)
                        <a href="{{ \App\Filament\Teacher\Resources\Quizzes\QuizResource::getUrl('view', ['record' => $quiz]) }}" class="t-list-card">
                            <div class="t-list-main">
                                <div class="t-list-title">{{ $quiz->title }}</div>
                                <div class="t-list-meta">
                                    <span class="t-badge-pill t-badge-indigo">{{ $quiz->learningClass?->name ?? 'Class' }}</span>
                                    <span>{{ $quiz->questions_count }} {{ Str::plural('question', $quiz->questions_count) }}</span>
                                    <span>·</span>
                                    <span>{{ $quiz->attempts_count }} {{ Str::plural('attempt', $quiz->attempts_count) }}</span>
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                @if($quiz->is_published)
                                    <span class="t-badge-pill t-badge-emerald">Published</span>
                                @else
                                    <span class="t-badge-pill t-badge-gray">Draft</span>
                                @endif
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div style="padding:28px 14px; text-align:center; color:#94a3b8; font-size:13px;">
                    No quizzes created yet. Create a quiz to assess your students.
                </div>
            @endif
        </div>

        {{-- Right: Recent Lessons --}}
        <div class="t-card-box">
            <div class="t-box-header">
                <div class="t-box-title">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#059669" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    Recent Lessons
                </div>
                <span style="font-size:12px; color:#94a3b8; font-weight:500;">Latest content</span>
            </div>

            @if($recentLessons->isNotEmpty())
                <div>
                    @foreach($recentLessons as $lesson)
                        <a href="{{ \App\Filament\Teacher\Resources\Lessons\LessonResource::getUrl('view', ['record' => $lesson]) }}" class="t-list-card">
                            <div class="t-list-main">
                                <div class="t-list-title">{{ $lesson->title }}</div>
                                <div class="t-list-meta">
                                    <span class="t-badge-pill t-badge-emerald">{{ $lesson->learningClass?->name ?? 'Class' }}</span>
                                    @if($lesson->video_url)
                                        <span class="t-badge-pill t-badge-indigo">Video</span>
                                    @endif
                                    @if($lesson->attachments_count > 0)
                                        <span>{{ $lesson->attachments_count }} files</span>
                                    @endif
                                </div>
                            </div>
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @endforeach
                </div>
            @else
                <div style="padding:28px 14px; text-align:center; color:#94a3b8; font-size:13px;">
                    No lessons created yet. Open any of your classes below to post lesson notes.
                </div>
            @endif
        </div>

    </div>

    {{-- ── Info notice ─────────────────────────────────────────── --}}
    <div class="info-notice">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="2" style="flex-shrink:0;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span style="font-size:12.5px;color:#92400e;">
            School, grade and class assignments are managed by your administrator. Contact them if you need to be added to a new class.
        </span>
    </div>

    {{-- ── Schools & Classes Section ───────────────────────────── --}}
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

        {{-- Instant Search Bar --}}
        <div class="t-class-filter-bar">
            <div class="t-search-input-wrap">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text"
                       id="t-class-search"
                       class="t-search-input"
                       placeholder="Filter classes or grades..."
                       oninput="filterClasses(this.value)">
            </div>
            <div style="font-size:12px; color:#64748b; font-weight:500;">
                Showing {{ $totalClasses }} classes across {{ $schools->flatMap->grades->count() }} grades
            </div>
        </div>

        @foreach($schools as $school)
            @php $classCount = $school->grades->flatMap->learningClasses->count(); @endphp
            <div class="school-card school-container-node">
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
                            <div class="grade-group-node">
                                <div class="grade-label">Grade {{ $grade->name }}</div>
                                <div class="classes-grid">
                                    @foreach($grade->learningClasses as $class)
                                        <a href="{{ \App\Filament\Teacher\Resources\LearningClasses\LearningClassResource::getUrl('view', ['record' => $class]) }}"
                                           class="class-card class-card-node"
                                           data-search="{{ strtolower($school->name . ' ' . $grade->name . ' ' . $class->name . ' ' . ($class->medium ?? '')) }}">
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
                                                <div style="display:flex; align-items:center; gap:6px;">
                                                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                    <span>{{ $class->students_count }} {{ Str::plural('student', $class->students_count) }}</span>
                                                </div>
                                                <span class="class-action-link">
                                                    Manage
                                                    <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    @endif

    </div>{{-- /tab-overview --}}

    {{-- ── Grades Tab: Student Score Profiles ───────────────────── --}}
    <div class="t-tab-panel" id="tab-grades" style="{{ $activeTab === 'tab-grades' ? 'display:block;' : 'display:none;' }}">

        <div class="t-grades-head">
            <div>
                <h2 style="font-size:16px;font-weight:700;color:#0f172a;">Student Grades</h2>
                <p style="font-size:12.5px;color:#64748b;margin-top:2px;">Full score profile for every student in your classes. Click a student to expand the details.</p>
            </div>
            <span class="t-grades-count">{{ $studentGrades->count() }} {{ Str::plural('student', $studentGrades->count()) }}</span>
        </div>

        <div class="t-grades-toolbar">
            <div class="t-search-input-wrap" style="flex:1 1 320px; max-width:380px;">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text"
                       id="grades-search"
                       class="t-search-input"
                       placeholder="Search students by name or email..."
                       oninput="filterGrades(this.value)">
            </div>
            <div id="grades-result-count" style="font-size:12px;color:#64748b;font-weight:500;"></div>
        </div>

        @if($studentGrades->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                </div>
                <div class="empty-title">No students assigned yet</div>
                <p class="empty-text">Students appear here as soon as they are enrolled in one of your classes. Contact your administrator if you believe this is wrong.</p>
            </div>
        @else
            <div style="display:flex;flex-direction:column;gap:12px;" id="grades-list">
                @foreach($studentGrades as $g)
                    @php
                        $student = $g['student'];
                        $avatarLetter = strtoupper(substr($student->user?->name ?? 'S', 0, 1));
                        $searchData = strtolower(trim(($student->user?->name ?? '') . ' ' . ($student->user?->email ?? '') . ' ' . $g['classes']->pluck('name')->implode(' ')));
                    @endphp
                    <div class="t-grade-card grades-row" data-search="{{ $searchData }}">
                        <div class="t-grade-head-row" role="button" tabindex="0" data-toggle="grade-detail-{{ $student->id }}"
                             onclick="toggleGradeDetail(this)"
                             onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleGradeDetail(this);}"
                             aria-expanded="false" aria-controls="grade-detail-{{ $student->id }}">
                            <div class="t-grade-avatar">{{ $avatarLetter }}</div>
                            <div class="t-grade-id">
                                <div class="t-grade-name">{{ $student->user?->name ?? 'Student' }}</div>
                                <div class="t-grade-email">{{ $student->user?->email ?? 'No email on file' }}</div>
                                <div class="t-grade-classes">
                                    @forelse($g['classes'] as $cls)
                                        <span class="t-class-chip">{{ $cls->name }}</span>
                                    @empty
                                        <span style="font-size:11px;color:#94a3b8;">No classes</span>
                                    @endforelse
                                </div>
                            </div>

                            <div class="t-grade-stats">
                                <div class="t-gstat">
                                    <div class="t-gstat-label">Quiz Avg</div>
                                    <div class="t-gstat-value">{{ $g['quizAvg'] !== null ? round($g['quizAvg']) . '%' : '—' }}</div>
                                    <div class="t-gstat-sub">{{ $g['quizCount'] }} {{ Str::plural('quiz', $g['quizCount']) }}</div>
                                </div>
                                <div class="t-gstat t-gstat-emerald">
                                    <div class="t-gstat-label">Quizzes Passed</div>
                                    <div class="t-gstat-value">{{ $g['quizPassed'] }}/{{ $g['quizCount'] }}</div>
                                    <div class="t-gstat-sub">passing rate</div>
                                </div>
                                <div class="t-gstat t-gstat-amber">
                                    <div class="t-gstat-label">Assignment Avg</div>
                                    <div class="t-gstat-value">{{ $g['assignmentAvg'] !== null ? round($g['assignmentAvg']) . '%' : '—' }}</div>
                                    <div class="t-gstat-sub">{{ $g['gradedCount'] }} {{ Str::plural('assignment', $g['gradedCount']) }} graded</div>
                                </div>
                                <div class="t-gstat t-gstat-rose">
                                    <div class="t-gstat-label">Pending</div>
                                    <div class="t-gstat-value">{{ $g['pendingCount'] }}</div>
                                    <div class="t-gstat-sub">{{ Str::plural('submission', $g['pendingCount']) }} to grade</div>
                                </div>
                            </div>

                            <div class="t-grade-chevron">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                </svg>
                            </div>
                        </div>

                        <div class="t-grade-detail" id="grade-detail-{{ $student->id }}">
                            <div class="t-gd-grid">
                                <div class="t-gd-box">
                                    <div class="t-gd-box-title">
                                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#6366f1" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        Quiz Attempts
                                    </div>
                                    @if($g['attempts']->isNotEmpty())
                                        <table class="t-gd-table">
                                            <thead>
                                                <tr>
                                                    <th>Quiz</th>
                                                    <th>Class</th>
                                                    <th>Score</th>
                                                    <th>%</th>
                                                    <th>Result</th>
                                                    <th>Completed</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($g['attempts'] as $attempt)
                                                    <tr>
                                                        <td><span class="t-gd-title">{{ $attempt->quiz?->title ?? 'Quiz' }}</span></td>
                                                        <td class="t-gd-muted">{{ $attempt->quiz?->learningClass?->name ?? '—' }}</td>
                                                        <td class="t-gd-muted">{{ $attempt->score }} / {{ $attempt->quiz?->total_points }}</td>
                                                        <td><span class="t-gp">{{ round((float) $attempt->percentage) }}%</span></td>
                                                        <td>
                                                            @if($attempt->is_passed)
                                                                <span class="t-gd-badge t-gd-pass">Passed</span>
                                                            @else
                                                                <span class="t-gd-badge t-gd-fail">Failed</span>
                                                            @endif
                                                        </td>
                                                        <td class="t-gd-muted">{{ $attempt->completed_at?->format('M d, Y') ?? '—' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div class="t-gd-empty">No quiz attempts yet.</div>
                                    @endif
                                </div>

                                <div class="t-gd-box">
                                    <div class="t-gd-box-title">
                                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#059669" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Assignment Submissions
                                    </div>
                                    @if($g['submissions']->isNotEmpty())
                                        <table class="t-gd-table">
                                            <thead>
                                                <tr>
                                                    <th>Assignment</th>
                                                    <th>Class</th>
                                                    <th>Score</th>
                                                    <th>%</th>
                                                    <th>Status</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($g['submissions'] as $submission)
                                                    @php $subPct = $submission->percentage(); @endphp
                                                    <tr>
                                                        <td><span class="t-gd-title">{{ $submission->assignment?->title ?? 'Assignment' }}</span></td>
                                                        <td class="t-gd-muted">{{ $submission->assignment?->learningClass?->name ?? '—' }}</td>
                                                        <td class="t-gd-muted">
                                                            @if($submission->status === 'graded')
                                                                {{ $submission->score }} / {{ $submission->assignment?->max_score }}
                                                            @else
                                                                —
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($submission->status === 'graded' && $subPct !== null)
                                                                <span class="t-gp">{{ $subPct }}%</span>
                                                            @else
                                                                <span class="t-gd-muted">—</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($submission->status === 'graded')
                                                                <span class="t-gd-badge t-gd-graded">Graded</span>
                                                            @elseif(in_array($submission->status, ['submitted'], true))
                                                                <span class="t-gd-badge t-gd-submitted">Submitted</span>
                                                            @else
                                                                <span class="t-gd-badge t-gd-pending">{{ ucfirst($submission->status) }}</span>
                                                            @endif
                                                        </td>
                                                        <td class="t-gd-muted">{{ $submission->graded_at?->format('M d, Y') ?? ($submission->submitted_at?->format('M d, Y') ?? '—') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div class="t-gd-empty">No assignment submissions yet.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>{{-- /tab-grades --}}

</div><!-- end .t-dash-wrap -->

<script>
    function filterClasses(query) {
        query = (query || '').toLowerCase().trim();
        const cards = document.querySelectorAll('.class-card-node');
        cards.forEach(card => {
            const data = card.getAttribute('data-search') || '';
            if (!query || data.includes(query)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });

        // Hide empty grade groups
        document.querySelectorAll('.grade-group-node').forEach(group => {
            const visible = group.querySelectorAll('.class-card-node[style*="display: block"], .class-card-node:not([style*="display: none"])');
            group.style.display = visible.length > 0 ? 'block' : 'none';
        });

        // Hide empty school cards
        document.querySelectorAll('.school-container-node').forEach(school => {
            const visible = school.querySelectorAll('.class-card-node[style*="display: block"], .class-card-node:not([style*="display: none"])');
            school.style.display = visible.length > 0 ? 'block' : 'none';
        });
    }

    /* Dashboard tab switching */
    document.querySelectorAll('.t-tab').forEach((btn) => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-tab-target');
            document.querySelectorAll('.t-tab').forEach((b) => {
                const active = b.getAttribute('data-tab-target') === target;
                b.classList.toggle('t-tab-active', active);
                b.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            document.querySelectorAll('.t-tab-panel').forEach((p) => {
                p.style.display = p.id === target ? 'block' : 'none';
            });
        });
    });

    /* Grades: search by name / email / class */
    function filterGrades(query) {
        query = (query || '').toLowerCase().trim();
        let visible = 0;
        document.querySelectorAll('.grades-row').forEach((row) => {
            const on = !query || (row.getAttribute('data-search') || '').includes(query);
            row.style.display = on ? '' : 'none';
            if (on) visible++;
        });
        const counter = document.getElementById('grades-result-count');
        if (counter) {
            counter.textContent = visible + ' ' + (visible === 1 ? 'student' : 'students') + ' shown';
        }
    }

    /* Grades: expand / collapse detailed score profile */
    function toggleGradeDetail(el) {
        const id = el.getAttribute('data-toggle');
        const detail = document.getElementById(id);
        if (!detail) return;
        const open = detail.style.display !== 'none';
        detail.style.display = open ? 'none' : 'block';
        const chevron = el.querySelector('.t-grade-chevron');
        if (chevron) chevron.classList.toggle('open', !open);
        el.setAttribute('aria-expanded', String(!open));
    }

    const gradesCounter = document.getElementById('grades-result-count');
    if (gradesCounter) {
        gradesCounter.textContent = document.querySelectorAll('.grades-row').length + ' students shown';
    }
</script>
</div>
