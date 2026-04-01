<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE campaign_geo_targets MODIFY country_code VARCHAR(8) NOT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE campaign_geo_targets ALTER COLUMN country_code TYPE VARCHAR(8)');
        }
        // SQLite: length is not enforced; VARCHAR(2) still accepts ALL in practice.
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE campaign_geo_targets MODIFY country_code VARCHAR(2) NOT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE campaign_geo_targets ALTER COLUMN country_code TYPE VARCHAR(2)');
        }
    }
};
