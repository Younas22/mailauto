<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warmup_plans', function (Blueprint $table) {
            $table->date('end_date')->nullable()->after('start_date');
            $table->unsignedSmallInteger('day1_emails')->default(10)->after('end_date');
            $table->unsignedTinyInteger('increase_factor')->default(2)->after('day1_emails');
        });
    }

    public function down(): void
    {
        Schema::table('warmup_plans', function (Blueprint $table) {
            $table->dropColumn(['end_date', 'day1_emails', 'increase_factor']);
        });
    }
};
