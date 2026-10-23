{{-- ── 🔤 Word Builder / Spelling Maze (Ages 8-10) ────────────────
     A word appears as blank slots. Tap the letter tiles in the right
     order to spell it out and move on to the next word.
     ──────────────────────────────────────────────────────────────── --}}
<div class="kg-play" x-data="{
    phase: 'ready',
    words: [],
    index: 0,
    placed: [],
    mistakes: 0,
    totalLetters: 0,
    locked: false,
    shakeTile: -1,

    init() {
        this.words = this.makeWords();
        this.totalLetters = this.words.reduce((sum, item) => sum + item.word.length, 0);
        this.phase = 'playing';
    },

    get correct() { return Math.max(0, this.totalLetters - this.mistakes); },
    get total() { return this.totalLetters; },
    get score() { return this.correct * 10; },
    get current() { return this.words[this.index] || null; },
    get tiles() { return this.current ? this.current.tiles : []; },
    get blanks() {
        const word = this.current;
        if (!word) return [];
        return word.word.split('').map((letter, position) => this.placed[position] || '_');
    },

    makeWords() {
        const bank = [
            'planet', 'rocket', 'jungle', 'castle', 'pirate', 'garden', 'monkey', 'turtle',
            'puzzle', 'rabbit', 'school', 'pencil', 'bridge', 'dragon', 'forest', 'island',
            'ladder', 'magnet', 'orange', 'pillow', 'silver', 'summer', 'window', 'yellow',
            'basket', 'carpet', 'doctor', 'engine', 'flower', 'guitar', 'hammer', 'jacket',
            'kitten', 'laptop', 'marble', 'oyster', 'peanut', 'ribbon', 'saddle', 'ticket',
            'violin', 'walnut', 'zipper', 'castle', 'bottle', 'camera', 'dolphin', 'elephant',
        ];
        const picked = [];
        while (picked.length < 5) {
            const word = bank[Math.floor(Math.random() * bank.length)];
            if (picked.indexOf(word) === -1) { picked.push(word); }
        }

        return picked.map((word) => {
            const letters = word.toUpperCase().split('');
            const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            const distractors = [];
            while (distractors.length < 3) {
                const candidate = alphabet[Math.floor(Math.random() * alphabet.length)];
                if (letters.indexOf(candidate) === -1 && distractors.indexOf(candidate) === -1) {
                    distractors.push(candidate);
                }
            }

            const tiles = letters.concat(distractors);
            for (let k = tiles.length - 1; k > 0; k--) {
                const j = Math.floor(Math.random() * (k + 1));
                const tmp = tiles[k];
                tiles[k] = tiles[j];
                tiles[j] = tmp;
            }

            return { word: word.toUpperCase(), tiles: tiles };
        });
    },

    isUsed(tile) {
        const word = this.current;
        if (!word) return false;
        return word.word.slice(this.placed.length).indexOf(tile) === -1;
    },

    tap(tile, tileIndex) {
        if (this.locked || this.phase !== 'playing') return;

        const word = this.current;
        const expected = word.word[this.placed.length];

        if (tile === expected) {
            this.placed.push(tile);
            if (this.placed.length === word.word.length) {
                this.locked = true;
                setTimeout(() => this.next(), 620);
            }
        } else {
            this.mistakes++;
            this.shakeTile = tileIndex;
            setTimeout(() => { this.shakeTile = -1; }, 450);
        }
    },

    next() {
        this.placed = [];
        this.locked = false;
        if (this.index + 1 >= this.words.length) {
            this.finish();
            return;
        }
        this.index++;
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
                <h3 class="kg-play-title">🔤 Word Builder</h3>
                <span class="kg-play-sub" x-text="'Word ' + (index + 1) + ' of ' + words.length"></span>
            </div>
        </div>
        <span class="kg-live-score" x-text="'⭐ ' + score"></span>
    </div>

    <div class="kg-prompt-card">
        <div class="kg-prompt-emoji">🔤</div>
        <p class="kg-prompt">Spell the word!</p>
        <div class="kg-blanks">
            <template x-for="(blank, bi) in blanks" :key="index + '-' + bi">
                <span class="kg-blank" x-text="blank"></span>
            </template>
        </div>
        <p class="kg-hint-text">Tap the tiles in order — wrong letters cost you points!</p>
    </div>

    <div class="kg-tiles">
        <template x-for="(tile, ti) in tiles" :key="index + '-' + ti">
            <button type="button"
                    class="kg-tile"
                    :class="{ 'kg-tile--used': isUsed(tile), 'kg-tile--shake': shakeTile === ti }"
                    @click="tap(tile, ti)"
                    x-text="tile"></button>
        </template>
    </div>
</div>
