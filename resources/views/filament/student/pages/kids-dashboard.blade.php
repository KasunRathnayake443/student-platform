@php
    use Illuminate\Support\Carbon;

    // ── Data for the active context ──────────────────────────────────────
    $activeClasses    = $activeContext ? $activeContext['classes'] : collect();
    $activeSchoolName = $activeContext ? ($activeContext['school']->name ?? 'My School') : 'My School';
    $activeGradeName  = $activeContext ? ($activeContext['grade']->name  ?? 'My Grade')  : 'My Grade';

    // ── Stars: quiz average as a star count out of 10 ────────────────────
    $quizAvg = 0;
    if ($student) {
        $attempts = $student->quizAttempts()->where('status', 'submitted')->get();
        if ($attempts->count()) {
            $quizAvg = round($attempts->avg('percentage') / 10); // 0-10 stars
        }
    }

    // ── Pending assignments across active classes ─────────────────────────
    $pendingAssignments = collect();
    foreach ($activeClasses as $class) {
        $pending = $class->assignments()
            ->where('is_published', true)
            ->whereDoesntHave('submissions', fn ($q) => $q->where('student_id', $student?->id))
            ->take(3)
            ->get();
        $pendingAssignments = $pendingAssignments->merge($pending);
    }
@endphp

<style>
/* ───── Kids Dashboard Styles ───── */
.kids-dashboard {
    font-family: 'Nunito', 'Fredoka One', system-ui, sans-serif;
    min-height: 100vh;
    background: linear-gradient(135deg, #ede9fe 0%, #f3e8ff 50%, #fce7f3 100%);
    padding: 0;
    margin: -1.5rem;
}

/* Header */
.kids-header {
    background: linear-gradient(135deg, #7c3aed 0%, #db2777 50%, #f97316 100%);
    padding: 1.5rem 2rem 2rem;
    position: relative;
    overflow: hidden;
    border-radius: 0 0 2rem 2rem;
    box-shadow: 0 8px 32px rgba(124, 58, 237, 0.35);
}
.kids-header::before {
    content: '';
    position: absolute;
    top: -50%; right: -10%;
    width: 300px; height: 300px;
    background: rgba(255,255,255,0.08);
    border-radius: 50%;
}
.kids-header-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    position: relative;
    z-index: 1;
}
.kids-mascot {
    font-size: 3.5rem;
    animation: kids-bounce 2s ease-in-out infinite;
    filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
}
@keyframes kids-bounce {
    0%, 100% { transform: translateY(0) rotate(-3deg); }
    50%       { transform: translateY(-10px) rotate(3deg); }
}
.kids-greeting h1 {
    font-size: 2rem;
    font-weight: 900;
    color: #fff;
    text-shadow: 2px 2px 0 rgba(0,0,0,0.15);
    margin: 0 0 0.25rem;
}
.kids-greeting p {
    color: rgba(255,255,255,0.85);
    font-size: 1rem;
    margin: 0;
    font-weight: 600;
}
.kids-date-badge {
    background: rgba(255,255,255,0.2);
    border: 2px solid rgba(255,255,255,0.4);
    border-radius: 999px;
    padding: 0.4rem 1rem;
    color: #fff;
    font-weight: 700;
    font-size: 0.875rem;
    backdrop-filter: blur(4px);
}

/* School Selector */
.kids-school-switcher {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    padding: 1.25rem 2rem;
    background: rgba(255,255,255,0.6);
    backdrop-filter: blur(8px);
    border-bottom: 2px dashed rgba(124,58,237,0.2);
}
.kids-school-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.25rem;
    border-radius: 999px;
    font-size: 0.9rem;
    font-weight: 800;
    cursor: pointer;
    border: 3px solid transparent;
    transition: all 0.2s ease;
    text-decoration: none;
}
.kids-school-btn.active {
    background: linear-gradient(135deg, #7c3aed, #db2777);
    color: #fff;
    border-color: #fff;
    box-shadow: 0 4px 16px rgba(124,58,237,0.4);
    transform: scale(1.05);
}
.kids-school-btn:not(.active) {
    background: #fff;
    color: #6b21a8;
    border-color: #d8b4fe;
}
.kids-school-btn:not(.active):hover {
    transform: scale(1.03);
    border-color: #7c3aed;
    box-shadow: 0 4px 12px rgba(124,58,237,0.2);
}
.kids-grade-label {
    display: inline-block;
    background: rgba(124,58,237,0.12);
    color: #7c3aed;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 0.15rem 0.6rem;
    border-radius: 999px;
    margin-left: auto;
}

/* Main content */
.kids-main {
    padding: 1.5rem 2rem 2rem;
    display: flex;
    flex-direction: column;
    gap: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}
.kids-section-title {
    font-size: 1.25rem;
    font-weight: 900;
    color: #4c1d95;
    margin: 0 0 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Stars bar */
.kids-stars-section {
    background: #fff;
    border-radius: 1.5rem;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(124,58,237,0.1);
    border: 3px solid #ede9fe;
}
.kids-stars-row {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    flex-wrap: wrap;
}
.kids-star {
    font-size: 2rem;
    transition: transform 0.2s;
    filter: grayscale(0);
}
.kids-star.empty {
    filter: grayscale(1);
    opacity: 0.3;
}
.kids-star:hover { transform: scale(1.3) rotate(10deg); }
.kids-stars-count {
    margin-left: auto;
    font-size: 1.5rem;
    font-weight: 900;
    color: #7c3aed;
}

/* Missions */
.kids-missions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 1rem;
}
.kids-mission-card {
    border-radius: 1.25rem;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    border: 3px solid rgba(255,255,255,0.6);
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
    text-decoration: none;
}
.kids-mission-card:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 12px 32px rgba(0,0,0,0.15);
}
.kids-mission-card.color-1 { background: linear-gradient(135deg, #fef08a, #fde047); }
.kids-mission-card.color-2 { background: linear-gradient(135deg, #93c5fd, #60a5fa); }
.kids-mission-card.color-3 { background: linear-gradient(135deg, #f9a8d4, #f472b6); }
.kids-mission-card.color-4 { background: linear-gradient(135deg, #86efac, #4ade80); }
.kids-mission-card.color-5 { background: linear-gradient(135deg, #fdba74, #fb923c); }
.kids-mission-emoji { font-size: 2.5rem; flex-shrink: 0; }
.kids-mission-info { flex: 1; min-width: 0; }
.kids-mission-class {
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgba(0,0,0,0.5);
    margin-bottom: 0.2rem;
}
.kids-mission-name {
    font-size: 1rem;
    font-weight: 900;
    color: rgba(0,0,0,0.75);
    line-height: 1.3;
}
.kids-start-btn {
    background: rgba(0,0,0,0.15);
    color: rgba(0,0,0,0.7);
    border: none;
    border-radius: 999px;
    padding: 0.35rem 0.9rem;
    font-weight: 900;
    font-size: 0.8rem;
    cursor: pointer;
    margin-top: 0.5rem;
    transition: background 0.2s;
}
.kids-start-btn:hover { background: rgba(0,0,0,0.25); }
.kids-no-missions {
    text-align: center;
    padding: 2rem;
    color: #7c3aed;
    font-weight: 700;
    font-size: 1.1rem;
}

/* Classes */
.kids-classes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 1rem;
}
.kids-class-tile {
    border-radius: 1.5rem;
    padding: 1.5rem 1rem;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 6px 24px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
    text-decoration: none;
    border: 3px solid rgba(255,255,255,0.5);
}
.kids-class-tile:hover {
    transform: translateY(-6px) scale(1.03);
    box-shadow: 0 16px 40px rgba(0,0,0,0.15);
}
.kids-class-tile.tile-1 { background: linear-gradient(160deg, #ef4444, #dc2626); }
.kids-class-tile.tile-2 { background: linear-gradient(160deg, #3b82f6, #2563eb); }
.kids-class-tile.tile-3 { background: linear-gradient(160deg, #a855f7, #7c3aed); }
.kids-class-tile.tile-4 { background: linear-gradient(160deg, #22c55e, #16a34a); }
.kids-class-tile.tile-5 { background: linear-gradient(160deg, #f97316, #ea580c); }
.kids-class-tile.tile-6 { background: linear-gradient(160deg, #ec4899, #db2777); }
.kids-class-emoji { font-size: 2.75rem; }
.kids-class-name {
    font-size: 1.1rem;
    font-weight: 900;
    color: #fff;
    text-shadow: 0 1px 3px rgba(0,0,0,0.2);
}
.kids-class-teacher {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.85);
    font-weight: 700;
}

/* Empty state */
.kids-empty {
    text-align: center;
    padding: 3rem 1rem;
    color: #6b21a8;
    font-size: 1.1rem;
    font-weight: 700;
}
</style>

<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&display=swap" rel="stylesheet">

<div class="kids-dashboard">

    {{-- ── HEADER ─────────────────────────────────────────────── --}}
    <div class="kids-header">
        <div class="kids-header-inner">
            <div class="kids-mascot">⭐</div>
            <div class="kids-greeting">
                <h1>Hi {{ $firstName }}! 🌟</h1>
                <p>Ready for today's adventures?</p>
            </div>
            <div class="kids-date-badge">
                📅 {{ now()->format('D, M j') }}
            </div>
        </div>
    </div>

    {{-- ── SCHOOL SWITCHER ─────────────────────────────────────── --}}
    @if($allContexts && $allContexts->count() > 1)
    <div class="kids-school-switcher">
        <span style="font-weight:900;color:#6b21a8;font-size:0.85rem;align-self:center;">🏫 My Schools:</span>
        @foreach($allContexts as $schoolGroup)
            @foreach($schoolGroup['contexts'] as $ctx)
                @php $isActive = $activeContext && $activeContext['key'] === $ctx['key']; @endphp
                <button
                    wire:click="switchContext('{{ $ctx['key'] }}')"
                    class="kids-school-btn {{ $isActive ? 'active' : '' }}"
                >
                    🏫 {{ $schoolGroup['school']->name }}
                    <span class="kids-grade-label">{{ $ctx['grade']->name ?? '' }}</span>
                </button>
            @endforeach
        @endforeach
    </div>
    @elseif($allContexts && $allContexts->count() === 1)
    <div class="kids-school-switcher" style="justify-content:center;">
        <button class="kids-school-btn active">
            🏫 {{ $allContexts->first()['school']->name }}
            <span class="kids-grade-label">{{ $activeGradeName }}</span>
        </button>
    </div>
    @endif

    {{-- ── MAIN CONTENT ────────────────────────────────────────── --}}
    <div class="kids-main">

        {{-- ⭐ My Stars --}}
        <div class="kids-stars-section">
            <div class="kids-section-title">⭐ My Stars</div>
            <div class="kids-stars-row">
                @for ($i = 1; $i <= 10; $i++)
                    <span class="kids-star {{ $i <= $quizAvg ? '' : 'empty' }}">⭐</span>
                @endfor
                <span class="kids-stars-count">{{ $quizAvg }} / 10</span>
            </div>
        </div>

        {{-- 🎯 Today's Missions --}}
        <div>
            <div class="kids-section-title">🎯 Today's Missions</div>
            @php
                $classEmojis = ['🔢','📖','🎨','🔬','🌍','🎵','🏃','✏️'];
                $missionColors = ['color-1','color-2','color-3','color-4','color-5'];
            @endphp

            @if($pendingAssignments->isEmpty())
                <div class="kids-no-missions">
                    🎉 All done! You finished all your missions today!
                </div>
            @else
                <div class="kids-missions-grid">
                    @foreach($pendingAssignments->take(6) as $i => $assignment)
                        <div class="kids-mission-card {{ $missionColors[$i % 5] }}">
                            <div class="kids-mission-emoji">{{ $classEmojis[$i % count($classEmojis)] }}</div>
                            <div class="kids-mission-info">
                                <div class="kids-mission-class">{{ $assignment->learningClass->name ?? 'Class' }}</div>
                                <div class="kids-mission-name">{{ $assignment->title }}</div>
                                <button class="kids-start-btn">START! 🚀</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- 📚 My Classes --}}
        <div>
            <div class="kids-section-title">📚 My Classes</div>
            @php
                $tileColors = ['tile-1','tile-2','tile-3','tile-4','tile-5','tile-6'];
                $tileEmojis = ['🔢','📖','🎨','🔬','🌍','🎵','🏃','✏️','🖥️','⚽'];
            @endphp
            @if($activeClasses->isEmpty())
                <div class="kids-empty">No classes yet — ask your teacher to add you! 😊</div>
            @else
                <div class="kids-classes-grid">
                    @foreach($activeClasses as $i => $class)
                        <div class="kids-class-tile {{ $tileColors[$i % 6] }}">
                            <div class="kids-class-emoji">{{ $tileEmojis[$i % count($tileEmojis)] }}</div>
                            <div class="kids-class-name">{{ $class->name }}</div>
                            <div class="kids-class-teacher">
                                {{ $class->teachers->first()?->user?->name ?? 'Teacher' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>
