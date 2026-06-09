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
        // ── Force HTTPS in production / on Render ─────────────
        if ($this->app->environment('production') || env('RENDER')) {
            URL::forceScheme('https');
        }

        // ── Strict model behaviour (catches N+1 & mass-assignment in dev) ──
        Model::shouldBeStrict(! $this->app->isProduction());

        // ── Global password rules ─────────────────────────────
        Password::defaults(function () {
            return $this->app->isProduction()
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
