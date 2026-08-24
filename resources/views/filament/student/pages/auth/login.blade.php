<x-filament-panels::page.simple>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    /* ---- Neutralise Filament simple-page chrome: go truly full screen ---- */
    html,
    body {
        overflow-x: hidden;
    }

    .fi-simple-layout {
        min-height: 100dvh;
        display: flex;
        flex-direction: column;
    }

    .fi-simple-main-ctn {
        flex: 1 !important;
        padding: 0 !important;
        margin: 0 !important;
        max-width: none !important;
        display: block !important;
    }

    main#fi-main-content.fi-simple-main {
        max-width: none !important;
        width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .fi-simple-header {
        display: none !important;
    }

    /* ---- Full screen split layout ---- */
    .slogin-shell {
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.05fr);
        min-height: 100dvh;
        width: 100%;
        background: #fdfcff;
    }

    @media (max-width: 1023px) {
        .slogin-shell {
            grid-template-columns: 1fr;
        }
        .slogin-hero {
            display: none !important;
        }
    }

    /* ---- Left: branded hero (edge to edge) ---- */
    .slogin-hero {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(1200px 600px at -10% -20%, rgba(236, 72, 153, 0.32) 0%, transparent 55%),
            linear-gradient(150deg, #7c3aed 0%, #6d28d9 45%, #3b0764 100%);
        color: #ffffff;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: clamp(2rem, 5vw, 4.5rem);
    }

    .slogin-hero-orbit {
        position: absolute;
        border-radius: 9999px;
        pointer-events: none;
    }

    .slogin-hero-orbit--ring {
        width: 520px;
        height: 520px;
        right: -180px;
        bottom: -180px;
        border: 2px dashed rgba(255, 255, 255, 0.22);
        animation: slogin-spin 60s linear infinite;
    }

    .slogin-hero-orbit--dot {
        width: 14px;
        height: 14px;
        background: #f0abfc;
        box-shadow: 0 0 24px 6px rgba(240, 171, 252, 0.65);
        right: calc(-180px + 253px);
        bottom: calc(-180px + 6px);
    }

    @keyframes slogin-spin {
        to { transform: rotate(360deg); }
    }

    .slogin-hero-glow {
        position: absolute;
        width: 420px;
        height: 420px;
        border-radius: 9999px;
        background: #ec4899;
        filter: blur(140px);
        opacity: 0.28;
        left: -120px;
        bottom: -140px;
        pointer-events: none;
    }

    .slogin-hero-inner {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        flex: 1;
    }

    .slogin-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.5rem 1rem;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.28);
        border-radius: 9999px;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        backdrop-filter: blur(8px);
        width: fit-content;
    }

    .slogin-badge-dot {
        width: 0.55rem;
        height: 0.55rem;
        border-radius: 9999px;
        background: #f0abfc;
        box-shadow: 0 0 12px 2px rgba(240, 171, 252, 0.8);
    }

    .slogin-headline {
        margin: clamp(2rem, 6vh, 4rem) 0 0;
        font-size: clamp(2.2rem, 3.4vw, 3.4rem);
        font-weight: 800;
        line-height: 1.12;
        letter-spacing: -0.03em;
    }

    .slogin-headline em {
        font-style: normal;
        background: linear-gradient(90deg, #f0abfc 0%, #fbcfe8 100%);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .slogin-tagline {
        margin-top: 1.15rem;
        max-width: 46ch;
        font-size: 1.02rem;
        font-weight: 500;
        line-height: 1.65;
        color: rgba(255, 255, 255, 0.82);
    }

    .slogin-perks {
        margin-top: clamp(2rem, 7vh, 3.5rem);
        display: grid;
        gap: 0.9rem;
    }

    .slogin-perk {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        padding: 0.85rem 1.1rem;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 1rem;
        backdrop-filter: blur(6px);
        font-weight: 600;
        font-size: 0.95rem;
    }

    .slogin-perk-icon {
        width: 2.35rem;
        height: 2.35rem;
        border-radius: 0.75rem;
        background: linear-gradient(135deg, rgba(240, 171, 252, 0.95) 0%, rgba(249, 168, 212, 0.85) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .slogin-hero-foot {
        position: relative;
        z-index: 2;
        margin-top: clamp(2rem, 8vh, 4rem);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.66);
    }

    .slogin-hero-foot strong {
        color: rgba(255, 255, 255, 0.92);
    }

    /* ---- Right: form side (edge to edge) ---- */
    .slogin-form-side {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: clamp(1.5rem, 4vw, 4rem);
        position: relative;
        background:
            radial-gradient(700px 400px at 115% -10%, rgba(124, 58, 237, 0.08) 0%, transparent 60%),
            radial-gradient(600px 380px at -15% 115%, rgba(236, 72, 153, 0.09) 0%, transparent 60%),
            #ffffff;
    }

    .slogin-mobile-brand {
        display: none;
    }

    @media (max-width: 1023px) {
        .slogin-mobile-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
            padding: 0.5rem 1.1rem;
            border-radius: 9999px;
            background: linear-gradient(135deg, #7c3aed 0%, #4c1d95 100%);
            color: #ffffff;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }
    }

    .slogin-form-box {
        width: 100%;
        max-width: 26.5rem;
    }

    .slogin-form-box h1 {
        font-size: clamp(1.8rem, 2.6vw, 2.3rem);
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #0f172a;
    }

    .slogin-form-box > p {
        margin-top: 0.5rem;
        font-size: 0.98rem;
        font-weight: 500;
        color: #64748b;
    }

    .slogin-divider {
        margin: 2rem 0;
        height: 1px;
        width: 100%;
        background: linear-gradient(90deg, #7c3aed 0%, rgba(236, 72, 153, 0.5) 50%, transparent 100%);
        opacity: 0.35;
    }

    /* ---- Form field styling (violet identity) ---- */
    .slogin-form-box form {
        display: flex;
        flex-direction: column;
        gap: 1.15rem;
    }

    .slogin-form-box label,
    .slogin-form-box .fi-fo-field-label,
    .slogin-form-box .fi-fo-field-label-content,
    .slogin-form-box .fi-checkbox-label,
    .slogin-form-box p,
    .slogin-form-box span:not(.fi-btn-label) {
        color: #1e293b !important;
        font-weight: 700 !important;
        font-size: 0.88rem !important;
    }

    .slogin-form-box .fi-fo-field-wrp-error-message,
    .slogin-form-box .fi-fo-field-error-message {
        color: #dc2626 !important;
        font-size: 0.82rem !important;
        font-weight: 700 !important;
        margin-top: 0.35rem !important;
    }

    .slogin-form-box input[type="email"],
    .slogin-form-box input[type="password"] {
        background-color: #faf5ff !important;
        border: 2px solid #ede9fe !important;
        border-radius: 0.9rem !important;
        padding: 0.9rem 1.1rem !important;
        color: #0f172a !important;
        font-size: 0.96rem !important;
        font-weight: 600 !important;
        transition: all 0.18s ease !important;
        box-shadow: none !important;
    }

    .slogin-form-box input[type="email"]:focus,
    .slogin-form-box input[type="password"]:focus {
        border-color: #7c3aed !important;
        background-color: #ffffff !important;
        box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.14) !important;
    }

    .slogin-form-box button[type="submit"] {
        width: 100% !important;
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 55%, #be185d 130%) !important;
        color: #ffffff !important;
        font-weight: 800 !important;
        font-size: 1rem !important;
        letter-spacing: 0.01em;
        padding: 1rem !important;
        border: none !important;
        border-radius: 0.9rem !important;
        cursor: pointer !important;
        margin-top: 0.5rem !important;
        box-shadow: 0 12px 28px -8px rgba(109, 40, 217, 0.45) !important;
        transition: transform 0.18s ease, box-shadow 0.18s ease !important;
    }

    .slogin-form-box button[type="submit"]:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 18px 34px -8px rgba(109, 40, 217, 0.55) !important;
    }

    .slogin-form-box button[type="submit"] span {
        color: #ffffff !important;
    }

    .slogin-foot-note {
        margin-top: 2.25rem;
        text-align: center;
        font-size: 0.78rem;
        font-weight: 600;
        color: #94a3b8;
    }
    </style>

    <div class="slogin-shell">
        <!-- Left: Student hero -->
        <section class="slogin-hero">
            <div class="slogin-hero-orbit slogin-hero-orbit--ring"></div>
            <div class="slogin-hero-orbit slogin-hero-orbit--dot"></div>
            <div class="slogin-hero-glow"></div>

            <div class="slogin-hero-inner">
                <span class="slogin-badge">
                    <span class="slogin-badge-dot"></span>
                    Student Portal
                </span>

                <div>
                    <h1 class="slogin-headline">Empower your <em>learning journey.</em></h1>
                    <p class="slogin-tagline">
                        Your personal learning workspace — join your classes, complete lessons and
                        assignments, take quizzes and watch your progress grow in real time.
                    </p>

                    <div class="slogin-perks">
                        <div class="slogin-perk">
                            <div class="slogin-perk-icon">🚀</div>
                            <span>Personalized learning workspace</span>
                        </div>
                        <div class="slogin-perk">
                            <div class="slogin-perk-icon">📚</div>
                            <span>Interactive quizzes &amp; assignment hub</span>
                        </div>
                        <div class="slogin-perk">
                            <div class="slogin-perk-icon">🏆</div>
                            <span>Live academic progress tracking</span>
                        </div>
                    </div>
                </div>

                <div class="slogin-hero-foot">
                    <span>&copy; {{ now()->format('Y') }} <strong>Student Platform</strong></span>
                    <span>For students only</span>
                </div>
            </div>
        </section>

        <!-- Right: Sign-in form -->
        <section class="slogin-form-side">
            <span class="slogin-mobile-brand">🎓 Student Portal · Student Platform</span>

            <div class="slogin-form-box">
                <h1>Welcome back 👋</h1>
                <p>Sign in with your student credentials to continue.</p>

                <div class="slogin-divider"></div>

                {{ $this->content }}

                <p class="slogin-foot-note">
                    Trouble signing in? Contact your school administrator.
                </p>
            </div>
        </section>
    </div>
</x-filament-panels::page.simple>
