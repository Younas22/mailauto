<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_logs', function (Blueprint $table) {
            $table->string('tracking_token', 64)->nullable()->unique()->after('sent_at');
            $table->unsignedInteger('open_count')->default(0)->after('tracking_token');
            $table->unsignedInteger('click_count')->default(0)->after('open_count');
        });
    }

    public function down(): void
    {
        Schema::table('campaign_logs', function (Blueprint $table) {
            $table->dropColumn(['tracking_token', 'open_count', 'click_count']);
        });
    }
};
