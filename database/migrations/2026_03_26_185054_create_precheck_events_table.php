<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('precheck_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->string('request_id')->nullable()->index();
            $table->string('integration_mode')->default('php_include')->index();
            $table->string('ip', 64)->nullable()->index();
            $table->string('host')->nullable()->index();
            $table->string('path')->nullable();
            $table->text('query')->nullable();
            $table->text('ua')->nullable();
            $table->string('accept_language')->nullable();
            $table->integer('risk_score')->default(0)->index();
            $table->string('verdict', 16)->default('allow')->index();
            $table->json('reason_codes_json')->nullable();
            $table->json('server_context_json')->nullable();
            $table->json('client_context_json')->nullable();
            $table->json('traffic_context_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('precheck_events');
    }
};
