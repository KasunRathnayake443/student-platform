{{-- ══════════════════════════════════════════════════════════════
     JUNIOR CALENDAR COMPONENT (Age 11-15)
     Modern Bento-Grid Aesthetic, Tech-Savvy, Indigo & Clean
     ══════════════════════════════════════════════════════════════ --}}

<style>
/* ───── Junior Calendar Specialized Styles ───── */
.junior-cal-container {
    font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    width: 100%;
    color: #334155;
}

/* ── Bento Metrics Row (4 Columns) ── */
.junior-cal-metrics {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
}
@media (max-width: 1100px) {
    .junior-cal-metrics { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 650px) {
    .junior-cal-metrics { grid-template-columns: 1fr; }
}

.junior-cal-metric-card {
    background: #ffffff;
    border: 1px solid #f1f5f9;
    border-radius: 1.25rem;
    padding: 1.25rem 1.5rem;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all 0.2s ease;
}
.junior-cal-metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
}
.junior-cal-metric-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.junior-cal-metric-label {
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #94a3b8;
}
.junior-cal-metric-val {
    font-size: 1.85rem;
    font-weight: 900;
    color: #0f172a;
    line-height: 1.1;
}
.junior-cal-metric-sub {
    font-size: 0.75rem;
    color: #64748b;
    font-weight: 500;
}
.junior-cal-metric-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 0.9rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
}
.metric-icon-blue   { background: #eff6ff; color: #2563eb; }
.metric-icon-purple { background: #f5f3ff; color: #7c3aed; }
.metric-icon-green  { background: #f0fdf4; color: #16a34a; }
.metric-icon-amber  { background: #fffbeb; color: #d97706; }

/* ── Main Two-Column Bento Layout ── */
.junior-cal-grid-layout {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 1.5rem;
    align-items: start;
}
@media (max-width: 1050px) {
    .junior-cal-grid-layout { grid-template-columns: 1fr; }
}

/* ── Calendar Bento Card ── */
.junior-cal-card {
    background: #ffffff;
    border: 1px solid #f1f5f9;
    border-radius: 1.25rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

/* ── Top Bar: Month Title & Filter Pills ── */
.junior-cal-nav-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f1f5f9;
}
.junior-cal-title-wrap {
    display: flex;
    align-items: center;
    gap: 0.85rem;
}
.junior-cal-month-title {
    font-size: 1.4rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
    letter-spacing: -0.01em;
}
.junior-cal-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.junior-cal-filter-pill {
    padding: 0.35rem 0.85rem;
    border-radius: 9999px;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #64748b;
    transition: all 0.15s ease;
}
.junior-cal-filter-pill:hover {
    border-color: #c7d2fe;
    color: #0f172a;
}
.junior-cal-filter-pill.active {
    background: #4f46e5;
    border-color: #4f46e5;
    color: #ffffff;
}
.junior-cal-btn-nav {
    width: 2.2rem;
    height: 2.2rem;
    border-radius: 0.65rem;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #334155;
    font-size: 1.1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
}
.junior-cal-btn-nav:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: #0f172a;
}
.junior-cal-btn-today {
    padding: 0.35rem 0.85rem;
    border-radius: 0.65rem;
    border: 1px solid #e0e7ff;
    background: #eef2ff;
    color: #4f46e5;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s ease;
}
.junior-cal-btn-today:hover {
    background: #e0e7ff;
    color: #3730a3;
}

/* ── Weekday Header ── */
.junior-cal-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.4rem;
}
.junior-cal-wd {
    text-align: center;
    font-size: 0.72rem;
    font-weight: 800;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    padding: 0.45rem 0;
}

/* ── Days Grid ── */
.junior-cal-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.4rem;
}
.junior-cal-day {
    min-height: 4.75rem;
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    border-radius: 0.85rem;
    padding: 0.45rem 0.45rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    cursor: pointer;
    position: relative;
    transition: all 0.15s ease;
}
.junior-cal-day:hover {
    background: #ffffff;
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    transform: translateY(-2px);
    z-index: 2;
}
.junior-cal-day.empty {
    background: transparent;
    border-color: transparent;
    cursor: default;
    box-shadow: none;
}
.junior-cal-day.empty:hover {
    background: transparent;
    transform: none;
    box-shadow: none;
}
.junior-cal-day-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.junior-cal-day-num {
    font-size: 0.85rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1;
}
.junior-cal-today-badge {
    font-size: 0.6rem;
    font-weight: 800;
    background: #4f46e5;
    color: #ffffff;
    padding: 0.1rem 0.35rem;
    border-radius: 0.35rem;
    line-height: 1.2;
}

/* Today highlight */
.junior-cal-day.today {
    background: #ffffff;
    border-color: #a5b4fc;
    box-shadow: 0 0 0 1px #a5b4fc, 0 4px 12px rgba(79, 70, 229, 0.08);
}
.junior-cal-day.today .junior-cal-day-num {
    color: #4f46e5;
    font-weight: 800;
}

/* Selected highlight */
.junior-cal-day.selected {
    background: #4f46e5 !important;
    border-color: #4f46e5 !important;
    box-shadow: 0 6px 18px rgba(79, 70, 229, 0.3) !important;
    transform: translateY(-2px);
    z-index: 3;
}
.junior-cal-day.selected .junior-cal-day-num {
    color: #ffffff !important;
}
.junior-cal-day.selected .junior-cal-pill-item {
    background: rgba(255, 255, 255, 0.22) !important;
    color: #ffffff !important;
    border-color: transparent !important;
}

/* Event Chips Inside Day Cell */
.junior-cal-day-chips {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    margin-top: 0.3rem;
}
.junior-cal-pill-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.15rem 0.35rem;
    border-radius: 0.4rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    border: 1px solid transparent;
}
.pill-asgn { background: #eff6ff; color: #1d4ed8; border-color: #dbeafe; }
.pill-quiz { background: #f5f3ff; color: #6d28d9; border-color: #ede9fe; }
.pill-note { background: #f0fdf4; color: #15803d; border-color: #dcfce7; }

/* ── Legend Bar ── */
.junior-cal-legend {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    flex-wrap: wrap;
    padding-top: 0.75rem;
    border-top: 1px solid #f1f5f9;
}
.junior-cal-legend-item {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
}
.junior-cal-legend-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

/* ── Right Column: Bento Side Inspector ── */
.junior-cal-sidecard {
    background: #ffffff;
    border: 1px solid #f1f5f9;
    border-radius: 1.25rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}
.junior-sidecard-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid #f1f5f9;
}
.junior-sidecard-title-wrap h3 {
    font-size: 1.05rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 0.15rem 0;
}
.junior-sidecard-title-wrap p {
    font-size: 0.75rem;
    color: #64748b;
    margin: 0;
}
.junior-sidecard-close {
    width: 2rem;
    height: 2rem;
    border-radius: 0.55rem;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #94a3b8;
    font-size: 0.95rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
}
.junior-sidecard-close:hover {
    background: #fef2f2;
    border-color: #fecaca;
    color: #e11d48;
}

/* Event List */
.junior-event-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.junior-event-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.85rem;
    padding: 0.85rem 1rem;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    text-decoration: none;
    transition: all 0.15s ease;
}
.junior-event-item:hover {
    background: #ffffff;
    border-color: #cbd5e1;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
}
.junior-event-icon {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 0.65rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.junior-event-info {
    flex: 1;
    min-width: 0;
}
.junior-event-class {
    font-size: 0.7rem;
    font-weight: 700;
    color: #6366f1;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.15rem;
}
.junior-event-title {
    font-size: 0.88rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.3;
    margin-bottom: 0.2rem;
}
.junior-event-meta {
    font-size: 0.72rem;
    color: #64748b;
    font-weight: 500;
}
.junior-event-action-text {
    font-size: 0.75rem;
    font-weight: 700;
    color: #4f46e5;
    margin-top: 0.35rem;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

/* Notes Section */
.junior-note-card {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 0.85rem;
    padding: 0.85rem 1rem;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}
.junior-note-content {
    font-size: 0.82rem;
    color: #166534;
    font-weight: 500;
    margin: 0;
    flex: 1;
    word-break: break-word;
    line-height: 1.45;
}
.junior-note-del {
    width: 1.75rem;
    height: 1.75rem;
    border-radius: 0.45rem;
    border: 1px solid #fca5a5;
    background: #ffffff;
    color: #e11d48;
    font-size: 0.8rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
    flex-shrink: 0;
}
.junior-note-del:hover {
    background: #fee2e2;
}

/* Note form */
.junior-note-form {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.junior-note-textarea {
    width: 100%;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    padding: 0.65rem 0.85rem;
    font-size: 0.82rem;
    font-family: inherit;
    background: #f8fafc;
    color: #334155;
    resize: none;
    outline: none;
    transition: all 0.15s ease;
    box-sizing: border-box;
}
.junior-note-textarea:focus {
    border-color: #6366f1;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}
.junior-btn-save-note {
    align-self: flex-end;
    padding: 0.45rem 1rem;
    background: #4f46e5;
    color: #ffffff;
    font-size: 0.8rem;
    font-weight: 700;
    border-radius: 0.65rem;
    border: none;
    cursor: pointer;
    transition: all 0.15s ease;
}
.junior-btn-save-note:hover {
    background: #4338ca;
}

/* Empty side state */
.junior-empty-side {
    text-align: center;
    padding: 2.25rem 1rem;
    color: #94a3b8;
    font-size: 0.85rem;
    font-weight: 500;
}
</style>

<div class="junior-cal-container" x-data="{
    events: JSON.parse(document.getElementById('cal-events-json').textContent || '{}'),
    notes: JSON.parse(document.getElementById('cal-notes-json').textContent || '{}'),
    panel: {{ $calendarSelectedDate ? 'true' : 'false' }},
    selDate: @js($calendarSelectedDate),
    activeFilter: 'all',
    editingNoteId: null,
    editNoteText: '',
    noteSeq: -1,
    openDay(d) {
        this.selDate = d;
        this.panel = true;
        this.editingNoteId = null;
        this.editNoteText = '';
        @this.set('calendarSelectedDate', d);
    },
    closePanel() {
        this.panel = false;
        this.selDate = null;
        this.editingNoteId = null;
        this.editNoteText = '';
        @this.set('calendarSelectedDate', null);
    },
    dayEvents(d) {
        const all = this.events[d] || [];
        if (this.activeFilter === 'assignments') return all.filter(e => e.type.startsWith('assignment'));
        if (this.activeFilter === 'quizzes') return all.filter(e => e.type.startsWith('quiz'));
        return all;
    },
    dayNotes(d) {
        if (this.activeFilter === 'assignments' || this.activeFilter === 'quizzes') return [];
        return this.notes[d] || [];
    },
    hasAny(d) {
        return (this.events[d] && this.events[d].length > 0) || (this.notes[d] && this.notes[d].length > 0);
    },
    addNote(d) {
        const txt = $wire.calendarNoteText;
        if (!txt || !txt.trim()) return;
        const arr = this.notes[d] ? [...this.notes[d]] : [];
        arr.push({id: this.noteSeq--, content: txt.trim()});
        this.notes = {...this.notes, [d]: arr};
        @this.call('saveCalendarNote');
    },
    startEdit(note) {
        this.editingNoteId = note.id;
        this.editNoteText = note.content;
    },
    cancelEdit() {
        this.editingNoteId = null;
        this.editNoteText = '';
    },
    saveEdit(d, note) {
        const txt = this.editNoteText;
        if (!txt || !txt.trim()) return;
        const arr = (this.notes[d] || []).map(n => n.id === note.id ? {...n, content: txt.trim()} : n);
        this.notes = {...this.notes, [d]: arr};
        this.editingNoteId = null;
        this.editNoteText = '';
        @this.call('updateCalendarNote', note.id, txt.trim());
    },
    deleteNote(d, note) {
        const arr = (this.notes[d] || []).filter(n => n.id !== note.id);
        this.notes = {...this.notes, [d]: arr.length ? arr : undefined};
        @this.call('deleteCalendarNote', note.id);
    }
}">

    <!-- ── Bento Metric Cards (4 Columns) ── -->
    <div class="junior-cal-metrics">
        <div class="junior-cal-metric-card">
            <div class="junior-cal-metric-info">
                <span class="junior-cal-metric-label">Monthly Tasks</span>
                <span class="junior-cal-metric-val">{{ $asgnCount ?? 0 }}</span>
                <span class="junior-cal-metric-sub">Assignments due</span>
            </div>
            <div class="junior-cal-metric-icon metric-icon-blue">📋</div>
        </div>

        <div class="junior-cal-metric-card">
            <div class="junior-cal-metric-info">
                <span class="junior-cal-metric-label">Quizzes & Tests</span>
                <span class="junior-cal-metric-val">{{ $quizCount ?? 0 }}</span>
                <span class="junior-cal-metric-sub">Scheduled evaluations</span>
            </div>
            <div class="junior-cal-metric-icon metric-icon-purple">🧠</div>
        </div>

        <div class="junior-cal-metric-card">
            <div class="junior-cal-metric-info">
                <span class="junior-cal-metric-label">Study Notes</span>
                <span class="junior-cal-metric-val">{{ $notesCount ?? 0 }}</span>
                <span class="junior-cal-metric-sub">Personal reminders</span>
            </div>
            <div class="junior-cal-metric-icon metric-icon-green">📌</div>
        </div>

        <div class="junior-cal-metric-card">
            <div class="junior-cal-metric-info">
                <span class="junior-cal-metric-label">Active Classes</span>
                <span class="junior-cal-metric-val">{{ $allClasses->count() }}</span>
                <span class="junior-cal-metric-sub">{{ $activeGradeName ?? 'Grade' }} Subjects</span>
            </div>
            <div class="junior-cal-metric-icon metric-icon-amber">📚</div>
        </div>
    </div>

    <!-- ── Main Bento Grid + Side Inspector Layout ── -->
    <div class="junior-cal-grid-layout">
        
        <!-- Left Column: Bento Month Calendar Card -->
        <div class="junior-cal-card">
            
            <!-- Navigation & Quick Filter Strip -->
            <div class="junior-cal-nav-bar">
                <div class="junior-cal-title-wrap">
                    <h2 class="junior-cal-month-title">{{ $calMonthName }}</h2>
                </div>

                <div class="junior-cal-actions">
                    <!-- Event Filters -->
                    <button type="button" class="junior-cal-filter-pill" :class="{ 'active': activeFilter === 'all' }" @click="activeFilter = 'all'">
                        All
                    </button>
                    <button type="button" class="junior-cal-filter-pill" :class="{ 'active': activeFilter === 'assignments' }" @click="activeFilter = 'assignments'">
                        📋 Assignments
                    </button>
                    <button type="button" class="junior-cal-filter-pill" :class="{ 'active': activeFilter === 'quizzes' }" @click="activeFilter = 'quizzes'">
                        🧠 Quizzes
                    </button>
                    <button type="button" class="junior-cal-filter-pill" :class="{ 'active': activeFilter === 'notes' }" @click="activeFilter = 'notes'">
                        📝 Notes
                    </button>

                    <!-- Nav buttons -->
                    <button wire:click="prevCalendarMonth" type="button" class="junior-cal-btn-nav" title="Previous Month">‹</button>
                    <button wire:click="currentCalendarMonth" type="button" class="junior-cal-btn-today" title="Jump to Today">Today</button>
                    <button wire:click="nextCalendarMonth" type="button" class="junior-cal-btn-nav" title="Next Month">›</button>
                </div>
            </div>

            <!-- Weekday Headers -->
            <div class="junior-cal-weekdays">
                @foreach($calWeekdays as $wd)
                    <div class="junior-cal-wd">{{ $wd }}</div>
                @endforeach
            </div>

            <!-- Days Grid -->
            <div class="junior-cal-days">
                {{-- Empty days --}}
                @for($i = 0; $i < $calFirstDayOfWeek; $i++)
                    <div class="junior-cal-day empty"></div>
                @endfor

                {{-- Month days --}}
                @for($d = 1; $d <= $calDaysInMonth; $d++)
                    @php 
                        $ds = $calMonthObj->copy()->day($d)->format('Y-m-d');
                        $dayHasEvents = isset($calEvents[$ds]) && count($calEvents[$ds]) > 0;
                        $dayHasNote = isset($calNotes[$ds]);
                        $isToday = ($ds === $calToday);
                        $isSelected = ($calendarSelectedDate === $ds);
                    @endphp
                    <div class="junior-cal-day {{ $isToday ? 'today' : '' }} {{ $isSelected ? 'selected' : '' }}"
                         @click="openDay('{{ $ds }}')"
                         :class="{ 'selected': selDate === '{{ $ds }}' }">
                        
                        <div class="junior-cal-day-top">
                            <span class="junior-cal-day-num">{{ $d }}</span>
                            @if($isToday)
                                <span class="junior-cal-today-badge">Today</span>
                            @endif
                        </div>

                        <!-- Mini Chips Inside Cell -->
                        <div class="junior-cal-day-chips">
                            @if($dayHasEvents)
                                @foreach(array_slice($calEvents[$ds], 0, 2) as $ev)
                                    @php $isQuiz = str_contains($ev['type'], 'quiz'); @endphp
                                    <div class="junior-cal-pill-item {{ $isQuiz ? 'pill-quiz' : 'pill-asgn' }}"
                                         x-show="activeFilter === 'all' || (activeFilter === 'quizzes' && '{{ $isQuiz }}') || (activeFilter === 'assignments' && '!{{ $isQuiz }}')">
                                        <span>{{ $isQuiz ? '🧠' : '📋' }}</span>
                                        <span>{{ \Illuminate\Support\Str::limit($ev['title'], 10) }}</span>
                                    </div>
                                @endforeach
                                @if(count($calEvents[$ds]) > 2)
                                    <div class="junior-cal-pill-item" style="background:#f1f5f9; color:#64748b; font-size:0.6rem;">
                                        +{{ count($calEvents[$ds]) - 2 }} more
                                    </div>
                                @endif
                            @endif

                            @if($dayHasNote)
                                <div class="junior-cal-pill-item pill-note" x-show="activeFilter === 'all' || activeFilter === 'notes'">
                                    <span>📌</span>
                                    <span>Note</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endfor
            </div>

            <!-- Legend Bar -->
            <div class="junior-cal-legend">
                <div class="junior-cal-legend-item">
                    <span class="junior-cal-legend-dot" style="background:#3b82f6;"></span>
                    <span>Assignments & Homework</span>
                </div>
                <div class="junior-cal-legend-item">
                    <span class="junior-cal-legend-dot" style="background:#8b5cf6;"></span>
                    <span>Quizzes & Exams</span>
                </div>
                <div class="junior-cal-legend-item">
                    <span class="junior-cal-legend-dot" style="background:#10b981;"></span>
                    <span>Personal Notes</span>
                </div>
                <div class="junior-cal-legend-item">
                    <span class="junior-cal-legend-dot" style="background:#4f46e5;"></span>
                    <span>Current Day</span>
                </div>
            </div>
        </div>

        <!-- Right Column: Bento Side Inspector Card -->
        <div class="junior-cal-sidecard">
            <template x-if="selDate">
                <div style="display:flex; flex-direction:column; gap:1.25rem;">
                    
                    <!-- Sidecard Header -->
                    <div class="junior-sidecard-head">
                        <div class="junior-sidecard-title-wrap">
                            <h3 x-text="new Date(selDate + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric', year: 'numeric' })"></h3>
                            <p>Daily breakdown & schedule</p>
                        </div>
                        <button type="button" @click="closePanel()" class="junior-sidecard-close" title="Close Panel">✕</button>
                    </div>

                    <!-- Event Details -->
                    <template x-if="dayEvents(selDate).length > 0">
                        <div>
                            <div style="font-size:0.72rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:#94a3b8; margin-bottom:0.6rem;">
                                Scheduled Items (<span x-text="dayEvents(selDate).length"></span>)
                            </div>
                            <div class="junior-event-list">
                                <template x-for="(ev, i) in dayEvents(selDate)" :key="i">
                                    <a class="junior-event-item" :href="ev.url">
                                        <div class="junior-event-icon" :style="'background:' + (ev.type.startsWith('quiz') ? '#f5f3ff' : '#eff6ff') + '; color:' + (ev.type.startsWith('quiz') ? '#7c3aed' : '#2563eb')">
                                            <span x-text="ev.type.startsWith('quiz') ? '🧠' : '📋'"></span>
                                        </div>
                                        <div class="junior-event-info">
                                            <div class="junior-event-class" x-text="ev.class"></div>
                                            <div class="junior-event-title" x-text="ev.title"></div>
                                            <div class="junior-event-meta" x-text="ev.meta"></div>
                                            <div class="junior-event-action-text">
                                                Open <span x-text="ev.type.startsWith('quiz') ? 'Quiz' : 'Assignment'"></span> →
                                            </div>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Empty Day State -->
                    <template x-if="dayEvents(selDate).length === 0 && dayNotes(selDate).length === 0">
                        <div class="junior-empty-side">
                            <div style="font-size:2rem; margin-bottom:0.4rem;">☕</div>
                            <div>No events or deadlines for this day.</div>
                        </div>
                    </template>

                    <!-- Notes Section (Multiple Notes) -->
                    <div>
                        <div style="font-size:0.72rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:#94a3b8; margin-bottom:0.6rem; display:flex; align-items:center; gap:0.5rem;">
                            Personal Study Notes
                            <span x-show="dayNotes(selDate).length > 0" style="background:#eef2ff; color:#4f46e5; border-radius:9999px; padding:0.1rem 0.45rem; font-size:0.68rem; font-weight:800;" x-text="dayNotes(selDate).length"></span>
                        </div>

                        {{-- List of existing notes --}}
                        <template x-for="(note, idx) in dayNotes(selDate)" :key="note.id">
                            <div class="junior-note-card" style="margin-bottom:0.5rem; position:relative; padding-bottom:0.5rem;">
                                <!-- View mode -->
                                <template x-if="editingNoteId !== note.id">
                                    <div>
                                        <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:0.5rem;">
                                            <p class="junior-note-content" x-text="note.content" style="margin:0; flex:1;"></p>
                                            <div style="display:flex; gap:0.3rem; flex-shrink:0;">
                                                <button type="button" style="background:none; border:none; color:#6366f1; cursor:pointer; font-size:0.75rem; font-weight:700; padding:0.2rem 0.4rem; border-radius:0.35rem; transition:background 0.15s;" @click="startEdit(note)" @mouseenter="$el.style.background='#eef2ff'" @mouseleave="$el.style.background='none'">✏️ Edit</button>
                                                <button type="button" class="junior-note-del" title="Delete Note" @click="deleteNote(selDate, note)">✕</button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <!-- Edit mode -->
                                <template x-if="editingNoteId === note.id">
                                    <div style="display:flex; flex-direction:column; gap:0.4rem;">
                                        <textarea class="junior-note-textarea" x-model="editNoteText" rows="3"></textarea>
                                        <div style="display:flex; gap:0.4rem;">
                                            <button type="button" class="junior-btn-save-note" @click="saveEdit(selDate, note)">Save</button>
                                            <button type="button" style="padding:0.35rem 0.75rem; border-radius:0.65rem; border:1px solid #e2e8f0; background:#fff; color:#64748b; font-size:0.78rem; font-weight:700; cursor:pointer;" @click="cancelEdit()">Cancel</button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Add new note form (always visible) --}}
                        <div class="junior-note-form" style="margin-top:0.4rem;">
                            <div style="font-size:0.7rem; font-weight:700; color:#6366f1; margin-bottom:0.35rem;" x-text="dayNotes(selDate).length > 0 ? '+ Add Another Note' : '+ Add a Note'"></div>
                            <textarea class="junior-note-textarea"
                                      x-model="$wire.calendarNoteText"
                                      placeholder="Add a study goal, reminder, or homework note..."
                                      rows="3"></textarea>
                            <button type="button" class="junior-btn-save-note" @click="addNote(selDate)">
                                Save Note
                            </button>
                        </div>
                    </div>

                </div>
            </template>

            <!-- Initial state before any day is clicked -->
            <template x-if="!selDate">
                <div class="junior-empty-side">
                    <div style="font-size:2.5rem; margin-bottom:0.5rem;">📅</div>
                    <div style="font-weight:700; color:#475569; margin-bottom:0.25rem;">Select a Date</div>
                    <div>Click on any date in the calendar to view deadlines, quizzes, or write notes.</div>
                </div>
            </template>
        </div>

    </div>
</div>
