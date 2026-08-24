const { spawn } = require('child_process');
const http = require('http');

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const PORT = 9223;
const START_URL = 'http://student-platform.test/dev-login-teacher1';

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
        '--user-data-dir=' + require('os').tmpdir() + '\\chrome-diag-profile',
        '--no-first-run',
        '--disable-gpu',
        '--window-size=1400,900',
        'about:blank',
    ], { stdio: 'ignore' });

    await sleep(2500);

    const tabs = await getJson('/json');
    const page = tabs.find((t) => t.type === 'page');
    const ws = new WebSocket(page.webSocketDebuggerUrl);
    let id = 0;
    const pending = new Map();
    const consoleLogs = [];
    const jsErrors = [];

    const send = (method, params = {}) =>
        new Promise((resolve) => {
            const msgId = ++id;
            pending.set(msgId, resolve);
            ws.send(JSON.stringify({ id: msgId, method, params }));
        });

    ws.onmessage = (ev) => {
        const msg = JSON.parse(ev.data);
        if (msg.id && pending.has(msg.id)) {
            pending.get(msg.id)(msg.result);
            pending.delete(msg.id);
        } else if (msg.method === 'Runtime.consoleAPICalled') {
            const text = (msg.params.args || []).map((a) => a.value ?? a.description ?? '').join(' ');
            consoleLogs.push(`[${msg.params.type}] ${text}`);
        } else if (msg.method === 'Runtime.exceptionThrown') {
            const d = msg.params.exceptionDetails;
            jsErrors.push((d.exception?.description || d.text || 'unknown') + ' ||| STACK: ' + JSON.stringify((d.stackTrace?.callFrames||[]).slice(0,5).map(f=>f.functionName+'@'+f.url+':'+f.lineNumber)));
        }
    };

    await new Promise((r) => (ws.onopen = r));
    await send('Runtime.enable');
    await send('Page.enable');

    await send('Page.navigate', { url: START_URL });
    await sleep(9000); // allow redirects + JS boot

    const evalJs = async (expr) => {
        const r = await send('Runtime.evaluate', { expression: expr, returnByValue: true });
        return r.result?.value;
    };

    const url = await evalJs('location.href');
    const livewireType = await evalJs('typeof window.Livewire');
    const alpineType = await evalJs('typeof window.Alpine');
    const nameValue = await evalJs(`document.querySelector('input[wire\\\\\\\\:model="data.name"]')?.value ?? null`);
    const emailValue = await evalJs(`document.querySelector('input[wire\\\\\\\\:model="data.email"]')?.value ?? null`);
    const inputCount = await evalJs(`document.querySelectorAll('#form input').length`);
    const saveBtn = await evalJs(`[...document.querySelectorAll('button')].some(b => b.textContent.includes('Save Changes'))`);
    const snapshotOk = await evalJs(`!!document.querySelector('[wire\\\\\\\\:snapshot]')`);

    console.log('=== DIAG RESULTS ===');
    console.log('final URL:', url);
    console.log('window.Livewire:', livewireType);
    console.log('window.Alpine:', alpineType);
    console.log('name field value:', JSON.stringify(nameValue));
    console.log('email field value:', JSON.stringify(emailValue));
    console.log('inputs inside #form:', inputCount);
    console.log('Save Changes button present:', saveBtn);
    console.log('wire:snapshot element present:', snapshotOk);
    console.log('=== JS ERRORS (' + jsErrors.length + ') ===');
    jsErrors.slice(0,10).forEach(e => console.log(e));
    console.log('=== CONSOLE (' + consoleLogs.length + ') ===');
    consoleLogs.slice(0, 25).forEach((l) => console.log(l));

    ws.close();
    chrome.kill();
    process.exit(0);
})().catch((e) => { console.error('DIAG FAILED:', e.message); process.exit(1); });
