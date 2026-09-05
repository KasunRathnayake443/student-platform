{{-- ══════════════════════════════════════════════════════════════
     KIDS CALENDAR COMPONENT (Age 5-10)
     Bubbly, Playful, Rainbow-themed, Gamified & Ultra-Friendly
     ══════════════════════════════════════════════════════════════ --}}

<style>
/* ───── Kids Calendar Specialized Styles ───── */
.kids-cal-container {
    font-family: 'Nunito', 'Fredoka One', system-ui, sans-serif;
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
    width: 100%;
}

/* Hero Header */
.kids-cal-hero {
    background: linear-gradient(135deg, #7c3aed 0%, #a855f7 25%, #db2777 55%, #f97316 100%);
    border-radius: 2rem;
    padding: 2rem 2.25rem;
    color: #ffffff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 12px 36px rgba(124, 58, 237, 0.35);
}
.kids-cal-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -5%;
    width: 280px;
    height: 280px;
    background: rgba(255, 255, 255, 0.12);
    border-radius: 50%;
    pointer-events: none;
}
.kids-cal-hero::after {
    content: '';
    position: absolute;
    bottom: -40%;
    left: 8%;
    width: 200px;
    height: 200px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 50%;
    pointer-events: none;
}
.kids-cal-hero-inner {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1.5rem;
}
.kids-cal-hero-left {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}
.kids-cal-mascot {
    font-size: 4.25rem;
    line-height: 1;
    animation: kids-cal-wiggle 2.5s ease-in-out infinite;
    filter: drop-shadow(0 8px 16px rgba(0, 0, 0, 0.25));
}
@keyframes kids-cal-wiggle {
    0%, 100% { transform: translateY(0) rotate(-6deg) scale(1); }
    50% { transform: translateY(-12px) rotate(6deg) scale(1.08); }
}
.kids-cal-greeting h2 {
    font-size: 2.2rem;
    font-weight: 900;
    margin: 0 0 0.35rem 0;
    text-shadow: 2px 2px 0 rgba(0, 0, 0, 0.18);
    line-height: 1.15;
    letter-spacing: -0.01em;
}
.kids-cal-greeting p {
    font-size: 1.1rem;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.95);
    margin: 0;
}
.kids-cal-stats-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}
.kids-cal-stat-pill {
    background: rgba(255, 255, 255, 0.22);
    backdrop-filter: blur(8px);
    border: 2px solid rgba(255, 255, 255, 0.45);
    border-radius: 9999px;
    padding: 0.55rem 1.15rem;
    font-size: 0.95rem;
    font-weight: 800;
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease;
}
.kids-cal-stat-pill:hover {
    transform: scale(1.05);
}

/* Calendar Main Layout */
.kids-cal-grid-layout {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 1.75rem;
    align-items: start;
}
@media (max-width: 1050px) {
    .kids-cal-grid-layout {
        grid-template-columns: 1fr;
    }
}

/* Calendar Box */
.kids-cal-card {
    background: #ffffff;
    border-radius: 2rem;
    padding: 2rem;
    border: 4px solid #ede9fe;
    box-shadow: 0 10px 32px rgba(124, 58, 237, 0.12);
}

