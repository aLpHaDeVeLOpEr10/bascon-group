<?php

namespace App\Providers;

use App\Auth\LegacyHashUserProvider;
use App\Services\RollupService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(RollupService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Auth driver that understands the legacy md5 (`users`) and plaintext
        // (`admin`) password formats and upgrades them to bcrypt on the first
        // successful login. See App\Auth\LegacyHashUserProvider.
        Auth::provider('legacy-eloquent', function ($app, array $config) {
            return (new LegacyHashUserProvider($app['hash'], $config['model']))
                ->setLegacyPlaintext((bool) ($config['legacy_plaintext'] ?? false));
        });
    }
}
