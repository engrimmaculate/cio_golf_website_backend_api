<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('venue');
            $table->string('host_country');
            $table->date('start_date');
            $table->date('end_date');
            $table->date('registration_deadline');
            $table->integer('total_slots');
            $table->integer('available_slots');
            $table->decimal('prize_pool', 12, 2)->default(0);
            $table->enum('status', ['draft', 'published', 'ongoing', 'completed'])->default('draft');
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};
