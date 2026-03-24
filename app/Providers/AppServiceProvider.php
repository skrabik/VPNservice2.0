<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        if ($this->app->runningInConsole()) {
            return;
        }

        $host = request()->getHost();

        if (! $this->isLocalHost($host)) {
            return;
        }

        // Localhost should ignore production cookie settings,
        // otherwise copied SESSION_DOMAIN / secure flags can cause 419s.
        config([
            'session.domain' => null,
            'session.secure' => false,
            'session.same_site' => 'lax',
        ]);
    }

    private function isLocalHost(string $host): bool
    {
        return in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            || Str::endsWith($host, '.test');
    }
}
