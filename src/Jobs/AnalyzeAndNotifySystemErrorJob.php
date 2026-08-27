<?php

declare(strict_types=1);

namespace HeritageApps\ErrorMonitor\Jobs;

use HeritageApps\ErrorMonitor\Contracts\ErrorAnalyzer;
use HeritageApps\ErrorMonitor\Contracts\ErrorNotifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Jobs\NotTenantAware;
use Throwable;

final class AnalyzeAndNotifySystemErrorJob implements NotTenantAware, ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $timeout = 120;

    public function __construct(public readonly int $systemErrorId) {}

    public function handle(ErrorAnalyzer $analyzer, ErrorNotifier $notifier): void
    {
        $modelClass = config('error-monitor.models.system_error');
        $error = $modelClass::find($this->systemErrorId);

        if (! $error) {
            // Pruned or otherwise gone before this job ran - nothing to notify about.
            return;
        }

        $analysis = null;

        if (config('error-monitor.ai_analysis_enabled', true)) {
            try {
                $analysis = $analyzer->analyze($error);
            } catch (Throwable $e) {
                Log::error('AnalyzeAndNotifySystemErrorJob: analysis failed', ['error' => $e->getMessage()]);
            }
        }

        $notifier->notify($error, $analysis);
    }
}
