<?php

use Illuminate\Support\Facades\Route;
use Yourname\WebTerminal\Http\Controllers\WebTerminalController;

$prefix     = config('web-terminal.prefix', 'admin');
$middleware = config('web-terminal.middleware', ['web', 'auth']);

Route::prefix($prefix)
    ->middleware($middleware)
    ->group(function () {

        // Terminal UI — auth only (token is entered inside the UI)
        Route::get('/terminal', [WebTerminalController::class, 'index'])
            ->name('terminal.index');

        // Command runner — requires valid terminal token on every request
        Route::post('/terminal/run', [WebTerminalController::class, 'run'])
            ->middleware('terminal.access')
            ->name('terminal.run');
    });
