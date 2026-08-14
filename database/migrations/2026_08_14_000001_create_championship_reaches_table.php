<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('championship_reaches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->unique()->constrained()->onDelete('cascade');
            $table->unsignedInteger('african_countries')->default(0);
            $table->unsignedInteger('continents')->default(0);
            $table->unsignedInteger('golf_clubs')->default(0);
            $table->unsignedInteger('official_sponsors')->default(0);
            $table->decimal('pro_purse', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('championship_reaches');
    }
};
