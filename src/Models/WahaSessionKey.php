<?php

namespace Vendor\Waha\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $host_key
 * @property string $session_name
 * @property string $api_key
 * @property Carbon|null $revoked_at
 */
class WahaSessionKey extends Model
{
    protected $table = 'waha_session_keys';

    protected $guarded = [];

    protected $casts = [
        'revoked_at' => 'datetime',
    ];
}
