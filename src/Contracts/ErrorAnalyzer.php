<?php

declare(strict_types=1);

namespace HeritageApps\ErrorMonitor\Contracts;

use HeritageApps\ErrorMonitor\Models\SystemError;

/**
 * Apps may bind their own implementation in a service provider to route
 * analysis through their own AI client (e.g. for cost tracking):
 *
 *   $this->app->bind(ErrorAnalyzer::class, MyCostTrackedAnalyzer::class);
 */
interface ErrorAnalyzer
{
    /**
     * Return a short root-cause writeup for the error, or null if analysis
     * is unavailable or fails. Implementations should catch their own
     * errors (network, API) internally and return null rather than throw.
     */
    public function analyze(SystemError $error): ?string;
}
