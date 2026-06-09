<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_lists', function (Blueprint $table) {
            $table->boolean('is_do_not_mail')->default(false)->after('unsubscribed_at');
            $table->timestamp('bounced_at')->nullable()->after('is_do_not_mail');
            $table->timestamp('complained_at')->nullable()->after('bounced_at');
        });
    }

    public function down(): void
    {
        Schema::table('email_lists', function (Blueprint $table) {
            $table->dropColumn(['is_do_not_mail', 'bounced_at', 'complained_at']);
        });
    }
};
