<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_logs', function (Blueprint $table) {
            $table->unsignedInteger('reply_count')->default(0)->after('click_count');
            $table->timestamp('replied_at')->nullable()->after('reply_count');
            $table->string('replied_by')->nullable()->after('replied_at');
        });
    }

    public function down(): void
    {
        Schema::table('campaign_logs', function (Blueprint $table) {
            $table->dropColumn(['reply_count', 'replied_at', 'replied_by']);
        });
    }
};
