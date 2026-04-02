<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('precheck_events', function (Blueprint $table) {
            $table->string('tenant_id')->nullable()->after('campaign_id')->index();
            $table->string('project_id')->nullable()->after('tenant_id')->index();
        });

        Schema::table('device_events', function (Blueprint $table) {
            $table->string('request_id')->nullable()->after('campaign_id')->index();
            $table->string('tenant_id')->nullable()->after('request_id')->index();
            $table->string('project_id')->nullable()->after('tenant_id')->index();
            $table->string('integration_mode')->nullable()->after('project_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('precheck_events', function (Blueprint $table) {
            $table->dropColumn(['tenant_id', 'project_id']);
        });

        Schema::table('device_events', function (Blueprint $table) {
            $table->dropColumn(['request_id', 'tenant_id', 'project_id', 'integration_mode']);
        });
    }
};
