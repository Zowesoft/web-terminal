<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Terminal — {{ config('app.name') }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #0d1117;
            --surface:   #010409;
            --border:    #30363d;
            --text:      #c9d1d9;
            --muted:     #8b949e;
            --blue:      #58a6ff;
            --green:     #3fb950;
            --red:       #f85149;
            --yellow:    #d29922;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Courier New', Courier, monospace;
            height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 16px;
            gap: 12px;
            overflow: hidden;
        }

        /* ── Header ─────────────────────────────── */
        header {
            display: flex;
            align-items: center;
            gap: 8px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }

        .dot { width: 13px; height: 13px; border-radius: 50%; cursor: default; }
        .dot-red    { background: #ff5f57; }
        .dot-yellow { background: #febc2e; }
        .dot-green  { background: #28c840; }

        .header-title {
            margin-left: 8px;
            font-size: 13px;
            color: var(--muted);
        }

        .header-user {
            margin-left: auto;
            font-size: 12px;
            color: var(--muted);
            background: #161b22;
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 3px 12px;
        }

        /* ── Token Gate ──────────────────────────── */
        #token-gate {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 18px;
        }

        .gate-icon { font-size: 40px; }

        .gate-title {
            font-size: 16px;
            color: var(--blue);
            font-weight: bold;
        }

        .gate-subtitle {
            font-size: 12px;
            color: var(--muted);
            text-align: center;
            max-width: 380px;
            line-height: 1.6;
        }

        #token-input {
            width: 380px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text);
            font-family: 'Courier New', monospace;
            font-size: 13px;
            padding: 11px 16px;
            outline: none;
            letter-spacing: 1px;
            transition: border-color 0.2s;
        }

        #token-input:focus { border-color: var(--blue); }

        #unlock-btn {
            background: #238636;
            border: 1px solid #2ea043;
            border-radius: 6px;
            color: #fff;
            cursor: pointer;
            font-size: 13px;
            font-family: 'Courier New', monospace;
            padding: 10px 32px;
            transition: background 0.2s;
        }

        #unlock-btn:hover { background: #2ea043; }
        #unlock-btn:disabled { opacity: 0.5; cursor: not-allowed; }

        #token-error {
            color: var(--red);
            font-size: 12px;
            display: none;
        }

        /* ── Terminal ────────────────────────────── */
        #terminal {
            flex: 1;
            display: none;
            flex-direction: column;
            gap: 10px;
            min-height: 0;
        }

        #output {
            flex: 1;
            overflow-y: auto;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 14px;
            font-size: 13px;
            line-height: 1.7;
            white-space: pre-wrap;
            word-break: break-all;
            scrollbar-width: thin;
            scrollbar-color: var(--border) transparent;
        }

        #output::-webkit-scrollbar { width: 6px; }
        #output::-webkit-scrollbar-track { background: transparent; }
        #output::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

        .line-cmd     { color: var(--blue); }
        .line-success { color: var(--text); }
        .line-error   { color: var(--red); }
        .line-info    { color: var(--muted); font-style: italic; }
        .line-warn    { color: var(--yellow); }

        /* ── Input Row ───────────────────────────── */
        #input-row {
            display: flex;
            align-items: center;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 11px 14px;
            gap: 8px;
            flex-shrink: 0;
            transition: border-color 0.2s;
        }

        #input-row:focus-within { border-color: var(--blue); }

        #prompt {
            color: var(--green);
            white-space: nowrap;
            font-size: 13px;
            user-select: none;
        }

        #cmd-input {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            color: var(--text);
            font-family: 'Courier New', monospace;
            font-size: 13px;
            caret-color: var(--blue);
        }

        #cmd-input:disabled { opacity: 0.5; }

        #spinner {
            display: none;
            width: 14px;
            height: 14px;
            border: 2px solid var(--border);
            border-top-color: var(--blue);
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            flex-shrink: 0;
        }

        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

{{-- ── Header ──────────────────────────────────── --}}
<header>
    <div class="dot dot-red" title="close"></div>
    <div class="dot dot-yellow" title="minimise"></div>
    <div class="dot dot-green" title="maximise"></div>
    <span class="header-title">{{ config('app.name') }} — Web Terminal</span>
    <span class="header-user">{{ auth()->user()->name }}</span>
</header>

{{-- ── Token Gate ───────────────────────────────── --}}
<div id="token-gate">
    <div class="gate-icon">🔐</div>
    <div class="gate-title">Terminal Authentication</div>
    <div class="gate-subtitle">
        Enter your personal terminal token to unlock the terminal session.
        Your token is stored in the database — run the setup command if you don't have one yet.
    </div>
    <input
        type="password"
        id="token-input"
        placeholder="Paste your terminal token..."
        autocomplete="off"
    />
    <div id="token-error"></div>
    <button id="unlock-btn" onclick="unlockTerminal()">Unlock Terminal</button>
</div>

{{-- ── Terminal (hidden until unlocked) ───────────── --}}
<div id="terminal">
    <div id="output"></div>
    <div id="input-row">
        <span id="prompt">{{ config('app.name') }}:~$&nbsp;</span>
        <input
            type="text"
            id="cmd-input"
            autocomplete="off"
            spellcheck="false"
            placeholder="type a command... (help for list)"
        />
        <div id="spinner"></div>
    </div>
</div>

