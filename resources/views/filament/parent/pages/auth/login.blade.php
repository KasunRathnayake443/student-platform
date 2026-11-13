<x-filament-panels::page.simple>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        html,
        body {
            overflow-x: hidden;
        }

        .fi-simple-layout {
            min-height: 100dvh;
            background:
                radial-gradient(900px 520px at 10% -10%, rgba(16, 185, 129, 0.18) 0%, transparent 60%),
                radial-gradient(800px 480px at 105% 110%, rgba(45, 212, 191, 0.16) 0%, transparent 60%),
                #f8fafc;
        }

        .fi-simple-main-ctn {
            flex: 1 !important;
            padding: 1.5rem !important;
            max-width: none !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        main#fi-main-content.fi-simple-main {
            width: 100% !important;
            max-width: 28rem !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .fi-simple-header {
            display: none !important;
        }

        .parent-login-shell {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            width: 100%;
        }

        .parent-login-card {
            position: relative;
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.5rem;
            padding: clamp(1.75rem, 5vw, 2.5rem);
            box-shadow: 0 24px 60px -28px rgba(15, 23, 42, 0.28);
        }

        .parent-login-card::before {
            content: '';
            position: absolute;
            inset: 0 0 auto;
            height: 5px;
            background: linear-gradient(90deg, #047857 0%, #10b981 50%, #2dd4bf 100%);
        }

        .parent-login-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.4rem 0.8rem;
            border-radius: 999px;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #047857;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .parent-login-badge-dot {
            width: 0.45rem;
            height: 0.45rem;
            border-radius: 999px;
            background: #10b981;
            box-shadow: 0 0 10px 2px rgba(16, 185, 129, 0.45);
        }

        .parent-login-card h1 {
            margin: 1.15rem 0 0;
            color: #0f172a;
            font-size: clamp(1.6rem, 4vw, 2rem);
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1.2;
        }

        .parent-login-card > p {
            margin: 0.55rem 0 0;
            color: #64748b;
            font-size: 0.94rem;
            line-height: 1.6;
            font-weight: 500;
        }

        .parent-login-divider {
            height: 1px;
            margin: 1.6rem 0;
            background: linear-gradient(90deg, #10b981 0%, #99f6e4 55%, transparent 100%);
            opacity: 0.6;
        }

        .parent-login-card form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .parent-login-card label,
        .parent-login-card .fi-fo-field-label,
        .parent-login-card .fi-fo-field-label-content,
        .parent-login-card .fi-checkbox-label {
            color: #1e293b !important;
            font-weight: 700 !important;
            font-size: 0.86rem !important;
        }

        .parent-login-card input[type="email"],
        .parent-login-card input[type="password"] {
            background-color: #f8fafc !important;
            border: 1.5px solid #e2e8f0 !important;
            border-radius: 0.8rem !important;
            padding: 0.85rem 1rem !important;
            color: #0f172a !important;
            font-size: 0.94rem !important;
            font-weight: 600 !important;
            box-shadow: none !important;
            transition: all 0.18s ease !important;
        }

        .parent-login-card input[type="email"]:focus,
        .parent-login-card input[type="password"]:focus {
            border-color: #10b981 !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.14) !important;
        }

        .parent-login-card button[type="submit"] {
            width: 100% !important;
            margin-top: 0.35rem !important;
            padding: 0.9rem !important;
            border-radius: 0.8rem !important;
            font-size: 0.96rem !important;
            font-weight: 800 !important;
            background: linear-gradient(135deg, #047857 0%, #10b981 100%) !important;
            border: none !important;
            box-shadow: 0 12px 26px -12px rgba(4, 120, 87, 0.6) !important;
        }

        .parent-login-note {
            margin-top: 1.5rem !important;
            padding-top: 1.15rem;
            border-top: 1px solid #f1f5f9;
            text-align: center;
            color: #94a3b8 !important;
            font-size: 0.76rem !important;
            line-height: 1.55;
            font-weight: 500 !important;
        }

        .parent-login-back {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            margin-bottom: 1.35rem;
            color: #047857;
            font-size: 0.8rem;
            font-weight: 800;
            text-decoration: none;
        }

        .parent-login-back:hover {
            color: #065f46;
            text-decoration: underline;
        }
    </style>

    <div class="parent-login-shell">
        <div class="parent-login-card">
            <a href="/" class="parent-login-back">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
                Back to Home
            </a>

            <span class="parent-login-badge">
                <span class="parent-login-badge-dot"></span>
                Parent Portal
            </span>

            <h1>Welcome back</h1>
            <p>Sign in to follow your child's learning progress, quiz scores, assignments, lessons, and notifications.</p>

            <div class="parent-login-divider"></div>

            {{ $this->content }}

            <p class="parent-login-note">
                Parent accounts are created by the school's super administrator.
                Contact them if you need access.
            </p>
        </div>
    </div>
</x-filament-panels::page.simple>
