<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\Database\Eloquent\Model;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // ── Ensure DomPDF font cache dir exists ───────────────
        if (! is_dir(storage_path('fonts'))) {
            @mkdir(storage_path('fonts'), 0755, true);
        }
        // ── URL Configuration ────────────────────────────────────────────────
        if (env('RENDER') || $this->app->environment('production')) {
            // For Render/production - use HTTPS
            URL::forceScheme('https');
        } else {
            // For LOCAL DEVELOPMENT ONLY - 100% FORCE HTTP
            URL::forceScheme('http');
            URL::forceRootUrl('http://127.0.0.1:8000');
            $this->app['config']->set('app.url', 'http://127.0.0.1:8000');
            $this->app['config']->set('session.secure', false);
        }

        // ── Strict model behaviour (catches N+1 & mass-assignment in dev) ──
        Model::shouldBeStrict(! $this->app->environment('production'));

        // ── Global password rules ─────────────────────────────
        Password::defaults(function () {
            return $this->app->environment('production')
                ? Password::min(8)->mixedCase()->numbers()
                : Password::min(6);
        });

        // ── Rate limiter: 5 login attempts / minute per email+IP ──
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->input('email') . '|' . $request->ip());
        });
    }
}
