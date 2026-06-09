<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domain_health_checks', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->unique();
            $table->enum('spf_status',   ['valid', 'missing', 'invalid'])->default('missing');
            $table->enum('dkim_status',  ['valid', 'missing', 'invalid'])->default('missing');
            $table->enum('dmarc_status', ['valid', 'missing', 'invalid'])->default('missing');
            $table->text('spf_record')->nullable();
            $table->text('dkim_record')->nullable();
            $table->text('dmarc_record')->nullable();
            $table->string('dkim_selector')->nullable();
            $table->timestamp('checked_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_health_checks');
    }
};
