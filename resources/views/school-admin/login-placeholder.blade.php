<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>School Admin Login — {{ config('app.name', 'Student Platform') }}</title>

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
                display: flex;
                align-items: center;
                justify-content: center;
                background:
                    radial-gradient(1000px 600px at 80% -10%, rgba(236, 72, 153, 0.32) 0%, transparent 55%),
                    radial-gradient(900px 500px at 5% 110%, rgba(124, 58, 237, 0.28) 0%, transparent 55%),
                    linear-gradient(160deg, #120b26 0%, #0f0a1e 55%, #0a0718 100%);
                color: #eef2ff;
                padding: 1.5rem;
                overflow-x: hidden;
            }

            .box {
                max-width: 460px;
                width: 100%;
                padding: 2.4rem;
                border-radius: 1.5rem;
                background: rgba(255, 255, 255, 0.05);
                border: 1px solid rgba(255, 255, 255, 0.12);
                text-align: center;
            }

            .icon {
                width: 4rem;
                height: 4rem;
                margin: 0 auto 1.25rem;
                border-radius: 1.1rem;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.8rem;
                background: linear-gradient(135deg, rgba(236, 72, 153, 0.9) 0%, rgba(190, 24, 93, 0.9) 100%);
                box-shadow: 0 14px 30px -8px rgba(236, 72, 153, 0.7);
            }

            .tag {
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
                padding: 0.3rem 0.75rem;
                border-radius: 9999px;
                font-size: 0.68rem;
                font-weight: 800;
                letter-spacing: 0.1em;
                text-transform: uppercase;
                color: #f9a8d4;
                background: rgba(236, 72, 153, 0.14);
                border: 1px solid rgba(236, 72, 153, 0.32);
            }

            h1 {
                margin-top: 1rem;
                font-size: 1.5rem;
                font-weight: 800;
                color: #f5f3ff;
            }

            p {
                margin-top: 0.75rem;
                font-size: 0.95rem;
                font-weight: 500;
                line-height: 1.65;
                color: rgba(226, 232, 240, 0.72);
            }

            .url {
                margin-top: 1.5rem;
                display: inline-block;
                padding: 0.5rem 1.1rem;
                border-radius: 0.8rem;
                background: rgba(255, 255, 255, 0.07);
                border: 1px solid rgba(255, 255, 255, 0.14);
                font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
                font-size: 0.85rem;
                font-weight: 700;
                color: #fbcfe8;
            }

            .back {
                margin-top: 1.75rem;
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                font-size: 0.85rem;
                font-weight: 700;
                color: rgba(238, 242, 255, 0.75);
                text-decoration: none;
                border-bottom: 1px solid rgba(238, 242, 255, 0.25);
                padding-bottom: 1px;
                transition: color 0.2s ease, border-color 0.2s ease;
            }

            .back:hover {
                color: #ffffff;
                border-color: rgba(255, 255, 255, 0.6);
            }
        </style>
    </head>
    <body>
        <div class="box">
            <div class="icon">🏫</div>
            <span class="tag">School Admin</span>
            <h1>School Admin portal</h1>
            <p>
                The school administrator portal is coming soon. It will let school
                administrators manage their staff, students, classes and schools from
                a single dashboard.
            </p>
            <div class="url">/school-admin/login</div>
            <br>
            <a class="back" href="{{ route('home') }}">← Back to portal selection</a>
        </div>
    </body>
</html>
