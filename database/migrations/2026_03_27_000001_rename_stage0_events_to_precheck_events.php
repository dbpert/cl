<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('stage0_events') && !Schema::hasTable('precheck_events')) {
            Schema::rename('stage0_events', 'precheck_events');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('precheck_events') && !Schema::hasTable('stage0_events')) {
            Schema::rename('precheck_events', 'stage0_events');
        }
    }
};