/* Month Navigation */
.kids-cal-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}
.kids-cal-nav-center {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.kids-cal-month-pill {
    background: linear-gradient(135deg, #fdf4ff 0%, #f3e8ff 100%);
    border: 3px solid #d8b4fe;
    border-radius: 9999px;
    padding: 0.65rem 1.75rem;
    display: flex;
    align-items: center;
    gap: 0.65rem;
    box-shadow: 0 4px 16px rgba(168, 85, 247, 0.15);
}
.kids-cal-month-title {
    font-size: 1.45rem;
    font-weight: 900;
    background: linear-gradient(135deg, #7c3aed, #db2777);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0;
    line-height: 1;
}
.kids-cal-year-badge {
    background: #7c3aed;
    color: #ffffff;
    font-size: 0.85rem;
    font-weight: 900;
    padding: 0.2rem 0.65rem;
    border-radius: 999px;
}
.kids-cal-btn-nav {
    width: 3.25rem;
    height: 3.25rem;
    border-radius: 1.25rem;
    border: 3px solid #e9d5ff;
    background: #ffffff;
    color: #7c3aed;
    font-size: 1.5rem;
    font-weight: 900;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
    box-shadow: 0 4px 14px rgba(124, 58, 237, 0.15);
}
.kids-cal-btn-nav:hover {
    background: linear-gradient(135deg, #7c3aed, #a855f7);
    border-color: #7c3aed;
    color: #ffffff;
    transform: scale(1.12) translateY(-2px);
    box-shadow: 0 8px 22px rgba(124, 58, 237, 0.35);
}
.kids-cal-btn-today {
    background: linear-gradient(135deg, #fef08a, #fde047);
    border: 3px solid #facc15;
    color: #854d0e;
    font-size: 0.95rem;
    font-weight: 900;
    padding: 0.65rem 1.25rem;
    border-radius: 9999px;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(234, 179, 8, 0.25);
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}
.kids-cal-btn-today:hover {
    transform: scale(1.08) translateY(-2px);
    box-shadow: 0 8px 20px rgba(234, 179, 8, 0.4);
}

/* Weekdays Row */
.kids-cal-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}
.kids-cal-wd {
    text-align: center;
    font-size: 0.85rem;
    font-weight: 900;
    padding: 0.6rem 0.25rem;
    border-radius: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.kids-cal-wd-0 { background: #fee2e2; color: #b91c1c; border: 2px solid #fca5a5; } /* Sun */
.kids-cal-wd-1 { background: #ffedd5; color: #c2410c; border: 2px solid #fdba74; } /* Mon */
.kids-cal-wd-2 { background: #fef9c3; color: #854d0e; border: 2px solid #fde047; } /* Tue */
.kids-cal-wd-3 { background: #dcfce7; color: #15803d; border: 2px solid #86efac; } /* Wed */
.kids-cal-wd-4 { background: #e0e7ff; color: #3730a3; border: 2px solid #a5b4fc; } /* Thu */
.kids-cal-wd-5 { background: #f3e8ff; color: #6b21a8; border: 2px solid #d8b4fe; } /* Fri */
.kids-cal-wd-6 { background: #fce7f3; color: #9d174d; border: 2px solid #f472b6; } /* Sat */

/* Days Grid */
.kids-cal-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.5rem;
}
.kids-cal-day {
    min-height: 5.25rem;
    background: #faf5ff;
    border: 3px solid #ede9fe;
    border-radius: 1.25rem;
    padding: 0.6rem 0.55rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    cursor: pointer;
    position: relative;
    transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.kids-cal-day:hover {
    transform: translateY(-4px) scale(1.04);
    border-color: #c084fc;
    background: #ffffff;
    box-shadow: 0 8px 24px rgba(168, 85, 247, 0.2);
    z-index: 2;
}
.kids-cal-day.empty {
    background: transparent;
    border-color: transparent;
    cursor: default;
    box-shadow: none;
}
.kids-cal-day.empty:hover {
    transform: none;
    box-shadow: none;
    background: transparent;
}
.kids-cal-day-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.kids-cal-day-num {
    font-size: 1.1rem;
    font-weight: 900;
    color: #4c1d95;
    line-height: 1;
}
.kids-cal-day-today-tag {
    font-size: 0.62rem;
    font-weight: 900;
    background: #f59e0b;
    color: #ffffff;
    border-radius: 999px;
    padding: 0.15rem 0.45rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    animation: kids-pulse 2s infinite;
}
@keyframes kids-pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.85; transform: scale(1.08); }
}

/* Today highlight */
.kids-cal-day.today {
    background: linear-gradient(135deg, #fef9c3 0%, #fef08a 100%);
    border: 3px solid #facc15;
    box-shadow: 0 6px 18px rgba(250, 204, 21, 0.35);
}
.kids-cal-day.today .kids-cal-day-num {
    color: #854d0e;
}

/* Selected highlight */
.kids-cal-day.selected {
    background: linear-gradient(135deg, #7c3aed 0%, #db2777 100%) !important;
    border-color: #ffffff !important;
    box-shadow: 0 10px 28px rgba(124, 58, 237, 0.45) !important;
    transform: scale(1.06) translateY(-2px);
    z-index: 3;
}
.kids-cal-day.selected .kids-cal-day-num {
    color: #ffffff !important;
}
.kids-cal-day.selected .kids-cal-badge-pill {
    border-color: rgba(255, 255, 255, 0.6) !important;
}

/* Day Badges / Event Chips inside cells */
.kids-cal-day-badges {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    margin-top: 0.35rem;
}
.kids-cal-badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.15rem 0.45rem;
    border-radius: 0.55rem;
    font-size: 0.68rem;
    font-weight: 800;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    border: 1.5px solid transparent;
}
.kids-badge-asgn {
    background: #dbeafe;
    color: #1e40af;
    border-color: #bfdbfe;
}
.kids-badge-quiz {
    background: #f3e8ff;
    color: #6b21a8;
    border-color: #d8b4fe;
}
.kids-badge-note {
    background: #dcfce7;
    color: #15803d;
    border-color: #86efac;
}

/* Legend */
.kids-cal-legend {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 1.25rem;
    margin-top: 1.5rem;
    padding-top: 1.25rem;
    border-top: 3px dashed #ede9fe;
}
.kids-cal-legend-item {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.88rem;
    font-weight: 800;
    color: #581c87;
}
.kids-cal-legend-emoji {
    font-size: 1.15rem;
}

/* Day Inspector Drawer / Side Panel */
.kids-cal-drawer {
    background: #ffffff;
    border-radius: 2rem;
    padding: 1.75rem;
    border: 4px solid #ede9fe;
    box-shadow: 0 10px 32px rgba(124, 58, 237, 0.12);
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}
.kids-drawer-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 1rem;
    border-bottom: 3px dashed #ede9fe;
}
.kids-drawer-title-wrap h3 {
    font-size: 1.35rem;
    font-weight: 900;
    color: #4c1d95;
    margin: 0 0 0.2rem 0;
    line-height: 1.2;
}
.kids-drawer-title-wrap p {
    font-size: 0.88rem;
    font-weight: 700;
    color: #a855f7;
    margin: 0;
}
.kids-drawer-close {
    width: 2.4rem;
    height: 2.4rem;
    border-radius: 0.85rem;
    border: 2px solid #fda4af;
    background: #fff1f2;
    color: #e11d48;
    font-size: 1.1rem;
    font-weight: 900;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}
.kids-drawer-close:hover {
    background: #e11d48;
    color: #ffffff;
    transform: scale(1.1);
}

/* Event cards inside drawer */
.kids-event-list {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}
.kids-event-card {
    border-radius: 1.4rem;
    padding: 1.1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    border: 3px solid;
    text-decoration: none;
    transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.kids-event-card:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.12);
}
.kids-event-card.type-asgn {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border-color: #93c5fd;
}
.kids-event-card.type-quiz {
    background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
    border-color: #d8b4fe;
}
.kids-event-emoji {
    font-size: 2.4rem;
    line-height: 1;
    flex-shrink: 0;
}
.kids-event-info {
    flex: 1;
    min-width: 0;
}
.kids-event-class-badge {
    font-size: 0.72rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b21a8;
    margin-bottom: 0.2rem;
}
.kids-event-title {
    font-size: 1.05rem;
    font-weight: 900;
    color: #1e1b4b;
    margin-bottom: 0.25rem;
    line-height: 1.25;
}
.kids-event-meta {
    font-size: 0.78rem;
    font-weight: 700;
    color: #6b7280;
}
.kids-event-btn {
    background: linear-gradient(135deg, #7c3aed, #a855f7);
    color: #ffffff;
    font-size: 0.85rem;
    font-weight: 900;
    padding: 0.5rem 0.95rem;
    border-radius: 999px;
    white-space: nowrap;
    box-shadow: 0 4px 12px rgba(124, 58, 237, 0.35);
    flex-shrink: 0;
}

/* Secret Notes section (Sticky note style) */
.kids-sticky-note-card {
    background: linear-gradient(135deg, #fef9c3 0%, #fef08a 100%);
    border: 3px solid #facc15;
    border-radius: 1.5rem;
    padding: 1.25rem;
    box-shadow: 0 8px 24px rgba(250, 204, 21, 0.25);
    position: relative;
}
.kids-sticky-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}
.kids-sticky-head h4 {
    font-size: 1.05rem;
    font-weight: 900;
    color: #854d0e;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}
.kids-sticky-content {
    font-size: 0.95rem;
    font-weight: 700;
    color: #713f12;
    line-height: 1.5;
    word-break: break-word;
    margin: 0;
}
.kids-sticky-del {
    background: #ffffff;
    border: 2px solid #fca5a5;
    color: #e11d48;
    border-radius: 0.65rem;
    padding: 0.3rem 0.65rem;
    font-size: 0.75rem;
    font-weight: 900;
    cursor: pointer;
    transition: all 0.2s ease;
}
.kids-sticky-del:hover {
    background: #e11d48;
    color: #ffffff;
}

/* Note Creator Form */
.kids-note-form {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}
.kids-note-textarea {
    width: 100%;
    border: 3px solid #ede9fe;
    border-radius: 1.15rem;
    padding: 0.75rem 1rem;
    font-size: 0.92rem;
    font-weight: 700;
    font-family: inherit;
    background: #faf5ff;
    color: #4c1d95;
    resize: none;
    outline: none;
    transition: all 0.2s ease;
    box-sizing: border-box;
}
.kids-note-textarea:focus {
    border-color: #a855f7;
    background: #ffffff;
    box-shadow: 0 0 0 4px rgba(168, 85, 247, 0.15);
}
.kids-btn-save-note {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border: 2px solid #34d399;
    color: #ffffff;
    font-size: 0.95rem;
    font-weight: 900;
    padding: 0.65rem 1.25rem;
    border-radius: 9999px;
    cursor: pointer;
    box-shadow: 0 6px 18px rgba(16, 185, 129, 0.3);
    transition: all 0.2s ease;
    align-self: flex-end;
}
.kids-btn-save-note:hover {
    transform: scale(1.06) translateY(-2px);
    box-shadow: 0 8px 22px rgba(16, 185, 129, 0.45);
}

/* Empty State */
.kids-drawer-empty {
    text-align: center;
    padding: 2rem 1rem;
    background: #faf5ff;
    border-radius: 1.5rem;
    border: 3px dashed #d8b4fe;
}
.kids-drawer-empty-emoji {
    font-size: 3.5rem;
    margin-bottom: 0.5rem;
    animation: kids-cal-wiggle 3s infinite;
}
.kids-drawer-empty h4 {
    font-size: 1.25rem;
    font-weight: 900;
    color: #6b21a8;
    margin: 0 0 0.35rem 0;
}
.kids-drawer-empty p {
    font-size: 0.92rem;
    font-weight: 700;
    color: #a855f7;
    margin: 0;
}
</style>

<div class="kids-cal-container" x-data="{
    events: JSON.parse(document.getElementById('cal-events-json').textContent || '{}'),
    notes: JSON.parse(document.getElementById('cal-notes-json').textContent || '{}'),
    panel: {{ $calendarSelectedDate ? 'true' : 'false' }},
    selDate: @js($calendarSelectedDate),
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
    dayEvents(d) { return this.events[d] || []; },
    dayNotes(d) { return this.notes[d] || []; },
    hasAny(d) { return (this.events[d] && this.events[d].length > 0) || (this.notes[d] && this.notes[d].length > 0); },
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

    <!-- ── Whimsical Hero Banner ── -->
    <div class="kids-cal-hero">
        <div class="kids-cal-hero-inner">
            <div class="kids-cal-hero-left">
                <div class="kids-cal-mascot">🗓️</div>
                <div class="kids-cal-greeting">
                    <h2>Your Magical Calendar, {{ $firstName }}! ✨</h2>
                    <p>Click any day to see homework missions, fun quizzes, and your secret notes! 🌟</p>
                </div>
            </div>
            <div class="kids-cal-stats-row">
                <div class="kids-cal-stat-pill">
                    <span>🎈</span>
                    <span>{{ $asgnCount ?? 0 }} Homework Missions</span>
                </div>
                <div class="kids-cal-stat-pill">
                    <span>🧠</span>
                    <span>{{ $quizCount ?? 0 }} Quizzes to Play</span>
                </div>
                <div class="kids-cal-stat-pill">
                    <span>📝</span>
                    <span>{{ $notesCount ?? 0 }} Secret Notes</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Calendar Grid + Day Drawer Layout ── -->
    <div class="kids-cal-grid-layout">
        
        <!-- Left Column: Big Interactive Month Grid -->
        <div class="kids-cal-card">
            
            <!-- Month Navigation Bar -->
            <div class="kids-cal-nav">
                <button wire:click="prevCalendarMonth" type="button" class="kids-cal-btn-nav" title="Previous Month">‹</button>
                
                <div class="kids-cal-nav-center">
                    <div class="kids-cal-month-pill">
                        <span style="font-size: 1.4rem;">🌈</span>
                        <h3 class="kids-cal-month-title">{{ $calMonthObj->format('F') }}</h3>
                        <span class="kids-cal-year-badge">{{ $calYearLabel }}</span>
                    </div>
                    
                    <button wire:click="currentCalendarMonth" type="button" class="kids-cal-btn-today" title="Jump to Today">
                        <span>🌟</span>
                        <span>Today</span>
                    </button>
                </div>

                <button wire:click="nextCalendarMonth" type="button" class="kids-cal-btn-nav" title="Next Month">›</button>
            </div>

            <!-- Rainbow Weekday Header -->
            <div class="kids-cal-weekdays">
                @foreach($calWeekdays as $idx => $wd)
                    <div class="kids-cal-wd kids-cal-wd-{{ $idx }}">
                        {{ $wd }}
                    </div>
                @endforeach
            </div>

            <!-- Days Grid -->
            <div class="kids-cal-days">
                {{-- Empty days from previous month --}}
                @for($i = 0; $i < $calFirstDayOfWeek; $i++)
                    <div class="kids-cal-day empty"></div>
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
                    <div class="kids-cal-day {{ $isToday ? 'today' : '' }} {{ $isSelected ? 'selected' : '' }}"
                         @click="openDay('{{ $ds }}')"
                         :class="{ 'selected': selDate === '{{ $ds }}' }">
                        
                        <div class="kids-cal-day-top">
                            <span class="kids-cal-day-num">{{ $d }}</span>
                            @if($isToday)
                                <span class="kids-cal-day-today-tag">Today! 🌟</span>
                            @endif
                        </div>

                        <!-- Micro Event Badges inside day cell -->
                        <div class="kids-cal-day-badges">
                            @if($dayHasEvents)
                                @foreach(array_slice($calEvents[$ds], 0, 2) as $ev)
                                    @php $isQuiz = str_contains($ev['type'], 'quiz'); @endphp
                                    <div class="kids-cal-badge-pill {{ $isQuiz ? 'kids-badge-quiz' : 'kids-badge-asgn' }}">
                                        <span>{{ $isQuiz ? '🧠' : '📋' }}</span>
                                        <span>{{ \Illuminate\Support\Str::limit($ev['title'], 10) }}</span>
                                    </div>
                                @endforeach
                                @if(count($calEvents[$ds]) > 2)
                                    <div class="kids-cal-badge-pill" style="background:#e0e7ff; color:#4338ca; font-size:0.6rem;">
                                        +{{ count($calEvents[$ds]) - 2 }} more
                                    </div>
                                @endif
                            @endif

                            @if($dayHasNote)
                                <div class="kids-cal-badge-pill kids-badge-note">
                                    <span>📝</span>
                                    <span>Note</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endfor
            </div>

            <!-- Legend Bar -->
            <div class="kids-cal-legend">
                <div class="kids-cal-legend-item">
                    <span class="kids-cal-legend-emoji">📋</span>
                    <span>Homework Missions</span>
                </div>
                <div class="kids-cal-legend-item">
                    <span class="kids-cal-legend-emoji">🧠</span>
                    <span>Quizzes & Games</span>
                </div>
                <div class="kids-cal-legend-item">
                    <span class="kids-cal-legend-emoji">📝</span>
                    <span>Secret Notes</span>
                </div>
                <div class="kids-cal-legend-item">
                    <span class="kids-cal-legend-emoji">🌟</span>
                    <span>Today's Date</span>
                </div>
            </div>
        </div>

        <!-- Right Column: Interactive Day Inspector Card -->
        <div class="kids-cal-drawer">
            <template x-if="selDate">
                <div style="display:flex; flex-direction:column; gap:1.25rem;">
                    
                    <!-- Drawer Header with formatted date -->
                    <div class="kids-drawer-head">
                        <div class="kids-drawer-title-wrap">
                            <h3 x-text="new Date(selDate + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' })"></h3>
                            <p>What's happening on this magical day! ✨</p>
                        </div>
                        <button type="button" @click="closePanel()" class="kids-drawer-close" title="Close Panel">✕</button>
                    </div>

                    <!-- Events for the Day -->
                    <template x-if="dayEvents(selDate).length > 0">
                        <div>
                            <div style="font-size:0.88rem; font-weight:900; color:#7c3aed; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.65rem;">
                                🚀 Missions on this Day
                            </div>
                            <div class="kids-event-list">
                                <template x-for="(ev, i) in dayEvents(selDate)" :key="i">
                                    <a class="kids-event-card" :class="ev.type.startsWith('quiz') ? 'type-quiz' : 'type-asgn'" :href="ev.url">
                                        <div class="kids-event-emoji" x-text="ev.type.startsWith('quiz') ? '🧠' : '📋'"></div>
                                        <div class="kids-event-info">
                                            <div class="kids-event-class-badge" x-text="'📘 ' + ev.class"></div>
                                            <div class="kids-event-title" x-text="ev.title"></div>
                                            <div class="kids-event-meta" x-text="ev.meta"></div>
                                        </div>
                                        <div class="kids-event-btn" x-text="ev.type.startsWith('quiz') ? 'Play Quiz 🎮' : 'Start Mission 🚀'"></div>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Empty Day State -->
                    <template x-if="dayEvents(selDate).length === 0 && dayNotes(selDate).length === 0">
                        <div class="kids-drawer-empty">
                            <div class="kids-drawer-empty-emoji">🎈</div>
                            <h4>Yay! No Homework!</h4>
                            <p>Enjoy your free time, draw a picture, or read a storybook! 🌟</p>
                        </div>
                    </template>

                    <!-- Secret Sticky Notes Section (Multiple) -->
                    <div>
                        <div style="font-size:0.88rem; font-weight:900; color:#7c3aed; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.65rem;">
                            🖍️ My Secret Day Notes
                            <span x-show="dayNotes(selDate).length > 0" style="font-size:0.75rem; font-weight:700; background:#ede9fe; color:#7c3aed; border-radius:9999px; padding:0.1rem 0.5rem; margin-left:0.4rem;" x-text="dayNotes(selDate).length + ' note' + (dayNotes(selDate).length > 1 ? 's' : '')"></span>
                        </div>

                        {{-- List of existing notes --}}
                        <template x-for="(note, idx) in dayNotes(selDate)" :key="note.id">
                            <div class="kids-sticky-note-card" style="margin-bottom:0.6rem;">
                                <!-- View mode -->
                                <template x-if="editingNoteId !== note.id">
                                    <div>
                                        <div class="kids-sticky-head">
                                            <h4><span>📌</span> <span>Note <span x-text="idx + 1"></span>:</span></h4>
                                            <div style="display:flex; gap:0.4rem;">
                                                <button type="button" class="kids-sticky-del" style="background:#ede9fe; color:#7c3aed; border-color:#ddd6fe;" @click="startEdit(note)">✏️ Edit</button>
                                                <button type="button" class="kids-sticky-del" @click="deleteNote(selDate, note)">🗑️ Remove</button>
                                            </div>
                                        </div>
                                        <p class="kids-sticky-content" x-text="note.content"></p>
                                    </div>
                                </template>
                                <!-- Edit mode -->
                                <template x-if="editingNoteId === note.id">
                                    <div>
                                        <div class="kids-sticky-head" style="margin-bottom:0.5rem;">
                                            <h4><span>✏️</span> <span>Edit Note:</span></h4>
                                            <button type="button" class="kids-sticky-del" @click="cancelEdit()">✕ Cancel</button>
                                        </div>
                                        <textarea class="kids-note-textarea" x-model="editNoteText" rows="3"></textarea>
                                        <button type="button" class="kids-btn-save-note" style="margin-top:0.5rem;" @click="saveEdit(selDate, note)">Save Changes ✨</button>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Add new note form (always visible) --}}
                        <div class="kids-note-form">
                            <div style="font-size:0.78rem; font-weight:800; color:#a855f7; margin-bottom:0.4rem;">
                                <span x-text="dayNotes(selDate).length > 0 ? '+ Add Another Note' : '+ Write a Note'"></span> 🖍️
                            </div>
                            <textarea class="kids-note-textarea"
                                      x-model="$wire.calendarNoteText"
                                      placeholder="Write a reminder, drawing idea, or note here... ✨"
                                      rows="3"></textarea>
                            <button type="button" class="kids-btn-save-note" @click="addNote(selDate)">
                                Save Note ✨
                            </button>
                        </div>
                    </div>

                </div>
            </template>

            <!-- Initial state before any day is clicked -->
            <template x-if="!selDate">
                <div class="kids-drawer-empty" style="background:#ffffff; border-style:solid; border-color:#ede9fe;">
                    <div class="kids-drawer-empty-emoji">🗓️</div>
                    <h4>Pick a Day!</h4>
                    <p>Click any date on the calendar to see all your homework, quizzes, and notes! ✨</p>
                </div>
            </template>
        </div>

    </div>
</div>
