<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Student Platform') }} — Portal</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }

            body {
                font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
                min-height: 100dvh;
                background: #0f0a1e;
                color: #eef2ff;
                overflow-x: hidden;
                -webkit-font-smoothing: antialiased;
            }

            /* ---- Ambient background ---- */
            .landing-bg {
                position: fixed;
                inset: 0;
                z-index: -1;
                background:
                    radial-gradient(1100px 600px at 15% -10%, rgba(124, 58, 237, 0.35) 0%, transparent 55%),
                    radial-gradient(900px 500px at 85% 0%, rgba(99, 102, 241, 0.28) 0%, transparent 55%),
                    radial-gradient(1000px 700px at 50% 110%, rgba(236, 72, 153, 0.20) 0%, transparent 60%),
                    linear-gradient(160deg, #120b26 0%, #0f0a1e 55%, #0a0718 100%);
            }

            .landing-grid-overlay {
                position: fixed;
                inset: 0;
                z-index: -1;
                background-image:
                    linear-gradient(rgba(255, 255, 255, 0.035) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255, 255, 255, 0.035) 1px, transparent 1px);
                background-size: 56px 56px;
                mask-image: radial-gradient(circle at 50% 20%, #000 0%, transparent 70%);
                -webkit-mask-image: radial-gradient(circle at 50% 20%, #000 0%, transparent 70%);
                pointer-events: none;
            }

            .landing-wrap {
                max-width: 1160px;
                margin: 0 auto;
                padding: clamp(1.5rem, 4vw, 3.5rem) clamp(1.25rem, 4vw, 3rem);
                display: flex;
                flex-direction: column;
                min-height: 100dvh;
            }

            /* ---- Top bar ---- */
            .landing-top {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
            }

            .landing-logo {
                display: inline-flex;
                align-items: center;
                gap: 0.65rem;
                font-weight: 800;
                font-size: 1.05rem;
                color: #f5f3ff;
            }

            .landing-logo-mark {
                width: 2.3rem;
                height: 2.3rem;
                border-radius: 0.75rem;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.1rem;
                background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%);
                box-shadow: 0 8px 20px -6px rgba(124, 58, 237, 0.65);
            }

            /* ---- Hero ---- */
            .landing-hero {
                text-align: center;
                margin-top: clamp(2.5rem, 8vh, 5.5rem);
            }

            .landing-eyebrow {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.45rem 1.05rem;
                background: rgba(255, 255, 255, 0.06);
                border: 1px solid rgba(255, 255, 255, 0.12);
                border-radius: 9999px;
                font-size: 0.78rem;
                font-weight: 700;
                letter-spacing: 0.14em;
                text-transform: uppercase;
                color: #c4b5fd;
            }

            .landing-eyebrow-dot {
                width: 0.5rem;
                height: 0.5rem;
                border-radius: 9999px;
                background: linear-gradient(135deg, #a78bfa 0%, #f472b6 100%);
                box-shadow: 0 0 12px 2px rgba(196, 181, 253, 0.6);
            }

            .landing-title {
                margin-top: 1.4rem;
                font-size: clamp(2.1rem, 5vw, 3.6rem);
                font-weight: 800;
                line-height: 1.1;
                letter-spacing: -0.03em;
                color: #f5f3ff;
                max-width: 18ch;
                margin-left: auto;
                margin-right: auto;
            }

            .landing-title em {
                font-style: normal;
                background: linear-gradient(90deg, #a78bfa 0%, #818cf8 45%, #f0abfc 100%);
                -webkit-background-clip: text;
                background-clip: text;
                color: transparent;
            }

            .landing-subtitle {
                margin-top: 1.15rem;
                max-width: 56ch;
                margin-left: auto;
                margin-right: auto;
                font-size: clamp(0.98rem, 1.4vw, 1.12rem);
                font-weight: 500;
                line-height: 1.7;
                color: rgba(226, 232, 240, 0.72);
            }

            /* ---- Cards ---- */
            .landing-cards {
                margin-top: clamp(2.5rem, 7vh, 4.5rem);
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 1.4rem;
            }

            @media (max-width: 900px) {
                .landing-cards { grid-template-columns: 1fr; }
            }

            .landing-card {
                position: relative;
                display: flex;
                flex-direction: column;
                gap: 1.1rem;
                padding: 1.8rem;
                border-radius: 1.5rem;
                background: rgba(255, 255, 255, 0.045);
                border: 1px solid rgba(255, 255, 255, 0.10);
                text-decoration: none;
                color: inherit;
                overflow: hidden;
                transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
            }

            .landing-card::before {
                content: "";
                position: absolute;
                inset: 0;
                z-index: 0;
                opacity: 0.12;
                pointer-events: none;
                transition: opacity 0.25s ease;
            }

            .landing-card:hover {
                transform: translateY(-6px);
                border-color: rgba(255, 255, 255, 0.22);
                box-shadow: 0 24px 50px -18px rgba(0, 0, 0, 0.7);
            }

            .landing-card:hover::before { opacity: 0.22; }

            .landing-card > * { position: relative; z-index: 1; }

            /* Per-role gradients */
            .landing-card--student::before {
                background: radial-gradient(400px 200px at 0% 0%, rgba(124, 58, 237, 0.9) 0%, transparent 65%);
            }
            .landing-card--teacher::before {
                background: radial-gradient(400px 200px at 0% 0%, rgba(99, 102, 241, 0.9) 0%, transparent 65%);
            }
            .landing-card--school::before {
                background: radial-gradient(400px 200px at 0% 0%, rgba(236, 72, 153, 0.9) 0%, transparent 65%);
            }

            .landing-card-icon {
                width: 3.4rem;
                height: 3.4rem;
                border-radius: 1rem;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.5rem;
                background: rgba(0, 0, 0, 0.25);
                border: 1px solid rgba(255, 255, 255, 0.12);
            }

            .landing-card--student .landing-card-icon {
                background: linear-gradient(135deg, rgba(124, 58, 237, 0.9) 0%, rgba(109, 40, 217, 0.9) 100%);
                box-shadow: 0 12px 24px -8px rgba(124, 58, 237, 0.7);
            }
            .landing-card--teacher .landing-card-icon {
                background: linear-gradient(135deg, rgba(99, 102, 241, 0.9) 0%, rgba(67, 56, 202, 0.9) 100%);
                box-shadow: 0 12px 24px -8px rgba(99, 102, 241, 0.7);
            }
            .landing-card--school .landing-card-icon {
                background: linear-gradient(135deg, rgba(236, 72, 153, 0.9) 0%, rgba(190, 24, 93, 0.9) 100%);
                box-shadow: 0 12px 24px -8px rgba(236, 72, 153, 0.7);
            }

            .landing-card h2 {
                font-size: 1.3rem;
                font-weight: 800;
                color: #f5f3ff;
            }

            .landing-card p {
                font-size: 0.92rem;
                font-weight: 500;
                line-height: 1.6;
                color: rgba(226, 232, 240, 0.7);
                flex: 1;
            }

            .landing-card-action {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                width: 100%;
                padding: 0.85rem 1.2rem;
                border-radius: 0.9rem;
                font-weight: 800;
                font-size: 0.95rem;
                color: #ffffff;
                background: rgba(255, 255, 255, 0.10);
                border: 1px solid rgba(255, 255, 255, 0.16);
                transition: background 0.2s ease, border-color 0.2s ease;
            }

            .landing-card:hover .landing-card-action {
                background: rgba(255, 255, 255, 0.16);
                border-color: rgba(255, 255, 255, 0.30);
            }

            .landing-card--school .landing-badge-tag {
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
                align-self: flex-start;
                padding: 0.28rem 0.7rem;
                border-radius: 9999px;
                font-size: 0.68rem;
                font-weight: 800;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                color: #f9a8d4;
                background: rgba(236, 72, 153, 0.14);
                border: 1px solid rgba(236, 72, 153, 0.32);
            }

            /* ---- Footer ---- */
            .landing-foot {
                margin-top: auto;
                padding-top: clamp(2rem, 6vh, 4rem);
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                font-size: 0.78rem;
                font-weight: 600;
                color: rgba(226, 232, 240, 0.5);
            }

            .landing-foot strong { color: rgba(240, 244, 255, 0.8); }

            @media (max-width: 560px) {
                .landing-foot { flex-direction: column; text-align: center; align-items: flex-start; }
            }
        </style>
    </head>
    <body>
        <div class="landing-bg"></div>
        <div class="landing-grid-overlay"></div>

        <div class="landing-wrap">
            <header class="landing-top">
                <span class="landing-logo">
                    <span class="landing-logo-mark">🎓</span>
                    {{ config('app.name', 'Student Platform') }}
                </span>
            </header>

            <section class="landing-hero">
                <span class="landing-eyebrow">
                    <span class="landing-eyebrow-dot"></span>
                    One platform for the whole school
                </span>
                <h1 class="landing-title">Choose your <em>portal</em> to get started.</h1>
                <p class="landing-subtitle">
                    Sign in as a student, teacher, or school administrator. Each portal is tailored
                    to the way you learn, teach, and lead.
                </p>
            </section>

            <main class="landing-cards">
                <a class="landing-card landing-card--student" href="/student/login">
                    <span class="landing-card-icon">🎒</span>
                    <h2>Student</h2>
                    <p>Join your classes, complete lessons, assignments and quizzes, and watch your progress grow.</p>
                    <span class="landing-card-action">Student login →</span>
                </a>

                <a class="landing-card landing-card--teacher" href="/teacher/login">
                    <span class="landing-card-icon">🧑‍🏫</span>
                    <h2>Teacher</h2>
                    <p>Create lessons and quizzes, assign work to your classes, and track every student's results.</p>
                    <span class="landing-card-action">Teacher login →</span>
                </a>

                <a class="landing-card landing-card--school" href="/school-admin/login">
                    <span class="landing-card-icon">🏫</span>
                    <span class="landing-badge-tag">School Admin</span>
                    <h2>School Admin</h2>
                    <p>Oversee your school's classes, staff and students from a dedicated administration portal.</p>
                    <span class="landing-card-action">School admin login →</span>
                </a>
            </main>

            <footer class="landing-foot">
                <span>&copy; {{ now()->format('Y') }} <strong>{{ config('app.name', 'Student Platform') }}</strong> · All rights reserved.</span>
                <span>Student · Teacher · School Admin</span>
            </footer>
        </div>
    </body>
</html>
