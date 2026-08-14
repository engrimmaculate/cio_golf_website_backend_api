<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sponsors', function (Blueprint $table) {
            $table->enum('type', ['sponsor', 'partner'])->default('partner')->after('name');
            $table->string('address')->nullable()->after('description');
            $table->string('phone')->nullable()->after('address');
            $table->string('email')->nullable()->after('phone');
            $table->integer('sort_order')->default(0)->after('featured');
            $table->boolean('is_active')->default(true)->after('sort_order');
        });

        Schema::table('sponsors', function (Blueprint $table) {
            $table->string('tier')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sponsors', function (Blueprint $table) {
            $table->dropColumn(['type', 'address', 'phone', 'email', 'sort_order', 'is_active']);
            $table->enum('tier', ['title', 'platinum', 'gold', 'silver', 'strategic'])->nullable()->change();
        });
    }
};
