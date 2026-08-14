<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->float('handicap')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('profile_photo')->nullable()->change();
            $table->text('experience')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->float('handicap')->change();
            $table->string('city')->change();
            $table->string('profile_photo')->change();
            $table->text('experience')->change();
        });
    }
};
