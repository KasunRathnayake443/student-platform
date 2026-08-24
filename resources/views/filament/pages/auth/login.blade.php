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
    .alogin-shell {
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.05fr);
        min-height: 100dvh;
        width: 100%;
        background: #fafafa;
    }

    @media (max-width: 1023px) {
        .alogin-shell {
            grid-template-columns: 1fr;
        }
        .alogin-hero {
            display: none !important;
        }
    }

    /* ---- Left: branded hero (edge to edge) ---- */
    .alogin-hero {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(1200px 600px at -10% -20%, rgba(245, 158, 11, 0.22) 0%, transparent 55%),
            linear-gradient(150deg, #1e293b 0%, #0f172a 50%, #020617 100%);
        color: #ffffff;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: clamp(2rem, 5vw, 4.5rem);
    }

    .alogin-hero-orbit {
        position: absolute;
        border-radius: 9999px;
        pointer-events: none;
    }

    .alogin-hero-orbit--ring {
        width: 520px;
        height: 520px;
        right: -180px;
        bottom: -180px;
        border: 2px dashed rgba(255, 255, 255, 0.18);
        animation: alogin-spin 60s linear infinite;
    }

    .alogin-hero-orbit--dot {
        width: 14px;
        height: 14px;
        background: #fbbf24;
        box-shadow: 0 0 24px 6px rgba(251, 191, 36, 0.65);
        right: calc(-180px + 253px);
        bottom: calc(-180px + 6px);
    }

    @keyframes alogin-spin {
        to { transform: rotate(360deg); }
    }

    .alogin-hero-glow {
        position: absolute;
        width: 420px;
        height: 420px;
        border-radius: 9999px;
        background: #f59e0b;
        filter: blur(140px);
        opacity: 0.2;
        left: -120px;
        bottom: -140px;
        pointer-events: none;
    }

    .alogin-hero-inner {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        flex: 1;
    }

    .alogin-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.5rem 1rem;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(251, 191, 36, 0.35);
        border-radius: 9999px;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        backdrop-filter: blur(8px);
        width: fit-content;
    }

    .alogin-badge-dot {
        width: 0.55rem;
        height: 0.55rem;
        border-radius: 9999px;
        background: #fbbf24;
        box-shadow: 0 0 12px 2px rgba(251, 191, 36, 0.8);
    }

    .alogin-headline {
        margin: clamp(2rem, 6vh, 4rem) 0 0;
        font-size: clamp(2.2rem, 3.4vw, 3.4rem);
        font-weight: 800;
        line-height: 1.12;
        letter-spacing: -0.03em;
    }

    .alogin-headline em {
        font-style: normal;
        background: linear-gradient(90deg, #fbbf24 0%, #fde68a 100%);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .alogin-tagline {
        margin-top: 1.15rem;
        max-width: 46ch;
        font-size: 1.02rem;
        font-weight: 500;
        line-height: 1.65;
        color: rgba(255, 255, 255, 0.82);
    }

    .alogin-perks {
        margin-top: clamp(2rem, 7vh, 3.5rem);
        display: grid;
        gap: 0.9rem;
    }

    .alogin-perk {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        padding: 0.85rem 1.1rem;
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 1rem;
        backdrop-filter: blur(6px);
        font-weight: 600;
        font-size: 0.95rem;
    }

    .alogin-perk-icon {
        width: 2.35rem;
        height: 2.35rem;
        border-radius: 0.75rem;
        background: linear-gradient(135deg, rgba(251, 191, 36, 0.95) 0%, rgba(245, 158, 11, 0.85) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .alogin-hero-foot {
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

    .alogin-hero-foot strong {
        color: rgba(255, 255, 255, 0.92);
    }

    /* ---- Right: form side (edge to edge) ---- */
    .alogin-form-side {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: clamp(1.5rem, 4vw, 4rem);
        position: relative;
        background:
            radial-gradient(700px 400px at 115% -10%, rgba(245, 158, 11, 0.09) 0%, transparent 60%),
            radial-gradient(600px 380px at -15% 115%, rgba(30, 41, 59, 0.07) 0%, transparent 60%),
            #ffffff;
    }

    .alogin-mobile-brand {
        display: none;
    }

    @media (max-width: 1023px) {
        .alogin-mobile-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
            padding: 0.5rem 1.1rem;
            border-radius: 9999px;
            background: linear-gradient(135deg, #1e293b 0%, #020617 100%);
            color: #ffffff;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }
    }

    .alogin-form-box {
        width: 100%;
        max-width: 26.5rem;
    }

    .alogin-form-box h1 {
        font-size: clamp(1.8rem, 2.6vw, 2.3rem);
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #0f172a;
    }

    .alogin-form-box > p {
        margin-top: 0.5rem;
        font-size: 0.98rem;
        font-weight: 500;
        color: #64748b;
    }

    .alogin-divider {
        margin: 2rem 0;
        height: 1px;
        width: 100%;
        background: linear-gradient(90deg, #f59e0b 0%, rgba(30, 41, 59, 0.4) 50%, transparent 100%);
        opacity: 0.45;
    }

    /* ---- Form field styling (slate & amber identity) ---- */
    .alogin-form-box form {
        display: flex;
        flex-direction: column;
        gap: 1.15rem;
    }

    .alogin-form-box label,
    .alogin-form-box .fi-fo-field-label,
    .alogin-form-box .fi-fo-field-label-content,
    .alogin-form-box .fi-checkbox-label,
    .alogin-form-box p,
    .alogin-form-box span:not(.fi-btn-label) {
        color: #1e293b !important;
        font-weight: 700 !important;
        font-size: 0.88rem !important;
    }

    .alogin-form-box .fi-fo-field-wrp-error-message,
    .alogin-form-box .fi-fo-field-error-message {
        color: #dc2626 !important;
        font-size: 0.82rem !important;
        font-weight: 700 !important;
        margin-top: 0.35rem !important;
    }

    .alogin-form-box input[type="email"],
    .alogin-form-box input[type="password"] {
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

    .alogin-form-box input[type="email"]:focus,
    .alogin-form-box input[type="password"]:focus {
        border-color: #d97706 !important;
        background-color: #ffffff !important;
        box-shadow: 0 0 0 4px rgba(217, 119, 6, 0.14) !important;
    }

    .alogin-form-box button[type="submit"] {
        width: 100% !important;
        background: linear-gradient(135deg, #d97706 0%, #b45309 60%) !important;
        color: #ffffff !important;
        font-weight: 800 !important;
        font-size: 1rem !important;
        letter-spacing: 0.01em;
        padding: 1rem !important;
        border: none !important;
        border-radius: 0.9rem !important;
        cursor: pointer !important;
        margin-top: 0.5rem !important;
        box-shadow: 0 12px 28px -8px rgba(180, 83, 9, 0.45) !important;
        transition: transform 0.18s ease, box-shadow 0.18s ease !important;
    }

    .alogin-form-box button[type="submit"]:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 18px 34px -8px rgba(180, 83, 9, 0.55) !important;
    }

    .alogin-form-box button[type="submit"] span {
        color: #ffffff !important;
    }

    .alogin-foot-note {
        margin-top: 2.25rem;
        text-align: center;
        font-size: 0.78rem;
        font-weight: 600;
        color: #94a3b8;
    }
    </style>

    <div class="alogin-shell">
        <!-- Left: Administrator hero -->
        <section class="alogin-hero">
            <div class="alogin-hero-orbit alogin-hero-orbit--ring"></div>
            <div class="alogin-hero-orbit alogin-hero-orbit--dot"></div>
            <div class="alogin-hero-glow"></div>

            <div class="alogin-hero-inner">
                <span class="alogin-badge">
                    <span class="alogin-badge-dot"></span>
                    Super Admin Portal
                </span>

                <div>
                    <h1 class="alogin-headline">Govern the entire <em>platform.</em></h1>
                    <p class="alogin-tagline">
                        This is the control room for Student Platform — oversee every school,
                        teacher and student on the system, define the academic structure and
                        manage global settings from one place.
                    </p>

                    <div class="alogin-perks">
                        <div class="alogin-perk">
                            <div class="alogin-perk-icon">🌐</div>
                            <span>Platform-wide schools &amp; user governance</span>
                        </div>
                        <div class="alogin-perk">
                            <div class="alogin-perk-icon">🏗️</div>
                            <span>Grades, subjects &amp; academic structure</span>
                        </div>
                        <div class="alogin-perk">
                            <div class="alogin-perk-icon">📊</div>
                            <span>Global insights, roles &amp; settings</span>
                        </div>
                    </div>
                </div>

                <div class="alogin-hero-foot">
                    <span>&copy; {{ now()->format('Y') }} <strong>Student Platform</strong></span>
                    <span>Super admins only</span>
                </div>
            </div>
        </section>

        <!-- Right: Sign-in form -->
        <section class="alogin-form-side">
            <span class="alogin-mobile-brand">🔐 Super Admin Portal · Student Platform</span>

            <div class="alogin-form-box">
                <h1>Super admin sign in</h1>
                <p>Enter your super administrator credentials to continue.</p>

                <div class="alogin-divider"></div>

                {{ $this->content }}

                <p class="alogin-foot-note">
                    Trouble signing in? Contact the platform owner.
                </p>
            </div>
        </section>
    </div>
</x-filament-panels::page.simple>
