<div class="profile-wrap">
    <style>
    .profile-grid {
        display: grid;
        grid-template-columns: 280px minmax(0, 1fr);
        gap: 18px;
        align-items: start;
    }
    @media (max-width: 860px) {
        .profile-grid { grid-template-columns: 1fr; }
    }

    .profile-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
    }
    .profile-card-head {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        background: #fafbfd;
        font-size: 13px;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Identity side */
    .identity-body { padding: 22px 20px; text-align: center; }
    .identity-photo {
        width: 96px;
        height: 96px;
        margin: 0 auto 12px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #10b981);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 700;
        font-size: 34px;
        overflow: hidden;
        box-shadow: 0 6px 18px rgba(99, 102, 241, .25);
    }
    .identity-photo img { width: 100%; height: 100%; object-fit: cover; }
    .identity-name { font-size: 16px; font-weight: 700; color: #0f172a; }
    .identity-email { font-size: 12.5px; color: #94a3b8; margin-top: 2px; word-break: break-all; }
    .identity-badge {
        display: inline-block;
        margin-top: 10px;
        font-size: 11px;
        font-weight: 600;
        color: #4f46e5;
        background: #eef2ff;
        padding: 4px 12px;
        border-radius: 999px;
    }
    .identity-meta { margin-top: 18px; border-top: 1px solid #f1f5f9; padding-top: 14px; text-align: left; }
    .identity-meta-row {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        padding: 7px 0;
        font-size: 12.5px;
    }
    .identity-meta-label { color: #94a3b8; font-weight: 600; flex-shrink: 0; }
    .identity-meta-value { color: #334155; font-weight: 600; text-align: right; word-break: break-word; }

    /* Form */
    .profile-form-body { padding: 20px; }
    .password-note {
        font-size: 11.5px;
        color: #94a3b8;
        margin-top: 10px;
    }

    /* Filament field polish inside profile page */
    .profile-form-body .fi-fo-field-wrp,
    .profile-form-body [data-field-wrapper] { margin-bottom: 4px; }
</style>

    <div class="profile-grid">

        {{-- ── Identity summary ─────────────────────── --}}
        <div class="profile-card">
            <div class="profile-card-head">👤 Account Overview</div>
            <div class="identity-body">
                <div class="identity-photo">
                    @if(auth()->user()?->teacher?->profile_photo_url)
                        <img src="{{ auth()->user()->teacher->profile_photo_url }}" alt="Profile photo">
                    @else
                        {{ strtoupper(substr(auth()->user()?->name ?? 'T', 0, 1)) }}
                    @endif
                </div>
                <div class="identity-name">{{ auth()->user()?->name }}</div>
                <div class="identity-email">{{ auth()->user()?->email }}</div>
                <span class="identity-badge">Teacher</span>

                <div class="identity-meta">
                    <div class="identity-meta-row">
                        <span class="identity-meta-label">Employee No.</span>
                        <span class="identity-meta-value">{{ auth()->user()?->teacher?->employee_no ?? '—' }}</span>
                    </div>
                    <div class="identity-meta-row">
                        <span class="identity-meta-label">Schools</span>
                        <span class="identity-meta-value">{{ auth()->user()?->teacher?->schools()->count() ?? 0 }}</span>
                    </div>
                    <div class="identity-meta-row">
                        <span class="identity-meta-label">Classes</span>
                        <span class="identity-meta-value">{{ auth()->user()?->teacher?->classes()->count() ?? 0 }}</span>
                    </div>
                    <div class="identity-meta-row">
                        <span class="identity-meta-label">Lessons</span>
                        <span class="identity-meta-value">{{ auth()->user()?->teacher?->lessons()->count() ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Editable form (Filament-rendered, mirrors super-admin Edit Teacher) ── --}}
        <div class="profile-card">
            <div class="profile-card-head">⚙️ Manage Profile</div>
            <div class="profile-form-body">
                {{ $this->content }}

                <p class="password-note">
                    Leave the password fields empty to keep your current password.
                    Changing your email will require re-verification.
                </p>
            </div>
        </div>

    </div>
</div>
