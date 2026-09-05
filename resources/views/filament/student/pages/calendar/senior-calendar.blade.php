{{-- ══════════════════════════════════════════════════════════════
     SENIOR CALENDAR COMPONENT (Age 16+)
     Executive Academic Scheduler, Dual-View (Grid + Timeline Agenda)
     ══════════════════════════════════════════════════════════════ --}}

<style>
/* ───── Senior Calendar Specialized Styles ───── */
.senior-cal-container {
    font-family: 'Inter', system-ui, sans-serif;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    width: 100%;
    color: #334155;
}

/* ── KPI Row (4 Cards) ── */
.senior-cal-kpi-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}
@media (max-width: 1100px) {
    .senior-cal-kpi-row { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 600px) {
    .senior-cal-kpi-row { grid-template-columns: 1fr; }
}

.senior-cal-kpi {
    background: #ffffff;
    border-radius: 0.875rem;
    padding: 1.25rem 1.5rem;
    border: 1px solid #e2e8f0;
    border-top: 3px solid;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.senior-cal-kpi:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
}
.senior-cal-kpi.kpi-urgent  { border-top-color: #ef4444; }
.senior-cal-kpi.kpi-course  { border-top-color: #3b82f6; }
.senior-cal-kpi.kpi-quiz    { border-top-color: #7c3aed; }
.senior-cal-kpi.kpi-notes   { border-top-color: #10b981; }

.senior-cal-kpi-label {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #64748b;
    margin-bottom: 0.4rem;
}
.senior-cal-kpi-val {
    font-size: 2rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1;
}
.senior-cal-kpi-sub {
    font-size: 0.75rem;
    color: #64748b;
    margin-top: 0.35rem;
    font-weight: 500;
}

/* ── Split Layout: Grid on Left, Timeline Agenda on Right ── */
.senior-cal-split {
    display: grid;
    grid-template-columns: 1.35fr 1fr;
    gap: 1.5rem;
    align-items: start;
}
@media (max-width: 1080px) {
    .senior-cal-split { grid-template-columns: 1fr; }
}

/* ── Calendar Card Container ── */
.senior-cal-card {
    background: #ffffff;
    border-radius: 0.875rem;
    border: 1px solid #e2e8f0;
    padding: 1.35rem;
    display: flex;
    flex-direction: column;
    gap: 1.15rem;
}

/* ── Academic Navigation Bar ── */
.senior-cal-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.85rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid #e2e8f0;
}
.senior-cal-title-block {
    display: flex;
    align-items: baseline;
    gap: 0.75rem;
}
.senior-cal-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
}
.senior-cal-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.senior-cal-btn-nav {
    width: 2rem;
    height: 2rem;
    border-radius: 0.5rem;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #475569;
    font-size: 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
}
.senior-cal-btn-nav:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #0f172a;
}
.senior-cal-btn-today {
    padding: 0.35rem 0.75rem;
    border-radius: 0.5rem;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #475569;
    font-size: 0.78rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
}
.senior-cal-btn-today:hover {
    background: #f1f5f9;
    color: #0f172a;
}

/* ── Filter Selects ── */
.senior-cal-filter-select {
    padding: 0.35rem 0.65rem;
    border-radius: 0.5rem;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #334155;
    font-size: 0.78rem;
    font-weight: 600;
    outline: none;
    font-family: inherit;
    cursor: pointer;
}
.senior-cal-filter-select:focus {
    border-color: #7c3aed;
}

/* ── Weekday Header ── */
.senior-cal-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 0.4rem;
}
.senior-cal-wd {
    text-align: center;
    font-size: 0.7rem;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

/* ── Days Grid ── */
.senior-cal-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    border: 1px solid #e2e8f0;
    border-radius: 0.65rem;
    overflow: hidden;
}
.senior-cal-day {
    min-height: 4.4rem;
    background: #ffffff;
    border-right: 1px solid #f1f5f9;
    border-bottom: 1px solid #f1f5f9;
    padding: 0.4rem 0.45rem;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    cursor: pointer;
    position: relative;
    transition: background 0.15s ease;
}
.senior-cal-day:nth-child(7n) {
    border-right: none;
}
.senior-cal-day:hover {
    background: #f8fafc;
}
.senior-cal-day.empty {
    background: #fafafa;
    cursor: default;
}
.senior-cal-day-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.25rem;
}
.senior-cal-day-num {
    font-size: 0.8rem;
    font-weight: 600;
    color: #334155;
}

