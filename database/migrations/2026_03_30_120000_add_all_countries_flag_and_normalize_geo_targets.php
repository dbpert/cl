<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('campaigns', 'all_countries')) {
            Schema::table('campaigns', function (Blueprint $table) {
                $table->boolean('all_countries')->default(false)->after('bot_content_file');
            });
        }

        if (Schema::hasTable('campaign_geo_targets') && Schema::hasColumn('campaign_geo_targets', 'country_code')) {
            DB::table('campaigns')
                ->whereIn('id', function ($query) {
                    $query->select('campaign_id')
                        ->from('campaign_geo_targets')
                        ->whereRaw('UPPER(country_code) = ?', ['ALL']);
                })
                ->update(['all_countries' => true]);

            DB::table('campaign_geo_targets')
                ->whereRaw('UPPER(country_code) = ?', ['ALL'])
                ->delete();

            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE campaign_geo_targets MODIFY country_code VARCHAR(2) NOT NULL');
            } elseif ($driver === 'pgsql') {
                DB::statement('ALTER TABLE campaign_geo_targets ALTER COLUMN country_code TYPE VARCHAR(2)');
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('campaign_geo_targets') && Schema::hasColumn('campaign_geo_targets', 'country_code')) {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE campaign_geo_targets MODIFY country_code VARCHAR(8) NOT NULL');
            } elseif ($driver === 'pgsql') {
                DB::statement('ALTER TABLE campaign_geo_targets ALTER COLUMN country_code TYPE VARCHAR(8)');
            }
        }

        if (Schema::hasColumn('campaigns', 'all_countries')) {
            Schema::table('campaigns', function (Blueprint $table) {
                $table->dropColumn('all_countries');
            });
        }
    }
};
