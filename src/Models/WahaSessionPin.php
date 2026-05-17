<?php

namespace AfroTechnology\Waha\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $session_name
 * @property string $host_key
 * @property Carbon|null $last_seen_at
 */
class WahaSessionPin extends Model
{
    protected $table = 'waha_session_pins';

    protected $guarded = [];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];
}
