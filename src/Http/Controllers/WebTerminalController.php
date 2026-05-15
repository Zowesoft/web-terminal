<?php

namespace Zowesoft\WebTerminal\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;

class WebTerminalController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        $adminColumn = config('web-terminal.admin_column', 'is_admin');

        abort_unless(
            auth()->check() && auth()->user()->{$adminColumn},
            403,
            'Access denied.'
        );

        return view('web-terminal::index');
    }

    public function run(Request $request): JsonResponse
    {
        $command = trim($request->input('command', ''));

        if (empty($command)) {
            return response()->json(['output' => '', 'status' => 'success']);
        }

        // Check blacklist
        $blacklist = config('web-terminal.blacklist', []);

        foreach ($blacklist as $banned) {
            if (str_contains(strtolower($command), strtolower($banned))) {
                return response()->json([
                    'output' => "❌ Blocked: this command is not allowed.",
                    'status' => 'error',
                ]);
            }
        }

        // Route artisan commands through Artisan::call()
        if (preg_match('/^(?:php\s+)?artisan\s+(.+)$/', $command, $matches)) {
            return $this->runArtisan($matches[1]);
        }

        return $this->runShell($command);
    }

    // -------------------------------------------------------------------------

    private function runArtisan(string $command): JsonResponse
    {
        try {
            $parts = explode(' ', trim($command));
            $cmd   = array_shift($parts);
            $args  = [];

            foreach ($parts as $part) {
                if (str_starts_with($part, '--')) {
                    // Handle --flag=value
                    if (str_contains($part, '=')) {
                        [$key, $val] = explode('=', $part, 2);
                        $args[$key]  = $val;
                    } else {
                        $args[$part] = true;
                    }
                } else {
                    $args[] = $part;
                }
            }

            Artisan::call($cmd, $args);
            $output = Artisan::output();

            return response()->json([
                'output' => $output ?: "✅ Command completed successfully.",
                'status' => 'success',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'output' => '❌ Artisan error: ' . $e->getMessage(),
                'status' => 'error',
            ]);
        }
    }

    private function runShell(string $command): JsonResponse
    {
        try {
            $process = Process::fromShellCommandline($command);

            $process->setWorkingDirectory(base_path());
            $process->setTimeout((int) config('web-terminal.timeout', 120));
            $process->setEnv([
                'PATH' => config('web-terminal.path', '/usr/local/bin:/usr/bin:/bin')
                    . ':' . (getenv('PATH') ?: ''),
                'HOME' => base_path(),
            ]);

            $process->run();

            $output = $process->getOutput() . $process->getErrorOutput();

            return response()->json([
                'output' => $output ?: '(no output)',
                'status' => $process->isSuccessful() ? 'success' : 'error',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'output' => '❌ Shell error: ' . $e->getMessage(),
                'status' => 'error',
            ]);
        }
    }
}
