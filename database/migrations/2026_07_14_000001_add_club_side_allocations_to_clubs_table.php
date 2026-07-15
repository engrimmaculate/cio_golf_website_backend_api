<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->integer('m1_18')->default(0);
            $table->integer('m20_28')->default(0);
            $table->integer('m_snr')->default(0);
            $table->integer('l1_20')->default(0);
            $table->integer('l21_28')->default(0);
            $table->integer('l_snr')->default(0);
            $table->string('edition')->default('7th');
        });
    }

    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->dropColumn([
                'm1_18', 'm20_28', 'm_snr', 'l1_20', 'l21_28', 'l_snr', 'edition',
            ]);
        });
    }
};
