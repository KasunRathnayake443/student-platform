const { spawn } = require('child_process');
const http = require('http');

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const PORT = 9227;
const START_URL = 'http://student-platform.test/teacher/my-profile';

function getJson(path) {
    return new Promise((resolve, reject) => {
        http.get({ host: '127.0.0.1', port: PORT, path }, (res) => {
            let d = '';
            res.on('data', (c) => (d += c));
            res.on('end', () => resolve(JSON.parse(d)));
        }).on('error', reject);
    });
}

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

(async () => {
    const chrome = spawn(CHROME, [
        '--headless=new',
        `--remote-debugging-port=${PORT}`,
        '--user-data-dir=' + require('os').tmpdir() + '\\chrome-diag-profile3',
        '--no-first-run', '--disable-gpu', '--window-size=1400,900', 'about:blank',
    ], { stdio: 'ignore' });

    await sleep(2500);

    const tabs = await getJson('/json');
    const page = tabs.find((t) => t.type === 'page');
    const ws = new WebSocket(page.webSocketDebuggerUrl);
    let id = 0;
    const pending = new Map();
    const jsErrors = [];

    const send = (method, params = {}) =>
        new Promise((resolve) => {
            const msgId = ++id;
            pending.set(msgId, resolve);
            ws.send(JSON.stringify({ id: msgId, method, params }));
        });

    ws.onmessage = (ev) => {
        const msg = JSON.parse(ev.data);
        if (msg.id && pending.has(msg.id)) { pending.get(msg.id)(msg.result); pending.delete(msg.id); }
        else if (msg.method === 'Runtime.exceptionThrown') {
            const d = msg.params.exceptionDetails;
            jsErrors.push(d.exception?.description || d.text || 'unknown');
        }
    };

    await new Promise((r) => (ws.onopen = r));
    await send('Runtime.enable');
    await send('Page.enable');

    await send('Page.navigate', { url: START_URL });
    await sleep(9000);

    const evalJs = async (expr) => {
        const r = await send('Runtime.evaluate', { expression: expr, returnByValue: true });
        return r.result?.value;
    };

    console.log('=== inputs inside #form ===');
    console.log(await evalJs(`
        (() => {
            const out = [];
            document.querySelectorAll('#form input, #form textarea').forEach(i => {
                out.push({ model: i.getAttribute('wire:model') || i.getAttribute('wire:model.blur') || '-', type: i.type, value: (i.value||'').slice(0,40), visible: !!(i.offsetParent || i.getClientRects().length) });
            });
            return JSON.stringify(out, null, 1);
        })()
    `));

    console.log('=== filepond / upload related nodes anywhere ===');
    console.log(await evalJs(`
        (() => {
            const sels = ['.filepond--root', '.filepond', '[class*="filepond"]', '[x-data*="FileUpload"]', '[x-data*="fileUpload"]', '.fi-fo-file-upload'];
            const found = {};
            sels.forEach(s => found[s] = document.querySelectorAll(s).length);
            return JSON.stringify(found);
        })()
    `));

    console.log('=== name input value & Alpine state ===');
    console.log(await evalJs(`
        (() => {
            const inp = document.querySelector('input[wire\\\\:model="data.name"]');
            if (!inp) return 'name input NOT FOUND';
            const comp = window.Livewire.first();
            return JSON.stringify({
                domValue: inp.value,
                lwName: comp.get ? String(comp.get('data.name')) : 'n/a',
                lwIsolated: comp.isolated,
                alpineBound: !!inp.closest('[x-data]'),
            });
        })()
    `));

    console.log('=== JS ERRORS (' + jsErrors.length + ') ===');
    jsErrors.slice(0, 4).forEach((e) => console.log(e.split('\n').slice(0,4).join('\n')));

    ws.close();
    chrome.kill();
    process.exit(0);
})().catch((e) => { console.error('DIAG FAILED:', e.message); process.exit(1); });
