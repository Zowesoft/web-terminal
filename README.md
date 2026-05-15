# 🖥️ Laravel Web Terminal

A secure, browser-based terminal for Laravel applications. Run Artisan, Git, Composer and shell commands directly from your browser — perfect for shared hosting environments where SSH access is unavailable.

---

## Features

- 🔐 **Dual security** — requires both Laravel auth session AND a personal terminal token
- ⚙️ **Artisan support** — run any Artisan command natively via `Artisan::call()`
- 🌿 **Git support** — `git pull`, `git status`, `git log` and more
- 📦 **Composer support** — `composer install`, `dump-autoload`, etc.
- 🛡️ **Command blacklist** — blocks dangerous commands like `rm -rf /`
- 🕹️ **Command history** — navigate previous commands with arrow keys
- ✏️ **Customisable** — publish config, views and migrations to override anything
- 🚀 Compatible with Laravel 9, 10, 11, 12 and 13

---

## Requirements

- PHP 8.1+
- Laravel 9.x / 10.x / 11.x / 12.x / 13.x
- `proc_open` or `shell_exec` enabled on your server (for shell commands)

> **Note:** Artisan commands work even if `shell_exec` is disabled, since they run in-process.

---

## Installation

### 1. Install via Composer

```bash
composer require Zowesoft/web-terminal
```

### 2. Publish and run the migration

```bash
php artisan vendor:publish --tag=web-terminal-migrations
php artisan migrate
```

This adds two columns to your `users` table:
- `is_admin` — boolean flag to grant terminal access
- `terminal_token` — the secret token required to unlock the terminal

### 3. Grant yourself access

```bash
php artisan tinker
```

```php
$user = User::where('email', 'you@example.com')->first();
$user->is_admin       = true;
$user->terminal_token = bin2hex(random_bytes(32));
$user->save();

// Copy this token — you'll need it to log in to the terminal
echo $user->terminal_token;
```

### 4. Visit the terminal

```
https://yourapp.com/admin/terminal
```

Log in with your Laravel account, paste your token when prompted, and you're in.

---

## Configuration

Publish the config file to customise behaviour:

```bash
php artisan vendor:publish --tag=web-terminal-config
```

```php
// config/web-terminal.php

return [
    'prefix'       => 'admin',          // URL prefix: /admin/terminal
    'middleware'   => ['web', 'auth'],  // Applied to all terminal routes
    'timeout'      => 120,              // Max seconds per command
    'admin_column' => 'is_admin',       // Column that marks admin users
    'token_column' => 'terminal_token', // Column that stores the token

    'blacklist' => [
        'rm -rf /',
        'rm -rf *',
        // add more blocked commands here
    ],

    'path' => '/usr/local/bin:/usr/bin:/bin', // Shell PATH for finding executables
];
```

---

## Customising the UI

Publish the views to override the terminal interface:

```bash
php artisan vendor:publish --tag=web-terminal-views
```

This copies the view to `resources/views/vendor/web-terminal/index.blade.php` where you can freely edit it.

---

## Environment Variables

You can configure the package via `.env` without publishing the config:

```env
WEB_TERMINAL_PREFIX=admin
WEB_TERMINAL_TIMEOUT=120
WEB_TERMINAL_PATH=/usr/local/bin:/usr/bin:/bin
```

---

## Security

This package uses two independent security layers:

```
Request → auth middleware → is_admin check → token check → command runs
```

1. **Laravel auth** — user must be logged in
2. **Admin flag** — `is_admin` must be `true` on the user record
3. **Terminal token** — a per-user secret sent as `X-Terminal-Token` on every request

Recommendations:
- Use a strong, unique token per user (the `bin2hex(random_bytes(32))` approach gives 64 hex chars)
- Add IP allowlisting via `.htaccess` or your server config for extra protection
- Consider disabling the terminal route in production when not in use via config

---

## Available Commands

| Category   | Examples |
|------------|---------|
| Artisan    | `artisan migrate --force`, `artisan cache:clear`, `artisan optimize` |
| Git        | `git pull origin main`, `git status`, `git log --oneline -10` |
| Composer   | `composer install --no-dev`, `composer dump-autoload` |
| PHP/Shell  | `php -v`, `ls -la`, `pwd` |
| Built-in   | `help`, `clear` |

---

## Troubleshooting

**Shell commands return no output**
Your host likely has `shell_exec` / `proc_open` disabled. Artisan commands will still work. Check with:
```bash
php -r "echo shell_exec('php -v');"
```

**`git` or `composer` not found**
Add the correct binary paths to `WEB_TERMINAL_PATH` in your `.env`. Find the path with:
```bash
which git
which composer
```

**403 on every request**
Make sure you have run the migration, set `is_admin = true` on your user, and are sending the correct token.

---

## License

MIT — see [LICENSE](LICENSE) for details.
