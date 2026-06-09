<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_logs', function (Blueprint $table) {
            $table->string('provider_message_id')->nullable()->after('provider')->index();
        });
    }

    public function down(): void
    {
        Schema::table('campaign_logs', function (Blueprint $table) {
            $table->dropIndex(['provider_message_id']);
            $table->dropColumn('provider_message_id');
        });
    }
};
