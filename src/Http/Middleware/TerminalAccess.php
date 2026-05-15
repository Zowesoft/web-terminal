<?php

namespace Zowesoft\WebTerminal\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TerminalAccess
{
    public function handle(Request $request, Closure $next): mixed
    {
        $adminColumn = config('web-terminal.admin_column', 'is_admin');
        $tokenColumn = config('web-terminal.token_column', 'terminal_token');

        // Must be authenticated
        if (! auth()->check()) {
            abort(403, 'Unauthenticated.');
        }

        $user = auth()->user();

        // Must be an admin
        if (! $user->{$adminColumn}) {
            abort(403, 'Access denied: not an admin.');
        }

        // Must provide the correct terminal token
        $providedToken = $request->header('X-Terminal-Token')
            ?? $request->input('terminal_token');

        $storedToken = $user->{$tokenColumn};

        if (
            ! $providedToken
            || ! $storedToken
            || ! hash_equals((string) $storedToken, (string) $providedToken)
        ) {
            abort(403, 'Invalid terminal token.');
        }

        return $next($request);
    }
}
