<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waha_session_keys', function (Blueprint $table) {
            $table->id();
            $table->string('host_key')->index();
            $table->string('session_name');
            $table->text('api_key');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->unique(['host_key', 'session_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waha_session_keys');
    }
};
