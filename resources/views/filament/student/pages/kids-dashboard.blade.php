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
/* ───── Kids Dashboard Styles (Age 5-10: Big, Bold, Playful) ───── */
.kids-dashboard {
    font-family: 'Nunito', 'Fredoka One', system-ui, sans-serif;
    min-height: calc(100vh - 1px);
    width: 100%;
    max-width: 100%;
    background: linear-gradient(135deg, #ede9fe 0%, #f3e8ff 30%, #fce7f3 60%, #fff7ed 100%);
    padding: 0;
    margin: 0;
    overflow: hidden;
    position: relative;
}

/* Header */
.kids-header {
    background: linear-gradient(135deg, #7c3aed 0%, #a855f7 25%, #db2777 55%, #f97316 100%);
    padding: 2.25rem 2.5rem 3rem;
    position: relative;
    overflow: hidden;
    border-radius: 0 0 2.5rem 2.5rem;
    box-shadow: 0 12px 40px rgba(124, 58, 237, 0.4);
}
.kids-header::before {
    content: '';
    position: absolute;
    top: -60%; right: -8%;
    width: 350px; height: 350px;
    background: rgba(255,255,255,0.08);
    border-radius: 50%;
}
.kids-header::after {
    content: '';
    position: absolute;
    bottom: -40%; left: 10%;
    width: 250px; height: 250px;
    background: rgba(255,255,255,0.06);
    border-radius: 50%;
}
.kids-header-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1.25rem;
    position: relative;
    z-index: 1;
}
.kids-mascot {
    font-size: 5rem;
    animation: kids-bounce 2s ease-in-out infinite;
    filter: drop-shadow(0 6px 12px rgba(0,0,0,0.2));
}
@keyframes kids-bounce {
    0%, 100% { transform: translateY(0) rotate(-5deg) scale(1); }
    50%       { transform: translateY(-16px) rotate(5deg) scale(1.08); }
}
.kids-greeting h1 {
    font-size: 2.8rem;
    font-weight: 900;
    color: #fff;
    text-shadow: 3px 3px 0 rgba(0,0,0,0.15);
    margin: 0 0 0.35rem;
    line-height: 1.1;
}
.kids-greeting p {
    color: rgba(255,255,255,0.9);
    font-size: 1.2rem;
    margin: 0;
    font-weight: 700;
}
.kids-date-badge {
    background: rgba(255,255,255,0.2);
    border: 3px solid rgba(255,255,255,0.45);
    border-radius: 999px;
    padding: 0.55rem 1.25rem;
    color: #fff;
    font-weight: 800;
    font-size: 1rem;
    backdrop-filter: blur(4px);
}

/* School Selector */
.kids-school-switcher {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 1.5rem 2.5rem;
    background: rgba(255,255,255,0.65);
    backdrop-filter: blur(8px);
    border-bottom: 3px dashed rgba(124,58,237,0.25);
}
.kids-school-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 999px;
    font-size: 1.05rem;
    font-weight: 800;
    cursor: pointer;
    border: 3px solid transparent;
    transition: all 0.25s ease;
    text-decoration: none;
}
.kids-school-btn.active {
    background: linear-gradient(135deg, #7c3aed, #db2777);
    color: #fff;
    border-color: #fff;
    box-shadow: 0 6px 24px rgba(124,58,237,0.45);
    transform: scale(1.08);
}
.kids-school-btn:not(.active) {
    background: #fff;
    color: #6b21a8;
    border-color: #d8b4fe;
    font-weight: 700;
}
.kids-school-btn:not(.active):hover {
    transform: scale(1.05);
    border-color: #7c3aed;
    box-shadow: 0 4px 16px rgba(124,58,237,0.25);
}

/* Main content */
.kids-main {
    padding: 2.5rem 2.5rem 4rem;
    display: flex;
    flex-direction: column;
    gap: 3rem;
    max-width: 1300px;
    margin: 0 auto;
}
.kids-section-title {
    font-size: 1.6rem;
    font-weight: 900;
    color: #4c1d95;
    margin: 0 0 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

/* Stars bar */
.kids-stars-section {
    background: #fff;
    border-radius: 2rem;
    padding: 2.25rem 2.5rem;
    box-shadow: 0 8px 32px rgba(124,58,237,0.12);
    border: 4px solid #ede9fe;
}
.kids-stars-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.kids-star {
    font-size: 2.75rem;
    transition: transform 0.2s;
    filter: grayscale(0);
}
.kids-star.empty {
    filter: grayscale(1);
    opacity: 0.3;
}
.kids-star:hover { transform: scale(1.35) rotate(12deg); }
.kids-stars-count {
    margin-left: auto;
    font-size: 1.8rem;
    font-weight: 900;
    color: #7c3aed;
}

/* Missions */
.kids-missions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}
.kids-mission-card {
    border-radius: 1.5rem;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1.25rem;
    box-shadow: 0 8px 28px rgba(0,0,0,0.1);
    border: 4px solid rgba(255,255,255,0.6);
    transition: transform 0.25s, box-shadow 0.25s;
    cursor: pointer;
    text-decoration: none;
}
.kids-mission-card:hover {
    transform: translateY(-6px) scale(1.03);
    box-shadow: 0 16px 40px rgba(0,0,0,0.18);
}
.kids-mission-card.color-1 { background: linear-gradient(135deg, #fef08a, #fde047); }
.kids-mission-card.color-2 { background: linear-gradient(135deg, #93c5fd, #60a5fa); }
.kids-mission-card.color-3 { background: linear-gradient(135deg, #f9a8d4, #f472b6); }
.kids-mission-card.color-4 { background: linear-gradient(135deg, #86efac, #4ade80); }
.kids-mission-card.color-5 { background: linear-gradient(135deg, #fdba74, #fb923c); }
.kids-mission-emoji { font-size: 3.25rem; flex-shrink: 0; }
.kids-mission-info { flex: 1; min-width: 0; }
.kids-mission-class {
    font-size: 0.82rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgba(0,0,0,0.45);
    margin-bottom: 0.3rem;
}
.kids-mission-name {
    font-size: 1.2rem;
    font-weight: 900;
    color: rgba(0,0,0,0.75);
    line-height: 1.3;
}
.kids-start-btn {
    background: rgba(0,0,0,0.15);
    color: rgba(0,0,0,0.7);
    border: none;
    border-radius: 999px;
    padding: 0.5rem 1.25rem;
    font-weight: 900;
    font-size: 0.95rem;
    cursor: pointer;
    margin-top: 0.6rem;
    transition: all 0.2s;
}
.kids-start-btn:hover { background: rgba(0,0,0,0.25); transform: scale(1.05); }
.kids-no-missions {
    text-align: center;
    padding: 2.5rem;
    color: #7c3aed;
    font-weight: 800;
    font-size: 1.3rem;
}

/* Classes */
.kids-classes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1.5rem;
}
.kids-class-tile {
    border-radius: 2rem;
    padding: 2rem 1.25rem;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.6rem;
    box-shadow: 0 8px 28px rgba(0,0,0,0.12);
    transition: transform 0.25s, box-shadow 0.25s;
    cursor: pointer;
    text-decoration: none;
    border: 4px solid rgba(255,255,255,0.5);
}
.kids-class-tile:hover {
    transform: translateY(-8px) scale(1.04);
    box-shadow: 0 20px 48px rgba(0,0,0,0.18);
}
.kids-class-tile.tile-1 { background: linear-gradient(160deg, #ef4444, #dc2626); }
.kids-class-tile.tile-2 { background: linear-gradient(160deg, #3b82f6, #2563eb); }
.kids-class-tile.tile-3 { background: linear-gradient(160deg, #a855f7, #7c3aed); }
.kids-class-tile.tile-4 { background: linear-gradient(160deg, #22c55e, #16a34a); }
.kids-class-tile.tile-5 { background: linear-gradient(160deg, #f97316, #ea580c); }
.kids-class-tile.tile-6 { background: linear-gradient(160deg, #ec4899, #db2777); }
.kids-class-emoji { font-size: 3.5rem; }
.kids-class-name {
    font-size: 1.3rem;
    font-weight: 900;
    color: #fff;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
.kids-class-teacher {
    font-size: 0.88rem;
    color: rgba(255,255,255,0.88);
    font-weight: 700;
}

/* Empty state */
.kids-empty {
    text-align: center;
    padding: 3.5rem 1rem;
    color: #6b21a8;
    font-size: 1.3rem;
    font-weight: 800;
}

/* Floating background emojis */
.kids-bg-float {
    position: absolute;
    font-size: 2.2rem;
    line-height: 1;
    opacity: 0.16;
    pointer-events: none;
    animation: kids-float 9s ease-in-out infinite;
    user-select: none;
    z-index: 0;
}
@keyframes kids-float {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-30px) rotate(14deg); }
}

/* Confetti burst */
.kids-confetti-piece {
    position: fixed;
    top: -24px;
    width: 12px;
    height: 18px;
    z-index: 9999;
    pointer-events: none;
    border-radius: 3px;
    animation: kids-confetti-fall linear forwards;
}
@keyframes kids-confetti-fall {
    0% { transform: translateY(-10vh) rotate(0deg); opacity: 1; }
    100% { transform: translateY(105vh) rotate(720deg); opacity: 0.85; }
}

/* Clickable mascot */
.kids-mascot {
    cursor: pointer;
}
.kids-mascot:active { transform: scale(1.4) rotate(-12deg); }

/* Interactive stars */
.kids-star {
    cursor: pointer;
    user-select: none;
}
.kids-star.pop { animation: kids-pop 0.45s ease; }
@keyframes kids-pop {
    0% { transform: scale(1); }
    40% { transform: scale(1.6) rotate(15deg); }
    100% { transform: scale(1); }
}

/* Clickable tiles / missions */
.kids-class-tile,
.kids-mission-card {
    position: relative;
    cursor: pointer;
}
.kids-class-tile:active,
.kids-mission-card:active { transform: scale(0.96) !important; }
.kids-class-tile:hover .kids-class-emoji-inner,
.kids-mission-card:hover .kids-mission-emoji { animation: kids-wiggle 0.6s ease infinite; }
@keyframes kids-wiggle {
    0%, 100% { transform: rotate(-8deg); }
    50% { transform: rotate(8deg); }
}

/* Responsive fit */
@media (max-width: 900px) {
    .kids-header { padding: 1.5rem 1.5rem 2rem; }
    .kids-header-inner { justify-content: center; text-align: center; }
    .kids-greeting h1 { font-size: 1.9rem; }
    .kids-main { padding: 1.5rem 1.25rem 2.5rem; gap: 2.5rem; }
    .kids-section-title { font-size: 1.4rem; }
    .kids-school-switcher { padding: 1.25rem 1.25rem; }
}
</style>

<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&family=Fredoka+One&display=swap" rel="stylesheet">

<div class="kids-dashboard">

    {{-- Floating background characters --}}
    <div class="kids-bg-float" style="top: 18%; left: 4%; animation-delay: 0s;">🎈</div>
    <div class="kids-bg-float" style="top: 45%; left: 92%; animation-delay: 1.4s;">🦄</div>
    <div class="kids-bg-float" style="top: 72%; left: 6%; animation-delay: 2.8s;">🌟</div>
    <div class="kids-bg-float" style="top: 85%; left: 88%; animation-delay: 4.1s;">🌈</div>
    <div class="kids-bg-float" style="top: 30%; left: 80%; animation-delay: 5.5s;">🎨</div>

    {{-- ── HEADER ─────────────────────────────────────────────── --}}
    <div class="kids-header">
        <div class="kids-header-inner">
            <div class="kids-mascot" role="button" title="Tap me for a surprise! 🎉" x-on:click="kidsCelebrate()">⭐</div>
            <div class="kids-greeting">
                <h1>Hi {{ $firstName }}! 🌟</h1>
                <p>Ready for today's adventures?</p>
            </div>
            <div style="display:flex; align-items:center; gap:1rem;">
                <div class="kids-date-badge">
                    📅 {{ now()->format('D, M j') }}
                </div>
            </div>
        </div>
    </div>

    {{-- ── SCHOOL SWITCHER ─────────────────────────────────────── --}}
    @if($allContexts && $allContexts->count() >= 1)
    <div class="kids-school-switcher">
        @foreach($allContexts as $schoolGroup)
            <div style="display:flex; flex-direction:column; align-items:center; gap:0.6rem;">
                <span style="font-weight:900;color:#6b21a8;font-size:1rem;">🏫 {{ $schoolGroup['school']->name }}</span>
                <div style="display:flex; flex-wrap:wrap; gap:0.6rem; justify-content:center;">
                    @foreach($schoolGroup['contexts'] as $ctx)
                        @php $isActive = $activeContext && $activeContext['key'] === $ctx['key']; @endphp
                        <button
                            wire:click="switchContext('{{ $ctx['key'] }}')"
                            class="kids-school-btn {{ $isActive ? 'active' : '' }}"
                        >
                            {{ $ctx['grade']->name ?? '' }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
    @endif

    {{-- ── MAIN CONTENT ────────────────────────────────────────── --}}
    <div class="kids-main">

        {{-- ⭐ My Stars (tap a star to play) --}}
        <div class="kids-stars-section">
            <div class="kids-section-title">⭐ My Stars</div>
            <div class="kids-stars-row" x-data="{ filled: {{ $quizAvg }} }">
                @for ($i = 1; $i <= 10; $i++)
                    <span
                        class="kids-star"
                        :class="filled >= {{ $i }} ? '' : 'empty'"
                        x-on:click="filled = (filled === {{ $i }}) ? filled - 1 : {{ $i }}; $event.currentTarget.classList.add('pop'); setTimeout(() => $event.currentTarget.classList.remove('pop'), 450);"
                        role="button"
                        title="Tap me!"
                    >⭐</span>
                @endfor
                <span class="kids-stars-count" x-text="filled + ' / 10'"></span>
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
                    🎉 All done! You finished all your missions today! 🏆
                </div>
            @else
                <div class="kids-missions-grid">
                    @foreach($pendingAssignments->take(6) as $i => $assignment)
                        <div
                            class="kids-mission-card {{ $missionColors[$i % 5] }}"
                            wire:click="viewClassLessons({{ $assignment->learning_class_id }})"
                            role="button"
                            title="Let's do this! 🚀"
                        >
                            <div class="kids-mission-emoji">{{ $classEmojis[$i % count($classEmojis)] }}</div>
                            <div class="kids-mission-info">
                                <div class="kids-mission-class">{{ $assignment->learningClass->name ?? 'Class' }}</div>
                                <div class="kids-mission-name">{{ $assignment->title }}</div>
                                <button type="button" class="kids-start-btn">START! 🚀</button>
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
                        <div
                            class="kids-class-tile {{ $tileColors[$i % 6] }}"
                            wire:click="viewClassLessons({{ $class->id }})"
                            role="button"
                            title="Tap to open {{ $class->name }} 📖"
                        >
                            <div class="kids-class-emoji"><span class="kids-class-emoji-inner">{{ $tileEmojis[$i % count($tileEmojis)] }}</span></div>
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

<script>
function kidsCelebrate() {
    const emojis = ['⭐', '🎉', '🌈', '🌟', '🎈', '🎊', '💜', '🦄', '🍭', '🎨'];
    const colors = ['#a855f7', '#ec4899', '#f97316', '#22c55e', '#3b82f6', '#facc15'];
    for (let i = 0; i < 60; i++) {
        const piece = document.createElement('div');
        piece.className = 'kids-confetti-piece';
        piece.style.left = Math.random() * 100 + 'vw';
        piece.style.animationDuration = (1.6 + Math.random() * 1.6) + 's';
        if (Math.random() < 0.4) {
            piece.textContent = emojis[Math.floor(Math.random() * emojis.length)];
            piece.style.background = 'transparent';
            piece.style.fontSize = (16 + Math.random() * 18) + 'px';
            piece.style.lineHeight = 1;
            piece.style.width = 'auto';
            piece.style.height = 'auto';
        } else {
            piece.style.background = colors[Math.floor(Math.random() * colors.length)];
        }
        document.body.appendChild(piece);
        setTimeout(() => piece.remove(), 4000);
    }
}
window.kidsCelebrate = kidsCelebrate;
</script>
