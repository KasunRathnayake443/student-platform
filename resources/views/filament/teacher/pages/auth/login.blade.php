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
    .tlogin-shell {
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.05fr);
        min-height: 100dvh;
        width: 100%;
        background: #f8fafc;
    }

    @media (max-width: 1023px) {
        .tlogin-shell {
            grid-template-columns: 1fr;
        }
        .tlogin-hero {
            display: none !important;
        }
    }

    /* ---- Left: branded hero (edge to edge) ---- */
    .tlogin-hero {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(1200px 600px at -10% -20%, rgba(16, 185, 129, 0.35) 0%, transparent 55%),
            linear-gradient(150deg, #4338ca 0%, #3730a3 45%, #1e1b4b 100%);
        color: #ffffff;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: clamp(2rem, 5vw, 4.5rem);
    }

    .tlogin-hero-orbit {
        position: absolute;
        border-radius: 9999px;
        pointer-events: none;
    }

    .tlogin-hero-orbit--ring {
        width: 520px;
        height: 520px;
        right: -180px;
        bottom: -180px;
        border: 2px dashed rgba(255, 255, 255, 0.22);
        animation: tlogin-spin 60s linear infinite;
    }

    .tlogin-hero-orbit--dot {
        width: 14px;
        height: 14px;
        background: #34d399;
        box-shadow: 0 0 24px 6px rgba(52, 211, 153, 0.65);
        right: calc(-180px + 253px);
        bottom: calc(-180px + 6px);
    }

    @keyframes tlogin-spin {
        to { transform: rotate(360deg); }
    }

    .tlogin-hero-glow {
        position: absolute;
        width: 420px;
        height: 420px;
        border-radius: 9999px;
        background: #10b981;
        filter: blur(140px);
        opacity: 0.28;
        left: -120px;
        bottom: -140px;
        pointer-events: none;
    }

    .tlogin-hero-inner {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        flex: 1;
    }

    .tlogin-badge {
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

    .tlogin-badge-dot {
        width: 0.55rem;
        height: 0.55rem;
        border-radius: 9999px;
        background: #34d399;
        box-shadow: 0 0 12px 2px rgba(52, 211, 153, 0.8);
    }

    .tlogin-headline {
        margin: clamp(2rem, 6vh, 4rem) 0 0;
        font-size: clamp(2.2rem, 3.4vw, 3.4rem);
        font-weight: 800;
        line-height: 1.12;
        letter-spacing: -0.03em;
    }

    .tlogin-headline em {
        font-style: normal;
        background: linear-gradient(90deg, #6ee7b7 0%, #a7f3d0 100%);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .tlogin-tagline {
        margin-top: 1.15rem;
        max-width: 46ch;
        font-size: 1.02rem;
        font-weight: 500;
        line-height: 1.65;
        color: rgba(255, 255, 255, 0.82);
    }

    .tlogin-perks {
        margin-top: clamp(2rem, 7vh, 3.5rem);
        display: grid;
        gap: 0.9rem;
    }

    .tlogin-perk {
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

    .tlogin-perk-icon {
        width: 2.35rem;
        height: 2.35rem;
        border-radius: 0.75rem;
        background: linear-gradient(135deg, rgba(52, 211, 153, 0.9) 0%, rgba(110, 231, 183, 0.75) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .tlogin-hero-foot {
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

    .tlogin-hero-foot strong {
        color: rgba(255, 255, 255, 0.92);
    }

    /* ---- Right: form side (edge to edge) ---- */
    .tlogin-form-side {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: clamp(1.5rem, 4vw, 4rem);
        position: relative;
        background:
            radial-gradient(700px 400px at 115% -10%, rgba(79, 70, 229, 0.08) 0%, transparent 60%),
            radial-gradient(600px 380px at -15% 115%, rgba(16, 185, 129, 0.09) 0%, transparent 60%),
            #ffffff;
    }

    .tlogin-mobile-brand {
        display: none;
    }

    @media (max-width: 1023px) {
        .tlogin-mobile-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
            padding: 0.5rem 1.1rem;
            border-radius: 9999px;
            background: linear-gradient(135deg, #4338ca 0%, #312e81 100%);
            color: #ffffff;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }
    }

    .tlogin-form-box {
        width: 100%;
        max-width: 26.5rem;
    }

    .tlogin-form-box h1 {
        font-size: clamp(1.8rem, 2.6vw, 2.3rem);
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #0f172a;
    }

    .tlogin-form-box > p {
        margin-top: 0.5rem;
        font-size: 0.98rem;
        font-weight: 500;
        color: #64748b;
    }

    .tlogin-divider {
        margin: 2rem 0;
        height: 1px;
        width: 100%;
        background: linear-gradient(90deg, #4f46e5 0%, rgba(16, 185, 129, 0.5) 50%, transparent 100%);
        opacity: 0.35;
    }

    /* ---- Form field styling (indigo identity) ---- */
    .tlogin-form-box form {
        display: flex;
        flex-direction: column;
        gap: 1.15rem;
    }

    .tlogin-form-box label,
    .tlogin-form-box .fi-fo-field-label,
    .tlogin-form-box .fi-fo-field-label-content,
    .tlogin-form-box .fi-checkbox-label,
    .tlogin-form-box p,
    .tlogin-form-box span:not(.fi-btn-label) {
        color: #1e293b !important;
        font-weight: 700 !important;
        font-size: 0.88rem !important;
    }

    .tlogin-form-box .fi-fo-field-wrp-error-message,
    .tlogin-form-box .fi-fo-field-error-message {
        color: #dc2626 !important;
        font-size: 0.82rem !important;
        font-weight: 700 !important;
        margin-top: 0.35rem !important;
    }

    .tlogin-form-box input[type="email"],
    .tlogin-form-box input[type="password"] {
        background-color: #f8fafc !important;
        border: 2px solid #e2e8f0 !important;
        border-radius: 0.9rem !important;
        padding: 0.9rem 1.1rem !important;
        color: #0f172a !important;
        font-size: 0.96rem !important;
        font-weight: 600 !important;
        transition: all 0.18s ease !important;
        box-shadow: none !important;
    }

    .tlogin-form-box input[type="email"]:focus,
    .tlogin-form-box input[type="password"]:focus {
        border-color: #4f46e5 !important;
        background-color: #ffffff !important;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.14) !important;
    }

    .tlogin-form-box button[type="submit"] {
        width: 100% !important;
        background: linear-gradient(135deg, #4f46e5 0%, #4338ca 55%, #0f766e 130%) !important;
        color: #ffffff !important;
        font-weight: 800 !important;
        font-size: 1rem !important;
        letter-spacing: 0.01em;
        padding: 1rem !important;
        border: none !important;
        border-radius: 0.9rem !important;
        cursor: pointer !important;
        margin-top: 0.5rem !important;
        box-shadow: 0 12px 28px -8px rgba(67, 56, 202, 0.45) !important;
        transition: transform 0.18s ease, box-shadow 0.18s ease !important;
    }

    .tlogin-form-box button[type="submit"]:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 18px 34px -8px rgba(67, 56, 202, 0.55) !important;
    }

    .tlogin-form-box button[type="submit"] span {
        color: #ffffff !important;
    }

    .tlogin-foot-note {
        margin-top: 2.25rem;
        text-align: center;
        font-size: 0.78rem;
        font-weight: 600;
        color: #94a3b8;
    }

    .tlogin-back-home {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        margin-bottom: 1.5rem;
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        background: #eef2ff;
        border: 1.5px solid #e0e7ff;
        color: #4f46e5;
        font-size: 0.82rem;
        font-weight: 800;
        text-decoration: none;
        transition: transform 0.18s ease, background 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
    }

    .tlogin-back-home:hover {
        transform: translateY(-1px);
        background: #e0e7ff;
        border-color: #a5b4fc;
        box-shadow: 0 6px 16px -6px rgba(79, 70, 229, 0.4);
    }

    .tlogin-back-home svg {
        flex-shrink: 0;
    }
    </style>

    <div class="tlogin-shell">
        <!-- Left: Teacher hero -->
        <section class="tlogin-hero">
            <div class="tlogin-hero-orbit tlogin-hero-orbit--ring"></div>
            <div class="tlogin-hero-orbit tlogin-hero-orbit--dot"></div>
            <div class="tlogin-hero-glow"></div>

            <div class="tlogin-hero-inner">
                <span class="tlogin-badge">
                    <span class="tlogin-badge-dot"></span>
                    Teacher Portal
                </span>

                <div>
                    <h1 class="tlogin-headline">Your classroom, <em>all in one place.</em></h1>
                    <p class="tlogin-tagline">
                        One workspace for every part of teaching — run your classes, publish lessons,
                        collect assignments, launch quizzes and grade with confidence.
                    </p>

                    <div class="tlogin-perks">
                        <div class="tlogin-perk">
                            <div class="tlogin-perk-icon">🏫</div>
                            <span>Manage classes &amp; enrolled students</span>
                        </div>
                        <div class="tlogin-perk">
                            <div class="tlogin-perk-icon">📝</div>
                            <span>Publish lessons, assignments &amp; quizzes</span>
                        </div>
                        <div class="tlogin-perk">
                            <div class="tlogin-perk-icon">✅</div>
                            <span>Grade submissions &amp; track progress</span>
                        </div>
                    </div>
                </div>

                <div class="tlogin-hero-foot">
                    <span>&copy; {{ now()->format('Y') }} <strong>Student Platform</strong></span>
                    <span>For teachers only</span>
                </div>
            </div>
        </section>

        <!-- Right: Sign-in form -->
        <section class="tlogin-form-side">
            <span class="tlogin-mobile-brand">📘 Teacher Portal · Student Platform</span>

            <div class="tlogin-form-box">
                <a href="/" class="tlogin-back-home">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
                    Back to Home
                </a>

                <h1>Welcome back 👋</h1>
                <p>Sign in with your teacher credentials to continue.</p>

                <div class="tlogin-divider"></div>

                {{ $this->content }}

                <p class="tlogin-foot-note">
                    Trouble signing in? Contact your school administrator.
                </p>
            </div>
        </section>
    </div>
</x-filament-panels::page.simple>
