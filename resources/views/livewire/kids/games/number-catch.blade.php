{{-- ── 🧺 Number Catch (Ages 5-7) ──────────────────────────────────
     Items fall from the sky. Slide the basket and catch the numbers
     that match the target shown at the top.
     ──────────────────────────────────────────────────────────────── --}}
<div class="kg-play" x-data="{
    phase: 'ready',
    queue: [],
    items: [],
    basketX: 50,
    goodCaught: 0,
    badCaught: 0,
    goodTotal: 6,
    spawned: 0,
    totalItems: 12,
    nextId: 1,
    raf: null,
    lastTime: 0,
    spawnTimer: null,
    fieldH: 330,
    target: 5,
    mode: 'exact',

    init() {
        this.fieldH = this.$el.querySelector('.kg-field').clientHeight || 330;
        this.start();
    },

    get correct() { return Math.max(0, this.goodCaught - this.badCaught); },
    get total() { return this.goodTotal; },
    get score() { return this.correct * 10; },
    get targetText() {
        return this.mode === 'exact'
            ? ('Catch ' + this.target)
            : ('Catch more than ' + this.target);
    },

    start() {
        this.phase = 'playing';
        this.items = [];
        this.goodCaught = 0;
        this.badCaught = 0;
        this.spawned = 0;
        this.goodTotal = 6;
        this.mode = Math.random() < 0.5 ? 'exact' : 'greater';
        this.target = this.mode === 'exact'
            ? 3 + Math.floor(Math.random() * 6)
            : 2 + Math.floor(Math.random() * 5);

        this.queue = [];
        for (let i = 0; i < this.totalItems; i++) {
            this.queue.push(i < this.goodTotal);
        }
        for (let i = this.queue.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            const tmp = this.queue[i];
            this.queue[i] = this.queue[j];
            this.queue[j] = tmp;
        }

        this.spawnTimer = setInterval(() => this.spawn(), 1250);
        this.lastTime = performance.now();
        this.raf = requestAnimationFrame((t) => this.tick(t));
    },

    spawn() {
        if (this.spawned >= this.totalItems) {
            clearInterval(this.spawnTimer);
            return;
        }

        const good = this.queue[this.spawned];
        this.spawned++;
        const emojis = ['🍎', '🍐', '🍊', '🍓', '🥝', '🍇', '🍑', '🍉'];

        this.items.push({
            id: this.nextId++,
            x: 8 + Math.random() * 80,
            y: -46,
            speed: 76 + Math.random() * 44,
            value: this.pickValue(good),
            emoji: emojis[Math.floor(Math.random() * emojis.length)],
            good: good,
            caught: false,
        });
    },

    pickValue(good) {
        if (this.mode === 'exact') {
            if (good) return this.target;
            let value;
            do {
                value = 1 + Math.floor(Math.random() * 9);
            } while (value === this.target);
            return value;
        }

        if (good) {
            return this.target + 1 + Math.floor(Math.random() * Math.max(1, 9 - this.target));
        }

        return 1 + Math.floor(Math.random() * this.target);
    },

    moveBasket(event) {
        const rect = event.currentTarget.getBoundingClientRect();
        this.basketX = Math.max(5, Math.min(95, ((event.clientX - rect.left) / rect.width) * 100));
    },

    tick(now) {
        const dt = Math.min(60, now - this.lastTime) / 1000;
        this.lastTime = now;
        const floor = this.fieldH - 42;

        for (let i = this.items.length - 1; i >= 0; i--) {
            const item = this.items[i];
            item.y += item.speed * dt;

            if (item.y >= floor) {
                item.caught = Math.abs(item.x - this.basketX) <= 18;
                if (item.caught) {
                    if (item.good) { this.goodCaught++; } else { this.badCaught++; }
                }
                this.items.splice(i, 1);
            }
        }

        if (this.spawned >= this.totalItems && this.items.length === 0) {
            this.finish();
            return;
        }

        this.raf = requestAnimationFrame((t) => this.tick(t));
    },

    finish() {
        if (this.phase !== 'playing') return;
        this.phase = 'done';
        if (this.raf) { cancelAnimationFrame(this.raf); }
        if (this.spawnTimer) { clearInterval(this.spawnTimer); }
        this.$wire.finishGame(this.correct, this.total);
    }
}">

    <div class="kg-play-top">
        <div class="kg-play-head">
            <button type="button" class="kg-btn kg-btn-ghost" wire:click="backToHub">← Games</button>
            <div>
                <h3 class="kg-play-title">🧺 Number Catch</h3>
                <span class="kg-play-sub" x-text="targetText"></span>
            </div>
        </div>
        <span class="kg-live-score" x-text="'⭐ ' + score"></span>
    </div>

    <div class="kg-field"
         tabindex="0"
         @pointermove="moveBasket($event)"
         @pointerdown="moveBasket($event)"
         @keydown.arrow-left.window="basketX = Math.max(5, basketX - 7)"
         @keydown.arrow-right.window="basketX = Math.min(95, basketX + 7)">

        <div class="kg-target-pill" x-text="targetText"></div>

        <template x-for="item in items" :key="item.id">
            <div class="kg-falling" :class="item.caught ? 'kg-falling--caught' : ''"
                 :style="'left:' + item.x + '%; top:' + item.y + 'px'">
                <span class="kg-falling-emoji" x-text="item.emoji"></span>
                <span class="kg-falling-num" x-text="item.value"></span>
            </div>
        </template>

        <div class="kg-basket" :style="'left:' + basketX + '%'">🧺</div>
    </div>

    <p class="kg-hint-text" style="text-align:center; margin:0;">
        Move with your mouse, finger or the ← → arrow keys and catch the matching numbers.
        Wrong numbers cost you a catch!
    </p>
</div>
