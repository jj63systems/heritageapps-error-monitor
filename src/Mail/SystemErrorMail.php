<?php

declare(strict_types=1);

namespace HeritageApps\ErrorMonitor\Mail;

use HeritageApps\ErrorMonitor\Models\SystemError;
use Illuminate\Mail\Mailable;

/**
 * Sent synchronously from within AnalyzeAndNotifySystemErrorJob, which is
 * already NotTenantAware - never dispatch this Mailable via ->queue()
 * directly, as the resulting job isn't tenant-aware-safe on its own and
 * Spatie's tenant-aware queue middleware will reject it.
 */
class SystemErrorMail extends Mailable
{
    public function __construct(
        public readonly SystemError $error,
        public readonly ?string $analysis,
    ) {}

    public function build(): self
    {
        $subject = '['.config('app.name').'] '
            .($this->error->occurrences > 1 ? 'Repeated' : 'New')
            .' error: '.class_basename($this->error->exception_class);

        return $this
            ->subject($subject)
            ->view('error-monitor::mail.system-error', [
                'error' => $this->error,
                'analysis' => $this->analysis,
            ]);
    }
}
