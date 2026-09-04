@php
    $tier = $tier ?? 'junior';
    $activeSchoolName = $activeContext ? ($activeContext['school']->name ?? 'My School') : 'My School';
    $activeGradeName  = $activeContext ? ($activeContext['grade']->name  ?? 'My Grade')  : 'My Grade';
@endphp

<div class="snp-page snp-tier-{{ $tier }}" style="min-height: 100vh; display: flex; flex-direction: column;">
    <style>
        /* Standalone Student Notification Page Layout */
        .snp-page {
            width: 100%;
            min-height: 100vh;
        }

        /* Tier Backgrounds */
        .snp-tier-kids {
            background: linear-gradient(135deg, #ede9fe 0%, #f3e8ff 30%, #fce7f3 60%, #fff7ed 100%);
            font-family: 'Nunito', 'Fredoka One', system-ui, sans-serif;
        }
        .snp-tier-junior {
            background: #f8fafc;
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
        }
        .snp-tier-senior {
            background: #f8fafc;
            font-family: 'Inter', system-ui, sans-serif;
        }

        /* Top Bar */
        .snp-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.1rem 2.2rem;
            flex-wrap: wrap;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 40;
        }
        .snp-tier-kids .snp-topbar {
            background: rgba(255, 255, 255, 0.9);
            border-bottom: 2.5px solid #ede9fe;
        }

        .snp-topbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .snp-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #0f172a;
            font-weight: 700;
            font-size: 0.85rem;
            padding: 0.45rem 0.95rem;
            border-radius: 999px;
            text-decoration: none;
            transition: all 0.15s ease;
            box-shadow: 0 1px 3px rgba(15,23,42,0.04);
        }
        .snp-back-btn:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
            transform: translateX(-2px);
        }
        .snp-tier-kids .snp-back-btn {
            border: 2px solid #ddd6fe;
            background: linear-gradient(135deg, #ffffff 0%, #faf5ff 100%);
            color: #7c3aed;
            font-weight: 900;
            font-size: 0.9rem;
            padding: 0.5rem 1.15rem;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.12);
        }
        .snp-tier-kids .snp-back-btn:hover {
            background: #f3e8ff;
            border-color: #c084fc;
            transform: translateY(-2px) scale(1.04);
        }

        .snp-brand-title {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .snp-tier-kids .snp-brand-title {
            font-size: 1.15rem;
            font-weight: 900;
            color: #6b21a8;
        }

        .snp-topbar-right {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }
        .snp-context-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: #f1f5f9;
            color: #475569;
            font-size: 0.78rem;
            font-weight: 600;
            padding: 0.4rem 0.85rem;
            border-radius: 999px;
            border: 1px solid #e2e8f0;
        }
        .snp-tier-kids .snp-context-pill {
            background: #fdf4ff;
            color: #7c3aed;
            border: 2px solid #f3e8ff;
            font-weight: 800;
            font-size: 0.82rem;
            padding: 0.45rem 1rem;
        }

        /* Main Content Workspace */
        .snp-main-body {
            flex: 1;
            width: 100%;
            max-width: 1180px;
            margin: 0 auto;
            padding: 2rem 2rem 4rem;
            box-sizing: border-box;
        }
        @media (max-width: 768px) {
            .snp-topbar { padding: 0.85rem 1.25rem; }
            .snp-main-body { padding: 1.25rem 1rem 3rem; }
        }
    </style>

    {{-- Top Bar --}}
    <header class="snp-topbar">
        <div class="snp-topbar-left">
            <a href="/student" class="snp-back-btn">
                <span>←</span>
                <span>{{ $tier === 'kids' ? 'Back to Adventure' : 'Back to Dashboard' }}</span>
            </a>
            <div class="snp-brand-title">
                <span>{{ $tier === 'kids' ? '📬 Magic Mailbox' : '🔔 Notification Center' }}</span>
            </div>
        </div>

        <div class="snp-topbar-right">
            <div class="snp-context-pill">
                📍 {{ $activeSchoolName }} › {{ $activeGradeName }}
            </div>
            <livewire:notification-panel :tier="$tier" />
        </div>
    </header>

    {{-- Main Workspace --}}
    <main class="snp-main-body">
        @include('filament.student.pages.components.notifications-view', [
            'tier' => $tier,
            'notifications' => $notifications,
            'notifStats' => $notifStats ?? [],
            'notificationFilter' => $notificationFilter ?? 'all',
            'notificationSearch' => $notificationSearch ?? '',
        ])
    </main>
</div>
