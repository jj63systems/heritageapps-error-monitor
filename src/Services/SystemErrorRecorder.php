<?php

declare(strict_types=1);

namespace HeritageApps\ErrorMonitor\Services;

use HeritageApps\ErrorMonitor\Jobs\AnalyzeAndNotifySystemErrorJob;
use HeritageApps\ErrorMonitor\Support\ErrorFingerprint;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Models\Tenant;
use Throwable;

/**
 * Upsert a system_errors row for this exception and, if it's new or the
 * repeat-notification window has elapsed, dispatch the analysis/email job.
 *
 * Uses a Postgres ON CONFLICT upsert plus a conditional-claim UPDATE so
 * concurrent requests hitting the same bug can't double-count or double-email.
 */
final class SystemErrorRecorder
{
    public function record(Throwable $e, ?Request $request): void
    {
        if (! config('error-monitor.enabled', true)) {
            return;
        }

        $connection = $this->connectionName();
        $fingerprint = ErrorFingerprint::make($e);
        $now = now();

        $row = DB::connection($connection)->selectOne(
            <<<'SQL'
            INSERT INTO system_errors
                (fingerprint, exception_class, message, file, line, trace, url, http_method, tenant_id, user_id, occurrences, first_seen_at, last_seen_at, created_at, updated_at)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?)
            ON CONFLICT (fingerprint) DO UPDATE SET
                occurrences = system_errors.occurrences + 1,
                last_seen_at = excluded.last_seen_at,
                message = excluded.message,
                trace = excluded.trace,
                url = excluded.url,
                http_method = excluded.http_method,
                tenant_id = excluded.tenant_id,
                user_id = excluded.user_id,
                updated_at = excluded.updated_at
            RETURNING id, last_notified_at
            SQL,
            [
                $fingerprint,
                $e::class,
                mb_substr($e->getMessage(), 0, 2000),
                $e->getFile(),
                $e->getLine(),
                mb_substr($e->getTraceAsString(), 0, 20000),
                $request?->fullUrl(),
                $request?->method(),
                Tenant::current()?->getKey(),
                auth()->id(),
                $now,
                $now,
                $now,
                $now,
            ]
        );

        if (! $row) {
            return;
        }

        $this->maybeNotify($connection, (int) $row->id, $row->last_notified_at, $now);
    }

    private function maybeNotify(string $connection, int $id, ?string $lastNotifiedAt, Carbon $now): void
    {
        $thresholdMinutes = app(\HeritageApps\ErrorMonitor\Contracts\ErrorMonitorSettings::class)
            ->repeatThresholdMinutes();
        $cutoff = $now->copy()->subMinutes($thresholdMinutes);

        // Only the request that successfully claims this row (last_notified_at
        // still null or past the threshold) dispatches the job - a concurrent
        // request racing on the same fingerprint sees 0 rows affected and skips.
        $claimed = DB::connection($connection)->update(
            'UPDATE system_errors SET last_notified_at = ? WHERE id = ? AND (last_notified_at IS NULL OR last_notified_at < ?)',
            [$now, $id, $cutoff]
        );

        if ($claimed > 0) {
            AnalyzeAndNotifySystemErrorJob::dispatch($id);
        }
    }

    private function connectionName(): string
    {
        $modelClass = config('error-monitor.models.system_error');

        return (new $modelClass)->getConnectionName() ?? config('database.default');
    }
}