<script>
// ─────────────────────────────────────────────────
//  State
// ─────────────────────────────────────────────────
let TERMINAL_TOKEN = null;
let running        = false;
const cmdHistory   = [];
let histIdx        = -1;

// ─────────────────────────────────────────────────
//  Help text
// ─────────────────────────────────────────────────
const HELP = `
Commands
─────────────────────────────────────────────
  Git
    git status
    git pull origin main
    git fetch --all
    git log --oneline -10
    git diff
    git branch -a
    git checkout <branch>
    git stash / git stash pop

  Composer
    composer install --no-dev --optimize-autoloader
    composer dump-autoload
    composer update
    composer require vendor/package

  Artisan
    artisan migrate --force
    artisan migrate:status
    artisan config:cache
    artisan config:clear
    artisan cache:clear
    artisan route:cache
    artisan route:clear
    artisan view:clear
    artisan queue:restart
    artisan storage:link
    artisan optimize
    artisan optimize:clear
    artisan make:controller Name
    artisan make:model Name -m

  PHP / System
    php -v
    php -m
    ls -la
    pwd
    env | grep APP

  Terminal Built-ins
    clear     — clear the screen
    help      — show this help
─────────────────────────────────────────────
`;

// ─────────────────────────────────────────────────
//  Token Gate
// ─────────────────────────────────────────────────
async function unlockTerminal() {
    const tokenInput = document.getElementById('token-input');
    const errorEl    = document.getElementById('token-error');
    const btn        = document.getElementById('unlock-btn');
    const token      = tokenInput.value.trim();

    if (!token) return;

    btn.disabled     = true;
    btn.textContent  = 'Verifying...';
    errorEl.style.display = 'none';

    try {
        const res = await fetch('{{ route("terminal.run") }}', {
            method: 'POST',
            headers: {
                'Content-Type':    'application/json',
                'X-CSRF-TOKEN':    '{{ csrf_token() }}',
                'X-Terminal-Token': token,
            },
            body: JSON.stringify({ command: 'pwd' }),
        });

        if (res.status === 403) {
            errorEl.textContent    = '❌ Invalid token. Please check and try again.';
            errorEl.style.display  = 'block';
            btn.disabled           = false;
            btn.textContent        = 'Unlock Terminal';
            return;
        }

        // ✅ Token accepted
        TERMINAL_TOKEN = token;
        document.getElementById('token-gate').style.display  = 'none';
        document.getElementById('terminal').style.display    = 'flex';
        document.getElementById('cmd-input').focus();
        print('✅  Terminal unlocked. Type "help" to see available commands.\n', 'info');

    } catch (err) {
        errorEl.textContent   = '❌ Connection error: ' + err.message;
        errorEl.style.display = 'block';
        btn.disabled          = false;
        btn.textContent       = 'Unlock Terminal';
    }
}

document.getElementById('token-input')
    .addEventListener('keydown', e => { if (e.key === 'Enter') unlockTerminal(); });

// ─────────────────────────────────────────────────
//  Terminal Output
// ─────────────────────────────────────────────────
const output = document.getElementById('output');

function print(text, type = 'success') {
    const div       = document.createElement('div');
    div.className   = `line-${type}`;
    div.textContent = text;
    output.appendChild(div);
    output.scrollTop = output.scrollHeight;
}

// ─────────────────────────────────────────────────
//  Command Input
// ─────────────────────────────────────────────────
const input   = document.getElementById('cmd-input');
const spinner = document.getElementById('spinner');

input.addEventListener('keydown', async (e) => {
    // History navigation
    if (e.key === 'ArrowUp') {
        histIdx      = Math.min(histIdx + 1, cmdHistory.length - 1);
        input.value  = cmdHistory[histIdx] ?? '';
        e.preventDefault();
        return;
    }
    if (e.key === 'ArrowDown') {
        histIdx      = Math.max(histIdx - 1, -1);
        input.value  = histIdx === -1 ? '' : cmdHistory[histIdx];
        e.preventDefault();
        return;
    }

    if (e.key !== 'Enter' || running) return;

    const cmd = input.value.trim();
    if (!cmd) return;

    cmdHistory.unshift(cmd);
    histIdx    = -1;
    input.value = '';

    print(`$ ${cmd}`, 'cmd');

    // Built-ins
    if (cmd === 'clear') { output.innerHTML = ''; return; }
    if (cmd === 'help')  { print(HELP, 'info'); return; }

    // Remote command
    running            = true;
    spinner.style.display = 'block';
    input.disabled     = true;

    try {
        const res = await fetch('{{ route("terminal.run") }}', {
            method: 'POST',
            headers: {
                'Content-Type':     'application/json',
                'X-CSRF-TOKEN':     '{{ csrf_token() }}',
                'X-Terminal-Token': TERMINAL_TOKEN,
            },
            body: JSON.stringify({ command: cmd }),
        });

        if (res.status === 403) {
            print('❌ Session rejected — reload the page and re-enter your token.', 'error');
        } else {
            const data = await res.json();
            print(
                data.output || '(no output)',
                data.status === 'error' ? 'error' : 'success'
            );
        }
    } catch (err) {
        print('❌ Network error: ' + err.message, 'error');
    } finally {
        running               = false;
        spinner.style.display = 'none';
        input.disabled        = false;
        input.focus();
    }
});
</script>

</body>
</html>
