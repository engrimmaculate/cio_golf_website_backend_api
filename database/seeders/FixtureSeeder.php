<?php

namespace Database\Seeders;

use App\Models\Fixture;
use App\Models\Tournament;
use Illuminate\Database\Seeder;

class FixtureSeeder extends Seeder
{
    public function run(): void
    {
        $tournament = Tournament::where('status', 'published')
            ->orderBy('start_date', 'desc')
            ->first();

        if (!$tournament) {
            $this->command->warn('No published tournament found. Skipping fixture seeding.');
            return;
        }

        $fixtures = [
            ['date' => '2026-11-08', 'tee_off_time' => '08:00:00', 'group_name' => 'Children Championship'],
            ['date' => '2026-11-09', 'tee_off_time' => '08:00:00', 'group_name' => 'Caddies Championship'],
            ['date' => '2026-11-11', 'tee_off_time' => '08:00:00', 'group_name' => 'Seniors Championship (19-28)'],
            ['date' => '2026-11-12', 'tee_off_time' => '08:00:00', 'group_name' => 'Pro Round 1'],
            ['date' => '2026-11-13', 'tee_off_time' => '08:00:00', 'group_name' => 'Pro Round 2'],
            ['date' => '2026-11-13', 'tee_off_time' => '10:00:00', 'group_name' => 'Ladies Amateur'],
            ['date' => '2026-11-13', 'tee_off_time' => '18:00:00', 'group_name' => 'Cocktail'],
            ['date' => '2026-11-14', 'tee_off_time' => '08:00:00', 'group_name' => 'Pro Round 3 + Amateur Event'],
            ['date' => '2026-11-15', 'tee_off_time' => '08:00:00', 'group_name' => 'Pro Final + Amateur Awards'],
        ];

        $count = 0;
        foreach ($fixtures as $data) {
            Fixture::updateOrCreate(
                [
                    'tournament_id' => $tournament->id,
                    'date' => $data['date'],
                    'group_name' => $data['group_name'],
                ],
                [
                    'tee_off_time' => $data['tee_off_time'],
                    'course' => $tournament->venue,
                    'hole' => 18,
                    'status' => 'scheduled',
                ]
            );
            $count++;
        }

        $this->command->info("{$count} fixtures seeded for \"{$tournament->name}\".");
    }
}
