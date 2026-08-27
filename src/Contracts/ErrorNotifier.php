<?php

declare(strict_types=1);

namespace HeritageApps\ErrorMonitor\Contracts;

use HeritageApps\ErrorMonitor\Models\SystemError;

/**
 * Apps may bind their own implementation in a service provider to send the
 * alert through their own branded Mailable:
 *
 *   $this->app->bind(ErrorNotifier::class, MyBrandedErrorNotifier::class);
 */
interface ErrorNotifier
{
    /**
     * Deliver the new/repeat error alert. $analysis is the AI writeup, or
     * null if analysis was disabled/unavailable.
     */
    public function notify(SystemError $error, ?string $analysis): void;
}
