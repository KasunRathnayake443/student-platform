{{-- ── 🧩 Pattern / Coding Blocks (Ages 7-10) ─────────────────────
     Place arrow blocks to write a tiny program, then run it and
     guide the robot to the star.
     ──────────────────────────────────────────────────────────────── --}}
<div class="kg-play" x-data="{
    phase: 'ready',
    levels: [],
    index: 0,
    solved: 0,
    program: [],
    robot: { row: 0, col: 0 },
    running: false,
    failed: false,
    message: '',
    maxBlocks: 12,

    init() {
        this.levels = this.makeLevels();
        this.resetLevel();
        this.phase = 'playing';
    },

    get correct() { return this.solved; },
    get total() { return this.levels.length; },
    get score() { return this.solved * 10; },
    get current() { return this.levels[this.index] || null; },

    cellRow(i) { return Math.floor((i - 1) / 5); },
    cellCol(i) { return (i - 1) % 5; },
    isRobot(i) { return this.robot.row === this.cellRow(i) && this.robot.col === this.cellCol(i); },
    isGoal(i) {
        const goal = this.current.goal;
        return goal.row === this.cellRow(i) && goal.col === this.cellCol(i);
    },
    isWall(i) {
        return this.current.walls.some((wall) => wall.row === this.cellRow(i) && wall.col === this.cellCol(i));
    },

    makeLevels() {
        return [
            { start: { row: 4, col: 0 }, goal: { row: 4, col: 4 }, walls: [] },
            {
                start: { row: 4, col: 0 },
                goal: { row: 0, col: 4 },
                walls: [
                    { row: 4, col: 2 }, { row: 3, col: 2 }, { row: 2, col: 2 }, { row: 1, col: 2 },
                ],
            },
            {
                start: { row: 4, col: 4 },
                goal: { row: 0, col: 0 },
                walls: [
                    { row: 4, col: 2 }, { row: 3, col: 2 }, { row: 2, col: 2 },
                ],
            },
        ];
    },

    resetLevel() {
        const level = this.current;
        this.robot = { row: level.start.row, col: level.start.col };
        this.program = [];
        this.failed = false;
        this.message = '';
    },

    moveIcon(move) {
        return { up: '⬆️', down: '⬇️', left: '⬅️', right: '➡️' }[move] || '?';
    },

    addMove(direction) {
        if (this.running || this.phase !== 'playing') return;
        if (this.program.length >= this.maxBlocks) {
            this.message = 'Your program is full — undo or clear a block first.';
            return;
        }
        this.program.push(direction);
        this.failed = false;
        this.message = '';
    },

    removeLast() {
        if (this.running) return;
        this.program.pop();
    },

    clearProgram() {
        if (this.running) return;
        this.program = [];
        this.message = '';
    },

    step(row, col, direction) {
        let nextRow = row;
        let nextCol = col;

        if (direction === 'up') { nextRow--; }
        else if (direction === 'down') { nextRow++; }
        else if (direction === 'left') { nextCol--; }
        else if (direction === 'right') { nextCol++; }

        if (nextRow < 0 || nextRow > 4 || nextCol < 0 || nextCol > 4) return null;
        if (this.current.walls.some((wall) => wall.row === nextRow && wall.col === nextCol)) return null;

        return { row: nextRow, col: nextCol };
    },

    wait(ms) {
        return new Promise((resolve) => setTimeout(resolve, ms));
    },

    async run() {
        if (this.running || this.phase !== 'playing') return;

        if (this.program.length === 0) {
            this.message = 'Add some arrow blocks first!';
            return;
        }

        const level = this.current;
        let row = level.start.row;
        let col = level.start.col;
        this.running = true;
        this.failed = false;
        this.message = '';

        for (const direction of this.program) {
            const next = this.step(row, col, direction);

            if (!next) {
                this.failed = true;
                this.message = 'Bonk! That path hits a wall or runs off the board.';
                break;
            }

            row = next.row;
            col = next.col;
            this.robot = { row: row, col: col };
            await this.wait(240);

            if (row === level.goal.row && col === level.goal.col) break;
        }

        if (!this.failed && row === level.goal.row && col === level.goal.col) {
            this.solved++;
            this.message = 'You made it to the star!';
            await this.wait(650);
            this.nextLevel();
        } else if (!this.failed) {
            this.message = 'So close — the robot did not reach the star. Try again!';
        }

        this.running = false;
    },

    nextLevel() {
        if (this.index + 1 >= this.levels.length) {
            this.finish();
            return;
        }
        this.index++;
        this.resetLevel();
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
                <h3 class="kg-play-title">🧩 Coding Blocks</h3>
                <span class="kg-play-sub" x-text="'Level ' + (index + 1) + ' of ' + levels.length + ' · Solved ' + solved"></span>
            </div>
        </div>
        <span class="kg-live-score" x-text="'⭐ ' + score"></span>
    </div>

    <div class="kg-coding-grid">
        <template x-for="i in 25" :key="i">
            <div class="kg-cell"
                 :class="{
                    'kg-cell--wall': isWall(i),
                    'kg-cell--goal': isGoal(i),
                    'kg-cell--robot': isRobot(i)
                 }">
                <span x-show="isGoal(i) && !isRobot(i)">⭐</span>
                <span x-show="isRobot(i)">🤖</span>
            </div>
        </template>
    </div>

    <div class="kg-program">
        <template x-for="(move, mi) in program" :key="mi">
            <span class="kg-block" x-text="moveIcon(move)"></span>
        </template>
        <span x-show="program.length === 0" class="kg-program-empty">Tap arrows to build your code</span>
    </div>

    <div class="kg-arrangers">
        <div class="kg-arrows">
            <button type="button" class="kg-arrow" @click="addMove('up')">⬆️</button>
            <button type="button" class="kg-arrow" @click="addMove('left')">⬅️</button>
            <button type="button" class="kg-arrow" @click="addMove('down')">⬇️</button>
            <button type="button" class="kg-arrow" @click="addMove('right')">➡️</button>
        </div>

        <div class="kg-coding-actions">
            <button type="button" class="kg-btn kg-btn-green" @click="run()">▶ Run</button>
            <button type="button" class="kg-btn kg-btn-ghost" @click="removeLast()">↩ Undo</button>
            <button type="button" class="kg-btn kg-btn-ghost" @click="clearProgram()">🗑 Clear</button>
        </div>
    </div>

    <div x-show="message" class="kg-feedback" :class="failed ? 'bad' : 'good'">
        <span class="kg-feedback-emoji" x-text="failed ? '🧱' : '🤖'"></span>
        <span class="kg-feedback-text" x-text="message"></span>
    </div>

    <p class="kg-hint-text" style="text-align:center; margin:0;">
        Build a program with the arrows, press Run, and get the robot to the star without hitting a wall.
    </p>
</div>