/* Today highlight */
.senior-cal-day.today {
    background: #f5f3ff;
}
.senior-cal-day.today .senior-cal-day-num {
    color: #7c3aed;
    font-weight: 800;
}
.senior-cal-today-tag {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #7c3aed;
}

/* Selected highlight */
.senior-cal-day.selected {
    background: #ede9fe !important;
    box-shadow: inset 0 0 0 2px #7c3aed;
}
.senior-cal-day.selected .senior-cal-day-num {
    color: #5b21b6;
    font-weight: 800;
}

/* Mini Academic Event Tags inside Cell */
.senior-cal-event-chips {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    overflow: hidden;
}
.senior-cal-chip {
    display: block;
    font-size: 0.65rem;
    font-weight: 600;
    padding: 0.15rem 0.35rem;
    border-radius: 0.3rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.2;
    border-left: 2.5px solid transparent;
}
.senior-chip-asgn {
    background: #eff6ff;
    color: #1e40af;
    border-left-color: #3b82f6;
}
.senior-chip-quiz {
    background: #f5f3ff;
    color: #6b21a8;
    border-left-color: #8b5cf6;
}
.senior-chip-note {
    background: #f0fdf4;
    color: #166534;
    border-left-color: #10b981;
}

/* ── Legend Bar ── */
.senior-cal-legend {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    flex-wrap: wrap;
    padding-top: 0.65rem;
    border-top: 1px solid #f1f5f9;
}
.senior-cal-legend-item {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.72rem;
    font-weight: 600;
    color: #64748b;
}
.senior-cal-legend-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
}

/* ── Right Column: Academic Agenda & Day Inspector ── */
.senior-cal-agenda-panel {
    background: #ffffff;
    border-radius: 0.875rem;
    border: 1px solid #e2e8f0;
    padding: 1.35rem;
    display: flex;
    flex-direction: column;
    gap: 1.35rem;
}
.senior-agenda-tab-bar {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 0.65rem;
}
.senior-agenda-tab-btn {
    padding: 0.35rem 0.75rem;
    font-size: 0.78rem;
    font-weight: 700;
    border-radius: 0.45rem;
    border: none;
    background: transparent;
    color: #64748b;
    cursor: pointer;
    transition: all 0.15s ease;
}
.senior-agenda-tab-btn:hover {
    color: #0f172a;
    background: #f1f5f9;
}
.senior-agenda-tab-btn.active {
    background: #f5f3ff;
    color: #7c3aed;
}

/* Selected Date Detail View */
.senior-day-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.senior-day-header h3 {
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}
.senior-day-header p {
    font-size: 0.75rem;
    color: #64748b;
    margin: 0.15rem 0 0 0;
}

