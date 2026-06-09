<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extend the status enum to include bounced and complained.
        DB::statement("ALTER TABLE campaign_logs MODIFY status ENUM('sent','failed','bounced','complained') NOT NULL DEFAULT 'sent'");

        Schema::table('campaign_logs', function (Blueprint $table) {
            $table->enum('bounce_type', ['hard', 'soft'])->nullable()->after('status');
            $table->string('complaint_reason')->nullable()->after('bounce_type');
            $table->timestamp('event_at')->nullable()->after('complaint_reason');
        });
    }

    public function down(): void
    {
        Schema::table('campaign_logs', function (Blueprint $table) {
            $table->dropColumn(['bounce_type', 'complaint_reason', 'event_at']);
        });

        DB::statement("ALTER TABLE campaign_logs MODIFY status ENUM('sent','failed') NOT NULL DEFAULT 'sent'");
    }
};
