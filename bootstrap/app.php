<?php

use App\Http\Middleware\EnsureOrganizerApproved;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'organizer.approved' => EnsureOrganizerApproved::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            '/api/payment/callback',
            '/api/payout/callback',
        ]);
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpException $exception, Request $request) {
            if ($exception->getStatusCode() === 419 && ($request->is('login') || $request->is('register'))) {
                $redirectRoute = $request->is('register') ? 'register' : 'login';

                return redirect()
                    ->route($redirectRoute)
                    ->with('status', 'Sesi kamu sudah berakhir. Silakan login ulang.');
            }
        });
        $exceptions->render(function (TokenMismatchException $exception, Request $request) {
            if ($request->is('login') || $request->is('register')) {
                $redirectRoute = $request->is('register') ? 'register' : 'login';

                return redirect()
                    ->route($redirectRoute)
                    ->with('status', 'Sesi kamu sudah berakhir. Silakan login ulang.');
            }
        });
    })->create();
