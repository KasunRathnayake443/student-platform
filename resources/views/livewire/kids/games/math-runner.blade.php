{{-- ── 🏃 Math Runner / Equation Quest (Ages 8-10) ────────────────
     A runner sprints toward a gate. Solve the problem on the gate and
     pick the right door to dash through before the clock runs out.
     ──────────────────────────────────────────────────────────────── --}}
<div class="kg-play" x-data="{
    phase: 'ready',
    gates: [],
    index: 0,
    correct: 0,
    total: 10,
    seconds: 90,
    timer: null,
    feedback: '',
    locked: false,
    slideKey: 0,

    init() {
        this.gates = this.makeGates();
        this.phase = 'playing';
        this.timer = setInterval(() => {
            if (this.phase !== 'playing') return;
            this.seconds--;
            if (this.seconds <= 0) { this.seconds = 0; this.finish(); }
        }, 1000);
    },

    get score() { return this.correct * 10; },
    get current() { return this.gates[this.index] || null; },

    makeGates() {
        const gates = [];

        for (let i = 0; i < this.total; i++) {
            const type = ['mul', 'div', 'add', 'sub'][Math.floor(Math.random() * 4)];
            let a, b, answer, prompt;

            if (type === 'mul') {
                a = 2 + Math.floor(Math.random() * 9);
                b = 2 + Math.floor(Math.random() * 9);
                answer = a * b;
                prompt = a + ' × ' + b + ' = ?';
            } else if (type === 'div') {
                b = 2 + Math.floor(Math.random() * 9);
                answer = 2 + Math.floor(Math.random() * 9);
                a = b * answer;
                prompt = a + ' ÷ ' + b + ' = ?';
            } else if (type === 'add') {
                a = 12 + Math.floor(Math.random() * 68);
                b = 12 + Math.floor(Math.random() * 68);
                answer = a + b;
                prompt = a + ' + ' + b + ' = ?';
            } else {
                a = 40 + Math.floor(Math.random() * 60);
                b = 11 + Math.floor(Math.random() * (a - 11));
                answer = a - b;
                prompt = a + ' − ' + b + ' = ?';
            }

            gates.push({ prompt: prompt, answer: answer, options: this.makeOptions(answer) });
        }

        return gates;
    },

    makeOptions(answer) {
        const options = [answer];
        while (options.length < 4) {
            const delta = (Math.floor(Math.random() * 7) + 1) * (Math.random() < 0.5 ? -1 : 1);
            const value = answer + delta;
            if (value >= 0 && options.indexOf(value) === -1) { options.push(value); }
        }
        for (let i = options.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            const tmp = options[i];
            options[i] = options[j];
            options[j] = tmp;
        }
        return options;
    },

    answer(gate, option) {
        if (this.locked || this.phase !== 'playing') return;

        if (option === gate.answer) {
            this.correct++;
            this.feedback = 'good';
            this.locked = true;
            setTimeout(() => this.next(), 480);
        } else {
            this.feedback = 'bad';
            this.seconds = Math.max(0, this.seconds - 5);
            setTimeout(() => { if (this.feedback === 'bad') { this.feedback = ''; } }, 950);
        }
    },

    next() {
        this.feedback = '';
        this.locked = false;
        if (this.index + 1 >= this.total) {
            this.finish();
            return;
        }
        this.index++;
        this.slideKey++;
    },

    finish() {
        if (this.phase !== 'playing') return;
        this.phase = 'done';
        if (this.timer) { clearInterval(this.timer); }
        this.$wire.finishGame(this.correct, this.total);
    }
}">

    <div class="kg-play-top">
        <div class="kg-play-head">
            <button type="button" class="kg-btn kg-btn-ghost" wire:click="backToHub">← Games</button>
            <div>
                <h3 class="kg-play-title">🏃 Math Runner</h3>
                <span class="kg-play-sub" x-text="'Gate ' + (index + 1) + ' of ' + total + ' · ⏱ ' + seconds + 's'"></span>
            </div>
        </div>
        <span class="kg-live-score" x-text="'⭐ ' + score"></span>
    </div>

    <div class="kg-runner-track">
        <div class="kg-runner-road"></div>
        <div class="kg-runner" :class="feedback === 'bad' ? 'kg-runner--stumble' : ''">🏃</div>

        <template x-for="gate in (current ? [current] : [])" :key="slideKey">
            <div class="kg-gate" :class="feedback === 'good' ? 'kg-gate--open' : ''">
                <div class="kg-gate-sign" x-text="gate.prompt"></div>
                <div class="kg-gate-post"></div>
                <div class="kg-gate-post kg-gate-post--right"></div>
            </div>
        </template>

        <div class="kg-goal">🏁</div>
    </div>

    <div class="kg-options" x-show="phase === 'playing'">
        <template x-for="(option, oi) in (current ? current.options : [])" :key="slideKey + '-' + oi">
            <button type="button" class="kg-option" @click="answer(current, option)" x-text="option"></button>
        </template>
    </div>

    <div x-show="feedback === 'good'" class="kg-feedback good">
        <span class="kg-feedback-emoji">🎉</span>
        <span class="kg-feedback-text">Correct! The gate swings open!</span>
    </div>

    <div x-show="feedback === 'bad'" class="kg-feedback bad">
        <span class="kg-feedback-emoji">🤔</span>
        <span class="kg-feedback-text">Oops! That gate stays shut and you lose 5 seconds. Try again!</span>
    </div>
</div>
