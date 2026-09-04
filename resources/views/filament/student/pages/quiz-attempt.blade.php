<div class="qa qa-{{ $tier }}" style="min-height:100vh; display: flex; flex-direction: column;">

    @php
        $orderedQuestions = $this->orderedQuestions();
        $optLetters = ['A', 'B', 'C', 'D', 'E', 'F'];
    @endphp

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700;800;900&family=Inter:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap');

        /* ══════════════════════════════════════════════════════════════════
           GLOBAL / BASE QUIZ ATTEMPT STYLES
           ══════════════════════════════════════════════════════════════════ */
        .qa {
            --ink: #0f172a;
            --ink-muted: #64748b;
            --surface: #ffffff;
            --border-color: #e2e8f0;
            --accent: #6366f1;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            color: #334155;
            background: #f8fafc;
        }

        .qa .qa-topbar {
            display: flex; align-items: center; justify-content: space-between;
            gap: 1rem; padding: 1rem 2.25rem; flex-wrap: wrap;
            background: rgba(255,255,255,0.85); backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 30;
        }
        .qa .qa-back {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: #ffffff; border: 1.5px solid var(--border-color); color: #334155;
            font-weight: 700; font-size: 0.88rem; padding: 0.55rem 1.15rem;
            border-radius: 9999px; text-decoration: none; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .qa .qa-back:hover { border-color: var(--accent); background: #f8fafc; color: var(--accent); transform: translateX(-2px); }
        .qa .qa-brand { font-weight: 800; color: #0f172a; font-size: 0.98rem; letter-spacing: -0.01em; display: flex; align-items: center; gap: 0.4rem; }

        .qa .qa-hero {
            padding: 2.5rem 2rem 2rem; text-align: center;
            border-bottom: 1px solid var(--border-color);
            position: relative; overflow: hidden;
        }
        .qa .qa-breadcrumb {
            font-size: 0.82rem; font-weight: 700; color: var(--accent); letter-spacing: 0.02em;
            margin-bottom: 0.85rem; display: inline-flex; gap: 0.45rem; align-items: center; flex-wrap: wrap;
            justify-content: center;
        }
        .qa .qa-breadcrumb .sep { opacity: 0.6; }
        .qa .qa-emoji { font-size: 2.8rem; margin-bottom: 0.5rem; line-height: 1; }
        .qa .qa-title {
            font-size: clamp(1.7rem, 3.5vw, 2.5rem); font-weight: 900; color: var(--ink);
            line-height: 1.2; margin: 0 0 1rem; letter-spacing: -0.02em;
        }
        .qa .qa-meta { display: inline-flex; gap: 0.55rem; flex-wrap: wrap; justify-content: center; }
        .qa .qa-chip {
            display: inline-flex; align-items: center; gap: 0.4rem;
            background: #ffffff; border: 1px solid var(--border-color); color: #334155;
            font-weight: 700; font-size: 0.82rem; padding: 0.45rem 0.95rem; border-radius: 9999px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
        }

        .qa .qa-body {
            flex: 1; width: 100%; max-width: 840px; margin: 0 auto; padding: 2rem 1.25rem 3.5rem;
            display: flex; flex-direction: column; gap: 1.5rem; transition: max-width 0.2s ease;
        }
        .qa .qa-body.qa-body-has-media {
            max-width: 1200px;
        }
        .qa .qa-card {
            background: #ffffff; border: 1px solid var(--border-color); border-radius: 1.25rem;
            padding: 2rem 2.25rem; box-shadow: 0 8px 30px -12px rgba(0,0,0,0.08);
            position: relative;
        }
        .qa .qa-card h2 {
            font-size: 1.15rem; font-weight: 800; color: var(--ink); margin: 0 0 0.85rem;
            display: flex; align-items: center; gap: 0.5rem;
        }
        .qa .qa-text { margin: 0; font-size: 0.98rem; line-height: 1.7; color: #475569; }
        .qa .qa-text strong { color: #0f172a; }

        .qa .qa-alert {
            border-radius: 1rem; padding: 1rem 1.25rem; font-weight: 700; font-size: 0.92rem;
            border: 1px solid #bfdbfe; background: #eff6ff; color: #1e40af;
        }
        .qa .qa-alert-info { border-color: #ddd6fe; background: #f5f3ff; color: #5b21b6; }
        .qa .qa-alert-err { border-color: #fecaca; background: #fef2f2; color: #991b1b; }

        .qa .qa-start-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: #ffffff !important;
            font-weight: 800; font-size: 1.1rem; padding: 0.95rem 2.5rem; border-radius: 9999px;
            border: none; cursor: pointer; text-decoration: none;
            box-shadow: 0 10px 24px -8px rgba(79, 70, 229, 0.6);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .qa .qa-start-btn:hover {
            background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%) !important;
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 14px 28px -8px rgba(79, 70, 229, 0.7);
        }
        .qa .qa-start-btn:disabled { background: #cbd5e1 !important; color: #94a3b8 !important; cursor: not-allowed; box-shadow: none; transform: none; }

        .qa .qa-cta-wrap { text-align: center; margin-top: 0.5rem; }
        .qa .qa-sub-note { font-size: 0.88rem; color: #64748b; font-weight: 600; margin-top: 0.75rem; }

        .qa .qa-score-row { display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap; }
        .qa .qa-ring {
            width: 6.5rem; height: 6.5rem; border-radius: 9999px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            background: #ecfdf5; border: 6px solid #a7f3d0; color: #065f46;
            font-size: 1.65rem; font-weight: 900;
        }
        .qa .qa-ring-fail { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
        .qa .qa-bullets { margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 0.6rem; }
        .qa .qa-bullets li { font-size: 0.98rem; color: #475569; display: flex; gap: 0.55rem; align-items: flex-start; font-weight: 600; }

        /* ── TAKE SCREEN ELEMENTS ── */
        .qa .qa-take-head {
            display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
            margin-bottom: 1.1rem;
        }
        .qa .qa-take-title { font-size: 1.15rem; font-weight: 900; color: var(--ink); margin: 0; }
        .qa .qa-take-sub { font-size: 0.82rem; font-weight: 700; color: var(--accent); }
        .qa .qa-timer {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: #f1f5f9; border: 1px solid #cbd5e1; color: #0f172a;
            font-weight: 800; font-size: 1.2rem; padding: 0.5rem 1.15rem; border-radius: 9999px;
            font-variant-numeric: tabular-nums; box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .qa .qa-timer.qa-timer-low { background: #fef2f2; border-color: #fca5a5; color: #b91c1c; animation: timerPulse 1s ease-in-out infinite; }
        @keyframes timerPulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.04); } }
        .qa .qa-timer-glyph { font-size: 1.1rem; }

        .qa .qa-progress {
            display: flex; align-items: center; gap: 0.45rem; flex-wrap: wrap; margin-bottom: 1.25rem;
        }
        .qa .qa-progress-label { font-size: 0.82rem; font-weight: 800; color: #64748b; margin-right: 0.35rem; }
        .qa .qa-dot {
            width: 1.65rem; height: 1.65rem; border-radius: 9999px; border: 2px solid var(--border-color);
            background: #ffffff; color: #94a3b8; font-size: 0.72rem; font-weight: 800;
            display: inline-flex; align-items: center; justify-content: center; cursor: pointer;
            transition: all 0.15s ease; padding: 0;
        }
        .qa .qa-dot:hover:not(:disabled) { border-color: var(--accent); color: var(--accent); }
        .qa .qa-dot.qa-dot-answered { background: #eef2ff; border-color: #c7d2fe; color: #4f46e5; font-weight: 900; }
        .qa .qa-dot.qa-dot-current { background: var(--accent); border-color: var(--accent); color: #ffffff; transform: scale(1.18); font-weight: 900; }
        .qa .qa-dot:disabled { opacity: 0.5; cursor: wait; }

        .qa .qa-qcard { margin: 0; }
        .qa .qa-qnum { font-size: 0.82rem; font-weight: 800; color: var(--accent); margin-bottom: 0.45rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .qa .qa-qtext { font-size: 1.35rem; font-weight: 800; color: var(--ink); line-height: 1.4; margin: 0 0 1.25rem; }

        /* ── SPLIT LAYOUT FOR QUESTIONS WITH MEDIA ── */
        .qa .qa-split-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            align-items: start;
        }
        @media (min-width: 1024px) {
            .qa .qa-split-layout {
                grid-template-columns: 1.15fr 1fr;
                gap: 2.5rem;
            }
        }
        @media (max-width: 880px) {
            .qa .qa-split-layout {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
        }

        .qa .qa-media-col {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            position: sticky;
            top: 5.5rem;
        }
        .qa .qa-media-col .qa-media-wrapper {
            margin: 0;
            width: 100%;
            min-height: 260px;
            max-height: 65vh;
            border-radius: 1.25rem;
            background: #0f172a;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1.5px solid var(--border-color);
            box-shadow: 0 8px 24px -6px rgba(0,0,0,0.12);
        }
        .qa .qa-media-col .qa-media {
            width: 100%;
            height: 100%;
            max-height: 65vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .qa .qa-media-col .qa-media img {
            width: 100%;
            max-height: 65vh;
            object-fit: contain;
            display: block;
            border-radius: inherit;
        }
        .qa .qa-media-col .qa-media video {
            width: 100%;
            max-height: 65vh;
            display: block;
            border-radius: inherit;
        }

        .qa .qa-content-col {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .qa .qa-content-col .qa-qtext {
            margin-bottom: 1.15rem;
        }

        /* ── MEDIA CONTAINER & LOADING SPINNER ── */
        .qa .qa-media-wrapper {
            position: relative; margin: 0 0 1.5rem; border-radius: 1.15rem; overflow: hidden;
            background: #0f172a; min-height: 180px; border: 1.5px solid var(--border-color);
            display: flex; align-items: center; justify-content: center;
        }
        .qa .qa-media {
            width: 100%; border-radius: inherit; overflow: hidden; display: flex; align-items: center; justify-content: center;
        }
        .qa .qa-media img {
            width: 100%; max-height: 420px; object-fit: contain; display: block; background: #0f172a;
        }
        .qa .qa-media video {
            width: 100%; max-height: 420px; display: block; background: #0f172a;
        }

        .qa .qa-media-loader {
            position: absolute; inset: 0; z-index: 10;
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.85rem;
            background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(8px);
            color: #ffffff; font-weight: 700; font-size: 0.95rem;
        }
        .qa .qa-spinner {
            width: 2.75rem; height: 2.75rem; border-radius: 9999px;
            border: 3.5px solid rgba(255, 255, 255, 0.2); border-top-color: #ffffff;
            animation: qaSpin 0.75s linear infinite;
        }
        @keyframes qaSpin { to { transform: rotate(360deg); } }

        /* Interaction lock while media is loading */
        .qa-loading-lock .qa-opts button,
        .qa-loading-lock .qa-nav button {
            opacity: 0.55 !important;
            pointer-events: none !important;
            cursor: wait !important;
        }

        .qa .qa-opts { display: flex; flex-direction: column; gap: 0.75rem; margin-top: 0.35rem; }
        .qa .qa-opt {
            display: flex; align-items: center; justify-content: space-between; gap: 0.9rem; width: 100%; text-align: left;
            background: #ffffff; border: 2px solid var(--border-color); border-radius: 1.1rem;
            padding: 0.95rem 1.25rem; font-size: 1.02rem; font-weight: 700; color: #334155;
            cursor: pointer; transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1); font-family: inherit;
        }
        .qa .qa-opt-left { display: flex; align-items: center; gap: 0.85rem; flex: 1; }
        .qa .qa-opt .qa-opt-letter {
            flex-shrink: 0; width: 2.1rem; height: 2.1rem; border-radius: 9999px; background: #f1f5f9;
            color: #475569; font-weight: 900; display: inline-flex; align-items: center; justify-content: center; font-size: 0.92rem;
            transition: all 0.15s ease;
        }
        .qa .qa-opt:hover:not(:disabled) { border-color: var(--accent); background: #f8fafc; transform: translateY(-1px); }
        .qa .qa-opt.qa-opt-selected {
            border-color: var(--accent); background: #eef2ff; color: #1e1b4b;
            box-shadow: 0 4px 16px -4px rgba(79, 70, 229, 0.25);
        }
        .qa .qa-opt.qa-opt-selected .qa-opt-letter { background: var(--accent); color: #ffffff; }
        .qa .qa-opt-check {
            width: 1.5rem; height: 1.5rem; border-radius: 9999px; background: var(--accent); color: #ffffff;
            display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 900;
            animation: qaPop 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        @keyframes qaPop { 0% { transform: scale(0); } 100% { transform: scale(1); } }

        .qa .qa-nav {
            display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-top: 1.75rem;
        }
        .qa .qa-nav-btn {
            display: inline-flex; align-items: center; gap: 0.45rem; background: #ffffff;
            border: 2px solid var(--border-color); color: #334155; font-weight: 800; font-size: 0.95rem;
            padding: 0.7rem 1.5rem; border-radius: 9999px; cursor: pointer; transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1); font-family: inherit;
        }
        .qa .qa-nav-btn:hover:not(:disabled) { border-color: var(--accent); background: #f8fafc; color: var(--accent); }
        .qa .qa-nav-btn.qa-nav-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: #ffffff !important; border: none;
            box-shadow: 0 8px 20px -8px rgba(79, 70, 229, 0.6);
        }
        .qa .qa-nav-btn.qa-nav-primary:hover:not(:disabled) {
            background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%) !important;
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -8px rgba(79, 70, 229, 0.7);
        }
        .qa .qa-nav-btn.qa-nav-submit {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;
            color: #ffffff !important;
            border: none;
            box-shadow: 0 8px 20px -8px rgba(16, 185, 129, 0.6);
        }
        .qa .qa-nav-btn.qa-nav-submit:hover:not(:disabled) {
            background: linear-gradient(135deg, #047857 0%, #059669 100%) !important;
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -8px rgba(16, 185, 129, 0.7);
        }
        .qa .qa-nav-btn:disabled { opacity: 0.45; cursor: not-allowed; transform: none; box-shadow: none; }

        /* ── RESULT SCREEN ── */
        .qa .qa-answer {
            border: 2px solid var(--border-color); border-left-width: 6px; border-radius: 1.15rem;
            padding: 1.1rem 1.3rem; background: #ffffff; margin-top: 0.9rem;
        }
        .qa .qa-answer-correct { border-color: #a7f3d0; border-left-color: #10b981; background: #f0fdf4; }
        .qa .qa-answer-wrong { border-color: #fecaca; border-left-color: #f43f5e; background: #fff7f7; }
        .qa .qa-answer-skip { border-color: #fde68a; border-left-color: #f59e0b; background: #fffbeb; }
        .qa .qa-answer-badge {
            font-size: 0.78rem; font-weight: 900; padding: 0.3rem 0.8rem; border-radius: 9999px; letter-spacing: 0.02em;
            display: inline-flex; align-items: center; gap: 0.3rem; margin-bottom: 0.7rem;
        }
        .qa .qa-badge-correct { background: #a7f3d0; color: #065f46; }
        .qa .qa-badge-wrong { background: #fecaca; color: #991b1b; }
        .qa .qa-badge-skip { background: #fde68a; color: #92400e; }
        .qa .qa-answer-num { font-size: 0.8rem; font-weight: 800; color: var(--accent); margin-bottom: 0.4rem; }
        .qa .qa-answer-text { font-size: 1.05rem; font-weight: 800; color: var(--ink); margin: 0 0 0.9rem; line-height: 1.4; }
        .qa .qa-res-row { display: flex; align-items: center; gap: 0.6rem; font-weight: 700; font-size: 0.92rem; color: #475569; margin-top: 0.5rem; }
        .qa .qa-res-row .qa-res-tag {
            background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 9999px; padding: 0.35rem 0.9rem; font-size: 0.85rem;
        }
        .qa .qa-res-row.qa-res-correct { color: #047857; }
        .qa .qa-res-row.qa-res-correct .qa-res-tag { background: #d1fae5; border-color: #a7f3d0; color: #065f46; }
        .qa .qa-res-row.qa-res-chosen { color: #9f1239; }
        .qa .qa-res-row.qa-res-chosen .qa-res-tag { background: #ffe4e6; border-color: #fecdd3; color: #9f1239; }
        .qa .qa-explanation {
            margin-top: 1rem; padding: 0.9rem 1.1rem; background: #f5f3ff; border: 1px solid #e4d8f7; border-radius: 0.9rem;
            font-size: 0.92rem; line-height: 1.6; color: #4c1d95; font-weight: 600;
        }

        /* ══════════════════════════════════════════════════════════════════
           TIER 1: KIDS (Ages 5–10) Playful, Rainbow, Chunky & Bouncy
           ══════════════════════════════════════════════════════════════════ */
        .qa-kids {
            font-family: 'Fredoka', 'Nunito', system-ui, sans-serif;
            background: linear-gradient(135deg, #ede9fe 0%, #f3e8ff 30%, #fce7f3 60%, #fff7ed 100%);
            --accent: #7c3aed;
            --border-color: #ede9fe;
        }
        .qa-kids .qa-topbar { background: rgba(255,255,255,0.88); border-bottom: 2px solid #ede9fe; }
        .qa-kids .qa-hero {
            background: linear-gradient(135deg, #7c3aed 0%, #a855f7 28%, #db2777 65%, #f97316 100%);
            color: #ffffff; border-bottom: none; box-shadow: 0 12px 30px rgba(124, 58, 237, 0.25);
        }
        .qa-kids .qa-hero .qa-breadcrumb { color: #fed7aa; }
        .qa-kids .qa-hero .qa-breadcrumb .sep { color: #fed7aa; }
        .qa-kids .qa-hero .qa-title { color: #ffffff; text-shadow: 2px 2px 0 rgba(0,0,0,0.15); font-size: clamp(2rem, 5vw, 3.2rem); }
        .qa-kids .qa-hero .qa-chip { background: rgba(255,255,255,0.2); border-color: rgba(255,255,255,0.3); color: #ffffff; backdrop-filter: blur(4px); }
        .qa-kids .qa-card {
            border-radius: 2rem; border: 4px solid #ede9fe; padding: 2.25rem 2.5rem;
            box-shadow: 0 16px 40px -12px rgba(124, 58, 237, 0.18);
        }
        .qa-kids .qa-qtext { font-size: 1.6rem; font-weight: 900; color: #2e1065; line-height: 1.35; }
        .qa-kids .qa-timer {
            font-size: 1.45rem; padding: 0.65rem 1.4rem; background: #fff7ed; border: 2px solid #fed7aa; color: #c2410c;
        }
        .qa-kids .qa-dot { width: 2rem; height: 2rem; font-size: 0.85rem; border-width: 3px; }
        .qa-kids .qa-dot.qa-dot-answered { background: #ede9fe; border-color: #a855f7; color: #6b21a8; }
        .qa-kids .qa-dot.qa-dot-current { background: #db2777; border-color: #db2777; transform: scale(1.22); }
        .qa-kids .qa-opt {
            border-radius: 1.5rem; border-width: 3.5px; padding: 1.15rem 1.4rem; font-size: 1.2rem;
            background: #ffffff;
        }
        .qa-kids .qa-opt:hover:not(:disabled) { transform: translateY(-3px) scale(1.01); box-shadow: 0 10px 24px -6px rgba(124, 58, 237, 0.2); }
        .qa-kids .qa-opt.qa-opt-selected {
            border-color: #db2777; background: #fdf2f8; color: #831843;
            box-shadow: 0 8px 25px -6px rgba(219, 39, 119, 0.4); transform: scale(1.02);
        }
        .qa-kids .qa-opt .qa-opt-letter-0 { background: #fee2e2; color: #b91c1c; }
        .qa-kids .qa-opt .qa-opt-letter-1 { background: #e0f2fe; color: #0369a1; }
        .qa-kids .qa-opt .qa-opt-letter-2 { background: #dcfce7; color: #15803d; }
        .qa-kids .qa-opt .qa-opt-letter-3 { background: #fef3c7; color: #b45309; }
        .qa-kids .qa-opt .qa-opt-letter-4 { background: #f3e8ff; color: #7e22ce; }
        .qa-kids .qa-opt .qa-opt-letter-5 { background: #fce7f3; color: #be185d; }
        .qa-kids .qa-opt.qa-opt-selected .qa-opt-letter { background: #db2777 !important; color: #ffffff !important; }
        .qa-kids .qa-opt-check { background: #db2777; width: 1.8rem; height: 1.8rem; font-size: 1rem; }
        .qa-kids .qa-nav-btn { font-size: 1.1rem; padding: 0.9rem 2rem; border-radius: 9999px; }
        .qa-kids .qa-nav-btn.qa-nav-primary {
            background: linear-gradient(135deg, #7c3aed 0%, #db2777 100%);
            color: #ffffff !important;
            box-shadow: 0 10px 25px -6px rgba(219, 39, 119, 0.5);
        }
        .qa-kids .qa-nav-btn.qa-nav-primary:hover:not(:disabled) {
            background: linear-gradient(135deg, #6d28d9 0%, #be185d 100%) !important;
            color: #ffffff !important;
            transform: translateY(-3px) scale(1.03);
            box-shadow: 0 14px 28px -6px rgba(219, 39, 119, 0.65);
        }
        .qa-kids .qa-start-btn {
            font-size: 1.35rem; padding: 1.15rem 3rem;
            background: linear-gradient(135deg, #7c3aed 0%, #db2777 50%, #f97316 100%);
            color: #ffffff !important;
            box-shadow: 0 12px 30px -8px rgba(219, 39, 119, 0.6);
        }
        .qa-kids .qa-start-btn:hover {
            background: linear-gradient(135deg, #6d28d9 0%, #be185d 50%, #ea580c 100%) !important;
            color: #ffffff !important;
            transform: translateY(-3px) scale(1.02);
        }

        /* ══════════════════════════════════════════════════════════════════
           TIER 2: JUNIOR / TEENS (Ages 11–15) Modern Bento Light
           ══════════════════════════════════════════════════════════════════ */
        .qa-junior {
            font-family: 'Inter', 'Plus Jakarta Sans', system-ui, sans-serif;
            background: #f8fafc;
            --accent: #4f46e5;
            --border-color: #e2e8f0;
        }
        .qa-junior .qa-hero {
            background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 40%, #e0e7ff 100%);
            border-bottom: 1px solid #e0e7ff;
        }
        .qa-junior .qa-card {
            border-radius: 1.25rem; border: 1px solid #e2e8f0; padding: 2rem 2.25rem;
            box-shadow: 0 10px 30px -10px rgba(79, 70, 229, 0.1);
        }
        .qa-junior .qa-opt {
            border-radius: 1rem; border: 2px solid #e2e8f0;
        }
        .qa-junior .qa-opt:hover:not(:disabled) { border-color: #6366f1; background: #faf5ff; }
        .qa-junior .qa-opt.qa-opt-selected {
            border-color: #4f46e5; background: #eef2ff;
            box-shadow: 0 4px 18px -4px rgba(79, 70, 229, 0.28);
        }
        .qa-junior .qa-nav-btn.qa-nav-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #ffffff !important;
        }
        .qa-junior .qa-nav-btn.qa-nav-primary:hover:not(:disabled) {
            background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%) !important;
            color: #ffffff !important;
        }

        /* ══════════════════════════════════════════════════════════════════
           TIER 3: SENIOR (Ages 16+) Clean Academic / Executive Interface
           ══════════════════════════════════════════════════════════════════ */
        .qa-senior {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #f8fafc;
            --accent: #3b82f6;
            --border-color: #e2e8f0;
        }
        .qa-senior .qa-hero {
            background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 2rem 2rem 1.75rem;
        }
        .qa-senior .qa-title { font-size: 1.85rem; font-weight: 800; color: #0f172a; }
        .qa-senior .qa-card {
            border-radius: 0.85rem; border: 1px solid #e2e8f0; padding: 1.75rem 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .qa-senior .qa-qtext { font-size: 1.25rem; font-weight: 700; line-height: 1.5; color: #0f172a; }
        .qa-senior .qa-opt {
            border-radius: 0.75rem; border: 1.5px solid #e2e8f0; padding: 0.85rem 1.15rem; font-size: 0.98rem; font-weight: 600;
        }
        .qa-senior .qa-opt.qa-opt-selected {
            border-color: #2563eb; background: #eff6ff; color: #1e3a8a;
            box-shadow: 0 2px 8px -2px rgba(37, 99, 235, 0.2);
        }
        .qa-senior .qa-opt.qa-opt-selected .qa-opt-letter { background: #2563eb; color: #ffffff; }
        .qa-senior .qa-opt-check { background: #2563eb; }
        .qa-senior .qa-timer {
            font-family: ui-monospace, 'Cascadia Code', monospace; font-size: 1.15rem;
            background: #ffffff; border: 1px solid #cbd5e1; color: #0f172a;
        }
        .qa-senior .qa-nav-btn {
            border-radius: 0.6rem; font-size: 0.9rem; padding: 0.6rem 1.3rem;
        }
        .qa-senior .qa-nav-btn.qa-nav-primary {
            background: #1e293b; color: #ffffff !important; box-shadow: none;
        }
        .qa-senior .qa-nav-btn.qa-nav-primary:hover:not(:disabled) {
            background: #0f172a !important; color: #ffffff !important; transform: translateY(-1px);
        }
        .qa-senior .qa-nav-btn.qa-nav-submit {
            background: #059669 !important; color: #ffffff !important;
        }
        .qa-senior .qa-nav-btn.qa-nav-submit:hover:not(:disabled) {
            background: #047857 !important; color: #ffffff !important;
        }
        .qa-senior .qa-start-btn {
            background: #1e293b; border-radius: 0.65rem; font-size: 1rem; padding: 0.85rem 2rem;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .qa-senior .qa-start-btn:hover { background: #0f172a !important; color: #ffffff !important; }
    </style>

    {{-- Top bar --}}
    <div class="qa-topbar">
        <a href="{{ \App\Filament\Student\Pages\Dashboard::getUrl(['tab' => 'quizzes']) }}" class="qa-back">
            <span>◀</span>
            <span>Back to My Quizzes</span>
        </a>
        <div class="qa-brand">
            @if($tier === 'kids')
                <span>🌟</span>
                <span>Student Fun Zone</span>
            @elseif($tier === 'junior')
                <span>⚡</span>
                <span>Student Hub</span>
            @else
                <span>🎓</span>
                <span>Academic Portal</span>
            @endif
        </div>
    </div>

    {{-- ====================== RESULT SCREEN ====================== --}}
    @if($finished)

        <header class="qa-hero">
            <div class="qa-breadcrumb">
                <span>🏫 {{ $schoolName }}</span>
                @if($gradeName)
                    <span class="sep">›</span>
                    <span>{{ $gradeName }}</span>
                @endif
                <span class="sep">›</span>
                <span>📘 {{ $className }}</span>
            </div>
            <div class="qa-emoji">{{ $passed ? ($tier === 'kids' ? '🏆' : '🎉') : ($tier === 'kids' ? '💪' : '📊') }}</div>
            <h1 class="qa-title">{{ $passed ? 'Quiz Passed!' : 'Quiz Finished' }}</h1>
            <div class="qa-meta">
                @if($wasTimeExpired)
                    <span class="qa-chip">⏰ Auto-submitted (Time Up)</span>
                @endif
                @if($attemptNumber)
                    <span class="qa-chip">Attempt #{{ $attemptNumber }}</span>
                @endif
                <span class="qa-chip">🧩 {{ $answeredCount }} of {{ $questionCount }} answered</span>
            </div>
        </header>

        <div class="qa-body">

            @if($notice)
                <div class="qa-alert qa-alert-info">{{ $notice }}</div>
            @endif

            <div class="qa-card">
                <div class="qa-score-row">
                    <div class="qa-ring {{ ! $passed ? 'qa-ring-fail' : '' }}">{{ round($finalPercentage) }}%</div>
                    <div>
                        <h2 style="margin-bottom: 0.3rem;">
                            @if($tier === 'kids')
                                {{ $passed ? 'You did it! Superstar! 🌟' : 'Awesome try! Keep going! 💪' }}
                            @else
                                {{ $passed ? 'Congratulations, you passed! 🎉' : 'Assessment Completed' }}
                            @endif
                        </h2>
                        <p class="qa-text" style="margin: 0;">
                            You scored <strong>{{ (float) $finalScore }} points</strong> ({{ round($finalPercentage) }}%) out of {{ $questionCount }} questions.
                            @if($passed)
                                Great mastery of this topic!
                            @else
                                A minimum of <strong>{{ $passingPercentage }}%</strong> is required to pass.
                            @endif
                        </p>
                    </div>
                </div>

                <div class="qa-cta-wrap" style="margin-top: 1.6rem; display: flex; gap: 0.85rem; justify-content: center; flex-wrap: wrap;">
                    @if($canTryAgain)
                        <button type="button" class="qa-start-btn" wire:click="startAttempt">
                            <span>🔁</span>
                            <span>{{ $tier === 'kids' ? 'Try Again!' : 'Retake Quiz' }}</span>
                        </button>
                    @endif
                    <a href="{{ \App\Filament\Student\Pages\Dashboard::getUrl(['tab' => 'quizzes']) }}" class="qa-back" style="padding: 0.9rem 2rem; font-size: 1rem;">
                        <span>🧠</span>
                        <span>Back to My Quizzes</span>
                    </a>
                </div>
            </div>

            @if($showCorrect)
                <div class="qa-card">
                    <h2>
                        <span>🔍</span>
                        <span>Question Breakdown & Review</span>
                    </h2>

                    @foreach($orderedQuestions as $index => $q)
                        @php
                            $chosenId = $this->answers[$q->id] ?? null;
                            $correctOpt = $q->options->firstWhere('is_correct', true);
                            $isRight = $chosenId !== null && $correctOpt && $chosenId === $correctOpt->id;
                            $skip = $chosenId === null;
                            $qHasImg = !empty($q->question_image_url);
                            $qHasVid = !empty($q->question_video_url);
                            $qHasMedia = $qHasImg || $qHasVid;
                        @endphp
                        <div class="qa-answer {{ $skip ? 'qa-answer-skip' : ($isRight ? 'qa-answer-correct' : 'qa-answer-wrong') }}">
                            @if($skip)
                                <span class="qa-answer-badge qa-badge-skip">😴 No answer recorded</span>
                            @elseif($isRight)
                                <span class="qa-answer-badge qa-badge-correct">✓ Correct answer</span>
                            @else
                                <span class="qa-answer-badge qa-badge-wrong">✗ Incorrect</span>
                            @endif

                            <div class="qa-answer-num">Question {{ $index + 1 }} of {{ $questionCount }}</div>
                            <p class="qa-answer-text">{{ $q->question_text }}</p>

                            @if($qHasMedia)
                                <div class="qa-split-layout" style="margin-top: 0.75rem;">
                                    <div class="qa-media-col" style="position: static;">
                                        @if($qHasImg)
                                            <div class="qa-media-wrapper" style="min-height: 180px; margin: 0;">
                                                <div class="qa-media">
                                                    <img src="{{ $q->question_image_url }}" alt="Question visual">
                                                </div>
                                            </div>
                                        @elseif($qHasVid)
                                            <div class="qa-media-wrapper" style="min-height: 180px; margin: 0;">
                                                <div class="qa-media">
                                                    <video controls preload="metadata" src="{{ $q->question_video_url }}"></video>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="qa-content-col">
                                        @foreach($this->orderedOptions($q) as $o)
                                            @php
                                                $isCorrectOpt = (bool) $o->is_correct;
                                                $isChosen = $chosenId === (int) $o->id;
                                            @endphp
                                            <div class="qa-res-row {{ $isCorrectOpt ? 'qa-res-correct' : ($isChosen ? 'qa-res-chosen' : '') }}">
                                                <span class="qa-res-tag">
                                                    {{ $isCorrectOpt ? '✓' : ($isChosen ? '✗' : '·') }} {{ $optLetters[$loop->index] }}. {{ $o->option_text }}
                                                </span>
                                                @if($isCorrectOpt)
                                                    <span>(Correct answer)</span>
                                                @elseif($isChosen)
                                                    <span>(Your choice)</span>
                                                @endif
                                            </div>
                                        @endforeach

                                        @if($q->explanation)
                                            <div class="qa-explanation">
                                                <strong>💡 Explanation:</strong> {{ $q->explanation }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                @foreach($this->orderedOptions($q) as $o)
                                    @php
                                        $isCorrectOpt = (bool) $o->is_correct;
                                        $isChosen = $chosenId === (int) $o->id;
                                    @endphp
                                    <div class="qa-res-row {{ $isCorrectOpt ? 'qa-res-correct' : ($isChosen ? 'qa-res-chosen' : '') }}">
                                        <span class="qa-res-tag">
                                            {{ $isCorrectOpt ? '✓' : ($isChosen ? '✗' : '·') }} {{ $optLetters[$loop->index] }}. {{ $o->option_text }}
                                        </span>
                                        @if($isCorrectOpt)
                                            <span>(Correct answer)</span>
                                        @elseif($isChosen)
                                            <span>(Your choice)</span>
                                        @endif
                                    </div>
                                @endforeach

                                @if($q->explanation)
                                    <div class="qa-explanation">
                                        <strong>💡 Explanation:</strong> {{ $q->explanation }}
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="qa-card">
                    <h2>🔒 Answers Hidden</h2>
                    <p class="qa-text">
                        Your teacher has set correct answer keys to remain confidential for this assessment.
                        Contact <strong>{{ $quiz->teacher?->user?->name ?? 'your teacher' }}</strong> if you have questions regarding your score.
                    </p>
                </div>
            @endif
        </div>

    {{-- ====================== TAKE SCREEN ====================== --}}
    @elseif($attempt)

        @php
            $visibleQuestion = $orderedQuestions[$currentIndex] ?? $orderedQuestions->first();
            $hasImg = !empty($visibleQuestion?->question_image_url);
            $hasVid = !empty($visibleQuestion?->question_video_url);
            $hasMedia = $hasImg || $hasVid;
        @endphp

        <header class="qa-hero" style="padding: 1.4rem 2.25rem;">
            <div class="qa-take-head" style="margin-bottom: 0;">
                <div>
                    <div class="qa-take-title">🧠 {{ $quiz->title }}</div>
                    <div class="qa-take-sub">Attempt #{{ $attemptNumber }} · Question {{ $currentIndex + 1 }} of {{ $questionCount }}</div>
                </div>
                @if($remainingSeconds !== null)
                    <div class="qa-timer"
                         x-data="qaTimer({{ $remainingSeconds }})"
                         x-init="qaStart()"
                         :class="clock !== null && clock <= 60 ? 'qa-timer-low' : ''">
                        <span class="qa-timer-glyph">⏱️</span>
                        <span x-text="qaClock"></span>
                    </div>
                @endif
            </div>
        </header>

        <div class="qa-body {{ $hasMedia ? 'qa-body-has-media' : '' }}">

            @if($notice)
                <div class="qa-alert {{ (str_contains($notice, 'run out') || str_contains($notice, 'not available')) ? 'qa-alert-err' : 'qa-alert-info' }}">
                    {{ $notice }}
                </div>
            @endif

            {{-- Question Card with Alpine Media Preloading & Interaction Locking --}}
            <div class="qa-card qa-qcard"
                 wire:key="question-box-{{ $visibleQuestion->id }}-{{ $currentIndex }}"
                 x-data="qaQuestionState({
                     qId: {{ $visibleQuestion->id }},
                     hasMedia: {{ $hasMedia ? 'true' : 'false' }}
                 })"
                 x-init="initQuestion()"
                 :class="{ 'qa-loading-lock': isMediaLoading }">

                {{-- Question Progress Stepper --}}
                <div class="qa-progress">
                    <span class="qa-progress-label">Question:</span>
                    @foreach($orderedQuestions as $qIndex => $_q)
                        <button type="button"
                                class="qa-dot {{ ($this->answers[$_q->id] ?? null) !== null ? 'qa-dot-answered' : '' }} {{ $qIndex === $currentIndex ? 'qa-dot-current' : '' }}"
                                x-on:click="beforeNavigate()"
                                wire:click="goToQuestion({{ $qIndex }})"
                                :disabled="isMediaLoading"
                                title="Question {{ $qIndex + 1 }}">
                            {{ $qIndex + 1 }}
                        </button>
                    @endforeach
                </div>

                @if($visibleQuestion)
                    @if($hasMedia)
                        {{-- ── 2-COLUMN SPLIT LAYOUT WHEN MEDIA IS PRESENT ── --}}
                        <div class="qa-split-layout">
                            {{-- LEFT COLUMN: MEDIA (Sticky, framed, instant clear & loading indicator) --}}
                            <div class="qa-media-col">
                                <div class="qa-media-wrapper" wire:key="media-wrap-{{ $visibleQuestion->id }}">
                                    {{-- Dedicated Loading Spinner Overlay --}}
                                    <div class="qa-media-loader" x-show="isMediaLoading" x-transition.opacity>
                                        <div class="qa-spinner"></div>
                                        <span class="qa-loader-text">
                                            @if($tier === 'kids')
                                                Loading picture... 🎨
                                            @elseif($tier === 'junior')
                                                Loading media... ⏳
                                            @else
                                                Loading question media...
                                            @endif
                                        </span>
                                    </div>

                                    {{-- Question Image --}}
                                    @if($hasImg)
                                        <div class="qa-media" x-show="!isMediaLoading" x-transition.opacity>
                                            <img src="{{ $visibleQuestion->question_image_url }}"
                                                 alt="Question visual"
                                                 x-on:load="onMediaLoaded()"
                                                 x-on:error="onMediaError()">
                                        </div>
                                    @elseif($hasVid)
                                        <div class="qa-media" x-show="!isMediaLoading" x-transition.opacity>
                                            <video controls
                                                   preload="auto"
                                                   src="{{ $visibleQuestion->question_video_url }}"
                                                   x-on:loadeddata="onMediaLoaded()"
                                                   x-on:canplay="onMediaLoaded()"
                                                   x-on:error="onMediaError()"></video>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- RIGHT COLUMN: QUESTION TEXT, OPTIONS & NAV --}}
                            <div class="qa-content-col">
                                <div class="qa-qnum">Question {{ $currentIndex + 1 }} of {{ $questionCount }}</div>
                                <p class="qa-qtext">{{ $visibleQuestion->question_text }}</p>

                                {{-- Options List (Clicking saves answer immediately, does NOT auto-advance) --}}
                                <div class="qa-opts">
                                    @foreach($this->orderedOptions($visibleQuestion) as $o)
                                        @php
                                            $isSelected = ($this->answers[$visibleQuestion->id] ?? null) === (int) $o->id;
                                        @endphp
                                        <button type="button"
                                                class="qa-opt {{ $isSelected ? 'qa-opt-selected' : '' }}"
                                                wire:click="selectAnswer({{ $visibleQuestion->id }}, {{ $o->id }})"
                                                :disabled="isMediaLoading">
                                            <div class="qa-opt-left">
                                                <span class="qa-opt-letter qa-opt-letter-{{ $loop->index }}">{{ $optLetters[$loop->index] }}</span>
                                                <span class="qa-opt-text">{{ $o->option_text }}</span>
                                            </div>
                                            @if($isSelected)
                                                <span class="qa-opt-check">✓</span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>

                                {{-- Navigation Row --}}
                                <div class="qa-nav">
                                    <button type="button"
                                            class="qa-nav-btn"
                                            x-on:click="beforeNavigate()"
                                            wire:click="previousQuestion"
                                            :disabled="isMediaLoading || {{ $currentIndex === 0 ? 'true' : 'false' }}">
                                        ◀ Prev
                                    </button>

                                    @if($this->isLastQuestion())
                                        <button type="button"
                                                class="qa-nav-btn qa-nav-primary qa-nav-submit"
                                                wire:click="submitAttempt"
                                                :disabled="isMediaLoading">
                                            ✅ Submit my answers
                                        </button>
                                    @else
                                        <button type="button"
                                                class="qa-nav-btn qa-nav-primary"
                                                x-on:click="beforeNavigate()"
                                                wire:click="nextOrSubmit"
                                                :disabled="isMediaLoading">
                                            Next ▶
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- ── SINGLE COLUMN CENTERED LAYOUT WHEN NO MEDIA ── --}}
                        <div class="qa-qnum">Question {{ $currentIndex + 1 }} of {{ $questionCount }}</div>
                        <p class="qa-qtext">{{ $visibleQuestion->question_text }}</p>

                        {{-- Options List (Clicking saves answer immediately, does NOT auto-advance) --}}
                        <div class="qa-opts">
                            @foreach($this->orderedOptions($visibleQuestion) as $o)
                                @php
                                    $isSelected = ($this->answers[$visibleQuestion->id] ?? null) === (int) $o->id;
                                @endphp
                                <button type="button"
                                        class="qa-opt {{ $isSelected ? 'qa-opt-selected' : '' }}"
                                        wire:click="selectAnswer({{ $visibleQuestion->id }}, {{ $o->id }})"
                                        :disabled="isMediaLoading">
                                    <div class="qa-opt-left">
                                        <span class="qa-opt-letter qa-opt-letter-{{ $loop->index }}">{{ $optLetters[$loop->index] }}</span>
                                        <span class="qa-opt-text">{{ $o->option_text }}</span>
                                    </div>
                                    @if($isSelected)
                                        <span class="qa-opt-check">✓</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>

                        {{-- Navigation Row --}}
                        <div class="qa-nav">
                            <button type="button"
                                    class="qa-nav-btn"
                                    x-on:click="beforeNavigate()"
                                    wire:click="previousQuestion"
                                    :disabled="isMediaLoading || {{ $currentIndex === 0 ? 'true' : 'false' }}">
                                ◀ Prev
                            </button>

                            @if($this->isLastQuestion())
                                <button type="button"
                                        class="qa-nav-btn qa-nav-primary qa-nav-submit"
                                        wire:click="submitAttempt"
                                        :disabled="isMediaLoading">
                                    ✅ Submit my answers
                                </button>
                            @else
                                <button type="button"
                                        class="qa-nav-btn qa-nav-primary"
                                        x-on:click="beforeNavigate()"
                                        wire:click="nextOrSubmit"
                                        :disabled="isMediaLoading">
                                    Next ▶
                                </button>
                            @endif
                        </div>
                    @endif
                @endif
            </div>
        </div>

    {{-- ====================== COVER SCREEN ====================== --}}
    @else

        <header class="qa-hero">
            <div class="qa-breadcrumb">
                <span>🏫 {{ $schoolName }}</span>
                @if($gradeName)
                    <span class="sep">›</span>
                    <span>{{ $gradeName }}</span>
                @endif
                <span class="sep">›</span>
                <span>📘 {{ $className }}</span>
            </div>
            <div class="qa-emoji">{{ $tier === 'kids' ? '🚀' : '🧠' }}</div>
            <h1 class="qa-title">{{ $quiz->title }}</h1>
            <div class="qa-meta">
                <span class="qa-chip">🧩 {{ $questionCount }} {{ \Illuminate\Support\Str::plural('question', $questionCount) }}</span>
                @if($timeLimit)
                    <span class="qa-chip">⏱️ {{ $timeLimit }} min</span>
                @endif
                <span class="qa-chip">🎯 Pass {{ $passingPercentage }}%</span>
                @if($maxAttempts)
                    <span class="qa-chip">🔁 {{ $maxAttempts }} {{ $maxAttempts > 1 ? 'attempts' : 'attempt' }}</span>
                @endif
            </div>
        </header>

        <div class="qa-body">

            @if($notice)
                <div class="qa-alert {{ $notice === 'This quiz is not available to take right now.' ? 'qa-alert-err' : 'qa-alert-info' }}">
                    {{ $notice }}
                </div>
            @endif

            @if($coverState === 'locked')
                <div class="qa-card">
                    <h2>🔒 Not open yet</h2>
                    <p class="qa-text">
                        This quiz opens on <strong>{{ $quiz->start_at?->format('M j, Y · g:i A') ?? 'soon' }}</strong>.
                        Check back then — your teacher will have it ready!
                    </p>
                </div>
            @elseif($coverState === 'closed')
                <div class="qa-card">
                    <h2>⛔ This quiz has ended</h2>
                    <p class="qa-text">
                        The deadline for this quiz has passed, so it is no longer available.
                        Ask your teacher if you have questions about your results.
                    </p>
                </div>
            @elseif($coverState === 'available')
                <div class="qa-card">
                    <h2>
                        <span>📖</span>
                        <span>{{ $tier === 'kids' ? 'Get ready to play!' : 'Assessment Information' }}</span>
                    </h2>
                    <ul class="qa-bullets">
                        <li><span>🧩</span> <strong>{{ $questionCount }} questions</strong>, presented one at a time.</li>
                        @if($timeLimit)
                            <li><span>⏱️</span> <strong>{{ $timeLimit }} minutes</strong> allocated in total.</li>
                        @endif
                        <li><span>🎯</span> Passing mark required: <strong>{{ $passingPercentage }}%</strong>.</li>
                        <li><span>💾</span> Your chosen answers are saved automatically as you go.</li>
                    </ul>
                    <div class="qa-cta-wrap">
                        <button type="button" class="qa-start-btn" wire:click="startAttempt">
                            <span>▶️</span>
                            <span>{{ $tier === 'kids' ? 'Start the Quiz!' : 'Begin Assessment' }}</span>
                        </button>
                        <div class="qa-sub-note">{{ $tier === 'kids' ? 'Ready when you are 😊' : 'Click to start your timer and questions.' }}</div>
                    </div>
                </div>
            @endif

            @if($quiz->instructions)
                <div class="qa-card">
                    <h2>
                        <span>📜</span>
                        <span>Teacher Instructions</span>
                    </h2>
                    <div class="qa-text qa-rich" style="font-size: 1rem; line-height: 1.8; color: #475569;">{!! $quiz->instructions !!}</div>
                </div>
            @endif
        </div>

    @endif
</div>

<script>
    function qaTimer(seconds) {
        return {
            clock: seconds,
            timerId: null,
            qaStart() {
                clearInterval(this.timerId);
                this.timerId = setInterval(() => {
                    this.clock -= 1;
                    if (this.clock <= 0) {
                        clearInterval(this.timerId);
                        if (this.$wire && this.$wire.timeUp) {
                            this.$wire.timeUp();
                        }
                    }
                }, 1000);
            },
            get qaClock() {
                const m = Math.max(0, Math.floor(this.clock / 60));
                const s = Math.max(0, this.clock % 60);
                return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
            }
        };
    }

    function qaQuestionState(config) {
        return {
            qId: config.qId,
            hasMedia: config.hasMedia,
            isMediaLoading: config.hasMedia,
            timeoutId: null,

            initQuestion() {
                if (this.hasMedia) {
                    this.isMediaLoading = true;
                    clearTimeout(this.timeoutId);
                    // Safety timeout: auto-unlock if media fails or takes > 5 seconds so student isn't permanently locked out
                    this.timeoutId = setTimeout(() => {
                        this.isMediaLoading = false;
                    }, 5000);
                } else {
                    this.isMediaLoading = false;
                }
            },

            onMediaLoaded() {
                clearTimeout(this.timeoutId);
                this.isMediaLoading = false;
            },

            onMediaError() {
                clearTimeout(this.timeoutId);
                this.isMediaLoading = false;
            },

            beforeNavigate() {
                if (this.hasMedia) {
                    this.isMediaLoading = true;
                }
            }
        };
    }
</script>