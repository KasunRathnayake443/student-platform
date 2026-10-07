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

    .sreset-shell {
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.05fr);
        min-height: 100dvh;
        width: 100%;
        background: #f8fafc;
    }

    @media (max-width: 1023px) {
        .sreset-shell {
            grid-template-columns: 1fr;
        }
        .sreset-hero {
            display: none !important;
        }
    }

    .sreset-hero {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(1200px 600px at -10% -20%, rgba(217, 70, 239, 0.35) 0%, transparent 55%),
            linear-gradient(150deg, #c026d3 0%, #86198f 50%, #3b0764 100%);
        color: #ffffff;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: clamp(2rem, 5vw, 4.5rem);
    }

    .sreset-hero-orbit {
        position: absolute;
        border-radius: 9999px;
        pointer-events: none;
    }

    .sreset-hero-orbit--ring {
        width: 520px;
        height: 520px;
        right: -180px;
        bottom: -180px;
        border: 2px dashed rgba(255, 255, 255, 0.22);
        animation: sreset-spin 60s linear infinite;
    }

    .sreset-hero-orbit--dot {
        width: 14px;
        height: 14px;
        background: #f0abfc;
        box-shadow: 0 0 24px 6px rgba(240, 171, 252, 0.65);
        right: calc(-180px + 253px);
        bottom: calc(-180px + 6px);
    }

    @keyframes sreset-spin {
        to { transform: rotate(360deg); }
    }

    .sreset-hero-glow {
        position: absolute;
        width: 420px;
        height: 420px;
        border-radius: 9999px;
        background: #a21caf;
        filter: blur(140px);
        opacity: 0.28;
        left: -120px;
        bottom: -140px;
        pointer-events: none;
    }

    .sreset-hero-inner {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        flex: 1;
    }

    .sreset-badge {
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

    .sreset-badge-dot {
        width: 0.55rem;
        height: 0.55rem;
        border-radius: 9999px;
        background: #f0abfc;
        box-shadow: 0 0 12px 2px rgba(240, 171, 252, 0.8);
    }

    .sreset-headline {
        margin: clamp(2rem, 6vh, 4rem) 0 0;
        font-size: clamp(2.2rem, 3.4vw, 3.4rem);
        font-weight: 800;
        line-height: 1.12;
        letter-spacing: -0.03em;
    }

    .sreset-headline em {
        font-style: normal;
        background: linear-gradient(90deg, #f0abfc 0%, #f9a8d4 100%);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .sreset-tagline {
        margin-top: 1.15rem;
        max-width: 46ch;
        font-size: 1.02rem;
        font-weight: 500;
        line-height: 1.65;
        color: rgba(255, 255, 255, 0.82);
    }

    .sreset-hero-foot {
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

    .sreset-hero-foot strong {
        color: rgba(255, 255, 255, 0.92);
    }

    .sreset-form-side {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: clamp(1.5rem, 4vw, 4rem);
        position: relative;
        background:
            radial-gradient(700px 400px at 115% -10%, rgba(192, 38, 211, 0.08) 0%, transparent 60%),
            radial-gradient(600px 380px at -15% 115%, rgba(217, 70, 239, 0.09) 0%, transparent 60%),
            #ffffff;
    }

    .sreset-mobile-brand {
        display: none;
    }

    @media (max-width: 1023px) {
        .sreset-mobile-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
            padding: 0.5rem 1.1rem;
            border-radius: 9999px;
            background: linear-gradient(135deg, #c026d3 0%, #86198f 100%);
            color: #ffffff;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }
    }

    .sreset-form-box {
        width: 100%;
        max-width: 26.5rem;
    }

    .sreset-form-box h1 {
        font-size: clamp(1.8rem, 2.6vw, 2.3rem);
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #0f172a;
    }

    .sreset-form-box > p {
        margin-top: 0.5rem;
        font-size: 0.98rem;
        font-weight: 500;
        color: #64748b;
    }

    .sreset-divider {
        margin: 2rem 0;
        height: 1px;
        width: 100%;
        background: linear-gradient(90deg, #c026d3 0%, rgba(217, 70, 239, 0.5) 50%, transparent 100%);
        opacity: 0.35;
    }

    .sreset-form-box form {
        display: flex;
        flex-direction: column;
        gap: 1.15rem;
    }

    .sreset-form-box label,
    .sreset-form-box .fi-fo-field-label,
    .sreset-form-box .fi-fo-field-label-content,
    .sreset-form-box .fi-checkbox-label,
    .sreset-form-box span:not(.fi-btn-label) {
        color: #1e293b !important;
        font-weight: 700 !important;
        font-size: 0.88rem !important;
    }

    .sreset-form-box .fi-fo-field-wrp-error-message,
    .sreset-form-box .fi-fo-field-error-message {
        color: #dc2626 !important;
        font-size: 0.82rem !important;
        font-weight: 700 !important;
        margin-top: 0.35rem !important;
    }

    .sreset-form-box input[type="email"],
    .sreset-form-box input[type="password"] {
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

    .sreset-form-box input[type="email"]:focus,
    .sreset-form-box input[type="password"]:focus {
        border-color: #c026d3 !important;
        background-color: #ffffff !important;
        box-shadow: 0 0 0 4px rgba(192, 38, 211, 0.14) !important;
    }

    .sreset-form-box button[type="submit"] {
        width: 100% !important;
        background: linear-gradient(135deg, #d946ef 0%, #c026d3 55%, #86198f 130%) !important;
        color: #ffffff !important;
        font-weight: 800 !important;
        font-size: 1rem !important;
        letter-spacing: 0.01em;
        padding: 1rem !important;
        border: none !important;
        border-radius: 0.9rem !important;
        cursor: pointer !important;
        margin-top: 0.5rem !important;
        box-shadow: 0 12px 28px -8px rgba(192, 38, 211, 0.45) !important;
        transition: transform 0.18s ease, box-shadow 0.18s ease !important;
    }

    .sreset-form-box button[type="submit"]:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 18px 34px -8px rgba(192, 38, 211, 0.55) !important;
    }

    .sreset-form-box button[type="submit"] span {
        color: #ffffff !important;
    }

    .sreset-back-home {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        margin-bottom: 1.5rem;
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        background: #fdf4ff;
        border: 1.5px solid #f5d0fe;
        color: #c026d3;
        font-size: 0.82rem;
        font-weight: 800;
        text-decoration: none;
        transition: transform 0.18s ease, background 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
    }

    .sreset-back-home:hover {
        transform: translateY(-1px);
        background: #fae8ff;
        border-color: #f0abfc;
        box-shadow: 0 6px 16px -6px rgba(192, 38, 211, 0.4);
    }

    .sreset-back-home svg {
        flex-shrink: 0;
    }

    .sreset-alt-link {
        margin-top: 1.75rem;
        text-align: center;
        font-size: 0.85rem;
        font-weight: 700;
        color: #64748b !important;
    }

    .sreset-alt-link a {
        color: #c026d3;
        text-decoration: none;
        font-weight: 800;
    }

    .sreset-alt-link a:hover {
        text-decoration: underline;
    }

    .sreset-foot-note {
        margin-top: 2.25rem;
        text-align: center;
        font-size: 0.78rem;
        font-weight: 600;
        color: #94a3b8 !important;
    }
    </style>

    <div class="sreset-shell">
        <!-- Left: School Admin hero -->
        <section class="sreset-hero">
            <div class="sreset-hero-orbit sreset-hero-orbit--ring"></div>
            <div class="sreset-hero-orbit sreset-hero-orbit--dot"></div>
            <div class="sreset-hero-glow"></div>

            <div class="sreset-hero-inner">
                <span class="sreset-badge">
                    <span class="sreset-badge-dot"></span>
                    School Admin Portal
                </span>

                <div>
                    <h1 class="sreset-headline">Lost your <em>password?</em></h1>
                    <p class="sreset-tagline">
                        Enter the email address linked to your school administrator account and we'll
                        send you a secure link to set a new password.
                    </p>
                </div>

                <div class="sreset-hero-foot">
                    <span>&copy; {{ now()->format('Y') }} <strong>{{ \App\Services\PlatformSettings::name() }}</strong></span>
                    <span>For school administrators only</span>
                </div>
            </div>
        </section>

        <!-- Right: Reset request form -->
        <section class="sreset-form-side">
            <span class="sreset-mobile-brand">🏫 School Admin Portal · {{ \App\Services\PlatformSettings::name() }}</span>

            <div class="sreset-form-box">
                <a href="/" class="sreset-back-home">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
                    Back to Home
                </a>

                <h1>Reset your password</h1>
                <p>Enter your account email to receive a password reset link.</p>

                <div class="sreset-divider"></div>

                {{ $this->content }}

                <p class="sreset-alt-link">
                    Remembered your password?
                    <a href="{{ filament()->getLoginUrl() }}">Back to sign in</a>
                </p>
            </div>
        </section>
    </div>
</x-filament-panels::page.simple>