<?php

namespace AfroTechnology\Waha\Webhooks\Console;

use AfroTechnology\Waha\Webhooks\Models\WahaWebhookEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

final class PruneWebhookEventsCommand extends Command
{
    protected $signature = 'waha:webhooks:prune {--days=}';

    protected $description = 'Prune old WAHA webhook events from the database (retention).';

    public function handle(): int
    {
        $defaultDays = (int) config('waha.webhooks.store.retention_days', 7);
        $days = $this->option('days');

        $daysInt = is_string($days) && $days !== '' && ctype_digit($days)
            ? (int) $days
            : $defaultDays;

        if ($daysInt <= 0) {
            $this->error('Retention days must be > 0.');

            return self::FAILURE;
        }

        $cutoff = Carbon::now()->subDays($daysInt);

        $count = WahaWebhookEvent::query()
            ->where('created_at', '<', $cutoff)
            ->delete();

        $this->info("Deleted {$count} webhook events older than {$daysInt} days.");

        return self::SUCCESS;
    }
}
