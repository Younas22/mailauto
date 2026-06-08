<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!DB::table('settings')->where('key', 'active_email_provider')->exists()) {
            DB::table('settings')->insert([
                'key'        => 'active_email_provider',
                'value'      => 'ses',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'active_email_provider')->delete();
    }
};
