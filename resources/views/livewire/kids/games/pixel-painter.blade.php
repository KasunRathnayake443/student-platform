{{-- ── 🎨 Interactive Canvas / Pixel Art Painter (Ages 5-10) ───────
     Colour by numbers: pick a colour, then fill in every tile to
     reveal the hidden picture.
     ──────────────────────────────────────────────────────────────── --}}
<div class="kg-play" x-data="{
    phase: 'ready',
    pictures: [],
    index: 0,
    selected: 1,
    painted: [],
    mistakes: 0,
    totalCells: 0,
    locked: false,
    shakeCell: -1,

    init() {
        this.pictures = this.makePictures();
        this.index = Math.floor(Math.random() * this.pictures.length);
        this.setup();
        this.phase = 'playing';
    },

    get current() { return this.pictures[this.index] || { name: '', palette: {}, rows: [] }; },
    get target() { return this.current.rows.join('').split('').map(Number); },
    get palette() { return Object.keys(this.current.palette).map(Number); },
    get total() { return this.totalCells; },
    get correct() { return Math.max(0, this.totalCells - this.mistakes); },
    get score() { return this.correct * 10; },
    get done() {
        let count = 0;
        const target = this.target;
        for (let i = 0; i < target.length; i++) {
            if (target[i] !== 0 && this.painted[i] === target[i]) { count++; }
        }
        return count;
    },

    makePictures() {
        return [
            {
                name: 'Heart',
                palette: { 1: '#ef4444', 2: '#f9a8d4' },
                rows: [
                    '0000000000',
                    '0110011000',
                    '1111111100',
                    '1111111100',
                    '0111111000',
                    '0011110000',
                    '0001100000',
                    '0000000000',
                    '0000000000',
                    '0000000000',
                ],
            },
            {
                name: 'Smiley',
                palette: { 1: '#facc15', 2: '#1f2937' },
                rows: [
                    '0000000000',
                    '0011111100',
                    '0111111110',
                    '1121112111',
                    '1111111111',
                    '1111111111',
                    '1122222211',
                    '0111111110',
                    '0011111100',
                    '0000000000',
                ],
            },
            {
                name: 'Star',
                palette: { 1: '#fbbf24', 2: '#f97316' },
                rows: [
                    '0000110000',
                    '0001111000',
                    '0001111000',
                    '1112112111',
                    '1111111111',
                    '0112222110',
                    '0011111100',
                    '0011111100',
                    '0111001110',
                    '0110000110',
                ],
            },
            {
                name: 'Rocket',
                palette: { 1: '#e2e8f0', 2: '#ef4444', 3: '#38bdf8', 4: '#f97316' },
                rows: [
                    '0000110000',
                    '0001111000',
                    '0001311000',
                    '0001311000',
                    '0011111100',
                    '0011111100',
                    '0111111110',
                    '0120110210',
                    '0010110100',
                    '0044444400',
                ],
            },
        ];
    },

    setup() {
        const target = this.target;
        this.totalCells = target.filter((value) => value !== 0).length;
        this.painted = new Array(target.length).fill(null);
        this.mistakes = 0;
        this.locked = false;
        this.selected = this.palette[0] || 1;
    },

    isEmpty(i) { return this.target[i] === 0; },
    isSolved(i) {
        const target = this.target[i];
        return target !== 0 && this.painted[i] === target;
    },

    pixelStyle(i) {
        const painted = this.painted[i];
        const target = this.target[i];

        if (target === 0) { return 'background:transparent;'; }
        if (painted === 'wrong') { return 'background:#fecaca;'; }
        if (painted !== null && painted === target) {
            return 'background:' + this.current.palette[painted] + ';';
        }
        return 'background:#ffffff;';
    },

    paint(i) {
        if (this.locked || this.phase !== 'playing') return;

        const target = this.target[i];
        if (target === 0) return;
        if (this.painted[i] === target) return;

        if (this.selected === target) {
            this.painted[i] = target;
        } else {
            this.painted[i] = 'wrong';
            this.mistakes++;
            this.shakeCell = i;
            setTimeout(() => { if (this.shakeCell === i) { this.shakeCell = -1; } }, 420);
        }

        if (this.done >= this.totalCells) {
            this.locked = true;
            setTimeout(() => this.finish(), 720);
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
                <h3 class="kg-play-title">🎨 Pixel Art Painter</h3>
                <span class="kg-play-sub" x-text="'Painted ' + done + ' of ' + totalCells + ' tiles'"></span>
            </div>
        </div>
        <span class="kg-live-score" x-text="'⭐ ' + score"></span>
    </div>

    <div class="kg-prompt-card">
        <div class="kg-prompt-emoji">🎨</div>
        <p class="kg-prompt">Colour by numbers!</p>
        <p class="kg-hint-text">
            Pick a colour, then tap the tiles with that number to reveal the
            <strong x-text="current.name"></strong>. Wrong colours cost you points!
        </p>
    </div>

    <div class="kg-palette">
        <template x-for="num in palette" :key="num">
            <button type="button"
                    class="kg-swatch"
                    :class="{ 'kg-swatch--active': selected === num }"
                    :style="'background:' + current.palette[num]"
                    @click="selected = num">
                <span x-text="num"></span>
            </button>
        </template>
    </div>

    <div class="kg-pixel-grid">
        <template x-for="i in 100" :key="i">
            <button type="button"
                    class="kg-pixel"
                    :class="{
                        'kg-pixel--empty': isEmpty(i - 1),
                        'kg-pixel--solved': isSolved(i - 1),
                        'kg-pixel--wrong': shakeCell === (i - 1)
                    }"
                    :data-number="isEmpty(i - 1) ? '' : target[i - 1]"
                    :style="pixelStyle(i - 1)"
                    @click="paint(i - 1)"></button>
        </template>
    </div>
</div>
