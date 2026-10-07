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

    .areset-shell {
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.05fr);
        min-height: 100dvh;
        width: 100%;
        background: #fafafa;
    }

    @media (max-width: 1023px) {
        .areset-shell {
            grid-template-columns: 1fr;
        }
        .areset-hero {
            display: none !important;
        }
    }

    .areset-hero {
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

    .areset-hero-orbit {
        position: absolute;
        border-radius: 9999px;
        pointer-events: none;
    }

    .areset-hero-orbit--ring {
        width: 520px;
        height: 520px;
        right: -180px;
        bottom: -180px;
        border: 2px dashed rgba(255, 255, 255, 0.18);
        animation: areset-spin 60s linear infinite;
    }

    .areset-hero-orbit--dot {
        width: 14px;
        height: 14px;
        background: #fbbf24;
        box-shadow: 0 0 24px 6px rgba(251, 191, 36, 0.65);
        right: calc(-180px + 253px);
        bottom: calc(-180px + 6px);
    }

    @keyframes areset-spin {
        to { transform: rotate(360deg); }
    }

    .areset-hero-glow {
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

    .areset-hero-inner {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        flex: 1;
    }

    .areset-badge {
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

    .areset-badge-dot {
        width: 0.55rem;
        height: 0.55rem;
        border-radius: 9999px;
        background: #fbbf24;
        box-shadow: 0 0 12px 2px rgba(251, 191, 36, 0.8);
    }

    .areset-headline {
        margin: clamp(2rem, 6vh, 4rem) 0 0;
        font-size: clamp(2.2rem, 3.4vw, 3.4rem);
        font-weight: 800;
        line-height: 1.12;
        letter-spacing: -0.03em;
    }

    .areset-headline em {
        font-style: normal;
        background: linear-gradient(90deg, #fbbf24 0%, #fde68a 100%);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .areset-tagline {
        margin-top: 1.15rem;
        max-width: 46ch;
        font-size: 1.02rem;
        font-weight: 500;
        line-height: 1.65;
        color: rgba(255, 255, 255, 0.82);
    }

    .areset-hero-foot {
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

    .areset-hero-foot strong {
        color: rgba(255, 255, 255, 0.92);
    }

    .areset-form-side {
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

    .areset-mobile-brand {
        display: none;
    }

    @media (max-width: 1023px) {
        .areset-mobile-brand {
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

    .areset-form-box {
        width: 100%;
        max-width: 26.5rem;
    }

    .areset-form-box h1 {
        font-size: clamp(1.8rem, 2.6vw, 2.3rem);
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #0f172a;
    }

    .areset-form-box > p {
        margin-top: 0.5rem;
        font-size: 0.98rem;
        font-weight: 500;
        color: #64748b;
    }

    .areset-divider {
        margin: 2rem 0;
        height: 1px;
        width: 100%;
        background: linear-gradient(90deg, #f59e0b 0%, rgba(30, 41, 59, 0.4) 50%, transparent 100%);
        opacity: 0.45;
    }

    .areset-form-box form {
        display: flex;
        flex-direction: column;
        gap: 1.15rem;
    }

    .areset-form-box label,
    .areset-form-box .fi-fo-field-label,
    .areset-form-box .fi-fo-field-label-content,
    .areset-form-box .fi-checkbox-label,
    .areset-form-box span:not(.fi-btn-label) {
        color: #1e293b !important;
        font-weight: 700 !important;
        font-size: 0.88rem !important;
    }

    .areset-form-box .fi-fo-field-wrp-error-message,
    .areset-form-box .fi-fo-field-error-message {
        color: #dc2626 !important;
        font-size: 0.82rem !important;
        font-weight: 700 !important;
        margin-top: 0.35rem !important;
    }

    .areset-form-box input[type="email"],
    .areset-form-box input[type="password"] {
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

    .areset-form-box input[type="email"]:focus,
    .areset-form-box input[type="password"]:focus {
        border-color: #d97706 !important;
        background-color: #ffffff !important;
        box-shadow: 0 0 0 4px rgba(217, 119, 6, 0.14) !important;
    }

    .areset-form-box button[type="submit"] {
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

    .areset-form-box button[type="submit"]:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 18px 34px -8px rgba(180, 83, 9, 0.55) !important;
    }

    .areset-form-box button[type="submit"] span {
        color: #ffffff !important;
    }

    .areset-back-home {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        margin-bottom: 1.5rem;
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        background: #fffbeb;
        border: 1.5px solid #fde68a;
        color: #b45309;
        font-size: 0.82rem;
        font-weight: 800;
        text-decoration: none;
        transition: transform 0.18s ease, background 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
    }

    .areset-back-home:hover {
        transform: translateY(-1px);
        background: #fef3c7;
        border-color: #fcd34d;
        box-shadow: 0 6px 16px -6px rgba(180, 83, 9, 0.4);
    }

    .areset-back-home svg {
        flex-shrink: 0;
    }

    .areset-alt-link {
        margin-top: 1.75rem;
        text-align: center;
        font-size: 0.85rem;
        font-weight: 700;
        color: #64748b !important;
    }

    .areset-alt-link a {
        color: #b45309;
        text-decoration: none;
        font-weight: 800;
    }

    .areset-alt-link a:hover {
        text-decoration: underline;
    }

    .areset-foot-note {
        margin-top: 2.25rem;
        text-align: center;
        font-size: 0.78rem;
        font-weight: 600;
        color: #94a3b8 !important;
    }
    </style>

    <div class="areset-shell">
        <!-- Left: Super admin hero -->
        <section class="areset-hero">
            <div class="areset-hero-orbit areset-hero-orbit--ring"></div>
            <div class="areset-hero-orbit areset-hero-orbit--dot"></div>
            <div class="areset-hero-glow"></div>

            <div class="areset-hero-inner">
                <span class="areset-badge">
                    <span class="areset-badge-dot"></span>
                    Super Admin Portal
                </span>

                <div>
                    <h1 class="areset-headline">Almost <em>there.</em></h1>
                    <p class="areset-tagline">
                        Choose a new password for your super administrator account. Make it
                        strong and keep it safe — you'll use it every time you sign in.
                    </p>
                </div>

                <div class="areset-hero-foot">
                    <span>&copy; {{ now()->format('Y') }} <strong>{{ \App\Services\PlatformSettings::name() }}</strong></span>
                    <span>For super admins only</span>
                </div>
            </div>
        </section>

        <!-- Right: Reset form -->
        <section class="areset-form-side">
            <span class="areset-mobile-brand">🔐 Super Admin Portal · {{ \App\Services\PlatformSettings::name() }}</span>

            <div class="areset-form-box">
                <a href="/" class="areset-back-home">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
                    Back to Home
                </a>

                <h1>Set a new password</h1>
                <p>Your new password must be at least 8 characters.</p>

                <div class="areset-divider"></div>

                {{ $this->content }}

                <p class="areset-alt-link">
                    <a href="{{ filament()->getLoginUrl() }}">Back to sign in</a>
                </p>
            </div>
        </section>
    </div>
</x-filament-panels::page.simple>