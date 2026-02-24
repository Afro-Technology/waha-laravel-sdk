<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waha_hosts', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('base_url');
            $table->string('api_key_header')->default('X-Api-Key');
            $table->text('admin_api_key')->nullable();
            $table->string('default_session')->default('default');
            $table->text('webhook_secret')->nullable();
            $table->string('mode')->default('admin_fallback');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waha_hosts');
    }
};
