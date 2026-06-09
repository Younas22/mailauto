<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warmup_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('domain');
            $table->string('provider')->default('ses'); // ses|resend
            $table->foreignId('email_group_id')->nullable()->constrained('email_groups')->nullOnDelete();
            $table->foreignId('email_template_id')->nullable()->constrained('email_templates')->nullOnDelete();
            $table->date('start_date');
            $table->unsignedSmallInteger('current_day')->default(1);
            $table->enum('status', ['pending', 'active', 'paused', 'completed', 'failed'])->default('pending');
            $table->unsignedInteger('daily_limit')->default(20);
            $table->decimal('max_bounce_rate', 5, 2)->default(5.00);    // % — pause threshold
            $table->decimal('max_complaint_rate', 5, 2)->default(0.10); // % — pause threshold
            $table->text('pause_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warmup_plans');
    }
};
