<x-filament-panels::page>
    @php
        $formatNumber = fn ($value) => rtrim(rtrim(number_format((float) $value, 1), '0'), '.');
        $quizPassRate = $quizStats['count'] > 0
            ? round(($quizStats['passed'] / $quizStats['count']) * 100)
            : 0;
    @endphp

    <style>
        .pp-wrap {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            color: #0f172a;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .pp-child-bar {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            padding: 1rem 1.15rem;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        }

        .pp-child-bar-label {
            color: #64748b;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.09em;
            text-transform: uppercase;
        }

        .pp-child-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
        }

        .pp-child-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            padding: 0.55rem 0.9rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 0.9rem;
            background: #f8fafc;
            color: #334155;
            cursor: pointer;
            text-align: left;
            transition: all 0.16s ease;
        }

        .pp-child-pill:hover {
            border-color: #6ee7b7;
            background: #f0fdf4;
        }

        .pp-child-pill.is-active {
            border-color: #10b981;
            background: linear-gradient(135deg, #ecfdf5 0%, #f0fdfa 100%);
            box-shadow: 0 8px 20px -14px rgba(4, 120, 87, 0.55);
        }

        .pp-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.15rem;
            height: 2.15rem;
            flex-shrink: 0;
            border-radius: 0.7rem;
            background: #d1fae5;
            color: #047857;
            font-size: 0.78rem;
            font-weight: 800;
        }

        .pp-child-pill.is-active .pp-avatar {
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
            color: #ffffff;
        }

        .pp-child-pill strong {
            display: block;
            color: #0f172a;
            font-size: 0.88rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .pp-child-pill small {
            display: block;
            margin-top: 0.15rem;
            color: #94a3b8;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .pp-summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.25rem 1.4rem;
            border-radius: 1.15rem;
            background: linear-gradient(120deg, #064e3b 0%, #047857 48%, #0d9488 100%);
            color: #ffffff;
            box-shadow: 0 18px 38px -24px rgba(6, 78, 59, 0.65);
            flex-wrap: wrap;
        }

        .pp-summary-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .pp-summary-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3.5rem;
            height: 3.5rem;
            flex-shrink: 0;
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.28);
            color: #ffffff;
            font-size: 1.05rem;
            font-weight: 800;
            backdrop-filter: blur(6px);
        }

        .pp-summary h2 {
            margin: 0;
            color: #ffffff;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        .pp-summary-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            margin-top: 0.45rem;
        }

        .pp-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.22rem 0.55rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.22);
            color: #ecfdf5;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .pp-summary-right {
            text-align: right;
        }

        .pp-summary-right strong {
            display: block;
            color: #ffffff;
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 1;
        }

        .pp-summary-right span {
            display: block;
            margin-top: 0.25rem;
            color: #a7f3d0;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .pp-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(155px, 1fr));
            gap: 0.9rem;
        }

        .pp-stat {
            padding: 1rem 1.1rem;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        }

        .pp-stat-label {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            color: #64748b;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .pp-stat-value {
            margin-top: 0.45rem;
            color: #0f172a;
            font-size: 1.55rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1;
        }

        .pp-stat-sub {
            margin-top: 0.35rem;
            color: #94a3b8;
            font-size: 0.76rem;
            font-weight: 600;
        }

        .pp-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        @media (max-width: 1100px) {
            .pp-grid {
                grid-template-columns: 1fr;
            }
        }

        .pp-card {
            display: flex;
            flex-direction: column;
            min-width: 0;
            border: 1px solid #e2e8f0;
            border-radius: 1.1rem;
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
            overflow: hidden;
        }

        .pp-card-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 1rem 1.15rem;
            border-bottom: 1px solid #f1f5f9;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .pp-card-head h3 {
            margin: 0;
            color: #0f172a;
            font-size: 0.98rem;
            font-weight: 800;
            letter-spacing: -0.01em;
        }

        .pp-card-head p {
            margin: 0.2rem 0 0;
            color: #94a3b8;
            font-size: 0.74rem;
            font-weight: 600;
        }

        .pp-card-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            flex-shrink: 0;
            border-radius: 0.6rem;
            background: #ecfdf5;
            font-size: 1rem;
        }

        .pp-card-head-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-shrink: 0;
        }

        .pp-list {
            max-height: 380px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        .pp-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.9rem;
            padding: 0.85rem 1.15rem;
            border-bottom: 1px solid #f8fafc;
        }

        .pp-row:last-child {
            border-bottom: none;
        }

        .pp-row-main {
            min-width: 0;
        }

        .pp-row-title {
            color: #1e293b;
            font-size: 0.86rem;
            font-weight: 700;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }

        .pp-row-sub {
            margin-top: 0.2rem;
            color: #94a3b8;
            font-size: 0.72rem;
            font-weight: 500;
            line-height: 1.45;
            overflow-wrap: anywhere;
        }

        .pp-row-end {
            flex-shrink: 0;
            text-align: right;
        }

        .pp-score {
            color: #047857;
            font-size: 1rem;
            font-weight: 800;
            line-height: 1.1;
        }

        .pp-score-sub {
            margin-top: 0.15rem;
            color: #94a3b8;
            font-size: 0.68rem;
            font-weight: 600;
        }

        .pp-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
            margin-top: 0.35rem;
            padding: 0.15rem 0.45rem;
            border-radius: 999px;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .pp-badge.is-good {
            background: #ecfdf5;
            color: #047857;
        }

        .pp-badge.is-bad {
            background: #fef2f2;
            color: #b91c1c;
        }

        .pp-badge.is-info {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .pp-badge.is-warn {
            background: #fffbeb;
            color: #b45309;
        }

        .pp-badge.is-muted {
            background: #f1f5f9;
            color: #64748b;
        }

        .pp-empty-row {
            padding: 1.5rem 1.15rem;
            color: #94a3b8;
            font-size: 0.82rem;
            font-weight: 600;
            text-align: center;
        }

        .pp-notification-row {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            padding: 0.85rem 1.15rem;
            border-bottom: 1px solid #f8fafc;
        }

        .pp-notification-row:last-child {
            border-bottom: none;
        }

        .pp-notification-dot {
            width: 0.46rem;
            height: 0.46rem;
            margin-top: 0.4rem;
            flex-shrink: 0;
            border-radius: 999px;
            background: #10b981;
        }

        .pp-notification-dot.is-read {
            background: #e2e8f0;
        }

        .pp-notification-title {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            flex-wrap: wrap;
            color: #1e293b;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .pp-notification-body {
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
            overflow: hidden;
            margin-top: 0.22rem;
            color: #64748b;
            font-size: 0.76rem;
            font-weight: 500;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .pp-notification-meta {
            margin-top: 0.3rem;
            color: #94a3b8;
            font-size: 0.68rem;
            font-weight: 600;
        }

        .pp-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: clamp(3rem, 10vw, 6rem) 1.5rem;
            border: 1px dashed #cbd5e1;
            border-radius: 1.25rem;
            background: #ffffff;
            text-align: center;
        }

        .pp-empty-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 4.5rem;
            height: 4.5rem;
            border-radius: 1.25rem;
            background: #ecfdf5;
            font-size: 2.1rem;
        }

        .pp-empty h2 {
            margin: 1.1rem 0 0;
            color: #0f172a;
            font-size: 1.3rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .pp-empty p {
            max-width: 34rem;
            margin: 0.5rem 0 0;
            color: #64748b;
            font-size: 0.9rem;
            line-height: 1.6;
            font-weight: 500;
        }

        /* ---- Tabs ---- */
        .pp-tab-bar {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.35rem;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
            overflow-x: auto;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        .pp-tab {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            flex-shrink: 0;
            padding: 0.6rem 0.85rem;
            border: none;
            border-radius: 0.72rem;
            background: transparent;
            color: #64748b;
            font-family: inherit;
            font-size: 0.8rem;
            font-weight: 700;
            line-height: 1;
            white-space: nowrap;
            cursor: pointer;
            transition: background 0.16s ease, color 0.16s ease, box-shadow 0.16s ease;
        }

        .pp-tab:hover {
            background: #f8fafc;
            color: #0f172a;
        }

        .pp-tab.is-active {
            background: linear-gradient(135deg, #047857 0%, #0d9488 100%);
            color: #ffffff;
            box-shadow: 0 8px 18px -12px rgba(4, 120, 87, 0.8);
        }

        .pp-tab-icon {
            font-size: 0.92rem;
            line-height: 1;
        }

        .pp-tab-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.15rem;
            height: 1.15rem;
            padding: 0 0.28rem;
            border-radius: 999px;
            background: #f1f5f9;
            color: #475569;
            font-size: 0.62rem;
            font-weight: 800;
            line-height: 1;
        }

        .pp-tab-count.is-alert {
            background: #fef2f2;
            color: #b91c1c;
        }

        .pp-tab.is-active .pp-tab-count {
            background: rgba(255, 255, 255, 0.18);
            color: #ffffff;
        }

        .pp-tab.is-active .pp-tab-count.is-alert {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }

        .pp-tab-panel {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            animation: pp-panel-in 0.18s ease;
        }

        @keyframes pp-panel-in {
            from {
                opacity: 0;
                transform: translateY(3px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .pp-tab-panel {
                animation: none;
            }
        }

        /* ---- Tab section headings ---- */
        .pp-section-head {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .pp-section-kicker {
            display: block;
            color: #059669;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .pp-section-head h2 {
            margin: 0.25rem 0 0;
            color: #0f172a;
            font-size: 1.3rem;
            font-weight: 800;
            letter-spacing: -0.025em;
        }

        .pp-section-head p {
            margin: 0.25rem 0 0;
            color: #64748b;
            font-size: 0.82rem;
            font-weight: 500;
            line-height: 1.5;
        }

        .pp-section-metric {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.42rem 0.75rem;
            border: 1px solid #d1fae5;
            border-radius: 999px;
            background: #ecfdf5;
            color: #047857;
            font-size: 0.72rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .pp-link-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.35rem 0.65rem;
            border: 1px solid #d1fae5;
            border-radius: 999px;
            background: #ecfdf5;
            color: #047857;
            font-family: inherit;
            font-size: 0.67rem;
            font-weight: 800;
            cursor: pointer;
            white-space: nowrap;
            transition: background 0.16s ease, border-color 0.16s ease, transform 0.16s ease;
        }

        .pp-link-btn:hover {
            background: #d1fae5;
            border-color: #6ee7b7;
            transform: translateY(-1px);
        }

        .pp-list--full {
            max-height: none;
        }

        /* ---- Profile ---- */
        .pp-profile-card {
            padding: 1.25rem 1.35rem;
            border: 1px solid #e2e8f0;
            border-radius: 1.1rem;
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        }

        .pp-profile-head {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .pp-profile-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3.6rem;
            height: 3.6rem;
            flex-shrink: 0;
            border-radius: 1rem;
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
            color: #ffffff;
            font-size: 1.05rem;
            font-weight: 800;
            box-shadow: 0 10px 22px -14px rgba(4, 120, 87, 0.8);
        }

        .pp-profile-head h2 {
            margin: 0;
            color: #0f172a;
            font-size: 1.2rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .pp-profile-head p {
            margin: 0.2rem 0 0;
            color: #64748b;
            font-size: 0.82rem;
            font-weight: 500;
        }

        .pp-role-badge {
            margin-left: auto;
            padding: 0.32rem 0.65rem;
            border: 1px solid #a7f3d0;
            border-radius: 999px;
            background: #ecfdf5;
            color: #047857;
            font-size: 0.64rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .pp-meta-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.7rem;
            margin-top: 1.15rem;
        }

        @media (max-width: 900px) {
            .pp-meta-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 520px) {
            .pp-meta-grid {
                grid-template-columns: 1fr;
            }
        }

        .pp-meta-item {
            min-width: 0;
            padding: 0.75rem 0.85rem;
            border: 1px solid #f1f5f9;
            border-radius: 0.8rem;
            background: #f8fafc;
        }

        .pp-meta-label {
            color: #94a3b8;
            font-size: 0.66rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .pp-meta-value {
            margin-top: 0.25rem;
            color: #1e293b;
            font-size: 0.82rem;
            font-weight: 700;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }

        .pp-profile-note {
            margin-top: 1rem;
            padding: 0.8rem 0.95rem;
            border: 1px solid #fde68a;
            border-radius: 0.8rem;
            background: #fffbeb;
            color: #92400e;
            font-size: 0.76rem;
            font-weight: 600;
            line-height: 1.55;
        }

        .pp-children-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(235px, 1fr));
            gap: 0.85rem;
            padding: 1rem 1.15rem 1.15rem;
        }

        .pp-child-card {
            display: flex;
            flex-direction: column;
            gap: 0.7rem;
            min-width: 0;
            padding: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.95rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .pp-child-card.is-active {
            border-color: #6ee7b7;
            box-shadow: 0 12px 24px -22px rgba(4, 120, 87, 0.8);
        }

        .pp-child-card-head {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            min-width: 0;
        }

        .pp-child-card-name {
            color: #0f172a;
            font-size: 0.88rem;
            font-weight: 800;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }

        .pp-child-card-sub {
            margin-top: 0.12rem;
            color: #94a3b8;
            font-size: 0.68rem;
            font-weight: 600;
        }

        .pp-school-chips,
        .pp-class-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.32rem;
        }

        .pp-school-chip {
            padding: 0.2rem 0.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            background: #ffffff;
            color: #475569;
            font-size: 0.66rem;
            font-weight: 600;
        }

        .pp-class-chip {
            padding: 0.2rem 0.48rem;
            border: 1px solid #d1fae5;
            border-radius: 999px;
            background: #ecfdf5;
            color: #047857;
            font-size: 0.65rem;
            font-weight: 700;
        }

        .pp-child-card-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.3rem;
            width: 100%;
            margin-top: auto;
            padding: 0.55rem 0.7rem;
            border: 1px solid #a7f3d0;
            border-radius: 0.7rem;
            background: #ecfdf5;
            color: #047857;
            font-family: inherit;
            font-size: 0.73rem;
            font-weight: 800;
            cursor: pointer;
            transition: background 0.16s ease, border-color 0.16s ease, transform 0.16s ease;
        }

        .pp-child-card-btn:hover {
            border-color: #6ee7b7;
            background: #d1fae5;
            transform: translateY(-1px);
        }

        .pp-child-card-btn.is-current {
            border-color: #10b981;
            background: linear-gradient(135deg, #047857 0%, #0d9488 100%);
            color: #ffffff;
        }

        @media (max-width: 640px) {
            .pp-summary {
                align-items: flex-start;
            }

            .pp-summary-right {
                text-align: left;
            }

            .pp-profile-head {
                align-items: flex-start;
            }

            .pp-role-badge {
                margin-left: 0;
            }
        }
    </style>

    <div class="pp-wrap">
        @if ($children->isEmpty())
            <div class="pp-empty">
                <span class="pp-empty-icon">👨‍👩‍👧</span>
                <h2>No students linked yet</h2>
                <p>
                    Your parent account is active, but no student has been assigned to it yet.
                    Please contact the school's super administrator to link your child.
                </p>
            </div>
        @else
            <div class="pp-child-bar">
                <span class="pp-child-bar-label">Viewing progress for</span>

                <div class="pp-child-pills">
                    @foreach ($children as $child)
                        <button
                            type="button"
                            wire:click="selectChild({{ $child->id }})"
                            class="pp-child-pill {{ (int) $child->id === (int) $activeChildId ? 'is-active' : '' }}"
                        >
                            <span class="pp-avatar">{{ $child->user?->initials() ?? '?' }}</span>
                            <span>
                                <strong>{{ $child->user?->name ?? 'Student' }}</strong>
                                <small>{{ $child->admission_no }}</small>
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>

            @if ($student)
                <div class="pp-summary">
                    <div class="pp-summary-left">
                        <span class="pp-summary-avatar">{{ $student->user?->initials() ?? '?' }}</span>

                        <div>
                            <h2>{{ $student->user?->name ?? 'Student' }}</h2>

                            <div class="pp-summary-meta">
                                @forelse ($enrollments as $enrollment)
                                    <span class="pp-chip">
                                        {{ $enrollment->school?->name ?? 'School' }}
                                        @if ($enrollment->grade)
                                            · Grade {{ $enrollment->grade->name }}
                                        @endif
                                    </span>
                                @empty
                                    <span class="pp-chip">No school enrollment yet</span>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="pp-summary-right">
                        <strong>{{ $totalChildren }}</strong>
                        <span>{{ $totalChildren === 1 ? 'Child linked' : 'Children linked' }}</span>
                    </div>
                </div>

                <nav class="pp-tab-bar" aria-label="Parent dashboard sections">
                    <button type="button" wire:click="setTab('overview')" class="pp-tab {{ $activeTab === 'overview' ? 'is-active' : '' }}">
                        <span class="pp-tab-icon">📊</span>
                        <span>Overview</span>
                    </button>

                    <button type="button" wire:click="setTab('quizzes')" class="pp-tab {{ $activeTab === 'quizzes' ? 'is-active' : '' }}">
                        <span class="pp-tab-icon">📝</span>
                        <span>Quizzes</span>
                        <span class="pp-tab-count">{{ $quizStats['count'] }}</span>
                    </button>

                    <button type="button" wire:click="setTab('assignments')" class="pp-tab {{ $activeTab === 'assignments' ? 'is-active' : '' }}">
                        <span class="pp-tab-icon">📚</span>
                        <span>Assignments</span>
                        @if ($assignmentStats['pending'] > 0)
                            <span class="pp-tab-count is-alert">{{ $assignmentStats['pending'] }}</span>
                        @endif
                    </button>

                    <button type="button" wire:click="setTab('lessons')" class="pp-tab {{ $activeTab === 'lessons' ? 'is-active' : '' }}">
                        <span class="pp-tab-icon">📖</span>
                        <span>Lessons</span>
                        <span class="pp-tab-count">{{ $lessonCount }}</span>
                    </button>

                    <button type="button" wire:click="setTab('notifications')" class="pp-tab {{ $activeTab === 'notifications' ? 'is-active' : '' }}">
                        <span class="pp-tab-icon">🔔</span>
                        <span>Notifications</span>
                        @if ($notificationStats['unread'] > 0)
                            <span class="pp-tab-count is-alert">{{ $notificationStats['unread'] }}</span>
                        @endif
                    </button>

                    <button type="button" wire:click="setTab('profile')" class="pp-tab {{ $activeTab === 'profile' ? 'is-active' : '' }}">
                        <span class="pp-tab-icon">👤</span>
                        <span>Profile</span>
                    </button>
                </nav>

                @if ($activeTab === 'overview')
                    <div class="pp-tab-panel">
                        <div class="pp-stats">
                    <div class="pp-stat">
                        <div class="pp-stat-label">📝 Quiz average</div>
                        <div class="pp-stat-value">{{ $formatNumber($quizStats['average']) }}%</div>
                        <div class="pp-stat-sub">{{ $quizStats['count'] }} completed {{ Str::plural('attempt', $quizStats['count']) }}</div>
                    </div>

                    <div class="pp-stat">
                        <div class="pp-stat-label">🎯 Quiz pass rate</div>
                        <div class="pp-stat-value">{{ $quizPassRate }}%</div>
                        <div class="pp-stat-sub">{{ $quizStats['passed'] }} passed of {{ $quizStats['count'] }}</div>
                    </div>

                    <div class="pp-stat">
                        <div class="pp-stat-label">📚 Assignment average</div>
                        <div class="pp-stat-value">{{ $formatNumber($assignmentStats['average']) }}%</div>
                        <div class="pp-stat-sub">{{ $assignmentStats['graded'] }} graded of {{ $assignmentStats['total'] }}</div>
                    </div>

                    <div class="pp-stat">
                        <div class="pp-stat-label">📖 Lessons available</div>
                        <div class="pp-stat-value">{{ $lessonCount }}</div>
                        <div class="pp-stat-sub">Across all enrolled classes</div>
                    </div>

                    <div class="pp-stat">
                        <div class="pp-stat-label">🔔 Unread notifications</div>
                        <div class="pp-stat-value">{{ $notificationStats['unread'] }}</div>
                        <div class="pp-stat-sub">{{ $notificationStats['total'] }} received in total</div>
                    </div>
                </div>

                <div class="pp-grid">
                    <section class="pp-card">
                        <div class="pp-card-head">
                            <div>
                                <h3>Quizzes &amp; scores</h3>
                                <p>{{ $quizStats['count'] }} completed {{ Str::plural('attempt', $quizStats['count']) }}</p>
                            </div>
                            <div class="pp-card-head-actions">
                                @if ($quizStats['count'] > 0)
                                    <button type="button" wire:click="setTab('quizzes')" class="pp-link-btn">View all</button>
                                @endif
                                <span class="pp-card-icon">📝</span>
                            </div>
                        </div>

                        <div class="pp-list">
                            @forelse ($quizAttempts as $attempt)
                                <div class="pp-row">
                                    <div class="pp-row-main">
                                        <div class="pp-row-title">{{ $attempt->quiz?->title ?? 'Quiz' }}</div>
                                        <div class="pp-row-sub">
                                            {{ $attempt->quiz?->learningClass?->name ?? 'Class' }}
                                            @if ($attempt->quiz?->learningClass?->grade?->school?->name)
                                                · {{ $attempt->quiz->learningClass->grade->school->name }}
                                            @endif
                                            @if ($attempt->completed_at)
                                                · {{ $attempt->completed_at->format('M j, Y') }}
                                            @endif
                                        </div>
                                    </div>

                                    <div class="pp-row-end">
                                        <div class="pp-score">{{ $formatNumber($attempt->percentage) }}%</div>
                                        <div class="pp-score-sub">
                                            {{ $formatNumber($attempt->score) }}
                                            @if ($attempt->quiz?->total_points)
                                                / {{ $attempt->quiz->total_points }}
                                            @endif
                                            points
                                        </div>
                                        <span class="pp-badge {{ $attempt->is_passed ? 'is-good' : 'is-bad' }}">
                                            {{ $attempt->is_passed ? 'Passed' : 'Not passed' }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="pp-empty-row">No quiz attempts yet.</div>
                            @endforelse
                        </div>
                    </section>

                    <section class="pp-card">
                        <div class="pp-card-head">
                            <div>
                                <h3>Assignments</h3>
                                <p>
                                    {{ $assignmentStats['submitted'] }} submitted ·
                                    {{ $assignmentStats['graded'] }} graded ·
                                    {{ $assignmentStats['pending'] }} pending
                                </p>
                            </div>
                            <div class="pp-card-head-actions">
                                @if ($assignmentStats['total'] > 0)
                                    <button type="button" wire:click="setTab('assignments')" class="pp-link-btn">View all</button>
                                @endif
                                <span class="pp-card-icon">📚</span>
                            </div>
                        </div>

                        <div class="pp-list">
                            @forelse ($assignments->take(4) as $row)
                                @php
                                    $assignment = $row['assignment'];
                                    $submission = $row['submission'];
                                @endphp

                                <div class="pp-row">
                                    <div class="pp-row-main">
                                        <div class="pp-row-title">{{ $assignment->title }}</div>
                                        <div class="pp-row-sub">
                                            {{ $assignment->learningClass?->name ?? 'Class' }}
                                            @if ($assignment->learningClass?->grade?->school?->name)
                                                · {{ $assignment->learningClass->grade->school->name }}
                                            @endif
                                            · Due {{ $assignment->end_at?->format('M j, Y') ?? 'anytime' }}
                                        </div>
                                    </div>

                                    <div class="pp-row-end">
                                        @if ($row['state'] === 'graded')
                                            <div class="pp-score">{{ $formatNumber($row['percentage']) }}%</div>
                                            <div class="pp-score-sub">
                                                {{ $formatNumber($submission->score) }} / {{ $assignment->max_score }}
                                            </div>
                                            <span class="pp-badge is-good">Graded</span>
                                        @elseif ($row['state'] === 'submitted')
                                            <span class="pp-badge is-info">Submitted</span>
                                            <div class="pp-score-sub">Awaiting grade</div>
                                        @elseif ($row['state'] === 'missing')
                                            <span class="pp-badge is-bad">Not submitted</span>
                                            <div class="pp-score-sub">Past due</div>
                                        @else
                                            <span class="pp-badge is-warn">Pending</span>
                                            <div class="pp-score-sub">Due soon</div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="pp-empty-row">No assignments have been published yet.</div>
                            @endforelse
                        </div>
                    </section>

                    <section class="pp-card">
                        <div class="pp-card-head">
                            <div>
                                <h3>Lessons</h3>
                                <p>{{ $lessonCount }} published {{ Str::plural('lesson', $lessonCount) }}</p>
                            </div>
                            <div class="pp-card-head-actions">
                                @if ($lessonCount > 0)
                                    <button type="button" wire:click="setTab('lessons')" class="pp-link-btn">View all</button>
                                @endif
                                <span class="pp-card-icon">📖</span>
                            </div>
                        </div>

                        <div class="pp-list">
                            @forelse ($lessons as $lesson)
                                <div class="pp-row">
                                    <div class="pp-row-main">
                                        <div class="pp-row-title">{{ $lesson->title }}</div>
                                        <div class="pp-row-sub">
                                            {{ $lesson->learningClass?->name ?? 'Class' }}
                                            @if ($lesson->teacher?->user?->name)
                                                · {{ $lesson->teacher->user->name }}
                                            @endif
                                            @if ($lesson->learningClass?->grade?->school?->name)
                                                · {{ $lesson->learningClass->grade->school->name }}
                                            @endif
                                        </div>
                                    </div>

                                    <div class="pp-row-end">
                                        <span class="pp-badge is-muted">{{ $lesson->created_at?->format('M j, Y') ?? 'Published' }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="pp-empty-row">No lessons have been published yet.</div>
                            @endforelse
                        </div>
                    </section>

                    <section class="pp-card">
                        <div class="pp-card-head">
                            <div>
                                <h3>Notifications</h3>
                                <p>
                                    {{ $notificationStats['total'] }} received ·
                                    {{ $notificationStats['unread'] }} unread
                                </p>
                            </div>
                            <div class="pp-card-head-actions">
                                @if ($notificationStats['total'] > 0)
                                    <button type="button" wire:click="setTab('notifications')" class="pp-link-btn">View all</button>
                                @endif
                                <span class="pp-card-icon">🔔</span>
                            </div>
                        </div>

                        <div class="pp-list">
                            @forelse ($notifications as $notification)
                                <div class="pp-notification-row">
                                    <span class="pp-notification-dot {{ $notification->pivot->is_read ? 'is-read' : '' }}"></span>

                                    <div style="min-width: 0;">
                                        <div class="pp-notification-title">
                                            {{ $notification->title }}

                                            @if (! $notification->pivot->is_read)
                                                <span class="pp-badge is-info">New</span>
                                            @endif
                                        </div>

                                        @if ($notification->body)
                                            <div class="pp-notification-body">{{ $notification->body }}</div>
                                        @endif

                                        <div class="pp-notification-meta">
                                            {{ $notification->sender?->name ?? 'School' }}
                                            · {{ $notification->created_at?->format('M j, Y g:i A') ?? '' }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="pp-empty-row">No notifications have been received yet.</div>
                            @endforelse
                        </div>
                    </section>
                        </div>
                    </div>
                @endif

                @if ($activeTab === 'quizzes')
                    <div class="pp-tab-panel">
                        <div class="pp-section-head">
                            <div>
                                <span class="pp-section-kicker">Quizzes</span>
                                <h2>Quiz results</h2>
                                <p>Completed attempts for {{ $student->user?->name ?? 'your child' }} across all enrolled classes.</p>
                            </div>
                            <span class="pp-section-metric">Average {{ $formatNumber($quizStats['average']) }}%</span>
                        </div>

                        <div class="pp-stats">
                            <div class="pp-stat">
                                <div class="pp-stat-label">📝 Completed attempts</div>
                                <div class="pp-stat-value">{{ $quizStats['count'] }}</div>
                                <div class="pp-stat-sub">Submitted or time expired</div>
                            </div>

                            <div class="pp-stat">
                                <div class="pp-stat-label">🎯 Average score</div>
                                <div class="pp-stat-value">{{ $formatNumber($quizStats['average']) }}%</div>
                                <div class="pp-stat-sub">Across all completed quizzes</div>
                            </div>

                            <div class="pp-stat">
                                <div class="pp-stat-label">✅ Pass rate</div>
                                <div class="pp-stat-value">{{ $quizPassRate }}%</div>
                                <div class="pp-stat-sub">{{ $quizStats['passed'] }} passed of {{ $quizStats['count'] }}</div>
                            </div>
                        </div>

                        <section class="pp-card">
                            <div class="pp-card-head">
                                <div>
                                    <h3>All quiz attempts</h3>
                                    <p>Newest results first · {{ $quizStats['count'] }} total</p>
                                </div>
                                <span class="pp-card-icon">📝</span>
                            </div>

                            <div class="pp-list pp-list--full">
                                @forelse ($quizAttempts as $attempt)
                                    <div class="pp-row">
                                        <div class="pp-row-main">
                                            <div class="pp-row-title">{{ $attempt->quiz?->title ?? 'Quiz' }}</div>
                                            <div class="pp-row-sub">
                                                {{ $attempt->quiz?->learningClass?->name ?? 'Class' }}
                                                @if ($attempt->quiz?->learningClass?->grade?->school?->name)
                                                    · {{ $attempt->quiz->learningClass->grade->school->name }}
                                                @endif
                                                @if ($attempt->completed_at)
                                                    · {{ $attempt->completed_at->format('M j, Y') }}
                                                @endif
                                            </div>
                                        </div>

                                        <div class="pp-row-end">
                                            <div class="pp-score">{{ $formatNumber($attempt->percentage) }}%</div>
                                            <div class="pp-score-sub">
                                                {{ $formatNumber($attempt->score) }}
                                                @if ($attempt->quiz?->total_points)
                                                    / {{ $attempt->quiz->total_points }}
                                                @endif
                                                points
                                            </div>
                                            <span class="pp-badge {{ $attempt->is_passed ? 'is-good' : 'is-bad' }}">
                                                {{ $attempt->is_passed ? 'Passed' : 'Not passed' }}
                                            </span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="pp-empty-row">No quiz attempts have been recorded yet.</div>
                                @endforelse
                            </div>
                        </section>
                    </div>
                @endif

                @if ($activeTab === 'assignments')
                    <div class="pp-tab-panel">
                        <div class="pp-section-head">
                            <div>
                                <span class="pp-section-kicker">Assignments</span>
                                <h2>Assignment progress</h2>
                                <p>Published assignments and submission status for {{ $student->user?->name ?? 'your child' }}.</p>
                            </div>
                            <span class="pp-section-metric">{{ $assignmentStats['pending'] }} pending</span>
                        </div>

                        <div class="pp-stats">
                            <div class="pp-stat">
                                <div class="pp-stat-label">📚 Total assignments</div>
                                <div class="pp-stat-value">{{ $assignmentStats['total'] }}</div>
                                <div class="pp-stat-sub">Published across enrolled classes</div>
                            </div>

                            <div class="pp-stat">
                                <div class="pp-stat-label">📤 Submitted</div>
                                <div class="pp-stat-value">{{ $assignmentStats['submitted'] }}</div>
                                <div class="pp-stat-sub">Turned in by your child</div>
                            </div>

                            <div class="pp-stat">
                                <div class="pp-stat-label">✅ Graded</div>
                                <div class="pp-stat-value">{{ $assignmentStats['graded'] }}</div>
                                <div class="pp-stat-sub">Scores returned by teachers</div>
                            </div>

                            <div class="pp-stat">
                                <div class="pp-stat-label">⏳ Pending</div>
                                <div class="pp-stat-value">{{ $assignmentStats['pending'] }}</div>
                                <div class="pp-stat-sub">Including past-due work</div>
                            </div>

                            <div class="pp-stat">
                                <div class="pp-stat-label">🎯 Assignment average</div>
                                <div class="pp-stat-value">{{ $formatNumber($assignmentStats['average']) }}%</div>
                                <div class="pp-stat-sub">Based on graded submissions</div>
                            </div>
                        </div>

                        <section class="pp-card">
                            <div class="pp-card-head">
                                <div>
                                    <h3>All assignments</h3>
                                    <p>Sorted by due date · {{ $assignmentStats['total'] }} total</p>
                                </div>
                                <span class="pp-card-icon">📚</span>
                            </div>

                            <div class="pp-list pp-list--full">
                                @forelse ($assignments as $row)
                                    @php
                                        $assignment = $row['assignment'];
                                        $submission = $row['submission'];
                                    @endphp

                                    <div class="pp-row">
                                        <div class="pp-row-main">
                                            <div class="pp-row-title">{{ $assignment->title }}</div>
                                            <div class="pp-row-sub">
                                                {{ $assignment->learningClass?->name ?? 'Class' }}
                                                @if ($assignment->learningClass?->grade?->school?->name)
                                                    · {{ $assignment->learningClass->grade->school->name }}
                                                @endif
                                                · Due {{ $assignment->end_at?->format('M j, Y') ?? 'anytime' }}
                                            </div>
                                        </div>

                                        <div class="pp-row-end">
                                            @if ($row['state'] === 'graded')
                                                <div class="pp-score">{{ $formatNumber($row['percentage']) }}%</div>
                                                <div class="pp-score-sub">
                                                    {{ $formatNumber($submission->score) }} / {{ $assignment->max_score }}
                                                </div>
                                                <span class="pp-badge is-good">Graded</span>
                                            @elseif ($row['state'] === 'submitted')
                                                <span class="pp-badge is-info">Submitted</span>
                                                <div class="pp-score-sub">Awaiting grade</div>
                                            @elseif ($row['state'] === 'missing')
                                                <span class="pp-badge is-bad">Not submitted</span>
                                                <div class="pp-score-sub">Past due</div>
                                            @else
                                                <span class="pp-badge is-warn">Pending</span>
                                                <div class="pp-score-sub">Due soon</div>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="pp-empty-row">No assignments have been published yet.</div>
                                @endforelse
                            </div>
                        </section>
                    </div>
                @endif

                @if ($activeTab === 'lessons')
                    <div class="pp-tab-panel">
                        <div class="pp-section-head">
                            <div>
                                <span class="pp-section-kicker">Lessons</span>
                                <h2>Published lessons</h2>
                                <p>Lessons currently available to {{ $student->user?->name ?? 'your child' }} across enrolled classes.</p>
                            </div>
                            <span class="pp-section-metric">{{ $lessonCount }} {{ Str::plural('lesson', $lessonCount) }}</span>
                        </div>

                        <section class="pp-card">
                            <div class="pp-card-head">
                                <div>
                                    <h3>Lesson library</h3>
                                    <p>Newest content first · {{ $lessonCount }} published</p>
                                </div>
                                <span class="pp-card-icon">📖</span>
                            </div>

                            <div class="pp-list pp-list--full">
                                @forelse ($lessons as $lesson)
                                    <div class="pp-row">
                                        <div class="pp-row-main">
                                            <div class="pp-row-title">{{ $lesson->title }}</div>
                                            <div class="pp-row-sub">
                                                {{ $lesson->learningClass?->name ?? 'Class' }}
                                                @if ($lesson->teacher?->user?->name)
                                                    · {{ $lesson->teacher->user->name }}
                                                @endif
                                                @if ($lesson->learningClass?->grade?->school?->name)
                                                    · {{ $lesson->learningClass->grade->school->name }}
                                                @endif
                                            </div>
                                        </div>

                                        <div class="pp-row-end">
                                            <span class="pp-badge is-muted">{{ $lesson->created_at?->format('M j, Y') ?? 'Published' }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="pp-empty-row">No lessons have been published yet.</div>
                                @endforelse
                            </div>
                        </section>
                    </div>
                @endif

                @if ($activeTab === 'notifications')
                    <div class="pp-tab-panel">
                        <div class="pp-section-head">
                            <div>
                                <span class="pp-section-kicker">Notifications</span>
                                <h2>School notifications</h2>
                                <p>Messages sent to {{ $student->user?->name ?? 'your child' }} by teachers and school administrators.</p>
                            </div>
                            <span class="pp-section-metric">{{ $notificationStats['unread'] }} unread</span>
                        </div>

                        <div class="pp-stats">
                            <div class="pp-stat">
                                <div class="pp-stat-label">🔔 Total received</div>
                                <div class="pp-stat-value">{{ $notificationStats['total'] }}</div>
                                <div class="pp-stat-sub">All-time notifications</div>
                            </div>

                            <div class="pp-stat">
                                <div class="pp-stat-label">🟢 Unread</div>
                                <div class="pp-stat-value">{{ $notificationStats['unread'] }}</div>
                                <div class="pp-stat-sub">Waiting to be reviewed</div>
                            </div>
                        </div>

                        <section class="pp-card">
                            <div class="pp-card-head">
                                <div>
                                    <h3>All notifications</h3>
                                    <p>Newest messages first · {{ $notificationStats['total'] }} total</p>
                                </div>
                                <span class="pp-card-icon">🔔</span>
                            </div>

                            <div class="pp-list pp-list--full">
                                @forelse ($notifications as $notification)
                                    <div class="pp-notification-row">
                                        <span class="pp-notification-dot {{ $notification->pivot->is_read ? 'is-read' : '' }}"></span>

                                        <div style="min-width: 0;">
                                            <div class="pp-notification-title">
                                                {{ $notification->title }}

                                                @if (! $notification->pivot->is_read)
                                                    <span class="pp-badge is-info">New</span>
                                                @endif
                                            </div>

                                            @if ($notification->body)
                                                <div class="pp-notification-body">{{ $notification->body }}</div>
                                            @endif

                                            <div class="pp-notification-meta">
                                                {{ $notification->sender?->name ?? 'School' }}
                                                · {{ $notification->created_at?->format('M j, Y g:i A') ?? '' }}
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="pp-empty-row">No notifications have been received yet.</div>
                                @endforelse
                            </div>
                        </section>
                    </div>
                @endif

                @if ($activeTab === 'profile')
                    <div class="pp-tab-panel">
                        <div class="pp-section-head">
                            <div>
                                <span class="pp-section-kicker">Profile</span>
                                <h2>Your parent account</h2>
                                <p>Account details and the children linked to your parent portal.</p>
                            </div>
                        </div>

                        <section class="pp-profile-card">
                            <div class="pp-profile-head">
                                <span class="pp-profile-avatar">{{ $parentUser?->initials() ?? '?' }}</span>

                                <div>
                                    <h2>{{ $parentName }}</h2>
                                    <p>{{ $parentUser?->email }}</p>
                                </div>

                                <span class="pp-role-badge">Parent account</span>
                            </div>

                            <div class="pp-meta-grid">
                                <div class="pp-meta-item">
                                    <div class="pp-meta-label">Email address</div>
                                    <div class="pp-meta-value">{{ $parentUser?->email ?? 'Not set' }}</div>
                                </div>

                                <div class="pp-meta-item">
                                    <div class="pp-meta-label">Account type</div>
                                    <div class="pp-meta-value">Parent / Guardian</div>
                                </div>

                                <div class="pp-meta-item">
                                    <div class="pp-meta-label">Linked children</div>
                                    <div class="pp-meta-value">{{ $totalChildren }} {{ Str::plural('child', $totalChildren) }}</div>
                                </div>

                                <div class="pp-meta-item">
                                    <div class="pp-meta-label">Member since</div>
                                    <div class="pp-meta-value">{{ $parentUser?->created_at?->format('M j, Y') ?? '—' }}</div>
                                </div>
                            </div>

                            <div class="pp-profile-note">
                                Parent accounts and student links are managed by your school's super administrator.
                                Contact them if your details or linked children need to be updated.
                            </div>
                        </section>

                        <section class="pp-card">
                            <div class="pp-card-head">
                                <div>
                                    <h3>Linked children</h3>
                                    <p>{{ $totalChildren }} {{ Str::plural('student', $totalChildren) }} connected to your account</p>
                                </div>
                                <span class="pp-card-icon">👨‍👩‍👧</span>
                            </div>

                            <div class="pp-children-grid">
                                @foreach ($children as $child)
                                    @php
                                        $childIsActive = (int) $child->id === (int) $activeChildId;
                                        $childClasses = $child->classes;
                                    @endphp

                                    <article class="pp-child-card {{ $childIsActive ? 'is-active' : '' }}">
                                        <div class="pp-child-card-head">
                                            <span class="pp-avatar">{{ $child->user?->initials() ?? '?' }}</span>
                                            <div style="min-width: 0;">
                                                <div class="pp-child-card-name">{{ $child->user?->name ?? 'Student' }}</div>
                                                <div class="pp-child-card-sub">{{ $child->admission_no }}</div>
                                            </div>
                                        </div>

                                        <div class="pp-school-chips">
                                            @forelse ($child->enrollments as $enrollment)
                                                <span class="pp-school-chip">
                                                    {{ $enrollment->school?->name ?? 'School' }}
                                                    @if ($enrollment->grade)
                                                        · Grade {{ $enrollment->grade->name }}
                                                    @endif
                                                </span>
                                            @empty
                                                <span class="pp-school-chip">No school enrollment yet</span>
                                            @endforelse
                                        </div>

                                        @if ($childClasses->isNotEmpty())
                                            <div class="pp-class-chips">
                                                @foreach ($childClasses->take(4) as $class)
                                                    <span class="pp-class-chip">{{ $class->display_emoji }} {{ $class->name }}</span>
                                                @endforeach

                                                @if ($childClasses->count() > 4)
                                                    <span class="pp-class-chip">+{{ $childClasses->count() - 4 }} more</span>
                                                @endif
                                            </div>
                                        @else
                                            <div class="pp-child-card-sub">No classes assigned yet</div>
                                        @endif

                                        <button
                                            type="button"
                                            wire:click="viewChild({{ $child->id }})"
                                            class="pp-child-card-btn {{ $childIsActive ? 'is-current' : '' }}"
                                        >
                                            {{ $childIsActive ? 'Currently viewing' : 'View progress' }} <span>→</span>
                                        </button>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    </div>
                @endif
            @endif
        @endif
    </div>
</x-filament-panels::page>
