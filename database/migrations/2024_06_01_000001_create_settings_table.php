<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->timestamps();
        });

        // Seed default settings
        $defaults = [
            // General
            ['key' => 'site_name',       'value' => 'MailAuto'],
            ['key' => 'site_tagline',    'value' => 'Email Automation Made Simple'],
            ['key' => 'admin_email',     'value' => ''],
            ['key' => 'timezone',        'value' => 'UTC'],
            ['key' => 'date_format',     'value' => 'Y-m-d'],
            ['key' => 'logo',            'value' => null],
            ['key' => 'favicon',         'value' => null],

            // Email (SMTP)
            ['key' => 'mail_driver',     'value' => 'smtp'],
            ['key' => 'smtp_host',       'value' => ''],
            ['key' => 'smtp_port',       'value' => '587'],
            ['key' => 'smtp_username',   'value' => ''],
            ['key' => 'smtp_password',   'value' => ''],
            ['key' => 'smtp_encryption', 'value' => 'tls'],
            ['key' => 'mail_from_email', 'value' => ''],
            ['key' => 'mail_from_name',  'value' => 'MailAuto'],

            // Amazon SES
            ['key' => 'ses_access_key',      'value' => ''],
            ['key' => 'ses_secret_key',      'value' => ''],
            ['key' => 'ses_region',          'value' => 'us-east-1'],
            ['key' => 'ses_verified_domain', 'value' => ''],
            ['key' => 'ses_sender_email',    'value' => ''],

            // Campaign
            ['key' => 'campaign_delay',             'value' => '5'],
            ['key' => 'campaign_daily_limit',       'value' => '500'],
            ['key' => 'campaign_max_per_campaign',  'value' => '10000'],
            ['key' => 'campaign_random_rotation',   'value' => '0'],
            ['key' => 'campaign_retry_failed',      'value' => '1'],

            // Logs
            ['key' => 'log_enabled',         'value' => '1'],
            ['key' => 'log_retention_days',  'value' => '30'],
            ['key' => 'log_auto_delete',     'value' => '1'],

            // Security
            ['key' => 'enable_2fa',         'value' => '0'],
        ];

        foreach ($defaults as $row) {
            DB::table('settings')->insert(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
