<?php

namespace Database\Seeders;

use App\Models\ChampionshipReach;
use App\Models\Tournament;
use Illuminate\Database\Seeder;

class ChampionshipReachSeeder extends Seeder
{
    public function run(): void
    {
        $reaches = [
            1 => ['african_countries' => 2, 'continents' => 1, 'golf_clubs' => 10, 'official_sponsors' => 1, 'pro_purse' => 5000000],
            2 => ['african_countries' => 3, 'continents' => 1, 'golf_clubs' => 15, 'official_sponsors' => 1, 'pro_purse' => 10000000],
            3 => ['african_countries' => 4, 'continents' => 2, 'golf_clubs' => 20, 'official_sponsors' => 2, 'pro_purse' => 15000000],
            4 => ['african_countries' => 5, 'continents' => 2, 'golf_clubs' => 25, 'official_sponsors' => 2, 'pro_purse' => 25000000],
            5 => ['african_countries' => 6, 'continents' => 3, 'golf_clubs' => 30, 'official_sponsors' => 3, 'pro_purse' => 50000000],
            6 => ['african_countries' => 8, 'continents' => 3, 'golf_clubs' => 35, 'official_sponsors' => 3, 'pro_purse' => 75000000],
            7 => ['african_countries' => 6, 'continents' => 3, 'golf_clubs' => 40, 'official_sponsors' => 2, 'pro_purse' => 100000000],
        ];

        $count = 0;
        foreach (Tournament::orderBy('id')->get() as $index => $tournament) {
            $edition = $tournament->edition_number ?? ($index + 1);
            $data = $reaches[$edition] ?? ['african_countries' => 0, 'continents' => 0, 'golf_clubs' => 0, 'official_sponsors' => 0, 'pro_purse' => $tournament->prize_pool ?? 0];

            ChampionshipReach::updateOrCreate(
                ['tournament_id' => $tournament->id],
                $data
            );
            $count++;
        }

        $this->command->info("{$count} championship reach records seeded.");
    }
}
