<?php

declare(strict_types=1);

namespace HeritageApps\ErrorMonitor\Services;

use HeritageApps\ErrorMonitor\Contracts\ErrorMonitorSettings;

/**
 * Default settings source, read from config/error-monitor.php (env-backed).
 * Bound unless the host app registers its own ErrorMonitorSettings.
 */
final class ConfigErrorMonitorSettings implements ErrorMonitorSettings
{
    public function recipients(): array
    {
        return config('error-monitor.recipients', []);
    }

    public function repeatThresholdMinutes(): int
    {
        return (int) config('error-monitor.repeat_threshold_minutes', 60);
    }
}
