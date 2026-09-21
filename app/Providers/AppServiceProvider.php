<?php

namespace App\Providers;

use App\Auth\LegacyHashUserProvider;
use App\Services\RollupService;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
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

        // @money($value) — sugar over the money() helper in app/Support, which
        // is where the format itself is defined. Both spellings work in a
        // view; the directive reads better inline, the function composes.
        Blade::directive('money', fn ($expression) => "<?php echo e(money($expression)); ?>");

        $this->blockDestructiveMigrateCommands();
    }

    /**
     * Refuse `migrate:fresh`, `migrate:refresh` and `migrate:reset` in production.
     *
     * These rebuild the schema correctly now that the legacy tables have a
     * create migration, so they are left alone in development — a wiped local
     * database is a dump import away.
     *
     * Production is the live accounts system, and dropping it costs real work
     * even with a backup to restore from. Laravel's own `--force` prompt is
     * one keystroke, and this app has no seeders, so a fresh production
     * database would come back structurally perfect and completely empty.
     */
    private function blockDestructiveMigrateCommands(): void
    {
        Event::listen(function (CommandStarting $event) {
            if (! app()->isProduction()) {
                return;
            }

            if (! in_array($event->command, ['migrate:fresh', 'migrate:refresh', 'migrate:reset'], true)) {
                return;
            }

            $db = config('database.connections.'.config('database.default').'.database');

            $event->output->writeln(<<<TXT

<error>  {$event->command} is disabled while APP_ENV=production.  </error>

  It would DROP every table in <comment>{$db}</comment> and rebuild them empty —
  the migrations restore the schema, but nothing restores the rows.

  To rebuild this database deliberately, import a dump and then migrate:

    <info>mysql -u USER -p {$db} < /root/bascon-backups/<dump>.sql</info>
    <info>php artisan migrate --force</info>

  Guard: AppServiceProvider::blockDestructiveMigrateCommands().

TXT);

            exit(1);
        });
    }
}
