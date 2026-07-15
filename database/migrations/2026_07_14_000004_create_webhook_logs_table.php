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
            $table->string('event');          // e.g. charge.success
            $table->string('reference')->nullable();
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->json('payload');           // full webhook body
            $table->string('status');          // received, processed, failed, skipped
            $table->text('message')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('is_sample')->default(false);  // true = logged for periodic sampling
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};
