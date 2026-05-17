<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waha_webhook_events', function (Blueprint $table): void {
            $table->bigIncrements('id');

            $table->string('host_key', 64)->index();
            $table->string('event_name', 128)->nullable()->index();

            $table->string('request_id', 128)->nullable()->index();
            $table->unsignedBigInteger('timestamp_ms')->nullable()->index();

            $table->string('hmac_algo', 32)->nullable();
            $table->boolean('hmac_present')->default(false);

            // Useful for integrity / dedup investigations without storing full raw payload.
            $table->char('raw_sha256', 64)->nullable()->index();

            // Structured payload; can be null if JSON parse fails.
            $table->json('payload')->nullable();

            // Optional raw body (may be large). Keep it nullable and size-limited in code.
            $table->longText('raw_body')->nullable();

            $table->string('status', 32)->default('queued')->index();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('processed_at')->nullable()->index();
            $table->timestamp('failed_at')->nullable()->index();
            $table->text('last_error')->nullable();

            $table->timestamps();

            // Optional “hard” uniqueness: request_id may collide across hosts, so use composite.
            $table->unique(['host_key', 'request_id'], 'waha_webhook_events_host_request_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waha_webhook_events');
    }
};
