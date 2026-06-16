<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->foreignId('template_id')
                  ->nullable()
                  ->constrained('email_templates')
                  ->nullOnDelete()
                  ->after('email_group_id');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\EmailTemplate::class, 'template_id');
            $table->dropColumn('template_id');
        });
    }
};
