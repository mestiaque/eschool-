// Minimal Chrome DevTools Protocol driver: log in, visit pages, record JS errors
// and failing same-origin requests, then run interaction checks.
import { spawn } from 'node:child_process';
import { readFileSync, mkdtempSync } from 'node:fs';
import { pathToFileURL } from 'node:url';
import { tmpdir } from 'node:os';
import { join } from 'node:path';

const BASE = 'http://127.0.0.1:8123';
const urls = JSON.parse(readFileSync(process.argv[2], 'utf8'));
const checksFile = process.argv[3];
const sleep = ms => new Promise(r => setTimeout(r, ms));

const chrome = spawn('google-chrome', ['--headless=new', '--no-sandbox', '--disable-gpu', '--remote-debugging-port=9333',
  '--user-data-dir=' + mkdtempSync(join(tmpdir(), 'cdp-')), '--window-size=1400,900', 'about:blank'], { stdio: 'ignore' });

let wsUrl;
for (let i = 0; i < 50 && !wsUrl; i++) {
  try { const t = await (await fetch('http://127.0.0.1:9333/json/list')).json(); wsUrl = t.find(x => x.type === 'page')?.webSocketDebuggerUrl; } catch {}
  if (!wsUrl) await sleep(200);
}
const ws = new WebSocket(wsUrl);
await new Promise(r => ws.addEventListener('open', r));
let id = 0; const pending = new Map(); const listeners = [];
ws.addEventListener('message', ev => {
  const msg = JSON.parse(ev.data);
  if (msg.id && pending.has(msg.id)) { pending.get(msg.id)(msg); pending.delete(msg.id); }
  else listeners.forEach(l => l(msg));
});
const send = (method, params = {}) => new Promise(r => { const i = ++id; pending.set(i, r); ws.send(JSON.stringify({ id: i, method, params })); });
const evaluate = async (expr) => (await send('Runtime.evaluate', { expression: expr, awaitPromise: true, returnByValue: true })).result?.result?.value;

let errors = [];
listeners.push(m => {
  if (m.method === 'Runtime.exceptionThrown') errors.push('JS: ' + (m.params.exceptionDetails.exception?.description || m.params.exceptionDetails.text).split('\n')[0]);
  if (m.method === 'Runtime.consoleAPICalled' && m.params.type === 'error') errors.push('console: ' + m.params.args.map(a => a.value ?? a.description ?? '').join(' ').slice(0, 200));
  if (m.method === 'Network.responseReceived' && !m.params.response.url.includes('undraw_profile') && m.params.response.url.startsWith(BASE) && m.params.response.status >= 400)
    errors.push(`HTTP ${m.params.response.status}: ${m.params.response.url.replace(BASE, '')}`);
});
await send('Runtime.enable'); await send('Page.enable'); await send('Network.enable');

const go = async (url) => {
  let loaded = false; const l = m => { if (m.method === 'Page.loadEventFired') loaded = true; };
  listeners.push(l);
  await send('Page.navigate', { url: BASE + url });
  for (let i = 0; i < 100 && !loaded; i++) await sleep(100);
  listeners.splice(listeners.indexOf(l), 1);
  await sleep(700);
};

// Log in
await go('/login');
await evaluate(`document.querySelector('[name=user_name]').value='browser@test.local';document.querySelector('[name=password]').value='browser-pass';document.querySelector('form[action$="/login"]').submit();true`);
await sleep(2500);
console.log('after login:', await evaluate('location.pathname'));

const report = {};
for (const url of urls) {
  errors = [];
  await go(url);
  const path = await evaluate('location.pathname');
  if (path === '/login') { report[url] = ['redirected to login']; continue; }
  if (errors.length) report[url] = [...new Set(errors)];
}
console.log('PAGE ERRORS:', JSON.stringify(report, null, 1));

if (checksFile) {
  const checks = (await import(pathToFileURL(checksFile).href)).default;
  for (const c of checks) {
    errors = [];
    await go(c.url);
    let result;
    try { result = await evaluate(`(async () => { const sleep = ms => new Promise(r => setTimeout(r, ms)); ${c.js} })()`); }
    catch (e) { result = 'eval failed ' + e; }
    console.log((result === true ? 'PASS ' : 'FAIL ') + c.name + (result === true ? '' : '  → ' + JSON.stringify(result)) + (errors.length ? '  errors: ' + JSON.stringify([...new Set(errors)]) : ''));
  }
}
ws.close(); chrome.kill();
process.exit(0);
