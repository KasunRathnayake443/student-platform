{{-- ══════════════════════════════════════════════════════════════
     KIDS GAME ARCADE (Ages 5-10)
     Animated learning games grouped by subject, filtered by age band.
     ══════════════════════════════════════════════════════════════ --}}

{{-- Livewire treats the first element in this view as the component root,
     so everything (including these styles) lives inside .kg-root. --}}
<div class="kg-root" x-data="{ burst: () => window.kidsCelebrate && window.kidsCelebrate() }"
     @kids-game-complete.window="burst()">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&family=Fredoka+One&display=swap');

        .kg-root {
            font-family: 'Nunito', 'Fredoka One', system-ui, sans-serif;
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            color: #1f2937;
        }

        /* ─── Hero ─── */
        .kg-hero {
            position: relative;
            overflow: hidden;
            border-radius: 2rem;
            padding: 1.75rem 2rem;
            color: #fff;
            background: linear-gradient(135deg, #7c3aed 0%, #a855f7 25%, #db2777 55%, #f97316 100%);
            box-shadow: 0 12px 36px rgba(124, 58, 237, .35);
        }
        .kg-hero::before {
            content: '';
            position: absolute;
            top: -55%; right: -4%;
            width: 260px; height: 260px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .12);
        }
        .kg-hero::after {
            content: '';
            position: absolute;
            bottom: -65%; left: 14%;
            width: 200px; height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .08);
        }
        .kg-hero-inner {
            position: relative;
            z-index: 2;
            display: flex;
            flex-wrap: wrap;
            gap: 1.25rem;
            align-items: center;
            justify-content: space-between;
        }
        .kg-hero h2 {
            margin: 0 0 .3rem;
            font-family: 'Fredoka One', 'Nunito', sans-serif;
            font-size: 2rem;
            letter-spacing: .5px;
        }
        .kg-hero p { margin: 0; font-size: 1rem; font-weight: 700; opacity: .92; }
        .kg-hero-stats { display: flex; gap: .65rem; flex-wrap: wrap; }
        .kg-stat-chip {
            min-width: 6rem;
            padding: .6rem 1rem;
            text-align: center;
            border-radius: 1.25rem;
            background: rgba(255, 255, 255, .18);
            border: 2px solid rgba(255, 255, 255, .35);
            backdrop-filter: blur(4px);
        }
        .kg-stat-chip .kg-stat-value { font-family: 'Fredoka One', sans-serif; font-size: 1.5rem; line-height: 1.1; }
        .kg-stat-chip .kg-stat-label { font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; opacity: .9; }

        /* ─── Age filter chips ─── */
        .kg-filters { display: flex; gap: .6rem; flex-wrap: wrap; }
        .kg-chip {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .6rem 1.1rem;
            border-radius: 999px;
            border: 3px solid #ede9fe;
            background: #fff;
            color: #6d28d9;
            font-family: 'Fredoka One', 'Nunito', sans-serif;
            font-size: .95rem;
            cursor: pointer;
            transition: transform .15s ease, border-color .15s ease, background .15s ease;
        }
        .kg-chip:hover { transform: translateY(-2px); }
        .kg-chip.active {
            border-color: #a855f7;
            background: linear-gradient(135deg, #f3e8ff, #fce7f3);
            box-shadow: 0 8px 18px -10px rgba(124, 58, 237, .7);
        }
        .kg-chip-hint {
            font-family: 'Nunito', sans-serif;
            font-size: .7rem;
            font-weight: 900;
            padding: .1rem .5rem;
            border-radius: 999px;
            background: #f3e8ff;
            color: #7c3aed;
        }
        .kg-chip.active .kg-chip-hint { background: #fff; }

        /* ─── Subject groups & cards ─── */
        .kg-subject-title {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin: .25rem 0 .25rem;
            font-family: 'Fredoka One', 'Nunito', sans-serif;
            font-size: 1.3rem;
            color: #4c1d95;
        }
        .kg-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(255px, 1fr));
            gap: 1.15rem;
        }
        .kg-card {
            display: flex;
            flex-direction: column;
            gap: .5rem;
            padding: 1.35rem;
            background: #fff;
            border: 3px solid #f3e8ff;
            border-radius: 1.75rem;
            box-shadow: 0 10px 24px -12px rgba(76, 29, 149, .25);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }
        .kg-card:hover {
            transform: translateY(-4px);
            border-color: #e9d5ff;
            box-shadow: 0 16px 32px -12px rgba(76, 29, 149, .35);
        }
        .kg-card-emoji {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 3.75rem; height: 3.75rem;
            margin-bottom: .2rem;
            font-size: 2.05rem;
            border-radius: 1.4rem;
            background: linear-gradient(135deg, #f3e8ff, #ffe4e6);
        }
        .kg-card-title { margin: 0; font-family: 'Fredoka One', sans-serif; font-size: 1.3rem; color: #4c1d95; }
        .kg-card-tag { margin: 0; font-size: .92rem; font-weight: 800; color: #db2777; }
        .kg-card-desc { margin: 0; font-size: .88rem; font-weight: 700; color: #6b7280; line-height: 1.4; }
        .kg-card-skill {
            align-self: flex-start;
            padding: .22rem .6rem;
            border-radius: 999px;
            background: #f5f3ff;
            color: #7c3aed;
            font-size: .68rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .07em;
        }
        .kg-card-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .6rem;
            margin-top: auto;
            padding-top: .45rem;
        }
        .kg-card-meta { font-size: .8rem; font-weight: 800; color: #8b5cf6; }
        .kg-age-badge { font-size: .72rem; font-weight: 900; color: #9333ea; background: #faf5ff; padding: .18rem .5rem; border-radius: 999px; }

        /* ─── Stars ─── */
        .kg-stars { display: inline-flex; gap: .12rem; font-size: 1rem; }
        .kg-star { opacity: .25; filter: grayscale(1); }
        .kg-star.on { opacity: 1; filter: none; }

        /* ─── Buttons ─── */
        .kg-btn {
            padding: .65rem 1.4rem;
            border: none;
            border-radius: 999px;
            background: linear-gradient(135deg, #8b5cf6, #6366f1);
            color: #fff;
            font-family: 'Fredoka One', 'Nunito', sans-serif;
            font-size: .98rem;
            cursor: pointer;
            box-shadow: 0 8px 18px -8px rgba(109, 40, 217, .7);
            transition: transform .15s ease, filter .15s ease;
        }
        .kg-btn:hover { transform: translateY(-2px); filter: brightness(1.05); }
        .kg-btn:active { transform: translateY(0); }
        .kg-btn-ghost { background: #fff; color: #7c3aed; border: 3px solid #e9d5ff; box-shadow: none; }
        .kg-btn-warm { background: linear-gradient(135deg, #f97316, #f43f5e); box-shadow: 0 8px 18px -8px rgba(244, 63, 94, .7); }
        .kg-btn-green { background: linear-gradient(135deg, #22c55e, #14b8a6); box-shadow: 0 8px 18px -8px rgba(20, 184, 166, .7); }

        /* ─── Play chrome ─── */
        .kg-play { display: flex; flex-direction: column; gap: 1.1rem; }
        .kg-play-top {
            display: flex;
            flex-wrap: wrap;
            gap: .9rem;
            align-items: center;
            justify-content: space-between;
            padding: .9rem 1.15rem;
            background: #fff;
            border: 3px solid #f3e8ff;
            border-radius: 1.5rem;
            box-shadow: 0 8px 20px -14px rgba(76, 29, 149, .3);
        }
        .kg-play-head { display: flex; align-items: center; gap: .8rem; }
        .kg-play-title { display: flex; align-items: center; gap: .5rem; margin: 0; font-family: 'Fredoka One', sans-serif; font-size: 1.3rem; color: #4c1d95; }
        .kg-play-sub { display: block; font-size: .85rem; font-weight: 800; color: #8b5cf6; }
        .kg-live-score { font-family: 'Fredoka One', sans-serif; font-size: 1.35rem; color: #db2777; }
        .kg-hint-text { font-size: .85rem; font-weight: 800; color: #9333ea; }

        /* ─── Shared game pieces ─── */
        .kg-field {
            position: relative;
            overflow: hidden;
            height: 330px;
            border-radius: 1.75rem;
            border: 4px solid #fde68a;
            background: linear-gradient(180deg, #eff6ff 0%, #fdf4ff 100%);
            outline: none;
        }
        .kg-field:focus { border-color: #a855f7; }
        .kg-prompt {
            margin: 0;
            text-align: center;
            font-family: 'Fredoka One', 'Nunito', sans-serif;
            font-size: 1.6rem;
            color: #4c1d95;
            line-height: 1.25;
            word-break: break-word;
        }
        .kg-prompt-card {
            padding: 1.6rem 1.4rem;
            text-align: center;
            border-radius: 1.75rem;
            border: 4px solid #fde68a;
            background: linear-gradient(135deg, #fffbeb, #fdf4ff);
            box-shadow: 0 14px 30px -18px rgba(180, 83, 9, .5);
        }
        .kg-prompt-emoji { font-size: 2.9rem; line-height: 1; }
        .kg-options { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: .8rem; }
        .kg-option {
            padding: 1rem 1.1rem;
            border: 4px solid #e9d5ff;
            border-radius: 1.4rem;
            background: #fff;
            color: #4c1d95;
            font-family: 'Fredoka One', 'Nunito', sans-serif;
            font-size: 1.2rem;
            text-align: center;
            cursor: pointer;
            box-shadow: 0 8px 18px -12px rgba(76, 29, 149, .35);
            transition: transform .15s ease, border-color .15s ease, background .15s ease;
            word-break: break-word;
        }
        .kg-option:hover { transform: translateY(-3px); border-color: #c4b5fd; }
        .kg-option:disabled { cursor: default; }
        .kg-option.is-correct { background: #dcfce7; border-color: #22c55e; color: #166534; animation: kg-pop .35s ease; }
        .kg-option.is-wrong { background: #fee2e2; border-color: #ef4444; color: #991b1b; animation: kg-shake .35s ease; }
        .kg-option.is-dim { opacity: .45; }
        @keyframes kg-pop { 0% { transform: scale(1); } 50% { transform: scale(1.07); } 100% { transform: scale(1); } }
        @keyframes kg-shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-7px); } 75% { transform: translateX(7px); } }

        .kg-feedback {
            display: flex;
            align-items: center;
            gap: .7rem;
            flex-wrap: wrap;
            padding: .9rem 1.1rem;
            border-radius: 1.4rem;
            font-weight: 800;
            font-size: 1rem;
        }
        .kg-feedback.good { background: #dcfce7; color: #166534; border: 3px solid #86efac; }
        .kg-feedback.bad { background: #fee2e2; color: #991b1b; border: 3px solid #fca5a5; }
        .kg-feedback-emoji { font-size: 1.6rem; }
        .kg-feedback-text { flex: 1; min-width: 12rem; }
        .kg-feedback-note { display: block; margin-top: .15rem; font-size: .88rem; font-weight: 700; opacity: .9; }

        /* ─── Results ─── */
        .kg-results {
            padding: 2rem 1.6rem;
            text-align: center;
            background: #fff;
            border: 4px solid #e9d5ff;
            border-radius: 2rem;
            box-shadow: 0 16px 34px -20px rgba(76, 29, 149, .45);
        }
        .kg-results-emoji { font-size: 3.8rem; line-height: 1; }
        .kg-results h3 { margin: .4rem 0; font-family: 'Fredoka One', sans-serif; font-size: 1.9rem; color: #4c1d95; }
        .kg-results-stars { margin: .5rem 0 .8rem; font-size: 2.4rem; letter-spacing: .15rem; }
        .kg-results-score { margin: 0 0 1.4rem; font-size: 1.05rem; font-weight: 800; color: #6b7280; }
        .kg-results-actions { display: flex; gap: .8rem; justify-content: center; flex-wrap: wrap; }

        /* ─── Confetti ─── */
        .kg-confetti { position: fixed; inset: 0; z-index: 9999; pointer-events: none; overflow: hidden; }
        .kg-confetti i {
            position: absolute;
            top: -24px;
            width: 12px; height: 18px;
            border-radius: 3px;
            animation: kg-fall linear forwards;
        }
        @keyframes kg-fall { to { transform: translateY(105vh) rotate(720deg); } }

        /* ─── Falling basket ─── */
        .kg-target-pill {
            position: absolute;
            top: .7rem;
            left: 50%;
            z-index: 3;
            transform: translateX(-50%);
            padding: .35rem 1rem;
            border-radius: 999px;
            background: #4c1d95;
            color: #fff;
            font-family: 'Fredoka One', sans-serif;
            font-size: 1rem;
        }
        .kg-falling {
            position: absolute;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .1rem;
            transform: translateX(-50%);
            transition: opacity .2s ease, transform .2s ease;
            will-change: top;
        }
        .kg-falling-emoji { font-size: 1.9rem; line-height: 1; }
        .kg-falling-num {
            min-width: 1.6rem;
            padding: .05rem .3rem;
            border-radius: 999px;
            background: #fff;
            border: 2px solid #c4b5fd;
            color: #4c1d95;
            font-family: 'Fredoka One', sans-serif;
            font-size: .85rem;
            text-align: center;
        }
        .kg-falling--caught { opacity: 0; transform: translateX(-50%) scale(1.5); }
        .kg-basket {
            position: absolute;
            bottom: 6px;
            transform: translateX(-50%);
            font-size: 2.4rem;
            transition: left .06s linear;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, .2));
        }

        /* ─── Running gates ─── */
        .kg-runner-track {
            position: relative;
            height: 150px;
            overflow: hidden;
            border-radius: 1.75rem;
            border: 4px solid #e9d5ff;
            background: linear-gradient(180deg, #ede9fe 0%, #f5f3ff 62%, #ddd6fe 62%, #c4b5fd 100%);
        }
        .kg-runner-road {
            position: absolute;
            right: 0; bottom: 0; left: 0;
            height: 34%;
            background: repeating-linear-gradient(90deg, #a78bfa 0 26px, #c4b5fd 26px 52px);
            animation: kg-road 1s linear infinite;
        }
        @keyframes kg-road { to { background-position: -52px 0; } }
        .kg-runner {
            position: absolute;
            bottom: 22%;
            left: 8%;
            z-index: 2;
            font-size: 3rem;
            animation: kg-bob .5s ease-in-out infinite alternate;
        }
        @keyframes kg-bob { from { transform: translateY(0); } to { transform: translateY(-8px); } }
        .kg-runner--stumble { animation: kg-shake .35s ease; }
        .kg-gate {
            position: absolute;
            right: 8%;
            bottom: 22%;
            z-index: 1;
            display: flex;
            align-items: flex-end;
            gap: .4rem;
            animation: kg-slide-in .45s ease;
        }
        @keyframes kg-slide-in { from { transform: translateX(130%); } to { transform: translateX(0); } }
        .kg-gate-sign {
            padding: .45rem .9rem;
            border-radius: 1rem;
            background: #fff;
            border: 3px solid #a855f7;
            color: #4c1d95;
            font-family: 'Fredoka One', sans-serif;
            font-size: 1.15rem;
            white-space: nowrap;
        }
        .kg-gate-post {
            width: 10px;
            height: 74px;
            border-radius: 6px;
            background: linear-gradient(180deg, #f97316, #db2777);
        }
        .kg-gate--open { animation: kg-gate-open .45s ease forwards; }
        @keyframes kg-gate-open { to { transform: translateX(150%); opacity: 0; } }
        .kg-goal { position: absolute; right: 1%; bottom: 22%; z-index: 1; font-size: 2.2rem; }

        /* ─── Floating balloons ─── */
        .kg-balloon-field {
            position: relative;
            height: 260px;
            overflow: hidden;
            border-radius: 1.75rem;
            border: 4px solid #bae6fd;
            background: linear-gradient(180deg, #e0f2fe 0%, #f0f9ff 100%);
        }
        .kg-balloon {
            position: absolute;
            bottom: 12px;
            left: var(--balloon-left);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 78px;
            height: 92px;
            border: none;
            border-radius: 50% 50% 50% 50% / 56% 56% 44% 44%;
            background: hsl(var(--balloon-hue) 85% 68%);
            box-shadow: inset -8px -10px 0 rgba(0, 0, 0, .12);
            cursor: pointer;
            animation: kg-float var(--float-duration) ease-in-out var(--float-delay) infinite alternate;
            transition: transform .18s ease, opacity .25s ease;
        }
        .kg-balloon::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 50%;
            transform: translateX(-50%);
            border-left: 7px solid transparent;
            border-right: 7px solid transparent;
            border-top: 14px solid hsl(var(--balloon-hue) 85% 55%);
        }
        @keyframes kg-float {
            from { transform: translateY(0) rotate(-3deg); }
            to { transform: translateY(-26px) rotate(3deg); }
        }
        .kg-balloon-letter {
            font-family: 'Fredoka One', sans-serif;
            font-size: 2rem;
            color: #fff;
            text-shadow: 0 2px 4px rgba(0, 0, 0, .3);
        }
        .kg-balloon--pop { transform: scale(1.6); opacity: 0; }
        .kg-balloon--wobble { animation: kg-shake .4s ease; }

        /* ─── Letter tiles ─── */
        .kg-blanks { display: flex; flex-wrap: wrap; justify-content: center; gap: .4rem; margin: .8rem 0 .4rem; }
        .kg-blank {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.6rem;
            height: 3rem;
            border-radius: .8rem;
            background: #fff;
            border: 3px solid #c4b5fd;
            font-family: 'Fredoka One', sans-serif;
            font-size: 1.5rem;
            color: #4c1d95;
        }
        .kg-tiles {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: .6rem;
            max-width: 460px;
            margin: 0 auto;
        }
        .kg-tile {
            width: 3.1rem;
            height: 3.1rem;
            border-radius: 1rem;
            border: 3px solid #e9d5ff;
            background: linear-gradient(135deg, #fff, #faf5ff);
            font-family: 'Fredoka One', sans-serif;
            font-size: 1.4rem;
            color: #4c1d95;
            cursor: pointer;
            box-shadow: 0 6px 14px -10px rgba(76, 29, 149, .5);
            transition: transform .15s ease, opacity .15s ease;
        }
        .kg-tile:hover { transform: translateY(-3px); }
        .kg-tile--used { opacity: .25; pointer-events: none; }
        .kg-tile--shake { animation: kg-shake .35s ease; }

        /* ─── Flipping cards ─── */
        .kg-memory-grid { display: grid; justify-content: center; gap: .7rem; }
        .kg-memory-grid--small { grid-template-columns: repeat(3, minmax(0, 96px)); }
        .kg-memory-grid--large { grid-template-columns: repeat(4, minmax(0, 96px)); }
        .kg-card-tile {
            position: relative;
            aspect-ratio: 1 / 1;
            padding: 0;
            border: none;
            background: transparent;
            cursor: pointer;
            perspective: 600px;
        }
        .kg-card-tile-face {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 1.1rem;
            backface-visibility: hidden;
            transition: transform .35s ease;
            font-size: 2.1rem;
        }
        .kg-card-tile-back {
            background: linear-gradient(135deg, #a855f7, #6366f1);
            color: #fff;
            font-family: 'Fredoka One', sans-serif;
        }
        .kg-card-tile-front {
            background: #fff;
            border: 3px solid #e9d5ff;
            transform: rotateY(180deg);
        }
        .kg-card-tile.is-up .kg-card-tile-back { transform: rotateY(180deg); }
        .kg-card-tile.is-up .kg-card-tile-front { transform: rotateY(0); }
        .kg-card-tile.is-done .kg-card-tile-front { background: #dcfce7; border-color: #22c55e; }

        /* ─── Coding grid ─── */
        .kg-coding-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: .35rem;
            max-width: 420px;
            margin: 0 auto;
            padding: .6rem;
            border-radius: 1.5rem;
            border: 4px solid #c7d2fe;
            background: #eef2ff;
        }
        .kg-cell {
            display: flex;
            align-items: center;
            justify-content: center;
            aspect-ratio: 1 / 1;
            border-radius: .6rem;
            background: #fff;
            font-size: 1.5rem;
        }
        .kg-cell--wall { background: #94a3b8; }
        .kg-cell--goal { background: #fef9c3; }
        .kg-cell--robot { background: #bfdbfe; }
        .kg-program {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: .35rem;
            min-height: 2.9rem;
            padding: .5rem .7rem;
            border-radius: 1.1rem;
            border: 3px dashed #c4b5fd;
            background: #faf5ff;
        }
        .kg-program-empty { font-size: .85rem; font-weight: 800; color: #a78bfa; }
        .kg-block {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.2rem;
            height: 2.2rem;
            border-radius: .7rem;
            background: #fff;
            border: 2px solid #ddd6fe;
            font-size: 1.15rem;
            animation: kg-pop .2s ease;
        }
        .kg-arrangers {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .kg-arrows { display: flex; gap: .5rem; }
        .kg-arrow {
            width: 3.1rem;
            height: 3.1rem;
            border-radius: 1rem;
            border: 3px solid #e9d5ff;
            background: #fff;
            font-size: 1.35rem;
            cursor: pointer;
            transition: transform .15s ease, border-color .15s ease;
        }
        .kg-arrow:hover { transform: translateY(-3px); border-color: #c4b5fd; }
        .kg-coding-actions { display: flex; flex-wrap: wrap; gap: .5rem; }

        /* ─── Pixel grid ─── */
        .kg-palette { display: flex; flex-wrap: wrap; justify-content: center; gap: .6rem; }
        .kg-swatch {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 3.4rem;
            height: 3.4rem;
            border-radius: 1rem;
            border: 4px solid #fff;
            box-shadow: 0 0 0 2px #e5e7eb;
            cursor: pointer;
            font-family: 'Fredoka One', sans-serif;
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .kg-swatch span {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 50%;
            background: rgba(255, 255, 255, .9);
            font-size: 1rem;
            color: #111827;
        }
        .kg-swatch:hover { transform: translateY(-3px); }
        .kg-swatch--active { box-shadow: 0 0 0 3px #7c3aed; transform: translateY(-3px); }
        .kg-pixel-grid {
            display: grid;
            grid-template-columns: repeat(10, minmax(0, 1fr));
            gap: 3px;
            max-width: 420px;
            margin: 0 auto;
            padding: .5rem;
            border-radius: 1.2rem;
            border: 4px solid #fbcfe8;
            background: #fdf2f8;
        }
        .kg-pixel {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            aspect-ratio: 1 / 1;
            padding: 0;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .kg-pixel::after {
            content: attr(data-number);
            font-family: 'Fredoka One', sans-serif;
            font-size: .72rem;
            color: #6b7280;
        }
        .kg-pixel--empty { cursor: default; }
        .kg-pixel--empty::after,
        .kg-pixel--solved::after { content: ''; }
        .kg-pixel--wrong { animation: kg-shake .35s ease; }

        @media (max-width: 640px) {
            .kg-hero { padding: 1.4rem; }
            .kg-prompt { font-size: 1.25rem; }
            .kg-field { height: 290px; }
        }
    </style>

    @if ($activeGameMeta === null)
        {{-- ══════════════ GAMES HUB ══════════════ --}}
        <div class="kg-hero">
            <div class="kg-hero-inner">
                <div>
                    <h2>Game Arcade 🎮</h2>
                    <p>Pick a game, play and collect stars!</p>
                </div>
                <div class="kg-hero-stats">
                    <div class="kg-stat-chip">
                        <div class="kg-stat-value">⭐ {{ $totalStars }}</div>
                        <div class="kg-stat-label">Stars</div>
                    </div>
                    <div class="kg-stat-chip">
                        <div class="kg-stat-value">{{ $totalPlays }}</div>
                        <div class="kg-stat-label">Games Played</div>
                    </div>
                    <div class="kg-stat-chip">
                        <div class="kg-stat-value">{{ count($catalog) }}</div>
                        <div class="kg-stat-label">Games</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="kg-filters">
            @foreach ($ageFilters as $ageFilter)
                <button type="button"
                        class="kg-chip {{ $filter === $ageFilter['key'] ? 'active' : '' }}"
                        wire:click="setFilter('{{ $ageFilter['key'] }}')">
                    <span>{{ $ageFilter['emoji'] }}</span>
                    <span>{{ $ageFilter['label'] }}</span>
                    <span class="kg-chip-hint">{{ $ageFilter['hint'] }}</span>
                </button>
            @endforeach
        </div>

        @forelse ($groups as $group)
            <div>
                <h3 class="kg-subject-title">{{ $group['subject']['emoji'] }} {{ $group['subject']['label'] }}</h3>
                <div class="kg-grid">
                    @foreach ($group['games'] as $game)
                        @php $stat = $stats[$game['key']] ?? ['best_score' => 0, 'stars' => 0, 'plays' => 0]; @endphp
                        <div class="kg-card">
                            <div class="kg-card-emoji">{{ $game['emoji'] }}</div>
                            <h4 class="kg-card-title">{{ $game['title'] }}</h4>
                            <p class="kg-card-tag">{{ $game['tagline'] }}</p>
                            <p class="kg-card-desc">{{ $game['description'] }}</p>
                            <span class="kg-card-skill">{{ $game['skill'] }}</span>

                            <div class="kg-card-foot">
                                <span class="kg-stars" title="{{ $stat['stars'] }} of {{ $maxStars }} stars">
                                    @for ($i = 1; $i <= $maxStars; $i++)
                                        <span class="kg-star {{ $i <= $stat['stars'] ? 'on' : '' }}">⭐</span>
                                    @endfor
                                </span>
                                <span class="kg-age-badge">
                                    {{ in_array(\App\Services\KidsGameService::AGE_ALL, $game['ages'], true) ? '5-10' : ($game['ages'][0] === \App\Services\KidsGameService::AGE_EARLY ? '5-7' : '8-10') }}
                                </span>
                            </div>

                            <div class="kg-card-foot">
                                <span class="kg-card-meta">
                                    @if ($stat['plays'] > 0)
                                        Best {{ $stat['best_score'] }} · {{ $stat['plays'] }} play{{ $stat['plays'] === 1 ? '' : 's' }}
                                    @else
                                        Not played yet
                                    @endif
                                </span>
                                <button type="button" class="kg-btn" wire:click="startGame('{{ $game['key'] }}')">
                                    Play ▶
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="kg-prompt-card">
                <div class="kg-prompt-emoji">🎮</div>
                <p class="kg-prompt">No games for this age group yet — try “All Ages”!</p>
            </div>
        @endforelse
    @elseif ($justFinished)
        {{-- ══════════════ RESULTS ══════════════ --}}
        <div class="kg-results">
            <div class="kg-results-emoji">
                {{ $lastStars >= 3 ? '🏆' : ($lastStars >= 2 ? '🎉' : ($lastStars >= 1 ? '💪' : '🌱')) }}
            </div>
            <h3>
                @if ($lastStars >= 3)
                    Perfect! You are a superstar!
                @elseif ($lastStars >= 2)
                    Great job! Nearly perfect!
                @elseif ($lastStars >= 1)
                    Good try! Keep practising!
                @else
                    Nice try! Let's play again!
                @endif
            </h3>

            <div class="kg-results-stars">
                @for ($i = 1; $i <= $maxStars; $i++)
                    <span class="kg-star {{ $i <= $lastStars ? 'on' : '' }}">⭐</span>
                @endfor
            </div>

            <p class="kg-results-score">
                You got <strong>{{ $lastCorrect }}</strong> out of <strong>{{ $lastTotal }}</strong> right —
                <strong>{{ $lastScore }}</strong> points!
            </p>

            <div class="kg-results-actions">
                <button type="button" class="kg-btn kg-btn-warm" wire:click="playAgain">🔄 Play Again</button>
                <button type="button" class="kg-btn kg-btn-ghost" wire:click="backToHub">🎮 Back to Games</button>
            </div>
        </div>
    @else
        {{-- ══════════════ PLAYING A GAME ══════════════ --}}
        @include('livewire.kids.games.' . $activeGameMeta['key'])
    @endif
</div>

<script>
    window.kidsCelebrate = window.kidsCelebrate || function () {
        const colors = ['#f97316', '#a855f7', '#22c55e', '#ec4899', '#38bdf8', '#facc15'];
        const host = document.createElement('div');
        host.className = 'kg-confetti';
        document.body.appendChild(host);

        for (let i = 0; i < 70; i++) {
            const piece = document.createElement('i');
            piece.style.left = (Math.random() * 100) + 'vw';
            piece.style.background = colors[Math.floor(Math.random() * colors.length)];
            piece.style.animationDuration = (2 + Math.random() * 1.6) + 's';
            piece.style.animationDelay = (Math.random() * 0.5) + 's';
            host.appendChild(piece);
        }

        setTimeout(() => host.remove(), 4200);
    };
</script>
