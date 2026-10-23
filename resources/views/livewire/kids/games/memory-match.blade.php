{{-- ── 🃏 Memory Card Match (Ages 5-10) ───────────────────────────
     Flip two cards at a time and find every matching pair. Fewer
     moves means more stars.
     ──────────────────────────────────────────────────────────────── --}}
<div class="kg-play" x-data="{
    phase: 'ready',
    cards: [],
    pairs: 6,
    flipped: [],
    matched: 0,
    moves: 0,
    lock: false,

    init() {
        // Younger children get a smaller board.
        this.pairs = @js($ageBand) === 'early' ? 3 : 6;
        this.cards = this.makeCards();
        this.phase = 'playing';
    },

    get penalty() { return Math.floor(Math.max(0, this.moves - (this.pairs * 2)) / 3); },
    get correct() { return Math.max(0, this.pairs - this.penalty); },
    get total() { return this.pairs; },
    get score() { return this.correct * 10; },

    makeCards() {
        const emojis = [
            '🦊', '🐼', '🦁', '🐸', '🐙', '🦄',
            '🐝', '🦖', '🐳', '🐢', '🦉', '🐞',
            '🍄', '🌈', '⭐', '🎈',
        ].slice();
        for (let k = emojis.length - 1; k > 0; k--) {
            const j = Math.floor(Math.random() * (k + 1));
            const tmp = emojis[k];
            emojis[k] = emojis[j];
            emojis[j] = tmp;
        }

        const chosen = emojis.slice(0, this.pairs);
        const cards = [];

        chosen.forEach((emoji, pair) => {
            cards.push({ emoji: emoji, pair: pair });
            cards.push({ emoji: emoji, pair: pair });
        });

        for (let k = cards.length - 1; k > 0; k--) {
            const j = Math.floor(Math.random() * (k + 1));
            const tmp = cards[k];
            cards[k] = cards[j];
            cards[j] = tmp;
        }

        return cards.map((card, index) => ({
            id: index,
            emoji: card.emoji,
            pair: card.pair,
            faceUp: false,
            done: false,
        }));
    },

    flip(card) {
        if (this.lock || this.phase !== 'playing') return;
        if (card.faceUp || card.done) return;

        card.faceUp = true;
        this.flipped.push(card);

        if (this.flipped.length < 2) return;

        this.moves++;
        this.lock = true;

        const first = this.flipped[0];
        const second = this.flipped[1];

        if (first.pair === second.pair) {
            setTimeout(() => {
                first.done = true;
                second.done = true;
                this.matched++;
                this.flipped = [];
                this.lock = false;
                if (this.matched >= this.pairs) { this.finish(); }
            }, 430);
        } else {
            setTimeout(() => {
                first.faceUp = false;
                second.faceUp = false;
                this.flipped = [];
                this.lock = false;
            }, 800);
        }
    },

    finish() {
        if (this.phase !== 'playing') return;
        this.phase = 'done';
        this.$wire.finishGame(this.correct, this.total);
    }
}">

    <div class="kg-play-top">
        <div class="kg-play-head">
            <button type="button" class="kg-btn kg-btn-ghost" wire:click="backToHub">← Games</button>
            <div>
                <h3 class="kg-play-title">🃏 Memory Match</h3>
                <span class="kg-play-sub" x-text="'Pairs found ' + matched + ' of ' + pairs + ' · Moves ' + moves"></span>
            </div>
        </div>
        <span class="kg-live-score" x-text="'⭐ ' + score"></span>
    </div>

    <div class="kg-memory-grid" :class="pairs <= 3 ? 'kg-memory-grid--small' : 'kg-memory-grid--large'">
        <template x-for="card in cards" :key="card.id">
            <button type="button"
                    class="kg-card-tile"
                    :class="{ 'is-up': card.faceUp || card.done, 'is-done': card.done }"
                    @click="flip(card)">
                <span class="kg-card-tile-face kg-card-tile-back">❓</span>
                <span class="kg-card-tile-face kg-card-tile-front" x-text="card.emoji"></span>
            </button>
        </template>
    </div>

    <p class="kg-hint-text" style="text-align:center; margin:0;">
        Flip two cards. If they match they stay face up — find them all in as few moves as you can!
    </p>
</div>
