<?php

declare(strict_types=1);

namespace HeritageApps\ErrorMonitor\Services;

use HeritageApps\ErrorMonitor\Contracts\ErrorMonitorSettings;
use HeritageApps\ErrorMonitor\Contracts\ErrorNotifier;
use HeritageApps\ErrorMonitor\Mail\SystemErrorMail;
use HeritageApps\ErrorMonitor\Models\SystemError;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Default notifier - queues the package's own SystemErrorMail to the
 * configured recipients. Bound unless the host app registers its own
 * ErrorNotifier (e.g. to send through a branded Mailable).
 */
final class MailErrorNotifier implements ErrorNotifier
{
    public function __construct(
        private readonly ErrorMonitorSettings $settings,
    ) {}

    public function notify(SystemError $error, ?string $analysis): void
    {
        $recipients = $this->settings->recipients();

        if (empty($recipients)) {
            Log::warning('MailErrorNotifier: no notification recipients configured, skipping alert', [
                'fingerprint' => $error->fingerprint,
            ]);

            return;
        }

        Mail::to($recipients)->queue(new SystemErrorMail($error, $analysis));
    }
}
