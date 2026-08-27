<?php

declare(strict_types=1);

namespace HeritageApps\ErrorMonitor\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Base model for a captured system error. Uses the app's default database
 * connection — apps whose landlord data lives on a named connection should
 * publish config/error-monitor.php and point 'models.system_error' at their
 * own subclass with the appropriate connection trait applied.
 *
 * @property int $id
 * @property string $fingerprint
 * @property string $exception_class
 * @property string|null $message
 * @property string|null $file
 * @property int|null $line
 * @property string|null $trace
 * @property string|null $url
 * @property string|null $http_method
 * @property int|null $tenant_id
 * @property int|null $user_id
 * @property int $occurrences
 * @property \Illuminate\Support\Carbon $first_seen_at
 * @property \Illuminate\Support\Carbon $last_seen_at
 * @property \Illuminate\Support\Carbon|null $last_notified_at
 */
class SystemError extends Model
{
    protected $table = 'system_errors';

    protected $fillable = [
        'fingerprint',
        'exception_class',
        'message',
        'file',
        'line',
        'trace',
        'url',
        'http_method',
        'tenant_id',
        'user_id',
        'occurrences',
        'first_seen_at',
        'last_seen_at',
        'last_notified_at',
    ];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'last_notified_at' => 'datetime',
    ];
}
