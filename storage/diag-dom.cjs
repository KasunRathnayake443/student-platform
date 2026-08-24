const { spawn } = require('child_process');
const http = require('http');

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const PORT = 9226;
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
        '--user-data-dir=' + require('os').tmpdir() + '\\chrome-diag-profile2',
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

    console.log('=== wire:id / wire:snapshot placement ===');
    console.log(await evalJs(`
        (() => {
            const out = [];
            document.querySelectorAll('[wire\\\\:id]').forEach(el => {
                out.push({
                    tag: el.tagName,
                    id: el.getAttribute('wire:id'),
                    cls: (el.className || '').toString().slice(0, 80),
                    hasSnapshot: el.hasAttribute('wire:snapshot'),
                    parent: el.parentElement ? el.parentElement.tagName : null,
                });
            });
            return JSON.stringify(out, null, 1);
        })()
    `));

    console.log('=== FileUpload element ancestry ===');
    console.log(await evalJs(`
        (() => {
            const fu = document.querySelector('[wire\\\\:model="data.profile_photo"], .filepond, [x-data*="fileUpload"], .fi-fo-file-upload');
            if (!fu) return 'no fileupload element found';
            let chain = [];
            let el = fu;
            for (let i = 0; i < 12 && el; i++) {
                chain.push(el.tagName + (el.hasAttribute && el.hasAttribute('wire:id') ? '[WIRE-ID]' : '') + (el.className ? '.' + el.className.toString().split(' ').slice(0,3).join('.') : ''));
                el = el.parentElement;
            }
            return chain.join('\\n → ');
        })()
    `));

    console.log('=== closest wire:id ancestor from a form input ===');
    console.log(await evalJs(`
        (() => {
            const inp = document.querySelector('#form input');
            if (!inp) return 'no input';
            let el = inp.closest('[wire\\\\:id]');
            return el ? ('FOUND: ' + el.tagName + ' #' + (el.id||'') ) : 'NO wire:id ANCESTOR!';
        })()
    `));

    console.log('=== JS ERRORS (' + jsErrors.length + ') ===');
    jsErrors.slice(0, 4).forEach((e) => console.log(e.split('\n').slice(0,3).join('\n')));

    ws.close();
    chrome.kill();
    process.exit(0);
})().catch((e) => { console.error('DIAG FAILED:', e.message); process.exit(1); });
