@php
    $record = $this->getRecord();
    $school = $record->grade?->school;
    $grade = $record->grade;

    $stats = [
        [
            'label' => 'Students',
            'value' => $record->students()->count(),
            'tone' => 'is-indigo',
            'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a3 3 0 10-2.5-4.5"/></svg>',
        ],
        [
            'label' => 'Teachers',
            'value' => $record->teachers()->count(),
            'tone' => 'is-violet',
            'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6-3.33V14c0 1.66-2.69 3-6 3s-6-1.34-6-3v-3.33L12 14z"/></svg>',
        ],
        [
            'label' => 'Lessons',
            'value' => $record->lessons()->count(),
            'tone' => 'is-emerald',
            'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.25A8.97 8.97 0 006 3.75c-1.05 0-2.06.18-3 .51v14.25A8.99 8.99 0 016 18c2.3 0 4.41.87 6 2.29m0-14.04a8.97 8.97 0 016-2.5c1.05 0 2.06.18 3 .51v14.25A8.99 8.99 0 0018 18a8.97 8.97 0 00-6 2.29m0-14.04v14.04"/></svg>',
        ],
        [
            'label' => 'Assignments',
            'value' => $record->assignments()->count(),
            'tone' => 'is-amber',
            'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>',
        ],
        [
            'label' => 'Quizzes',
            'value' => $record->quizzes()->count(),
            'tone' => 'is-sky',
            'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.674M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>',
        ],
    ];
@endphp

<div class="tcv-page">
    <style>
        .tcv-page {
            padding: 24px 26px 48px;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #0f172a;
        }

        /* ── Hero ─────────────────────────────── */
        .tcv-hero {
            position: relative;
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #eef2f7;
            border-radius: 18px;
            padding: 24px 26px;
            margin-bottom: 22px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .03);
        }
        .tcv-hero::before {
            content: '';
            position: absolute;
            top: 0; bottom: 0; left: 0;
            width: 5px;
            background: linear-gradient(180deg, #6366f1, #10b981);
        }
        .tcv-hero-main {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            flex-wrap: wrap;
        }
        .tcv-emoji {
            width: 62px; height: 62px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 31px; line-height: 1;
            background: #eef2ff;
            border: 1px solid #e0e7ff;
            border-radius: 16px;
        }
        .tcv-hero-text { flex: 1; min-width: 220px; }
        .tcv-crumbs {
            display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
            font-size: 11.5px; font-weight: 600; letter-spacing: .04em;
            text-transform: uppercase; color: #94a3b8;
        }
        .tcv-crumbs .tcv-sep { color: #cbd5e1; }
        .tcv-title {
            margin: 6px 0 10px;
            font-size: 24px; font-weight: 800; line-height: 1.2;
            letter-spacing: -.02em; color: #0f172a;
        }
        .tcv-tags { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .tcv-tag {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 11px; border-radius: 999px;
            font-size: 11.5px; font-weight: 600;
            color: #475569; background: #f8fafc; border: 1px solid #eef2f7;
        }
        .tcv-tag .tcv-dot { width: 6px; height: 6px; border-radius: 50%; }
        .tcv-tag-on { color: #047857; background: #ecfdf5; border-color: #d1fae5; }
        .tcv-tag-on .tcv-dot { background: #10b981; }
        .tcv-tag-off { color: #b91c1c; background: #fef2f2; border-color: #fee2e2; }
        .tcv-tag-off .tcv-dot { background: #ef4444; }

        /* ── Stats ────────────────────────────── */
        .tcv-stats {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
        }
        .tcv-stat {
            display: flex; align-items: center; gap: 11px;
            padding: 12px 14px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid #eef2f7;
        }
        .tcv-stat-icon {
            width: 34px; height: 34px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            border-radius: 10px;
        }
        .tcv-stat-icon svg { width: 17px; height: 17px; }
        .tcv-stat-icon.is-indigo { background: #eef2ff; color: #4f46e5; }
        .tcv-stat-icon.is-violet { background: #f5f3ff; color: #7c3aed; }
        .tcv-stat-icon.is-emerald { background: #ecfdf5; color: #059669; }
        .tcv-stat-icon.is-amber { background: #fffbeb; color: #d97706; }
        .tcv-stat-icon.is-sky { background: #eff6ff; color: #0284c7; }
        .tcv-stat-body { display: flex; flex-direction: column; line-height: 1.15; min-width: 0; }
        .tcv-stat-body b { font-size: 17px; font-weight: 800; color: #0f172a; }
        .tcv-stat-body span {
            font-size: 10.5px; font-weight: 600; letter-spacing: .05em;
            text-transform: uppercase; color: #94a3b8;
        }

        /* ── Workspace ────────────────────────── */
        .tcv-panel-head {
            display: flex; align-items: flex-end; justify-content: space-between;
            gap: 12px; flex-wrap: wrap; margin-bottom: 12px;
        }
        .tcv-panel-title { font-size: 14px; font-weight: 700; color: #0f172a; }
        .tcv-panel-hint { font-size: 12px; color: #94a3b8; margin-top: 2px; }

        @media (max-width: 1024px) {
            .tcv-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        @media (max-width: 640px) {
            .tcv-page { padding: 16px 14px 40px; }
            .tcv-hero { padding: 20px; }
            .tcv-title { font-size: 20px; }
            .tcv-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
    </style>

    <section class="tcv-hero">
        <div class="tcv-hero-main">
            <div class="tcv-emoji">{{ $record->display_emoji }}</div>

            <div class="tcv-hero-text">
                <div class="tcv-crumbs">
                    @if($school)
                        <span>{{ $school->name }}</span>
                    @endif
                    @if($school && $grade)
                        <span class="tcv-sep">/</span>
                    @endif
                    @if($grade)
                        <span>{{ $grade->name }}</span>
                    @endif
                </div>

                <h1 class="tcv-title">{{ $record->name }}</h1>

                <div class="tcv-tags">
                    <span class="tcv-tag {{ $record->is_active ? 'tcv-tag-on' : 'tcv-tag-off' }}">
                        <span class="tcv-dot"></span>
                        {{ $record->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    @if(filled($record->medium))
                        <span class="tcv-tag">{{ $record->medium }} medium</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="tcv-stats">
            @foreach($stats as $stat)
                <div class="tcv-stat">
                    <span class="tcv-stat-icon {{ $stat['tone'] }}">{!! $stat['icon'] !!}</span>
                    <span class="tcv-stat-body">
                        <b>{{ $stat['value'] }}</b>
                        <span>{{ $stat['label'] }}</span>
                    </span>
                </div>
            @endforeach
        </div>
    </section>

    <section class="tcv-workspace">
        <div class="tcv-panel-head">
            <div>
                <div class="tcv-panel-title">Class workspace</div>
                <div class="tcv-panel-hint">Manage students, teachers, lessons, assignments and quizzes for this class.</div>
            </div>
        </div>

        {{ $this->content }}
    </section>
</div>
