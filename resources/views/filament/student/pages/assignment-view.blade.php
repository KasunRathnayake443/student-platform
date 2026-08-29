<div class="{{ $tier === 'kids' ? 'av av-kids' : 'av av-rugged' }}" style="min-height:100vh; display:flex; flex-direction:column;">

    @php
        $avState = $this->availabilityState();
        $avCanSubmit = $this->canSubmitNow();
        $avAllowsText = $this->allowsText();
        $avAllowsFiles = $this->allowsFiles();
        $avMimes = $this->acceptedMimeTypes();
        $avTypeLabels = $this->submissionTypeLabels();
        $gradePct = $submission ? $submission->percentage() : null;
        $gradeLetter = $gradePct !== null
            ? ($gradePct >= 90 ? 'A' : ($gradePct >= 80 ? 'B' : ($gradePct >= 70 ? 'C' : ($gradePct >= 60 ? 'D' : 'F'))))
            : null;
        $startsIn = $this->timeUntil($assignment->start_at);
        $stateLabel = match ($avState) {
            'graded' => 'GRADED',
            'not_started' => 'NOT STARTED YET',
            'late' => 'LATE WINDOW OPEN',
            'closed' => 'CLOSED — DEADLINE PASSED',
            default => 'OPEN FOR SUBMISSION',
        };
        $statePillClass = match ($avState) {
            'graded' => 'av-pill-ok',
            'open' => 'av-pill-ok',
            'not_started', 'late' => 'av-pill-warn',
            default => 'av-pill-bad',
        };
        $plate = 'FORGED·' . strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $className) ?: 'UNIT');
        $plate2 = 'SERIAL: ' . ($assignment->id ? strtoupper(substr(md5('asg'.$assignment->id), 0, 8)) : '—') . ' · EST. FOREVER';
    @endphp

    <style>
        .av {
            --av-accent: #f59e0b;
            --av-accent-glow: rgba(245, 158, 11, 0.25);
            --av-steel: #3a414a;
            --av-steel-2: #262b31;
            --av-plate: #1c2025;
            --av-plate-2: #15181c;
            --av-ink: #e8ecf0;
            --av-dim: #9aa4b2;
            --av-ok: #4ade80;
            --av-warn: #fbbf24;
            --av-bad: #fb7185;
            --av-info: #38bdf8;
            font-family: 'Bahnschrift', 'DIN Alternate', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Arial, sans-serif;
            background: #0c0e11;
            color: var(--av-ink);
        }

        /* Forged plate texture */
        .av-rugged {
            background:
                radial-gradient(circle at 22% 8%, rgba(245,158,11,0.05), transparent 34%),
                radial-gradient(circle at 78% 92%, rgba(56,189,248,0.04), transparent 40%),
                linear-gradient(180deg, #14171b 0%, #0c0e11 100%);
        }

        .av .av-topbar {
            display: flex; align-items: center; justify-content: space-between;
            gap: 1rem; padding: 1rem 2.5rem; flex-wrap: wrap;
            background: linear-gradient(180deg, rgba(24,27,32,0.96), rgba(18,20,24,0.92));
            border-bottom: 1px solid #333940;
            box-shadow: 0 2px 14px -6px rgba(0,0,0,0.6), inset 0 1px 0 #3a414a;
            position: sticky; top: 0; z-index: 20;
        }
        .av .av-back {
            display: inline-flex; align-items: center; gap: 0.45rem;
            background: linear-gradient(180deg, #2a2f35, #20242a);
            border: 1px solid #3a414a; color: var(--av-accent);
            font-weight: 800; font-size: 0.88rem; padding: 0.5rem 1.1rem;
            border-radius: 10px; text-decoration: none;
            text-transform: uppercase; letter-spacing: 0.05em;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, color 0.15s ease;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.06);
        }
        .av .av-back:hover { border-color: var(--av-accent); color: #fff; box-shadow: inset 0 1px 0 rgba(255,255,255,0.06), 0 0 16px -6px var(--av-accent-glow); }
        .av .av-brand { font-weight: 900; color: var(--av-ink); font-size: 0.92rem; letter-spacing: 0.18em; text-transform: uppercase; }

        .av .av-hero {
            padding: 2.6rem 2.5rem 2.4rem; text-align: center; color: var(--av-ink);
            position: relative;
            border-bottom: 3px solid var(--av-accent);
            background:
                repeating-linear-gradient(-45deg, rgba(245,158,11,0.05) 0 12px, transparent 12px 24px),
                linear-gradient(180deg, #171a1f 0%, #101317 100%);
        }
        .av .av-hero::before {
            content: ''; position: absolute; top: 0; left: 2.5rem; right: 2.5rem; height: 3px;
            background: linear-gradient(90deg, transparent, var(--av-accent), transparent);
        }
        .av .av-breadcrumb {
            font-size: 0.78rem; font-weight: 800; color: var(--av-dim); letter-spacing: 0.12em;
            text-transform: uppercase; margin-bottom: 1.1rem;
            display: inline-flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; justify-content: center;
        }
        .av .av-breadcrumb .sep { color: var(--av-accent); }
        .av .av-title {
            font-size: clamp(1.8rem, 4.5vw, 2.9rem); font-weight: 900;
            text-transform: uppercase; letter-spacing: 0.02em; line-height: 1.12;
            margin: 0 0 0.85rem; color: var(--av-ink);
            text-shadow: 0 2px 22px rgba(0,0,0,0.55);
        }
        .av .av-title::after {
            content: ''; display: block; width: 96px; height: 5px; margin: 1rem auto 0;
            background: linear-gradient(90deg, var(--av-accent), var(--av-accent) 4px, #0c0e11 4px 8px, var(--av-accent) 8px 12px, #0c0e11 12px 16px, var(--av-accent) 16px);
            box-shadow: 0 0 18px -4px var(--av-accent-glow);
        }
        .av .av-meta { display: inline-flex; gap: 0.55rem; flex-wrap: wrap; justify-content: center; margin-top: 1.1rem; }
        .av .av-chip {
            display: inline-flex; align-items: center; gap: 0.4rem;
            background: linear-gradient(180deg, #262b31, #1b1f24);
            border: 1px solid #3a414a; color: var(--av-ink);
            font-weight: 800; font-size: 0.8rem; padding: 0.42rem 0.9rem; border-radius: 8px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.06);
            letter-spacing: 0.04em;
        }
        .av .av-pill { font-size: 0.8rem; padding: 0.5rem 1rem; border-radius: 8px; }
        .av .av-pill-ok { background: linear-gradient(180deg, rgba(74,222,128,0.16), rgba(74,222,128,0.06)); color: var(--av-ok); border: 1px solid rgba(74,222,128,0.55); }
        .av .av-pill-warn { background: linear-gradient(180deg, rgba(251,191,36,0.16), rgba(251,191,36,0.06)); color: var(--av-warn); border: 1px solid rgba(251,191,36,0.55); }
        .av .av-pill-bad { background: linear-gradient(180deg, rgba(251,113,133,0.16), rgba(251,113,133,0.06)); color: var(--av-bad); border: 1px solid rgba(251,113,133,0.55); }

        .av .av-body {
            flex: 1; width: 100%; max-width: 900px; margin: 0 auto; padding: 2.5rem 1.5rem 3rem;
            display: flex; flex-direction: column; gap: 1.5rem;
        }
        .av .av-card {
            background: linear-gradient(180deg, var(--av-plate), var(--av-plate-2));
            border: 1px solid var(--av-steel); border-radius: 14px;
            padding: 1.85rem 2rem; position: relative;
            box-shadow: 0 12px 30px -16px rgba(0,0,0,0.75), inset 0 1px 0 rgba(255,255,255,0.05);
        }
        /* Zinc top rail + rivets */
        .av .av-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
            border-radius: 14px 14px 0 0;
            background: linear-gradient(90deg, transparent, color-mix(in srgb, var(--av-accent) 70%, transparent), transparent);
        }
        .av .av-card::after {
            content: ''; position: absolute; top: 0.8rem; left: 0.8rem; width: 7px; height: 7px;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 35%, #59626d, #2e333a);
            box-shadow: 0 0 0 1px #0a0c0e;
        }
        .av .av-card h2 {
            font-size: 0.92rem; font-weight: 900; color: var(--av-accent); margin: 0 0 1rem;
            display: flex; align-items: center; gap: 0.5rem;
            text-transform: uppercase; letter-spacing: 0.12em;
        }
        .av .av-card h2::before {
            content: '▞ '; letter-spacing: 0; opacity: 0.85;
        }
        .av .av-about p { margin: 0 0 1rem; font-size: 0.98rem; line-height: 1.75; color: var(--av-dim); }
        .av .av-about p:last-child { margin-bottom: 0; }
        .av .av-instructions { font-size: 0.98rem; line-height: 1.8; color: var(--av-ink); overflow-wrap: break-word; }
        .av .av-instructions h1, .av .av-instructions h2, .av .av-instructions h3, .av .av-instructions h4 {
            color: var(--av-accent); font-weight: 900; margin: 1.5rem 0 0.75rem;
            text-transform: uppercase; letter-spacing: 0.05em;
        }
        .av .av-instructions h1 { font-size: 1.5rem; } .av .av-instructions h2 { font-size: 1.3rem; }
        .av .av-instructions h3 { font-size: 1.15rem; } .av .av-instructions h4 { font-size: 1rem; }
        .av .av-instructions ul, .av .av-instructions ol { margin: 0.5rem 0 1rem 1.4rem; line-height: 1.8; color: var(--av-dim); }
        .av .av-instructions a { color: var(--av-info); font-weight: 800; }
        .av .av-instructions img { max-width: 100%; height: auto; border-radius: 8px; }
        .av .av-instructions table { width: 100%; border-collapse: collapse; margin: 1rem 0; font-size: 0.92rem; }
        .av .av-instructions th, .av .av-instructions td { border: 1px solid var(--av-steel); padding: 0.55rem 0.8rem; text-align: left; }
        .av .av-instructions th { background: #20242a; color: var(--av-accent); }

        .av .av-attach-list { display: flex; flex-direction: column; gap: 0.6rem; }
        .av .av-attach-link {
            display: flex; align-items: center; gap: 0.6rem;
            background: linear-gradient(180deg, #262b31, #1d2127);
            border: 1px solid var(--av-steel); color: var(--av-ink);
            font-weight: 700; font-size: 0.88rem; padding: 0.7rem 1rem; border-radius: 9px;
            text-decoration: none; transition: border-color 0.15s ease;
        }
        .av .av-attach-link:hover { border-color: var(--av-accent); }
        .av .av-attach-link .size { margin-left: auto; color: var(--av-dim); font-size: 0.75rem; font-weight: 600; }

        .av .av-banner { border-radius: 10px; padding: 1rem 1.4rem; font-weight: 700; font-size: 0.9rem; line-height: 1.55; border: 1px solid transparent; }
        .av .av-banner-ok { background: rgba(74,222,128,0.08); border-color: rgba(74,222,128,0.4); color: var(--av-ok); }
        .av .av-banner-warn { background: rgba(251,191,36,0.08); border-color: rgba(251,191,36,0.42); color: var(--av-warn); }
        .av .av-banner-bad { background: rgba(251,113,133,0.08); border-color: rgba(251,113,133,0.42); color: var(--av-bad); }

        .av .av-alert { border-radius: 10px; padding: 0.95rem 1.3rem; font-weight: 800; font-size: 0.9rem; }
        .av .av-alert-success { background: rgba(74,222,128,0.1); border: 1px solid rgba(74,222,128,0.5); color: var(--av-ok); }
        .av .av-alert-error { background: rgba(251,113,133,0.1); border: 1px solid rgba(251,113,133,0.5); color: var(--av-bad); }
        .av .av-alert-info { background: rgba(56,189,248,0.1); border: 1px solid rgba(56,189,248,0.5); color: var(--av-info); }

        .av .av-form { display: flex; flex-direction: column; gap: 1rem; }
        .av .av-label { font-size: 0.74rem; font-weight: 900; color: var(--av-dim); text-transform: uppercase; letter-spacing: 0.12em; }
        .av .av-textarea {
            width: 100%; min-height: 9rem; padding: 0.9rem 1.1rem; border-radius: 9px;
            border: 1px solid var(--av-steel); font-family: inherit; font-size: 0.95rem; line-height: 1.6;
            background: #12151a; color: var(--av-ink); resize: vertical; outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .av .av-textarea:focus { border-color: var(--av-accent); box-shadow: 0 0 0 3px var(--av-accent-glow); }
        .av .av-file-label {
            display: flex; align-items: center; justify-content: center; gap: 0.5rem;
            border: 2px dashed var(--av-steel); background: rgba(245,158,11,0.04); color: var(--av-accent);
            font-weight: 800; font-size: 0.9rem; padding: 1.5rem 1rem; border-radius: 9px;
            cursor: pointer; text-align: center; transition: background 0.15s ease, border-color 0.15s ease;
            text-transform: uppercase; letter-spacing: 0.06em;
        }
        .av .av-file-label:hover { background: rgba(245,158,11,0.09); border-color: var(--av-accent); }
        .av .av-type-chips { display: flex; flex-wrap: wrap; gap: 0.4rem; }
        .av .av-type-chip {
            background: #20242a; border: 1px solid var(--av-steel); color: var(--av-dim);
            font-size: 0.72rem; font-weight: 800; padding: 0.28rem 0.7rem; border-radius: 6px;
            text-transform: uppercase; letter-spacing: 0.06em;
        }
        .av .av-submit-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem;
            background: linear-gradient(180deg, #fbbf24 0%, #f59e0b 60%, #d97706 100%); color: #101014;
            font-weight: 900; font-size: 0.95rem; padding: 0.9rem 1.9rem; border: 1px solid #92400e; border-radius: 9px;
            cursor: pointer; align-self: flex-start; text-transform: uppercase; letter-spacing: 0.08em;
            box-shadow: 0 10px 24px -8px rgba(245,158,11,0.5), inset 0 1px 0 rgba(255,255,255,0.35);
            transition: transform 0.12s ease, box-shadow 0.15s ease;
        }
        .av .av-submit-btn:hover { transform: translateY(-1px); box-shadow: 0 14px 28px -8px rgba(245,158,11,0.6), inset 0 1px 0 rgba(255,255,255,0.35); }
        .av .av-submit-btn:disabled { opacity: 0.55; cursor: not-allowed; transform: none; box-shadow: none; }
        .av .av-submit-busy { opacity: 0.75; cursor: progress; }
        .av .av-field-error { color: var(--av-bad); font-size: 0.82rem; font-weight: 800; margin-top: 0.35rem; }
        .av .av-field-invalid { border-color: var(--av-bad) !important; box-shadow: 0 0 0 2px rgba(251,113,133,0.18); }
        .av .av-edit-btn {
            display: inline-flex; align-items: center; gap: 0.45rem; margin-top: 1.25rem;
            background: linear-gradient(180deg, #2a2f35, #20242a); border: 1px solid var(--av-accent); color: var(--av-accent);
            font-weight: 900; font-size: 0.85rem; padding: 0.65rem 1.3rem; border-radius: 9px;
            cursor: pointer; text-transform: uppercase; letter-spacing: 0.07em;
            transition: background 0.15s ease, color 0.15s ease;
        }
        .av .av-edit-btn:hover { background: #20242a; color: #fff; }
        .av .av-cancel-btn {
            display: inline-flex; align-items: center; gap: 0.45rem;
            background: linear-gradient(180deg, #2a2f35, #20242a); border: 1px solid var(--av-steel); color: var(--av-dim);
            font-weight: 800; font-size: 0.9rem; padding: 0.85rem 1.5rem; border-radius: 9px;
            cursor: pointer; transition: border-color 0.15s ease;
        }
        .av .av-cancel-btn:hover { border-color: var(--av-bad); color: var(--av-bad); }
        .av .av-edit-actions { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; }

        .av .av-pending-list { display: flex; flex-direction: column; gap: 0.55rem; }
        .av .av-pending-item {
            display: flex; align-items: center; gap: 0.75rem;
            background: #171b20; border: 1px solid var(--av-steel); border-radius: 9px;
            padding: 0.6rem 0.9rem;
        }
        .av .av-pending-thumb { width: 2.6rem; height: 2.6rem; object-fit: cover; border-radius: 6px; flex-shrink: 0; }
        .av .av-pending-ico { font-size: 1.4rem; flex-shrink: 0; }
        .av .av-pending-info { display: flex; flex-direction: column; min-width: 0; flex: 1; }
        .av .av-pending-info strong { font-size: 0.85rem; color: var(--av-ink); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .av .av-pending-info small { font-size: 0.72rem; color: var(--av-dim); font-weight: 600; }
        .av .av-pending-open {
            background: #20242a; border: 1px solid var(--av-steel); color: var(--av-info);
            font-weight: 800; font-size: 0.75rem; padding: 0.3rem 0.7rem; border-radius: 6px;
            text-decoration: none; white-space: nowrap; text-transform: uppercase; letter-spacing: 0.05em;
        }
        .av .av-pending-open:hover { border-color: var(--av-info); }
        .av .av-pending-remove {
            background: rgba(251,113,133,0.12); border: 1px solid rgba(251,113,133,0.5); color: var(--av-bad);
            font-weight: 900; font-size: 0.8rem; width: 1.95rem; height: 1.95rem;
            border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;
            transition: background 0.15s ease;
        }
        .av .av-pending-remove:hover { background: rgba(251,113,133,0.22); }

        .av .av-existing-item { display: flex; align-items: stretch; gap: 0.5rem; }
        .av .av-existing-item.is-removing .av-attach-link {
            background: rgba(251,113,133,0.1); border-color: rgba(251,113,133,0.55); color: var(--av-bad); text-decoration: line-through;
        }
        .av .av-remove-btn {
            background: #20242a; border: 1px solid var(--av-steel); color: var(--av-bad);
            font-weight: 800; font-size: 0.75rem; padding: 0.8rem 0.9rem; border-radius: 9px;
            cursor: pointer; white-space: nowrap; text-transform: uppercase; letter-spacing: 0.05em;
            transition: border-color 0.15s ease;
        }
        .av .av-remove-btn:hover { border-color: var(--av-bad); }

        .av .av-submitted { background: #12151a; border: 1px solid var(--av-steel); border-radius: 11px; padding: 1.25rem; display: flex; flex-direction: column; gap: 0.75rem; }
        .av .av-submitted h3 { margin: 0; font-size: 1rem; font-weight: 900; color: var(--av-ink); text-transform: uppercase; letter-spacing: 0.05em; }
        .av .av-submitted-meta { font-size: 0.8rem; color: var(--av-dim); font-weight: 700; }
        .av .av-submission-text { background: #0e1115; border: 1px solid var(--av-steel); border-radius: 8px; padding: 1rem; font-size: 0.95rem; line-height: 1.7; color: var(--av-ink); overflow-wrap: break-word; }

        .av .av-grade { text-align: center; padding: 0.5rem 0; }
        .av .av-grade-ring {
            width: 9.5rem; height: 9.5rem; margin: 0 auto 1rem; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; flex-direction: column;
            background: conic-gradient(var(--av-ok) 0%, var(--av-ok) {{ $gradePct !== null ? $gradePct : 0 }}%, var(--av-steel) {{ $gradePct !== null ? $gradePct : 0 }}%, var(--av-steel) 100%);
            padding: 0.75rem; box-shadow: 0 0 30px -10px rgba(74,222,128,0.5);
        }
        .av .av-grade-ring-inner {
            width: 100%; height: 100%; background: linear-gradient(180deg, #171b20, #101317); border-radius: 50%;
            display: flex; align-items: center; justify-content: center; flex-direction: column; gap: 0.1rem;
            border: 1px solid var(--av-steel);
        }
        .av .av-grade-pct { font-size: 1.7rem; font-weight: 900; color: var(--av-ok); }
        .av .av-grade-letter { font-size: 0.9rem; font-weight: 900; color: var(--av-ok); letter-spacing: 0.1em; }
        .av .av-grade-score { font-weight: 800; color: var(--av-dim); font-size: 0.95rem; }
        .av .av-feedback { background: rgba(74,222,128,0.07); border: 1px solid rgba(74,222,128,0.4); border-radius: 9px; padding: 1.1rem 1.3rem; font-size: 0.95rem; line-height: 1.7; color: #bdeed1; overflow-wrap: break-word; }
        .av .av-grade-meta { display: flex; flex-wrap: wrap; gap: 0.5rem; justify-content: center; margin-top: 0.85rem; }

        .av .av-empty {
            background: #101317; border: 1px dashed var(--av-steel); border-radius: 14px;
            padding: 2.6rem; text-align: center; color: var(--av-dim); font-weight: 800;
            text-transform: uppercase; letter-spacing: 0.08em;
        }
        .av .av-footer {
            text-align: center; padding: 0 1.5rem 3rem;
            display: flex; flex-direction: column; align-items: center; gap: 0.7rem;
        }
        .av .av-footer .av-back { padding: 0.8rem 1.6rem; font-size: 0.88rem; }
        .av .av-plate-stamp {
            font-size: 0.66rem; font-weight: 800; letter-spacing: 0.22em; color: #4b5563;
            text-transform: uppercase; user-select: none;
        }

        @media (max-width: 640px) {
            .av .av-topbar { padding: 0.9rem 1.25rem; }
            .av .av-hero { padding: 2rem 1.25rem 1.9rem; }
            .av .av-card { padding: 1.5rem 1.25rem; }
        }

        /* ─── Kids tier: bright, friendly, rounded ─── */
        .av-kids {
            --av-accent: #e11d48;
            --av-accent-glow: rgba(225,29,72,0.2);
            --av-steel: #fce7e7;
            --av-plate: #ffffff;
            --av-plate-2: #ffffff;
            --av-ink: #0f172a;
            --av-dim: #64748b;
            --av-ok: #15803d;
            --av-warn: #b45309;
            --av-bad: #b91c1c;
            --av-info: #1d4ed8;
            background: linear-gradient(180deg, #fff5f7 0%, #fef2f2 45%, #ffffff 100%);
            color: #334155;
        }
        .av-kids .av-topbar { background: rgba(255,255,255,0.7); backdrop-filter: blur(8px); border-bottom: 1px solid #fce7e7; box-shadow: none; }
        .av-kids .av-back { background: #ffffff; border: 1px solid #e2e8f0; color: #9f1239; border-radius: 9999px; box-shadow: none; }
        .av-kids .av-back:hover { border-color: #fda4af; background: #fff1f2; box-shadow: none; }
        .av-kids .av-brand { color: #9f1239; }
        .av-kids .av-hero {
            background: linear-gradient(135deg, #fff5f7 0%, #fff1f2 100%);
            border-bottom: 1px solid #fde2e2;
        }
        .av-kids .av-hero::before { display: none; }
        .av-kids .av-title { text-transform: none; letter-spacing: 0; text-shadow: none; }
        .av-kids .av-title::after {
            background: linear-gradient(90deg, #fda4af, #fecdd3);
            box-shadow: 0 8px 20px -8px rgba(225,29,72,0.3);
        }
        .av-kids .av-breadcrumb { color: #be123c; font-weight: 700; letter-spacing: 0.02em; }
        .av-kids .av-chip { background: #ffffff; border: 1px solid #fde2e2; color: #9f1239; border-radius: 9999px; box-shadow: 0 2px 8px -4px rgba(225,29,72,0.15); }
        .av-kids .av-pill-ok { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
        .av-kids .av-pill-warn { background: #fef3c7; color: #b45309; border-color: #fde68a; }
        .av-kids .av-pill-bad { background: #fee2e2; color: #b91c1c; border-color: #fca5a5; }
        .av-kids .av-card { border-radius: 1.25rem; border: 1px solid #fce7f3; box-shadow: 0 8px 28px -18px rgba(159,18,57,0.25); }
        .av-kids .av-card::before, .av-kids .av-card::after { display: none; }
        .av-kids .av-card h2 { color: #9f1239; text-transform: none; letter-spacing: 0; }
        .av-kids .av-card h2::before { display: none; }
        .av-kids .av-about p { color: #475569; }
        .av-kids .av-attach-link { background: #f8fafc; border: 1px solid #e2e8f0; color: #0f172a; border-radius: 0.8rem; }
        .av-kids .av-banner { border-radius: 1.1rem; }
        .av-kids .av-banner-ok { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
        .av-kids .av-banner-warn { background: #fffbeb; border-color: #fde68a; color: #b45309; }
        .av-kids .av-banner-bad { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
        .av-kids .av-alert-success { background: #f0fdf4; border-color: #86efac; color: #166534; }
        .av-kids .av-alert-error { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
        .av-kids .av-alert-info { background: #eff6ff; border-color: #bfdbfe; color: #1e40af; }
        .av-kids .av-submit-btn { background: linear-gradient(135deg, #e11d48 0%, #f43f5e 100%); color: #ffffff; border: none; border-radius: 9999px; box-shadow: 0 8px 20px -8px rgba(225,29,72,0.6); text-transform: none; letter-spacing: 0; }
        .av-kids .av-edit-btn { background: #ffffff; border: 1px solid #fda4af; color: #be123c; border-radius: 9999px; text-transform: none; letter-spacing: 0; }
        .av-kids .av-edit-btn:hover { background: #fff1f2; color: #be123c; }
        .av-kids .av-cancel-btn { background: #f1f5f9; border: 1px solid #cbd5e1; color: #475569; border-radius: 9999px; }
        .av-kids .av-cancel-btn:hover { border-color: #94a3b8; color: #475569; }
        .av-kids .av-textarea { background: #ffffff; border-radius: 0.85rem; }
        .av-kids .av-textarea:focus { border-color: #fb7185; box-shadow: 0 0 0 3px rgba(251,113,133,0.15); }
        .av-kids .av-file-label { border: 2px dashed #fda4af; background: #fff5f7; color: #9f1239; border-radius: 0.85rem; }
        .av-kids .av-file-label:hover { background: #fff1f2; }
        .av-kids .av-type-chip { background: #ffe4e6; border-color: #fecdd3; color: #9f1239; border-radius: 9999px; text-transform: none; letter-spacing: 0; }
        .av-kids .av-pending-item { background: #ffffff; border-radius: 0.85rem; }
        .av-kids .av-pending-open { background: #f8fafc; color: #334155; border-radius: 0.6rem; text-transform: none; letter-spacing: 0; }
        .av-kids .av-pending-remove { border-radius: 9999px; }
        .av-kids .av-remove-btn { background: #fef2f2; border-color: #fecaca; color: #b91c1c; border-radius: 0.8rem; text-transform: none; letter-spacing: 0; }
        .av-kids .av-submitted { background: #f8fafc; border-color: #e2e8f0; border-radius: 1rem; }
        .av-kids .av-submission-text { background: #ffffff; border-color: #e2e8f0; border-radius: 0.8rem; }
        .av-kids .av-grade-ring-inner { background: #ffffff; border-color: #e2e8f0; }
        .av-kids .av-grade-pct { color: #15803d; }
        .av-kids .av-grade-letter { color: #166534; }
        .av-kids .av-grade-score { color: #475569; }
        .av-kids .av-feedback { background: #f0fdf4; border-color: #bbf7d0; color: #165e35; border-radius: 0.9rem; }
        .av-kids .av-empty { background: #ffffff; border-color: #fda4af; border-radius: 1.25rem; color: #9f1239; text-transform: none; letter-spacing: 0; }
        .av-kids .av-plate-stamp { color: #cbd5e1; }
        .av-kids .av-title { font-size: clamp(2rem, 5vw, 3rem); }
        .av-kids .av-card { padding: 2rem 2.25rem; }
        .av-kids .av-about p, .av-kids .av-instructions { font-size: 1.12rem; line-height: 1.85; }
        .av-kids .av-chip { font-size: 0.9rem; padding: 0.55rem 1.1rem; }
        .av-kids .av-brand { font-size: 1.05rem; }
        .av-kids .av-back { font-size: 0.95rem; padding: 0.65rem 1.2rem; }
        .av-kids .av-card h2 { font-size: 1.05rem; }
    </style>

    {{-- Top bar --}}
    <div class="av-topbar">
        <a href="{{ \App\Filament\Student\Pages\Dashboard::getUrl(['tab' => 'assignments']) }}" class="av-back">◄ Back to My Assignments</a>
        <div class="av-brand">▚ Student Portal</div>
    </div>

    {{-- Hero header --}}
    <header class="av-hero">
        <div class="av-breadcrumb">
            <span>🏫 {{ $schoolName }}</span>
            @if($gradeName)
                <span class="sep">▸</span>
                <span>📗 {{ $gradeName }}</span>
            @endif
            <span class="sep">▸</span>
            <span>📘 {{ $className }}</span>
        </div>

        <h1 class="av-title">📋 {{ $assignment->title }}</h1>

        <div class="av-meta">
            <span class="av-chip av-pill {{ $statePillClass }}">{{ $stateLabel }}</span>
            <span class="av-chip">👨‍🏫 {{ $teacherName }}</span>
            <span class="av-chip">🎯 {{ $assignment->max_score }} MARKS</span>
            @if($assignment->end_at)
                <span class="av-chip">📅 DUE {{ $assignment->end_at->format('M j, Y · g:i A') }}</span>
            @else
                <span class="av-chip">📅 NO DEADLINE</span>
            @endif
        </div>
    </header>

    <main class="av-body">

        @if($submissionMessage)
            <div class="av-alert av-alert-{{ $submissionMessageType }}">
                {{ $submissionMessageType === 'success' ? '✓' : '⚠' }} {{ $submissionMessage }}
            </div>
        @endif

        {{-- Details --}}
        @if($assignment->description || $assignment->instructions)
            <section class="av-card av-about">
                <h2>Assignment details</h2>
                @if($assignment->description)
                    <p>{!! nl2br(e($assignment->description)) !!}</p>
                @endif
                @if($assignment->instructions)
                    @if(\Illuminate\Support\Str::contains($assignment->instructions, '<'))
                        <div class="av-instructions">{!! $assignment->instructions !!}</div>
                    @else
                        <div class="av-instructions">{!! nl2br(e($assignment->instructions)) !!}</div>
                    @endif
                @endif
            </section>
        @endif

        {{-- Reference attachments --}}
        @if($assignment->attachments && $assignment->attachments->count())
            <section class="av-card">
                <h2>Reference materials</h2>
                <div class="av-attach-list">
                    @foreach($assignment->attachments as $attachment)
                        <a href="{{ \Illuminate\Support\Facades\Storage::url($attachment->file_path) }}" download class="av-attach-link">
                            📄 {{ $attachment->original_name ?? 'Download attachment' }}
                            @if($attachment->file_size)
                                <span class="size">{{ round($attachment->file_size / 1024, 1) }} KB</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Availability banner --}}
        @if($avState === 'not_started')
            <div class="av-banner av-banner-warn">
                ⏱ SUBMISSIONS LOCKED — THE ASSIGNMENT STARTS
                <strong>{{ $assignment->start_at ? $assignment->start_at->format('M j, Y · g:i A') : 'on its scheduled start date' }}</strong>.
                @if($startsIn)
                    <br>🔓 The submission form unlocks automatically in <strong>{{ $startsIn }}</strong>. No action needed — check back then.
                @else
                    <br>🔓 The submission form will unlock automatically at that time.
                @endif
            </div>
        @elseif($avState === 'closed')
            <div class="av-banner av-banner-bad">
                ⛔ DEADLINE PASSED — The deadline has passed and late submissions are not allowed for this assignment.
            </div>
        @elseif($avState === 'late')
            <div class="av-banner av-banner-warn">
                ⚠ LATE WINDOW — The normal deadline has passed, but late submissions are still allowed until
                <strong>{{ $assignment->lateSubmissionDeadline() ? $assignment->lateSubmissionDeadline()->format('M j, Y · g:i A') : 'the late deadline' }}</strong>.
            </div>
        @elseif($avState === 'open')
            <div class="av-banner av-banner-ok">
                ✓ SUBMISSIONS OPEN
                @if($assignment->end_at)
                    — Submit on time, the deadline is <strong>{{ $assignment->end_at->format('M j, Y · g:i A') }}</strong>.
                @endif
            </div>
        @elseif($avState === 'graded')
            <div class="av-banner av-banner-ok">
                ✓ SUBMISSION GRADED — Great work, whatever grade, the machine keeps turning.
            </div>
        @endif

        {{-- ── Graded (shows your submission read-only + grade) ── --}}
        @if($avState === 'graded' && $submission)
            <section class="av-card">
                <h2>Your grade</h2>
                <div class="av-grade">
                    <div class="av-grade-ring">
                        <div class="av-grade-ring-inner">
                            <div class="av-grade-pct">{{ round($gradePct) }}%</div>
                            @if($gradeLetter)
                                <div class="av-grade-letter">GRADE {{ $gradeLetter }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="av-grade-score">
                        SCORE: {{ number_format($submission->score, $submission->score == (int) $submission->score ? 0 : 2) }} / {{ $assignment->max_score }}
                    </div>

                    @if($submission->feedback)
                        <div style="margin-top: 1rem; text-align: left;">
                            <h3 style="font-size: 0.95rem; font-weight: 900; color: var(--av-ok); margin: 0 0 0.5rem 0; text-transform: uppercase; letter-spacing: 0.08em;">☰ Teacher feedback</h3>
                            <div class="av-feedback">{!! $submission->feedback !!}</div>
                        </div>
                    @endif

                    <div class="av-grade-meta">
                        <span class="av-chip">👨‍🏫 {{ $submission->grader?->user?->name ?? 'Teacher' }}</span>
                        @if($submission->graded_at)
                            <span class="av-chip">📅 Graded {{ $submission->graded_at->format('M j, Y · g:i A') }}</span>
                        @endif
                    </div>
                </div>
            </section>

            <section class="av-card">
                <h2>Your submission</h2>
                <div class="av-submitted">
                    <div class="av-submitted-meta">
                        Submitted <strong>{{ $submission->submitted_at ? $submission->submitted_at->format('M j, Y · g:i A') : '—' }}</strong>
                        · <span style="color: {{ $submission->is_late ? 'var(--av-bad)' : 'var(--av-ok)' }}; font-weight: 800;">{{ $submission->is_late ? 'LATE SUBMISSION' : 'ON TIME' }}</span>
                        · <span style="color: var(--av-dim);">This submission has been graded and can no longer be edited.</span>
                    </div>

                    @if($submission->content)
                        <div>
                            <span class="av-label">Your written answer</span>
                            <div class="av-submission-text">{!! nl2br(e($submission->content)) !!}</div>
                        </div>
                    @endif

                    @if($submission->attachments && $submission->attachments->count())
                        <div>
                            <span class="av-label">Your attached files</span>
                            <div class="av-attach-list">
                                @foreach($submission->attachments as $file)
                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($file->file_path) }}" download class="av-attach-link">
                                        📄 {{ $file->original_name ?? 'Download file' }}
                                        @if($file->file_size)
                                            <span class="size">{{ round($file->file_size / 1024, 1) }} KB</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        {{-- ── Submitted (awaiting grade / returned) ── --}}
        @if($submission && $avState !== 'graded')
            <section class="av-card">
                <h2>{{ $submission->status === 'returned' ? '↩ Returned for revision' : 'Your submission' }}</h2>

                <div class="av-submitted">
                    <div class="av-submitted-meta">
                        Submitted <strong>{{ $submission->submitted_at ? $submission->submitted_at->format('M j, Y · g:i A') : '—' }}</strong>
                        · <span style="color: {{ $submission->is_late ? 'var(--av-bad)' : 'var(--av-ok)' }}; font-weight: 800;">{{ $submission->is_late ? 'LATE SUBMISSION' : 'ON TIME' }}</span>
                        @if($submission->status === 'returned')
                            · <span style="color: var(--av-warn); font-weight: 800;">RETURNED BY TEACHER — PLEASE REVISE</span>
                        @else
                            · <span style="color: var(--av-dim);">WAITING FOR THE TEACHER TO GRADE IT</span>
                        @endif
                    </div>

                    @if($submission->content)
                        <div>
                            <span class="av-label">Your written answer</span>
                            <div class="av-submission-text">{!! nl2br(e($submission->content)) !!}</div>
                        </div>
                    @endif

                    @if($submission->attachments && $submission->attachments->count())
                        <div>
                            <span class="av-label">Your attached files</span>
                            <div class="av-attach-list">
                                @foreach($submission->attachments as $file)
                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($file->file_path) }}" download class="av-attach-link">
                                        📄 {{ $file->original_name ?? 'Download file' }}
                                        @if($file->file_size)
                                            <span class="size">{{ round($file->file_size / 1024, 1) }} KB</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(! $avCanSubmit)
                        <div class="av-banner av-banner-bad" style="margin-top: 0.5rem;">
                            Submissions are now closed for this assignment, so it can no longer be edited.
                        </div>
                    @endif
                </div>

                @if($avCanSubmit && ! $editing)
                    <button type="button" wire:click="startEditing" class="av-edit-btn">✏ Edit submission</button>
                @endif
            </section>

            {{-- Edit mode --}}
            @if($editing)
                <section class="av-card av-edit-card" wire:key="edit-form">
                    <h2>Edit your submission</h2>

                    <div class="av-alert av-alert-info" style="margin-bottom: 1rem;">
                        📝 When you are happy with your changes, tap <strong>Save changes</strong>. Your teacher will see the updated answer.
                    </div>

                    <div class="av-form">
                        @if($avTypeLabels)
                            <div style="display:flex; flex-direction:column; gap:0.4rem;">
                                <span class="av-label">Allowed submission types</span>
                                <div class="av-type-chips">
                                    @foreach($avTypeLabels as $label)
                                        <span class="av-type-chip">{{ $label }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($avAllowsText)
                            <label class="av-label" for="av-edit-content">Your written answer</label>
                            <textarea id="av-edit-content" class="av-textarea {{ $errors->has('submissionContent') ? 'av-field-invalid' : '' }}" wire:model="submissionContent" placeholder="Type your answer here..."></textarea>
                            @error('submissionContent')
                                <div class="av-field-error">⚠ {{ $message }}</div>
                            @enderror
                        @endif

                        @if($submission->attachments && $submission->attachments->count())
                            <div>
                                <span class="av-label">Your files</span>
                                <div class="av-attach-list">
                                    @foreach($submission->attachments as $file)
                                        @php $isRemoving = in_array($file->id, $pendingRemovalAttachmentIds, true); @endphp
                                        <div class="av-existing-item {{ $isRemoving ? 'is-removing' : '' }}">
                                            <a href="{{ \Illuminate\Support\Facades\Storage::url($file->file_path) }}" download class="av-attach-link" style="flex: 1;">
                                                📄 {{ $file->original_name ?? 'Download file' }}
                                                @if($file->file_size)
                                                    <span class="size">{{ round($file->file_size / 1024, 1) }} KB</span>
                                                @endif
                                            </a>
                                            <button type="button" wire:click="toggleAttachmentRemoval({{ $file->id }})" class="av-remove-btn">
                                                {{ $isRemoving ? '↩ Undo' : '✕ Remove' }}
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($avAllowsFiles)
                            <div>
                                <span class="av-label">Add more files</span>
                                <label for="av-edit-files" class="av-file-label">⚙ Click to choose more files</label>
                                <input id="av-edit-files" type="file" wire:model="submissionFiles" multiple style="display: none;" accept="{{ implode(',', $avMimes) }}">
                                <div wire:loading wire:target="submissionFiles" style="font-size: 0.8rem; color: var(--av-accent); font-weight: 700;">Uploading files… ⏳</div>
                                @error('submissionFiles')
                                    <div class="av-field-error">⚠ {{ $message }}</div>
                                @enderror

                                @if($submissionFiles && count($submissionFiles))
                                    <div class="av-pending-list">
                                        @foreach($submissionFiles as $pendingIndex => $pendingFile)
                                            <div class="av-pending-item">
                                                @if($this->isImageUpload($pendingFile))
                                                    <img src="{{ $this->pendingFileUrl($pendingFile) }}" alt="" class="av-pending-thumb">
                                                @else
                                                    <span class="av-pending-ico">📄</span>
                                                @endif
                                                <div class="av-pending-info">
                                                    <strong>{{ $pendingFile->getClientOriginalName() }}</strong>
                                                    <small>{{ $this->pendingFileSize($pendingFile) }}</small>
                                                </div>
                                                <a href="{{ $this->pendingFileUrl($pendingFile) }}" target="_blank" rel="noopener" class="av-pending-open">Open</a>
                                                <button type="button" wire:click="removePendingFile({{ $pendingIndex }})" class="av-pending-remove" title="Remove file">✕</button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="av-edit-actions">
                            <button type="button" wire:click="saveSubmissionChanges" class="av-submit-btn" wire:loading.attr="disabled" wire:target="saveSubmissionChanges">
                            <span wire:loading.remove wire:target="saveSubmissionChanges">Save changes</span>
                            <span wire:loading wire:target="saveSubmissionChanges">Saving… ⏳</span>
                        </button>
                            <button type="button" wire:click="cancelEditing" class="av-cancel-btn">Cancel</button>
                        </div>
                    </div>
                </section>
            @endif
        @endif

        {{-- ── No submission yet ── --}}
        @if(! $submission)
            @if($avState === 'not_started' || $avState === 'closed')
                <section class="av-card">
                    <h2>Submission</h2>
                    <div class="av-empty">
                        @if($avState === 'not_started')
                            🔒 SUBMISSIONS ARE LOCKED UNTIL {{ $assignment->start_at ? $assignment->start_at->format('M j, Y · g:i A') : 'THE START DATE' }}. CHECK BACK THEN!
                        @else
                            ⛔ THIS ASSIGNMENT IS CLOSED AND CAN NO LONGER BE SUBMITTED.
                        @endif
                    </div>
                </section>
            @elseif($avCanSubmit)
                <section class="av-card">
                    <h2>Submit your assignment</h2>

                    <div class="av-form" wire:key="submit-form">
                        @if($avTypeLabels)
                            <div style="display:flex; flex-direction:column; gap:0.4rem;">
                                <span class="av-label">Allowed submission types</span>
                                <div class="av-type-chips">
                                    @foreach($avTypeLabels as $label)
                                        <span class="av-type-chip">{{ $label }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($avAllowsText)
                            <label class="av-label" for="av-new-content">Your written answer</label>
                            <textarea id="av-new-content" class="av-textarea {{ $errors->has('submissionContent') ? 'av-field-invalid' : '' }}" wire:model="submissionContent" placeholder="Type your answer here..."></textarea>
                            @error('submissionContent')
                                <div class="av-field-error">⚠ {{ $message }}</div>
                            @enderror
                        @endif

                        @if($avAllowsFiles)
                            <label for="av-new-files" class="av-file-label">⚙ Click to choose your answer files</label>
                            <input id="av-new-files" type="file" wire:model="submissionFiles" multiple style="display: none;" accept="{{ implode(',', $avMimes) }}">
                            <div wire:loading wire:target="submissionFiles" style="font-size: 0.8rem; color: var(--av-accent); font-weight: 700;">Uploading files… ⏳</div>
                            @error('submissionFiles')
                                <div class="av-field-error">⚠ {{ $message }}</div>
                            @enderror

                            @if($submissionFiles && count($submissionFiles))
                                <div class="av-pending-list">
                                    @foreach($submissionFiles as $pendingIndex => $pendingFile)
                                        <div class="av-pending-item">
                                            @if($this->isImageUpload($pendingFile))
                                                <img src="{{ $this->pendingFileUrl($pendingFile) }}" alt="" class="av-pending-thumb">
                                            @else
                                                <span class="av-pending-ico">📄</span>
                                            @endif
                                            <div class="av-pending-info">
                                                <strong>{{ $pendingFile->getClientOriginalName() }}</strong>
                                                <small>{{ $this->pendingFileSize($pendingFile) }}</small>
                                            </div>
                                            <a href="{{ $this->pendingFileUrl($pendingFile) }}" target="_blank" rel="noopener" class="av-pending-open">Open</a>
                                            <button type="button" wire:click="removePendingFile({{ $pendingIndex }})" class="av-pending-remove" title="Remove file">✕</button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif

                        <button type="button" wire:click="submitAssignment" class="av-submit-btn" wire:loading.attr="disabled" wire:loading.class="av-submit-busy" wire:target="submitAssignment">
                            <span wire:loading.remove wire:target="submitAssignment">Submit Assignment</span>
                            <span wire:loading wire:target="submitAssignment">Sending… ⏳</span>
                        </button>
                    </div>
                </section>
            @endif
        @endif

        @if(! $assignment->description && ! $assignment->instructions && ! ($assignment->attachments && $assignment->attachments->count()))
            <div class="av-empty">
                📋 No additional details were provided by the teacher for this assignment.
            </div>
        @endif

    </main>

    <footer class="av-footer">
        <a href="{{ \App\Filament\Student\Pages\Dashboard::getUrl(['tab' => 'assignments']) }}" class="av-back">◄ Back to My Assignments</a>
        <span class="av-plate-stamp">{{ $plate }} · {{ $plate2 }}</span>
    </footer>
</div>