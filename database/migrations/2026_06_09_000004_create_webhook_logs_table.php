<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider');          // ses | resend
            $table->string('event_type');        // bounce, complaint, delivery, …
            $table->json('payload');             // raw body for debugging
            $table->boolean('processed')->default(false);
            $table->text('process_error')->nullable();
            $table->timestamp('received_at');
            $table->timestamps();

            $table->index(['provider', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};
