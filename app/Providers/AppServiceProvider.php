<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Carbon::setLocale(config('app.locale', 'id'));
        date_default_timezone_set(config('app.timezone', 'Asia/Jakarta'));

        RateLimiter::for('login', function (Request $request) {
            $username = strtolower((string) ($request->input('username') ?? $request->input('email') ?? ''));
            $key = $username ? $username.'|'.$request->ip() : $request->ip();

            return Limit::perMinute(5)->by($key)->response(function (Request $request, array $headers) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam 1 menit.',
                    ], 429, $headers);
                }

                return back()
                    ->withInput($request->only('username', 'email'))
                    ->with('warning', 'Terlalu banyak percobaan login. Silakan coba lagi dalam 1 menit.')
                    ->withHeaders($headers);
            });
        });
    }
}
