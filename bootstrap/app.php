<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function ($middleware) {
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);

        // Exclude login route from CSRF for WebView APK compatibility
        $middleware->validateCsrfTokens(except: [
            'login',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi keamanan CSRF telah kedaluwarsa. Silakan muat ulang halaman.',
                ], 419);
            }

            if (url()->previous() && url()->previous() !== url()->current()) {
                return redirect()->back()->withInput($request->except('_token', 'password', 'password_confirmation'))
                    ->with('warning', 'Sesi Anda telah kedaluwarsa. Silakan coba kirim ulang form.');
            }

            return response()->view('errors.419', ['exception' => $e], 419);
        });
    })->create();
