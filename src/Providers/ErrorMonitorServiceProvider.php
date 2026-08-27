<?php

declare(strict_types=1);

namespace HeritageApps\ErrorMonitor\Providers;

use HeritageApps\ErrorMonitor\Contracts\ErrorAnalyzer;
use HeritageApps\ErrorMonitor\Contracts\ErrorMonitorSettings;
use HeritageApps\ErrorMonitor\Contracts\ErrorNotifier;
use HeritageApps\ErrorMonitor\Services\ConfigErrorMonitorSettings;
use HeritageApps\ErrorMonitor\Services\MailErrorNotifier;
use HeritageApps\ErrorMonitor\Services\OpenAiFaultAnalyzer;
use HeritageApps\ErrorMonitor\Services\SystemErrorRecorder;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;
use Throwable;

class ErrorMonitorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/error-monitor.php',
            'error-monitor'
        );

        // Safe, working-out-of-the-box defaults - apps override any of these
        // by binding their own implementation in their own service provider.
        $this->app->bindIf(ErrorMonitorSettings::class, ConfigErrorMonitorSettings::class, shared: true);
        $this->app->bindIf(ErrorAnalyzer::class, OpenAiFaultAnalyzer::class, shared: true);
        $this->app->bindIf(ErrorNotifier::class, MailErrorNotifier::class, shared: true);

        $this->app->singleton(SystemErrorRecorder::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'error-monitor');

        $this->publishes([
            __DIR__.'/../../config/error-monitor.php' => config_path('error-monitor.php'),
        ], 'error-monitor-config');

        $this->publishes([
            __DIR__.'/../../database/migrations/landlord' => database_path('migrations/landlord'),
        ], 'error-monitor-migrations');

        $this->publishes([
            __DIR__.'/../../resources/views' => resource_path('views/vendor/error-monitor'),
        ], 'error-monitor-views');

        if (config('error-monitor.enabled', true)) {
            $this->registerExceptionReporting();
        }
    }

    /**
     * Hook into the app's exception handler so every unhandled exception is
     * captured automatically - no bootstrap/app.php edit required by the
     * host app. Mirrors what `$exceptions->report(...)` does in bootstrap/app.php.
     */
    private function registerExceptionReporting(): void
    {
        $handler = $this->app->make(ExceptionHandler::class);

        if (! method_exists($handler, 'reportable')) {
            return;
        }

        $handler->reportable(function (Throwable $e): void {
            try {
                $request = $this->app->bound('request') ? $this->app->make('request') : null;
                $this->app->make(SystemErrorRecorder::class)->record($e, $request);
            } catch (Throwable $inner) {
                // Never let a recording failure mask the original exception.
            }
        });
    }
}
