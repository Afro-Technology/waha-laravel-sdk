<?php

namespace Vendor\Waha\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $key
 * @property string $base_url
 * @property string|null $api_key_header
 * @property string|null $admin_api_key
 * @property string|null $default_session
 * @property string|null $webhook_secret
 * @property string $mode
 * @property bool $is_active
 */
class WahaHost extends Model
{
    protected $table = 'waha_hosts';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
