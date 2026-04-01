<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->string('integration_mode', 50)->default('front_controller')->after('name')->index();
            $table->string('target_mode', 20)->default('redirect')->after('integration_mode');
            $table->string('target_redirect_url')->nullable()->after('target_mode');
            $table->string('target_content_file')->nullable()->after('target_redirect_url');
            $table->string('bot_content_file')->nullable()->after('target_content_file');
        });

        Schema::table('campaigns', function (Blueprint $table) {
            if (Schema::hasColumn('campaigns', 'domain')) {
                $table->dropColumn('domain');
            }
            if (Schema::hasColumn('campaigns', 'traffic_source')) {
                $table->dropColumn('traffic_source');
            }
            if (Schema::hasColumn('campaigns', 'target_url')) {
                $table->dropColumn('target_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->string('domain')->nullable()->index();
            $table->string('traffic_source')->nullable();
            $table->string('target_url')->nullable();
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn([
                'integration_mode',
                'target_mode',
                'target_redirect_url',
                'target_content_file',
                'bot_content_file',
            ]);
        });
    }
};
