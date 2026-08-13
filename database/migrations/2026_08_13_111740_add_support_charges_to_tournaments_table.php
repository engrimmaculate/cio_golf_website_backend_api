<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            if (!Schema::hasColumn('tournaments', 'support_charges')) {
                $table->decimal('support_charges', 10, 2)->default(500.00)->after('registration_fee');
            }

            $table->unsignedInteger('max_tournament_player_expected')->nullable()->after('support_charges');
            $table->unsignedInteger('max_male_expected_per_club')->nullable()->after('max_tournament_player_expected');
            $table->unsignedInteger('max_female_expected_per_club')->nullable()->after('max_male_expected_per_club');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $columnsToDrop = [
                'max_tournament_player_expected',
                'max_male_expected_per_club',
                'max_female_expected_per_club',
            ];

            if (Schema::hasColumn('tournaments', 'support_charges')) {
                $columnsToDrop[] = 'support_charges';
            }

            $table->dropColumn($columnsToDrop);
        });
    }
};