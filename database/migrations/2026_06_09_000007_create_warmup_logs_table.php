<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warmup_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warmup_plan_id')->constrained('warmup_plans')->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('daily_limit')->default(0);
            $table->unsignedInteger('emails_sent')->default(0);
            $table->unsignedInteger('emails_failed')->default(0);
            $table->unsignedInteger('bounce_count')->default(0);
            $table->unsignedInteger('complaint_count')->default(0);
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['warmup_plan_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warmup_logs');
    }
};
