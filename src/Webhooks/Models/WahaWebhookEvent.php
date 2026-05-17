<?php

namespace AfroTechnology\Waha\Webhooks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $status
 * @property int $attempts
 * @property Carbon|null $processed_at
 * @property Carbon|null $failed_at
 * @property string|null $last_error
 */
final class WahaWebhookEvent extends Model
{
    public const STATUS_QUEUED = 'queued';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_PROCESSED = 'processed';

    public const STATUS_FAILED = 'failed';

    protected $table = 'waha_webhook_events';

    protected $fillable = [
        'host_key',
        'event_name',
        'request_id',
        'timestamp_ms',
        'hmac_algo',
        'hmac_present',
        'raw_sha256',
        'payload',
        'raw_body',
        'status',
        'attempts',
        'processed_at',
        'failed_at',
        'last_error',
    ];

    protected $casts = [
        'timestamp_ms' => 'integer',
        'hmac_present' => 'boolean',
        'payload' => 'array',
        'attempts' => 'integer',
        'processed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];
}
