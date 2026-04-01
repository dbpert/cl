<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_geo_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->string('country_code', 2);
            $table->string('country_name', 120);
            $table->timestamps();

            $table->unique(['campaign_id', 'country_code']);
            $table->index('country_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_geo_targets');
    }
};
