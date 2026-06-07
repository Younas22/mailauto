<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('email_lists', function (Blueprint $table) {
            $table->string('unsubscribe_token', 64)->nullable()->unique()->after('status');
            $table->timestamp('unsubscribed_at')->nullable()->after('unsubscribe_token');
        });
    }

    public function down(): void
    {
        Schema::table('email_lists', function (Blueprint $table) {
            $table->dropColumn(['unsubscribe_token', 'unsubscribed_at']);
        });
    }
};
