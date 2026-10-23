{{-- ── 🎈 Phonics Balloon Pop (Ages 5-7) ──────────────────────────
     Balloons float up with letters on them. Listen to the sound and
     pop the balloon that carries the matching letter.
     ──────────────────────────────────────────────────────────────── --}}
<div class="kg-play" x-data="{
    phase: 'ready',
    rounds: [],
    index: 0,
    correct: 0,
    total: 10,
    popped: -1,
    wobble: -1,

    init() {
        this.rounds = this.makeRounds();
        this.phase = 'playing';
        // Speech is a nice to have — never let it break the game.
        setTimeout(() => this.speak(), 350);
    },

    get score() { return this.correct * 10; },
    get current() { return this.rounds[this.index] || null; },
    get promptText() {
        const round = this.current;
        return round ? ('Find the letter that makes the “' + round.letter + '” sound') : '';
    },

    makeRounds() {
        const letters = ['b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'r', 's', 't', 'v', 'w', 'y', 'z'];
        const used = [];
        const rounds = [];

        for (let i = 0; i < this.total; i++) {
            let letter;
            do {
                letter = letters[Math.floor(Math.random() * letters.length)];
            } while (used.indexOf(letter) !== -1 && used.length < letters.length);
            used.push(letter);

            const distractors = [];
            while (distractors.length < 3) {
                const candidate = letters[Math.floor(Math.random() * letters.length)];
                if (candidate !== letter && distractors.indexOf(candidate) === -1) {
                    distractors.push(candidate);
                }
            }

            const options = [letter].concat(distractors);
            for (let k = options.length - 1; k > 0; k--) {
                const j = Math.floor(Math.random() * (k + 1));
                const tmp = options[k];
                options[k] = options[j];
                options[j] = tmp;
            }

            rounds.push({ letter: letter, options: options });
        }

        return rounds;
    },

    pop(round, option, optionIndex) {
        if (this.phase !== 'playing' || this.popped !== -1) return;

        if (option === round.letter) {
            this.popped = optionIndex;
            this.correct++;
            setTimeout(() => this.next(), 620);
        } else {
            this.wobble = optionIndex;
            setTimeout(() => { this.wobble = -1; }, 520);
        }
    },

    next() {
        this.popped = -1;
        this.wobble = -1;
        if (this.index + 1 >= this.total) {
            this.finish();
            return;
        }
        this.index++;
        setTimeout(() => this.speak(), 250);
    },

    speak() {
        try {
            if (!window.speechSynthesis) return;
            const round = this.current;
            if (!round) return;
            window.speechSynthesis.cancel();
            const utterance = new SpeechSynthesisUtterance(
                'Find the letter that makes the ' + round.letter + ' sound'
            );
            utterance.lang = 'en-US';
            utterance.rate = 0.85;
            window.speechSynthesis.speak(utterance);
        } catch (error) {
            // Speech is optional — ignore any failure.
        }
    },

    finish() {
        if (this.phase !== 'playing') return;
        this.phase = 'done';
        try { if (window.speechSynthesis) { window.speechSynthesis.cancel(); } } catch (error) {}
        this.$wire.finishGame(this.correct, this.total);
    }
}">

    <div class="kg-play-top">
        <div class="kg-play-head">
            <button type="button" class="kg-btn kg-btn-ghost" wire:click="backToHub">← Games</button>
            <div>
                <h3 class="kg-play-title">🎈 Phonics Balloon Pop</h3>
                <span class="kg-play-sub" x-text="'Balloon ' + (index + 1) + ' of ' + total"></span>
            </div>
        </div>
        <span class="kg-live-score" x-text="'⭐ ' + score"></span>
    </div>

    <div class="kg-prompt-card">
        <div class="kg-prompt-emoji">🎈</div>
        <p class="kg-prompt" x-text="promptText"></p>
        <button type="button" class="kg-btn kg-btn-ghost" @click="speak()">🔊 Listen again</button>
    </div>

    <div class="kg-balloon-field">
        <template x-for="(option, oi) in (current ? current.options : [])" :key="index + '-' + oi">
            <button type="button"
                    class="kg-balloon"
                    :class="{
                        'kg-balloon--pop': popped === oi,
                        'kg-balloon--wobble': wobble === oi
                    }"
                    :style="'--float-duration:' + (4.6 + oi * 0.9) + 's; --float-delay:' + (oi * 0.7) + 's; --balloon-left:' + (8 + oi * 22) + '%; --balloon-hue:' + (oi * 67) + ';'"
                    @click="pop(current, option, oi)">
                <span class="kg-balloon-letter" x-text="option"></span>
            </button>
        </template>
    </div>

    <p class="kg-hint-text" style="text-align:center; margin:0;">
        Pop the balloon with the matching letter. Pop the wrong one and it wobbles!
    </p>
</div>