/* Event List */
.senior-agenda-list {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}
.senior-agenda-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.65rem;
    padding: 0.75rem 0.85rem;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    text-decoration: none;
    transition: all 0.15s ease;
}
.senior-agenda-item:hover {
    background: #ffffff;
    border-color: #cbd5e1;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
}
.senior-agenda-tag {
    font-size: 0.68rem;
    font-weight: 700;
    padding: 0.2rem 0.5rem;
    border-radius: 0.35rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    flex-shrink: 0;
    margin-top: 0.1rem;
}
.senior-tag-asgn { background: #dbeafe; color: #1e40af; }
.senior-tag-quiz { background: #ede9fe; color: #6b21a8; }
.senior-agenda-content {
    flex: 1;
    min-width: 0;
}
.senior-agenda-title {
    font-size: 0.85rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.3;
    margin-bottom: 0.2rem;
}
.senior-agenda-meta {
    font-size: 0.72rem;
    color: #64748b;
}

/* Academic Note Box */
.senior-note-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.65rem;
    padding: 0.85rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.senior-note-content {
    font-size: 0.82rem;
    color: #1e293b;
    line-height: 1.5;
    margin: 0;
    word-break: break-word;
}
.senior-note-del-btn {
    align-self: flex-start;
    font-size: 0.72rem;
    font-weight: 600;
    color: #e11d48;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0;
    margin-top: 0.25rem;
}
.senior-note-del-btn:hover {
    text-decoration: underline;
}

/* Note form */
.senior-note-textarea {
    width: 100%;
    border: 1px solid #cbd5e1;
    border-radius: 0.5rem;
    padding: 0.6rem 0.75rem;
    font-size: 0.82rem;
    font-family: inherit;
    background: #ffffff;
    color: #1e293b;
    resize: none;
    outline: none;
    transition: all 0.15s ease;
    box-sizing: border-box;
}
.senior-note-textarea:focus {
    border-color: #7c3aed;
    box-shadow: 0 0 0 2px rgba(124, 58, 237, 0.1);
}
.senior-note-save-btn {
    align-self: flex-end;
    padding: 0.4rem 0.85rem;
    background: #0f172a;
    color: #ffffff;
    font-size: 0.78rem;
    font-weight: 600;
    border-radius: 0.45rem;
    border: none;
    cursor: pointer;
    transition: all 0.15s ease;
}
.senior-note-save-btn:hover {
    background: #334155;
}

/* Empty View */
.senior-agenda-empty {
    text-align: center;
    padding: 2rem 1rem;
    color: #94a3b8;
    font-size: 0.82rem;
}
</style>

<div class="senior-cal-container" x-data="{
    events: JSON.parse(document.getElementById('cal-events-json').textContent || '{}'),
    notes: JSON.parse(document.getElementById('cal-notes-json').textContent || '{}'),
    panel: {{ $calendarSelectedDate ? 'true' : 'false' }},
    selDate: @js($calendarSelectedDate),
    agendaTab: 'day',
    classFilter: 'all',
    typeFilter: 'all',
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
        return all.filter(e => {
            const matchesClass = (this.classFilter === 'all' || e.class === this.classFilter);
            const matchesType = (this.typeFilter === 'all' || 
                (this.typeFilter === 'assignments' && e.type.startsWith('assignment')) ||
                (this.typeFilter === 'quizzes' && e.type.startsWith('quiz'))
            );
            return matchesClass && matchesType;
        });
    },
    dayNotes(d) {
        return this.notes[d] || [];
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

    <!-- ── Academic KPI Status Row (4 Cards) ── -->
    <div class="senior-cal-kpi-row">
        <div class="senior-cal-kpi kpi-urgent">
            <div>
                <div class="senior-cal-kpi-label">Urgent (48h)</div>
                <div class="senior-cal-kpi-val">{{ $urgentCount ?? 0 }}</div>
            </div>
            <div class="senior-cal-kpi-sub">Imminent deadlines</div>
        </div>

        <div class="senior-cal-kpi kpi-course">
            <div>
                <div class="senior-cal-kpi-label">Active Coursework</div>
                <div class="senior-cal-kpi-val">{{ $asgnCount ?? 0 }}</div>
            </div>
            <div class="senior-cal-kpi-sub">Assignments this month</div>
        </div>

        <div class="senior-cal-kpi kpi-quiz">
            <div>
                <div class="senior-cal-kpi-label">Evaluations & Exams</div>
                <div class="senior-cal-kpi-val">{{ $quizCount ?? 0 }}</div>
            </div>
            <div class="senior-cal-kpi-sub">Scheduled quizzes</div>
        </div>

        <div class="senior-cal-kpi kpi-notes">
            <div>
                <div class="senior-cal-kpi-label">Academic Agenda</div>
                <div class="senior-cal-kpi-val">{{ $notesCount ?? 0 }}</div>
            </div>
            <div class="senior-cal-kpi-sub">Active personal notes</div>
        </div>
    </div>

    <!-- ── Split View: High-Density Calendar Grid + Timeline Agenda ── -->
    <div class="senior-cal-split">
        
        <!-- Left Column: Academic Calendar Grid -->
        <div class="senior-cal-card">
            
            <!-- Academic Top Navigation Bar -->
            <div class="senior-cal-topbar">
                <div class="senior-cal-title-block">
                    <h2 class="senior-cal-title">{{ $calMonthName }}</h2>
                    <span style="font-size:0.75rem; color:#64748b; font-weight:600;">{{ $calDaysInMonth }} Days</span>
                </div>

                <div class="senior-cal-controls">
                    <!-- Class Filter -->
                    <select class="senior-cal-filter-select" x-model="classFilter">
                        <option value="all">All Subjects</option>
                        @foreach($allClasses as $cls)
                            <option value="{{ $cls->name }}">{{ $cls->name }}</option>
                        @endforeach
                    </select>

                    <!-- Type Filter -->
                    <select class="senior-cal-filter-select" x-model="typeFilter">
                        <option value="all">All Items</option>
                        <option value="assignments">📋 Assignments</option>
                        <option value="quizzes">🧠 Quizzes</option>
                    </select>

                    <!-- Nav buttons -->
                    <button wire:click="prevCalendarMonth" type="button" class="senior-cal-btn-nav" title="Previous Month">‹</button>
                    <button wire:click="currentCalendarMonth" type="button" class="senior-cal-btn-today" title="Jump to Today">Today</button>
                    <button wire:click="nextCalendarMonth" type="button" class="senior-cal-btn-nav" title="Next Month">›</button>
                </div>
            </div>

            <!-- Weekday Header -->
            <div class="senior-cal-weekdays">
                @foreach($calWeekdays as $wd)
                    <div class="senior-cal-wd">{{ $wd }}</div>
                @endforeach
            </div>

            <!-- Days Grid -->
            <div class="senior-cal-days">
                {{-- Empty days --}}
                @for($i = 0; $i < $calFirstDayOfWeek; $i++)
                    <div class="senior-cal-day empty"></div>
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
                    <div class="senior-cal-day {{ $isToday ? 'today' : '' }} {{ $isSelected ? 'selected' : '' }}"
                         @click="openDay('{{ $ds }}')"
                         :class="{ 'selected': selDate === '{{ $ds }}' }">
                        
                        <div class="senior-cal-day-header">
                            <span class="senior-cal-day-num">{{ $d }}</span>
                            @if($isToday)
                                <span class="senior-cal-today-tag" title="Today"></span>
                            @endif
                        </div>

                        <!-- Micro Event Chips -->
                        <div class="senior-cal-event-chips">
                            @if($dayHasEvents)
                                @foreach(array_slice($calEvents[$ds], 0, 2) as $ev)
                                    @php $isQuiz = str_contains($ev['type'], 'quiz'); @endphp
                                    <div class="senior-cal-chip {{ $isQuiz ? 'senior-chip-quiz' : 'senior-chip-asgn' }}"
                                         x-show="classFilter === 'all' || classFilter === '{{ $ev['class'] }}'">
                                        <span>{{ \Illuminate\Support\Str::limit($ev['class'], 8) }}:</span>
                                        <span>{{ \Illuminate\Support\Str::limit($ev['title'], 10) }}</span>
                                    </div>
                                @endforeach
                                @if(count($calEvents[$ds]) > 2)
                                    <span style="font-size:0.6rem; color:#64748b; font-weight:600; padding-left:2px;">
                                        +{{ count($calEvents[$ds]) - 2 }} more
                                    </span>
                                @endif
                            @endif

                            @if($dayHasNote)
                                <div class="senior-cal-chip senior-chip-note">
                                    <span>📌 Agenda Note</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endfor
            </div>

            <!-- Legend Bar -->
            <div class="senior-cal-legend">
                <div class="senior-cal-legend-item">
                    <span class="senior-cal-legend-dot" style="background:#3b82f6;"></span>
                    <span>Coursework Deadline</span>
                </div>
                <div class="senior-cal-legend-item">
                    <span class="senior-cal-legend-dot" style="background:#8b5cf6;"></span>
                    <span>Quiz / Assessment</span>
                </div>
                <div class="senior-cal-legend-item">
                    <span class="senior-cal-legend-dot" style="background:#10b981;"></span>
                    <span>Academic Note</span>
                </div>
                <div class="senior-cal-legend-item">
                    <span class="senior-cal-legend-dot" style="background:#7c3aed;"></span>
                    <span>Today</span>
                </div>
            </div>
        </div>

        <!-- Right Column: Dual Agenda & Day Inspector -->
        <div class="senior-cal-agenda-panel">
            
            <!-- Tab switch: Day Inspector vs Month Timeline -->
            <div class="senior-agenda-tab-bar">
                <button type="button" class="senior-agenda-tab-btn" :class="{ 'active': agendaTab === 'day' }" @click="agendaTab = 'day'">
                    Selected Date Inspector
                </button>
                <button type="button" class="senior-agenda-tab-btn" :class="{ 'active': agendaTab === 'timeline' }" @click="agendaTab = 'timeline'">
                    Monthly Deadlines Timeline ({{ count($calEvents) }})
                </button>
            </div>

            <!-- TAB 1: Selected Day Inspector -->
            <div x-show="agendaTab === 'day'">
                <template x-if="selDate">
                    <div style="display:flex; flex-direction:column; gap:1.25rem;">
                        <div class="senior-day-header">
                            <div>
                                <h3 x-text="new Date(selDate + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric', year: 'numeric' })"></h3>
                                <p>Course schedule & submission deadlines</p>
                            </div>
                            <button type="button" @click="closePanel()" style="background:none; border:none; color:#94a3b8; font-size:1.1rem; cursor:pointer;" title="Close">✕</button>
                        </div>

                        <!-- Events on Selected Date -->
                        <template x-if="dayEvents(selDate).length > 0">
                            <div>
                                <div style="font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#94a3b8; margin-bottom:0.5rem;">
                                    Scheduled Items
                                </div>
                                <div class="senior-agenda-list">
                                    <template x-for="(ev, i) in dayEvents(selDate)" :key="i">
                                        <a class="senior-agenda-item" :href="ev.url">
                                            <span class="senior-agenda-tag" :class="ev.type.startsWith('quiz') ? 'senior-tag-quiz' : 'senior-tag-asgn'" x-text="ev.type.startsWith('quiz') ? 'Quiz' : 'Task'"></span>
                                            <div class="senior-agenda-content">
                                                <div style="font-size:0.7rem; color:#6366f1; font-weight:700;" x-text="ev.class"></div>
                                                <div class="senior-agenda-title" x-text="ev.title"></div>
                                                <div class="senior-agenda-meta" x-text="ev.meta"></div>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template x-if="dayEvents(selDate).length === 0">
                            <div class="senior-agenda-empty">
                                No course deadlines scheduled for this date.
                            </div>
                        </template>

                        <!-- Academic Notes for Selected Date -->
                        <div style="border-top:1px solid #e2e8f0; padding-top:1rem;">
                            <div style="font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#94a3b8; margin-bottom:0.5rem; display:flex; align-items:center; gap:0.5rem;">
                                Date Agenda Notes
                                <span x-show="dayNotes(selDate).length > 0" style="background:#f0fdf4; color:#16a34a; border-radius:9999px; padding:0.1rem 0.45rem; font-size:0.68rem; font-weight:800;" x-text="dayNotes(selDate).length"></span>
                            </div>

                            {{-- List of existing notes --}}
                            <template x-for="(note, idx) in dayNotes(selDate)" :key="note.id">
                                <div class="senior-note-box" style="margin-bottom:0.5rem;">
                                    <!-- View mode -->
                                    <template x-if="editingNoteId !== note.id">
                                        <div style="display:flex; align-items:flex-start; gap:0.5rem;">
                                            <p class="senior-note-content" x-text="note.content" style="flex:1; margin:0;"></p>
                                            <div style="display:flex; gap:0.3rem; flex-shrink:0;">
                                                <button type="button" style="background:none; border:none; color:#7c3aed; cursor:pointer; font-size:0.72rem; font-weight:700; padding:0.2rem 0.4rem; border-radius:0.35rem;" @click="startEdit(note)">✏️ Edit</button>
                                                <button type="button" class="senior-note-del-btn" @click="deleteNote(selDate, note)">Remove</button>
                                            </div>
                                        </div>
                                    </template>
                                    <!-- Edit mode -->
                                    <template x-if="editingNoteId === note.id">
                                        <div style="display:flex; flex-direction:column; gap:0.4rem;">
                                            <textarea class="senior-note-textarea" x-model="editNoteText" rows="3"></textarea>
                                            <div style="display:flex; gap:0.4rem;">
                                                <button type="button" class="senior-note-save-btn" @click="saveEdit(selDate, note)">Save</button>
                                                <button type="button" style="padding:0.3rem 0.65rem; border:1px solid #e2e8f0; border-radius:0.4rem; background:#fff; color:#64748b; font-size:0.75rem; font-weight:600; cursor:pointer;" @click="cancelEdit()">Cancel</button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- Add new note form (always visible) --}}
                            <div style="display:flex; flex-direction:column; gap:0.5rem; margin-top:0.25rem;">
                                <div style="font-size:0.7rem; font-weight:600; color:#7c3aed;" x-text="dayNotes(selDate).length > 0 ? '+ Add Another Note' : '+ Add a Note'"></div>
                                <textarea class="senior-note-textarea"
                                          x-model="$wire.calendarNoteText"
                                          placeholder="Add study plan, reminder, or notes for this date..."
                                          rows="3"></textarea>
                                <button type="button" class="senior-note-save-btn" @click="addNote(selDate)">
                                    Save Note
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="!selDate">
                    <div class="senior-agenda-empty">
                        <div style="font-size:2rem; margin-bottom:0.4rem;">🗓️</div>
                        <div style="font-weight:600; color:#334155;">Select a Date</div>
                        <div style="font-size:0.75rem; margin-top:0.2rem;">Click on any calendar day to inspect schedule and write notes.</div>
                    </div>
                </template>
            </div>

            <!-- TAB 2: Monthly Deadlines Timeline -->
            <div x-show="agendaTab === 'timeline'">
                <div style="display:flex; flex-direction:column; gap:0.75rem;">
                    @php
                        $sortedTimeline = [];
                        foreach ($calEvents as $dateStr => $dayEvs) {
                            foreach ($dayEvs as $ev) {
                                $sortedTimeline[] = array_merge($ev, ['date' => $dateStr]);
                            }
                        }
                    @endphp

                    @if(empty($sortedTimeline))
                        <div class="senior-agenda-empty">
                            No deadlines recorded for {{ $calMonthName }}.
                        </div>
                    @else
                        <div class="senior-agenda-list">
                            @foreach($sortedTimeline as $timelineItem)
                                @php 
                                    $isQuiz = str_contains($timelineItem['type'], 'quiz');
                                    $itemDate = \Carbon\Carbon::parse($timelineItem['date']);
                                    $isPast = $itemDate->isPast() && ! $itemDate->isToday();
                                @endphp
                                <a class="senior-agenda-item" href="{{ $timelineItem['url'] }}" style="{{ $isPast ? 'opacity: 0.6;' : '' }}">
                                    <span class="senior-agenda-tag {{ $isQuiz ? 'senior-tag-quiz' : 'senior-tag-asgn' }}">
                                        {{ $itemDate->format('M j') }}
                                    </span>
                                    <div class="senior-agenda-content">
                                        <div style="font-size:0.7rem; color:#6366f1; font-weight:700;">{{ $timelineItem['class'] }}</div>
                                        <div class="senior-agenda-title">{{ $timelineItem['title'] }}</div>
                                        <div class="senior-agenda-meta">{{ $timelineItem['meta'] }}</div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>

    </div>
</div>
