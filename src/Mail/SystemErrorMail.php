<?php

declare(strict_types=1);

namespace HeritageApps\ErrorMonitor\Mail;

use HeritageApps\ErrorMonitor\Models\SystemError;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SystemErrorMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

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
