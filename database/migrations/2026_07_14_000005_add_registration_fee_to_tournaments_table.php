<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
         Schema::table('tournaments', function (Blueprint $table) {
            $table->decimal('registration_fee', 10, 2)->default(50000)->after('prize_pool')->change();
        });
    }

    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn('registration_fee');
        });
    }
};
