<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['player', 'committee', 'admin'])->default('player')->after('password');
            $table->string('nationality')->nullable()->after('role');
            $table->string('country')->nullable()->after('nationality');
            $table->string('phone')->nullable()->after('country');
            $table->string('avatar')->nullable()->after('phone');
            $table->float('handicap')->nullable()->after('avatar');
            $table->string('golf_club')->nullable()->after('handicap');
            $table->integer('ranking')->nullable()->after('golf_club');
            $table->string('tournament_experience')->nullable()->after('ranking');
            $table->string('handicap_certificate')->nullable()->after('tournament_experience');
            $table->string('id_document')->nullable()->after('handicap_certificate');
            $table->string('passport_photo')->nullable()->after('id_document');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role', 'nationality', 'country', 'phone', 'avatar',
                'handicap', 'golf_club', 'ranking', 'tournament_experience',
                'handicap_certificate', 'id_document', 'passport_photo'
            ]);
        });
    }
};
