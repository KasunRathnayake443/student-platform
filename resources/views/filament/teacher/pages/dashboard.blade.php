<x-teacher-layout title="Dashboard">
    <!-- Stats Row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        @php
            $totalClasses = $schools->flatMap(fn($s) => $s->grades->flatMap(fn($g) => $g->learningClasses))->count();
            $totalStudents = $schools->flatMap(fn($s) => $s->grades->flatMap(fn($g) => $g->learningClasses))->sum(fn($c) => $c->students()->count());
        @endphp

        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px 22px;">
            <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;">Schools</div>
            <div style="font-size:32px;font-weight:700;color:#0f172a;line-height:1;">{{ $schools->count() }}</div>
            <div style="font-size:12px;color:#94a3b8;margin-top:4px;">Assigned to you</div>
        </div>

        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px 22px;">
            <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;">Classes</div>
            <div style="font-size:32px;font-weight:700;color:#0f172a;line-height:1;">{{ $totalClasses }}</div>
            <div style="font-size:12px;color:#94a3b8;margin-top:4px;">Active classes</div>
        </div>

        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px 22px;">
            <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;">Students</div>
            <div style="font-size:32px;font-weight:700;color:#0f172a;line-height:1;">{{ $totalStudents }}</div>
            <div style="font-size:12px;color:#94a3b8;margin-top:4px;">Total enrolled</div>
        </div>

        <div style="background:linear-gradient(135deg,#6366f1,#8b5cf6);border-radius:16px;padding:20px 22px;">
            <div style="font-size:11px;font-weight:600;color:rgba(255,255,255,.65);text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;">Today</div>
            <div style="font-size:22px;font-weight:700;color:#fff;line-height:1;">{{ now()->format('d M') }}</div>
            <div style="font-size:12px;color:rgba(255,255,255,.65);margin-top:4px;">{{ now()->format('l') }}</div>
        </div>
    </div>

    <!-- Schools & Classes -->
    @if($schools->isEmpty())
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:64px 24px;text-align:center;">
            <div style="width:56px;height:56px;background:#f1f5f9;border-radius:16px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px;">
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6"/>
                </svg>
            </div>
            <h3 style="font-size:16px;font-weight:600;color:#0f172a;margin-bottom:6px;">No schools assigned yet</h3>
            <p style="font-size:14px;color:#94a3b8;">Contact your administrator to get assigned to schools and classes.</p>
        </div>
    @else
        <div style="margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-size:16px;font-weight:700;color:#0f172a;">My Schools & Classes</h2>
        </div>

        <div style="display:flex;flex-direction:column;gap:20px;">
            @foreach($schools as $school)
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
                    <!-- School header -->
                    <div style="padding:18px 22px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:14px;background:#fafbfc;">
                        <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#6366f1,#10b981);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:16px;flex-shrink:0;">
                            {{ strtoupper(substr($school->name, 0, 1)) }}
                        </div>
                        <div>
                            <div style="font-size:15px;font-weight:700;color:#0f172a;">{{ $school->name }}</div>
                            <div style="font-size:12px;color:#94a3b8;margin-top:2px;">
                                {{ $school->code }}
                                @if($school->address) · {{ $school->address }} @endif
                            </div>
                        </div>
                        <div style="margin-left:auto;font-size:12px;color:#6366f1;background:#eef2ff;padding:4px 12px;border-radius:999px;font-weight:600;">
                            {{ $school->grades->flatMap->learningClasses->count() }} {{ Str::plural('class', $school->grades->flatMap->learningClasses->count()) }}
                        </div>
                    </div>

                    <!-- Classes grid -->
                    <div style="padding:20px 22px;">
                        @if($school->grades->isEmpty())
                            <p style="font-size:13px;color:#94a3b8;text-align:center;padding:16px;">No classes assigned in this school.</p>
                        @else
                            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px;">
                                @foreach($school->grades as $grade)
                                    @foreach($grade->learningClasses as $class)
                                        <div style="border:1px solid #e2e8f0;border-radius:12px;padding:16px;transition:box-shadow .15s,border-color .15s;cursor:pointer;"
                                             onmouseover="this.style.borderColor='#a5b4fc';this.style.boxShadow='0 4px 12px rgba(99,102,241,.1)'"
                                             onmouseout="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                                                <span style="font-size:11px;font-weight:600;color:#6366f1;background:#eef2ff;padding:3px 10px;border-radius:999px;">
                                                    Grade {{ $grade->name }}
                                                </span>
                                                <span style="font-size:11px;font-weight:600;color:{{ $class->is_active ? '#059669' : '#dc2626' }};background:{{ $class->is_active ? '#d1fae5' : '#fee2e2' }};padding:3px 10px;border-radius:999px;">
                                                    {{ $class->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                            <div style="font-size:15px;font-weight:700;color:#0f172a;margin-bottom:4px;">{{ $class->name }}</div>
                                            @if($class->medium)
                                                <div style="font-size:12px;color:#94a3b8;margin-bottom:12px;">{{ $class->medium }} Medium</div>
                                            @endif
                                            <div style="display:flex;align-items:center;gap:16px;font-size:12px;color:#64748b;padding-top:12px;border-top:1px solid #f1f5f9;">
                                                <span style="display:flex;align-items:center;gap:5px;">
                                                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                    {{ $class->students()->count() }} Students
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-teacher-layout>
